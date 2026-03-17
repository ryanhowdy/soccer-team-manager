<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create new permissions
        Permission::create(['name' => 'edit others']); // create or update anything for another player

        // reset cache again after creating permission
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // assign to roles
        $adminRole = Role::findByName('admin');
        $adminRole->givePermissionTo('edit others');

        $mgrRole = Role::findByName('manager');
        $mgrRole->givePermissionTo('edit others');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
