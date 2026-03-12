<?php

$localPath = 'C:/Users/kaygo/Desktop/Unifiedtransform';
$livePath = 'C:/Users/kaygo/Desktop/livecpanel/schools/mienebischool'; // Adjust this if the root is different

$exclude = ['.git', '.idea', '.vscode', 'vendor', 'node_modules', 'storage', 'bootstrap/cache'];

function scan($dir, $basePath, &$results, $exclude)
{
    if (!is_dir($dir))
        return;

    $files = scandir($dir);

    foreach ($files as $file) {
        if ($file === '.' || $file === '..')
            continue;
        if (in_array($file, $exclude))
            continue;

        $path = $dir . '/' . $file;
        $relativePath = str_replace($basePath . '/', '', $path);

        if (is_dir($path)) {
            scan($path, $basePath, $results, $exclude);
        } else {
            $results[$relativePath] = [
                'size' => filesize($path),
                'mtime' => filemtime($path)
            ];
        }
    }
}

echo "Scanning Local: $localPath...\n";
$localFiles = [];
scan($localPath, $localPath, $localFiles, $exclude);

echo "Scanning Live: $livePath...\n";
$liveFiles = [];
scan($livePath, $livePath, $liveFiles, $exclude);

$missingInLive = [];
$missingInLocal = [];
$different = [];

foreach ($localFiles as $file => $info) {
    if (!isset($liveFiles[$file])) {
        $missingInLive[] = $file;
    } else {
        $liveInfo = $liveFiles[$file];
        if ($info['size'] !== $liveInfo['size']) {
            $different[] = $file . " (Size: Local={$info['size']} vs Live={$liveInfo['size']})";
        }
    }
}

foreach ($liveFiles as $file => $info) {
    if (!isset($localFiles[$file])) {
        $missingInLocal[] = $file;
    }
}

echo "\n--- SUMMARY ---\n";
echo "Files in Local but MISSING in Live: " . count($missingInLive) . "\n";
echo "Files in Live but MISSING in Local: " . count($missingInLocal) . "\n";
echo "Files Modified (Size mismatch): " . count($different) . "\n";

echo "\n--- MISSING IN LIVE (Top 50) ---\n";
foreach (array_slice($missingInLive, 0, 50) as $f)
    echo "[MISSING] $f\n";

echo "\n--- DIFFERENT (Top 50) ---\n";
foreach (array_slice($different, 0, 50) as $f)
    echo "[DIFF] $f\n";
