<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PipeCounterController extends Controller
{
    /**
     * Receive a base64-encoded photo and ask Gemini Vision to count the pipes.
     */
    public function count(Request $request)
    {
        $request->validate([
            'image' => 'required|string', // base64 data URI
        ]);

        $apiKey = config('services.gemini.api_key');

        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'error'   => 'GEMINI_API_KEY belum dikonfigurasi.',
            ], 500);
        }

        // Strip the data URI prefix  ("data:image/jpeg;base64,...")
        $imageData = $request->input('image');
        $base64    = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
        $mimeType  = 'image/jpeg';

        if (preg_match('/^data:(image\/\w+);base64,/', $imageData, $m)) {
            $mimeType = $m[1];
        }

        // Build Gemini API request
        $prompt = <<<PROMPT
Kamu adalah seorang penghitung pipa baja profesional di gudang pabrik.

Lihat foto ini dengan saksama. Foto ini menunjukkan tumpukan/susunan pipa baja yang terlepas dari bundel (pipa lepas/loose pipes).

Tugas kamu:
1. Hitung dengan teliti jumlah UJUNG pipa yang terlihat di foto (penampang lingkaran).
2. Perhatikan pipa yang saling tumpuk — hitung semua yang terlihat ujungnya.
3. Jika ada pipa yang sebagian tersembunyi di belakang pipa lain, estimasikan jumlahnya.

PENTING: Jawab HANYA dalam format JSON berikut, tanpa teks tambahan:
{"count": <angka>, "confidence": "<high/medium/low>", "notes": "<catatan singkat>"}

Contoh jawaban:
{"count": 15, "confidence": "high", "notes": "15 ujung pipa terlihat jelas dari penampang depan"}
PROMPT;

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                        [
                            'inline_data' => [
                                'mime_type' => $mimeType,
                                'data'      => $base64,
                            ],
                        ],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature'     => 0.1,
                'maxOutputTokens' => 256,
            ],
        ];

        // Auto-detect key format: standard (AIzaSy) vs auth (AQ.)
        $isStandardKey = str_starts_with($apiKey, 'AIzaSy');
        $baseUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent";
        $url = $isStandardKey ? ($baseUrl . '?key=' . $apiKey) : $baseUrl;

        $headers = ['Content-Type: application/json'];
        if (!$isStandardKey) {
            $headers[] = 'x-goog-api-key: ' . $apiKey;
        }

        try {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_HTTPHEADER     => $headers,
                CURLOPT_POSTFIELDS     => json_encode($payload),
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                Log::error('Gemini cURL error: ' . $curlError);
                return response()->json([
                    'success' => false,
                    'error'   => 'Gagal menghubungi server AI: ' . $curlError,
                ], 500);
            }

            if ($httpCode !== 200) {
                Log::error('Gemini API error: HTTP ' . $httpCode . ' — ' . $response);

                // If rate limited on primary model, try fallback model
                if ($httpCode === 429) {
                    Log::info('Trying fallback model gemini-2.0-flash-lite...');
                    $fallbackUrl = str_replace('gemini-2.0-flash', 'gemini-2.0-flash-lite', $url);
                    $ch2 = curl_init($fallbackUrl);
                    curl_setopt_array($ch2, [
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_POST           => true,
                        CURLOPT_HTTPHEADER     => $headers,
                        CURLOPT_POSTFIELDS     => json_encode($payload),
                        CURLOPT_TIMEOUT        => 30,
                        CURLOPT_SSL_VERIFYPEER => false,
                    ]);
                    $response = curl_exec($ch2);
                    $httpCode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);

                    if ($httpCode === 200) {
                        // Fallback succeeded, continue processing below
                        goto parseResponse;
                    }

                    return response()->json([
                        'success' => false,
                        'error'   => 'Kuota API habis. Coba lagi dalam beberapa menit.',
                    ], 429);
                }

                return response()->json([
                    'success' => false,
                    'error'   => 'API Error (HTTP ' . $httpCode . '). Periksa API key.',
                ], $httpCode >= 400 ? $httpCode : 500);
            }

            parseResponse:
            $result = json_decode($response, true);

            // Extract text from Gemini response
            $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

            // Try to parse JSON from the response
            // Strip markdown code fences if present
            $text = preg_replace('/```json\s*/i', '', $text);
            $text = preg_replace('/```\s*/', '', $text);
            $text = trim($text);

            $parsed = json_decode($text, true);

            if ($parsed && isset($parsed['count'])) {
                return response()->json([
                    'success'    => true,
                    'count'      => (int) $parsed['count'],
                    'confidence' => $parsed['confidence'] ?? 'medium',
                    'notes'      => $parsed['notes'] ?? '',
                ]);
            }

            // Fallback: try to extract a number from the text
            if (preg_match('/(\d+)/', $text, $matches)) {
                return response()->json([
                    'success'    => true,
                    'count'      => (int) $matches[1],
                    'confidence' => 'low',
                    'notes'      => 'Parsed from raw text: ' . substr($text, 0, 100),
                ]);
            }

            return response()->json([
                'success' => false,
                'error'   => 'AI tidak bisa menghitung pipa dari foto ini. Coba foto dari sudut lain.',
                'raw'     => substr($text, 0, 200),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Gemini exception: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error'   => 'Kesalahan sistem: ' . $e->getMessage(),
            ], 500);
        }
    }
}
