<?php
// debug.php - Server Diagnostics

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "<h1>Laravel Diagnostics</h1>";

// 1. Check .env
echo "<h3>1. Environment</h3>";
echo "APP_URL: " . env('APP_URL') . "<br>";
echo "APP_ENV: " . env('APP_ENV') . "<br>";
echo "APP_KEY: " . (env('APP_KEY') ? "✅ Set" : "❌ MISSING") . "<br>";

// 2. Check Permissions
echo "<h3>2. Storage Permissions</h3>";
$storage = __DIR__ . '/../storage';
echo "Storage writable: " . (is_writable($storage) ? "✅ Yes" : "❌ NO") . "<br>";
echo "Logs writable: " . (is_writable($storage . '/logs') ? "✅ Yes" : "❌ NO") . "<br>";

// 3. Last 10 Log Lines
echo "<h3>3. Recent Errors (storage/logs/laravel.log)</h3>";
$logFile = $storage . '/logs/laravel.log';
if (file_exists($logFile)) {
    $lines = array_slice(file($logFile), -20);
    echo "<pre style='background:#eee; padding:10px;'>" . implode("", $lines) . "</pre>";
} else {
    echo "❌ Log file not found at " . $logFile;
}

// 4. DB Status
echo "<h3>4. Database Connection</h3>";
try {
    \Illuminate\Support\Facades\DB::connection()->getPdo();
    echo "✅ Connected to: " . \Illuminate\Support\Facades\DB::connection()->getDatabaseName();
} catch (\Exception $e) {
    echo "❌ Connection Failed: " . $e->getMessage();
}
