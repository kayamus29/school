<?php
use Illuminate\Support\Facades\DB;
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$studentId = 2; // Justus
$wallet = DB::table('wallets')->where('student_id', $studentId)->first();
echo "Student ID: " . $studentId . "\n";
echo "Wallet Balance: " . ($wallet ? $wallet->balance : 'Not Found') . "\n";
