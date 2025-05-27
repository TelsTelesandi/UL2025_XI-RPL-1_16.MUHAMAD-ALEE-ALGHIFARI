<?php
$image_path = __DIR__ . '/assets/images/logo.png';
if (file_exists($image_path)) {
    $type = mime_content_type($image_path);
    header('Content-Type: ' . $type);
    readfile($image_path);
} else {
    echo "Image not found at: " . $image_path;
} 