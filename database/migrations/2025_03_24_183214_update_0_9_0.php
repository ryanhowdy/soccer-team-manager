<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        Permission::create(['name' => 'edit things']); // games, competitions, locations, etc.
        Permission::create(['name' => 'update rosters']);
        Permission::create(['name' => 'update players']);
        Permission::create(['name' => 'update competition place']);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create roles and assign created permissions

        // role: Admin
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo('edit things');
        $adminRole->givePermissionTo('update rosters');
        $adminRole->givePermissionTo('update players');
        $adminRole->givePermissionTo('update competition place');

        // role: Manager
        $mgrRole = Role::create(['name' => 'manager']);
        $mgrRole->givePermissionTo('update rosters');
        $mgrRole->givePermissionTo('update players');
        $mgrRole->givePermissionTo('update competition place');

        // give first user admin role
        $user = User::first();
        if ($user) {
            $user->assignRole($adminRole);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
