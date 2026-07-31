<?php
$data = file_get_contents('https://web.archive.org/web/20230601000000im_/https://www.spindo.co.id/assets/images/spindo-logo.png');
if ($data) {
    file_put_contents('public/images/spindo-logo.png', $data);
    echo "Downloaded " . strlen($data) . " bytes from archive.org\n";
    if (strpos($data, "\x89PNG") === 0) {
        echo "Valid PNG file.\n";
        // Convert to base64
        $b64 = base64_encode($data);
        file_put_contents('public/images/spindo-logo.b64', $b64);
        echo "Base64 saved.\n";
    }
}
