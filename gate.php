<?php
// gate.php - Admin Creation & Routing Fix

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

$app->make(Kernel::class)->bootstrap();

echo "<h2>Infrastructure Fix Gate</h2>";

try {
    // 1. Create Admin User
    $user = User::updateOrCreate(
        ['email' => 'admin@bestsolution.ng'],
        [
            'first_name' => 'Best Solution',
            'last_name' => 'Admin',
            'role' => 'Admin',
            'password' => Hash::make('password'),
            'status' => 'active',
            'gender' => 'male'
        ]
    );
    $user->syncRoles('Admin');
    echo "<p style='color:green'>✅ Admin User Created/Updated: <b>admin@bestsolution.ng</b> / <b>password</b></p>";

    // 2. Fix .htaccess (Self-Correction)
    $htaccessPath = __DIR__ . '/.htaccess';
    $htaccessContent = "DirectoryIndex index.html index.php
Options -Indexes +FollowSymLinks

RewriteEngine On

# Explicitly handle /login and other routes
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [L]

# php -- BEGIN cPanel-generated handler
<IfModule mime_module>
  AddHandler application/x-httpd-ea-php81 .php .php8 .phtml
</IfModule>
# php -- END cPanel-generated handler";

    file_put_content($htaccessPath, $htaccessContent);
    echo "<p style='color:green'>✅ .htaccess Routing Fixed.</p>";

    echo "<hr><p><b>Next Step:</b> Try visiting <a href='/login'>/login</a></p>";

} catch (\Exception $e) {
    echo "<p style='color:red'>❌ Error: " . $e->getMessage() . "</p>";
}
