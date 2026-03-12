<?php
/**
 * Unifiedtransform Storage Link Utility
 * Visit yourdomain.com/link.php to run this once.
 */

// We need to link /mienebi_app/storage/app/public to /public_html/storage
$target = __DIR__ . '/../mienebi_app/storage/app/public';
$link = __DIR__ . '/storage';

if (file_exists($link)) {
    echo "Storage link already exists.<br>";
} else {
    if (symlink($target, $link)) {
        echo "Successfully created the storage symlink!<br>";
    } else {
        echo "Failed to create storage symlink. Please check your folder permissions.<br>";
    }
}

echo "<b>Note:</b> Please delete this file (link.php) from your public_html after running it for security.";
