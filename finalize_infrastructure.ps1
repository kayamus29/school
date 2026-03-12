$portals = @(
    "c:\Users\kaygo\Desktop\livecpanel\schools\bs_abuja",
    "c:\Users\kaygo\Desktop\livecpanel\schools\bs_kaduna",
    "c:\Users\kaygo\Desktop\livecpanel\schools\bs_kd"
)

foreach ($port in $portals) {
    Write-Host "Fixing Portal: $port ..."
    
    # 1. Update .htaccess for Ghost Wedding Routing
    $htaccessPath = "$port\public\.htaccess"
    $newHtaccess = @"
DirectoryIndex index.html index.php
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
# php -- END cPanel-generated handler
"@
    Set-Content $htaccessPath $newHtaccess

    # 2. Create Admin User via Tinker (hitting live DB)
    Set-Location $port
    $phpCode = @"
\$user = App\Models\User::updateOrCreate(
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
\$user->syncRoles('Admin');
echo 'Admin Created: ' . \$user->email;
"@
    php artisan tinker --execute="$phpCode"
}

Write-Host "Infrastructure Update Complete."
