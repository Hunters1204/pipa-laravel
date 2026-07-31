<?php
$ch = curl_init('https://www.spindo.co.id/assets/images/spindo-logo.png');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$data = curl_exec($ch);
curl_close($ch);

if ($data) {
    file_put_contents('public/images/spindo-logo.png', $data);
    echo "Downloaded: " . strlen($data) . " bytes\n";
    // Check if it's a valid PNG
    if (strpos($data, "\x89PNG") === 0) {
        echo "Valid PNG file.\n";
    } else {
        echo "Not a valid PNG file! It might be HTML or blocked.\n";
        echo substr($data, 0, 100);
    }
}
