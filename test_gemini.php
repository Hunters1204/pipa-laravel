<?php
$apiKey = 'AQ.Ab8RN6I-k1P5JrMM0qjIFrcMejH1esBNKHe5J_RPkEzcErc-JQ';
$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.6-flash:generateContent';
$payload = json_encode(['contents' => [['parts' => [['text' => 'say hello in indonesian']]]]]);
$ch = curl_init($url);
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'x-goog-api-key: ' . $apiKey], CURLOPT_POSTFIELDS => $payload, CURLOPT_TIMEOUT => 15, CURLOPT_SSL_VERIFYPEER => false]);
$r = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "Method (1) HTTP $code\n";
echo substr($r, 0, 500) . "\n";
