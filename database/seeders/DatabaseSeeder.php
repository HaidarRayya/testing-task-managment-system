<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Enums\UserPermission;
use App\Enums\UserRole;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use App\Services\ManagePermisionService;
use Database\Factories\TaskFactory;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $role = Role::create([
            'name' => UserRole::ADMIN->value,
        ]);
        User::create([
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => 'admin123123!!',
            'role_id' => $role->id,
        ]);
        $permissions = ManagePermisionService::arrayPermissions();
        $permissions = $permissions[UserRole::ADMIN->value];
        foreach ($permissions as $permission) {
            $permission = Permission::create([
                'name' =>  $permission,
            ]);
            $role->permissions()->attach($permission->id);
        }
        // Task::factory(5000)->create();
    }
}
