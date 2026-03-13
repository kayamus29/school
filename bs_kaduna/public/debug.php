: " . (env('APP_KEY') ? "OK" : "MISSING") . "<br>";
echo "Storage Writable: " . (is_writable(__DIR__ . '/../storage') ? "YES" : "NO") . "<br>";
echo "Logs Writable: " . (is_writable(__DIR__ . '/../storage/logs') ? "YES" : "NO") . "<br>";

echo "<h3>Recent Errors:</h3>";
$log = __DIR__ . '/../storage/logs/laravel.log';
if (file_exists($log)) {
    $lines = array_slice(file($log), -15);
    echo "<pre>" . htmlspecialchars(implode("", $lines)) . "</pre>";
} else { echo "Log file not found."; }
?>1~
