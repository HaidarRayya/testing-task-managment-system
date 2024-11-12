<?php

namespace Tests\Feature;

use App\Enums\UserPermission;
use App\Models\Permission;
use Illuminate\Support\Str;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class RoleTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * A basic feature test example.
     */
    public function test_get_all_roles()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);
        $role = Role::create([
            'name' => "developer"
        ]);
        $response = $this->get('/api/admin/roles',  [
            "Authorization" => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
    }
    public function test_not_allowed_do_get_roles()
    {
        $role = Role::create([
            'name' => "developer"
        ]);

        $user = User::factory()->create([
            'role_id' => $role->id
        ]);
        $token = JWTAuth::fromUser($user);

        $response = $this->get('/api/admin/roles',  [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(403);
    }
    public function test1_valied1_create_role()
    {

        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $response = $this->post('/api/admin/roles', [
            'name' => "developer"
        ], [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(201);
    }
    public function test_valied2_create_role()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $response  = $this->post('/api/admin/roles', [
            'name' => "tester"
        ], [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(201);
    }

    public function test_valied_create_role_too_many_requests()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $response1  = $this->post('/api/admin/roles', [
            'name' => "developer"
        ], [
            "Authorization" => 'Bearer ' . $token,
        ]);

        $response2  = $this->post('/api/admin/roles', [
            'name' => "tester"
        ], [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response2->assertTooManyRequests();
    }


    public function test_failed_create_role()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $response = $this->post('/api/admin/roles', [
            'name' => 'worker'
        ], [
            "Authorization" => 'Bearer ' . $token,
        ]);

        $response->assertStatus(422);
    }
    public function test_valid_one_role()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);
        $role = Role::create([
            'name' => "developer"
        ]);
        $response = $this->get("/api/admin/roles/$role->id", [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(200);
    }

    public function test_failed_one_role()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);
        $role = Role::create([
            'name' => "developer"
        ]);
        $x = ($role->id) + 1;
        $response = $this->get("/api/admin/roles/$x", [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(404);
    }

    public function test_valid_update_role()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $role = Role::create([
            'name' => "developer"
        ]);

        $response = $this->put("/api/admin/roles/$role->id", [
            'description' => Str::random(30)
        ], [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(200);
    }
    public function test_failed1_update_role()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $role = Role::create([
            'name' => "developer"
        ]);
        $response = $this->put("/api/admin/roles/$role->id", [
            'name' => "worker",
            'description' => Str::random(30)
        ], [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(422);
    }
    public function test_failed2_update_role()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $role1 = Role::create([
            'name' => "developer"
        ]);
        $role2 = Role::create([
            'name' => "tester"
        ]);
        $response = $this->put("/api/admin/roles/$role2->id", [
            'name' => "developer",
            'description' => Str::random(30)
        ], [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(422);
    }

    public function test_valid_delete_role()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $role = Role::create([
            'name' => "developer"
        ]);

        $response = $this->delete("/api/admin/roles/$role->id", headers: [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(204);
    }

    public function test_failed_delete_role()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $role = Role::create([
            'name' => "developer"
        ]);
        $x = ($role->id) + 1;

        $response = $this->delete("/api/admin/roles/$x", headers: [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(404);
    }
    public function test_valid_final_delete_role()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $role = Role::create([
            'name' => "developer"
        ]);
        $role->delete();
        $response = $this->delete("/api/admin/roles/$role->id/finalDelete", headers: [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(204);
    }
    public function test_all_deleted__role()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $role = Role::create([
            'name' => "developer"
        ]);
        $role->delete();
        $response = $this->get("/api/admin/deletedRoles", headers: [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(200);
    }
    public function test_valied_restore_role()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $role = Role::create([
            'name' => "developer"
        ]);
        $role->delete();
        $response = $this->post("/api/admin/roles/ $role->id/restore", headers: [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(200);
    }

    public function test_failed_restore_role()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $role = Role::create([
            'name' => "developer"
        ]);
        $role->delete();
        $x = ($role->id) + 1;

        $response = $this->post("/api/admin/roles/$x/restore", headers: [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(404);
    }
    public function test_valid_add_permission_to_role()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $role = Role::create([
            'name' => "developer"
        ]);

        $permission = Permission::userPermission(UserPermission::CREATE_ATTACHMENT->value)->first();

        $response = $this->post("/api/admin/roles/$role->id/addPermissions",  [
            'permissions' => array($permission->id)
        ], [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(200);
    }

    public function test_failed_add_permission_to_role()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $role = Role::create([
            'name' => "developer"
        ]);

        $permission = Permission::userPermission(UserPermission::CREATE_COMMENT->value)->first();

        $response = $this->post("/api/admin/roles/$role->id/addPermissions",  [
            'permissions' => array($permission->id)
        ], [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(422);
    }

    public function test_remove_permission_from_role()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $role = Role::create([
            'name' => "developer"
        ]);
        $permission = Permission::userPermission(UserPermission::CREATE_ATTACHMENT->value)->first();

        $role->permissions()->attach($permission->id);

        $response = $this->post("/api/admin/roles/$role->id/removePermission",  [
            'permissions' =>  array($permission->id)
        ], [
            "Authorization" => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
    }
}