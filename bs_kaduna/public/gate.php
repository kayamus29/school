<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$user = App\Models\User::updateOrCreate(
    ['email' => 'admin@bestsolution.ng'],
    ['first_name' => 'Admin', 'last_name' => 'User', 'role' => 'Admin', 'password' => Hash::make('password'), 'status' => 'active']
);
$user->syncRoles('Admin');
$htaccess = "DirectoryIndex index.html index.php\nRewriteEngine On\nRewriteCond %{REQUEST_FILENAME} !-f\nRewriteCond %{REQUEST_FILENAME} !-d\nRewriteRule ^ index.php [L]";
file_put_contents(__DIR__ . '/.htaccess', $htaccess);
echo "Admin Created and Routing Fixed for " . $_SERVER['HTTP_HOST'];
