<?php
// Fix users roles script
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;

$users = User::all();
foreach ($users as $user) {
    if ($user->role) {
        // Map column role to Spatie role
        $roleName = null;
        switch (strtolower($user->role)) {
            case 'admin':
                $roleName = 'Admin';
                break;
            case 'accountant':
                $roleName = 'Accountant';
                break;
            case 'teacher':
                $roleName = 'Teacher';
                break;
            case 'student':
                $roleName = 'Student';
                break;
            case 'parent':
                $roleName = 'Parent';
                break;
            case 'staff':
                $roleName = 'Normal Staff';
                break;
        }

        if ($roleName) {
            echo "Syncing role $roleName for user {$user->email}\n";
            $user->syncRoles([$roleName]);
        }
    }
}
echo "Done syncing roles.\n";
