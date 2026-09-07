<?php

$folder = __DIR__ . '/assets/images';

echo "<h2>Isi Folder Images</h2>";

echo "<p><strong>Folder yang dicek:</strong></p>";
echo "<p>$folder</p>";

if (!is_dir($folder)) {
    echo "<p style='color:red; font-weight:bold;'>✗ FOLDER TIDAK DITEMUKAN</p>";
    exit;
}

echo "<p style='color:green; font-weight:bold;'>✓ FOLDER DITEMUKAN</p>";

$files = scandir($folder);

echo "<h3>File yang ditemukan PHP:</h3>";

echo "<pre>";

foreach ($files as $file) {
    if ($file !== '.' && $file !== '..') {
        echo "[" . $file . "]\n";
    }
}

echo "</pre>";
?>