<?php
$logoPath = __DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'logo.png';

if (file_exists($logoPath)) {
    $imageInfo = getimagesize($logoPath);
    if ($imageInfo !== false) {
        header('Content-Type: ' . $imageInfo['mime']);
        readfile($logoPath);
    } else {
        echo "File exists but is not a valid image";
    }
} else {
    echo "Image not found at: " . $logoPath;
} 