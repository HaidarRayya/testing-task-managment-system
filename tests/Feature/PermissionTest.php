<?php

namespace Tests\Feature;

use App\Models\Permission;
use Illuminate\Support\Str;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class PermissionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_get_all_permissions()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $permission = Permission::create([
            'name' => "start-work-task"
        ]);
        $response = $this->get('/api/admin/permissions',  [
            "Authorization" => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
    }
    public function test_valid_create_permission()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $response = $this->post('/api/admin/permissions', [
            'name' => "start-work-task"
        ], [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(201);
    }

    public function test_failed_create_permission()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $response = $this->post('/api/admin/permissions', [
            'name' => "start-work"
        ], [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(422);
    }

    public function test_valid_show_permission()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $permission = Permission::create([
            'name' => "start-work-task"
        ]);
        $response = $this->get("/api/admin/permissions/$permission->id", [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(200);
    }
    public function test_failed_show_permission()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $permission = Permission::create([
            'name' => "start-work-task"
        ]);
        $x = ($permission->id) + 1;
        $response = $this->get("/api/admin/permissions/$x", [
            "Authorization" => 'Bearer ' . $token,
        ]);

        $response->assertStatus(404);
    }

    public function test_valid_update_permission()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $permission = Permission::create([
            'name' => "start-work-task"
        ]);
        $response = $this->put("/api/admin/permissions/$permission->id", [
            "description" => Str::random(30)
        ], [
            "Authorization" => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
    }

    public function test_falied_update_permission()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $permission = Permission::create([
            'name' => "start-work-task"
        ]);
        $response = $this->put("/api/admin/permissions/$permission->id", [
            "name" => "start-work-taskssss",
            "description" => Str::random(30)
        ], [
            "Authorization" => 'Bearer ' . $token,
        ]);


        $response->assertStatus(422);
    }

    public function test_valid_delete_permission()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $permission = Permission::create([
            'name' => "start-work-task"
        ]);
        $response = $this->delete("/api/admin/permissions/$permission->id", headers: [
            "Authorization" => 'Bearer ' . $token,
        ]);

        $response->assertStatus(204);
    }
    public function test_falied_delete_permission()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $permission = Permission::create([
            'name' => "start-work-task"
        ]);
        $x = ($permission->id) + 1;

        $response = $this->delete("/api/admin/permissions/$x", headers: [
            "Authorization" => 'Bearer ' . $token,
        ]);

        $response->assertStatus(404);
    }

    public function test_valied_create_permission_too_many_requests()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $response1 = $this->post('/api/admin/permissions', [
            'name' => "start-work-task"
        ], [
            "Authorization" => 'Bearer ' . $token,
        ]);

        $response2 = $this->post('/api/admin/permissions', [
            'name' => "start-work-task"
        ], [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response2->assertTooManyRequests();
    }

    public function test_user_cant_get_permissions()
    {
        $role = Role::create([
            'name' => "developer"
        ]);

        $user = User::factory()->create([
            'role_id' => $role->id
        ]);
        $token = JWTAuth::fromUser($user);

        $response = $this->get('/api/admin/permissions',  [
            "Authorization" => 'Bearer ' . $token,
        ]);

        $response->assertStatus(403);
    }

    /////
    public function test_valid_final_delete_permission()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $permission = Permission::create([
            'name' => "start-work-task"
        ]);
        $permission->delete();
        $response = $this->delete("/api/admin/permissions/$permission->id/finalDelete", headers: [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(204);
    }

    public function test_falied_final_delete_permission()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $permission = Permission::create([
            'name' => "start-work-task"
        ]);
        $permission->delete();
        $x = ($permission->id) + 1;

        $response = $this->delete("/api/admin/permissions/$x/finalDelete", headers: [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(404);
    }

    public function test_valied_restore_permission()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $permission = Permission::create([
            'name' => "start-work-task"
        ]);
        $permission->delete();
        $response = $this->post("/api/admin/permissions/$permission->id/restore", headers: [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(200);
    }


    public function test_failed_restore_permission()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $permission = Permission::create([
            'name' => "start-work-task"
        ]);
        $permission->delete();
        $x = ($permission->id) + 1;

        $response = $this->post("/api/admin/permissions/$x/restore", headers: [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(404);
    }
    public function test_get_all_deleted_permissions()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $permission = Permission::create([
            'name' => "start-work-task"
        ]);
        $permission->delete();
        $response = $this->get('/api/admin/deletedPermissions',  [
            "Authorization" => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
    }
}