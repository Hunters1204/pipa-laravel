<?php
$dir = 'C:\Users\dendi aprilio\.gemini\antigravity-ide\brain\e141e47e-e1bd-48be-a1bc-3e79ad5f9e13\.tempmediaStorage';
$files = glob($dir . '/*.png');
foreach ($files as $file) {
    $size = getimagesize($file);
    if ($size) {
        echo basename($file) . " -> " . $size[0] . "x" . $size[1] . " (Size: " . filesize($file) . ")\n";
    }
}
