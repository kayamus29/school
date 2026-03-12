<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $teacherRole = Role::findByName('Teacher');
        $createExpensesPermission = Permission::findByName('create expenses');
        $editUsersPermission = Permission::findByName('edit users');

        if ($teacherRole) {
            if ($createExpensesPermission) {
                $teacherRole->givePermissionTo($createExpensesPermission);
            }
            if ($editUsersPermission) {
                $teacherRole->givePermissionTo($editUsersPermission);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $teacherRole = Role::findByName('Teacher');
        $createExpensesPermission = Permission::findByName('create expenses');
        $editUsersPermission = Permission::findByName('edit users');

        if ($teacherRole) {
            if ($teacherRole->hasPermissionTo('create expenses')) {
                $teacherRole->revokePermissionTo($createExpensesPermission);
            }
            if ($teacherRole->hasPermissionTo('edit users')) {
                $teacherRole->revokePermissionTo($editUsersPermission);
            }
        }
    }
};