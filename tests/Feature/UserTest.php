<?php

namespace Tests\Feature;

use Illuminate\Support\Str;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * A basic feature test example.
     */

    public function test_get_all_users()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);
        $role = Role::create([
            'name' => "developer"
        ]);
        $user = User::factory()->create([
            'role_id' => $role->id
        ]);
        $response = $this->get('/api/admin/users',  [
            "Authorization" => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
    }
    public function test_valid_create_user()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);
        Role::create([
            'name' => "developer"
        ]);
        $response = $this->post('/api/admin/users', [
            'first_name' => Str::random(6),
            'last_name' => Str::random(6),
            'email' => Str::random(10) . "@gmail.com",
            'password' => Str::random(10) . "0H!",
            'role' => "developer"
        ], [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(201);
    }

    public function test_failed_create_user()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);
        Role::create([
            'name' => "developer"
        ]);
        $response = $this->post('/api/admin/users', [
            'first_name' => Str::random(6),
            'last_name' => Str::random(6),
            'email' => Str::random(10) . "@gmail.com",
            'password' => Str::random(10) . "0H!",
            'role' => "worker"
        ], [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(422);
    }
    public function test_failed_create_user_role_not_found()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $response = $this->post('/api/admin/users', [
            'first_name' => Str::random(6),
            'last_name' => Str::random(6),
            'email' => Str::random(10) . "@gmail.com",
            'password' => Str::random(10) . "0H!",
            'role' => "developer"
        ], [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(500);
    }
    public function test_valid_show_user()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);
        $role = Role::create([
            'name' => "developer"
        ]);
        $user = User::factory()->create([
            'role_id' => $role->id
        ]);
        $response = $this->get("/api/admin/users/$user->id", [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(200);
    }
    public function test_failed_show_user()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $role = Role::create([
            'name' => "developer"
        ]);
        $user = User::factory()->create([
            'role_id' => $role->id
        ]);

        $x = ($user->id) + 1;
        $response = $this->get("/api/admin/users/$x", [
            "Authorization" => 'Bearer ' . $token,
        ]);

        $response->assertStatus(404);
    }

    public function test_valid_update_user()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);


        $role = Role::create([
            'name' => "developer"
        ]);
        $user = User::factory()->create([
            'role_id' => $role->id
        ]);
        $response = $this->put("/api/admin/users/$user->id", [
            'first_name' => Str::random(6),
            'last_name' => Str::random(6),
        ], [
            "Authorization" => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
    }

    public function test_falied_update_user()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $role = Role::create([
            'name' => "developer"
        ]);
        $user1 = User::factory()->create([
            'role_id' => $role->id
        ]);

        $user2 = User::factory()->create([
            'role_id' => $role->id
        ]);
        $response = $this->put("/api/admin/users/$user2->id", [
            "email" => $user1->email
        ], [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(422);
    }

    public function test_valid_delete_user()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $role = Role::create([
            'name' => "developer"
        ]);
        $user = User::factory()->create([
            'role_id' => $role->id
        ]);

        $response = $this->delete("/api/admin/users/$user->id", headers: [
            "Authorization" => 'Bearer ' . $token,
        ]);

        $response->assertStatus(204);
    }
    public function test_falied_delete_user()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);


        $role = Role::create([
            'name' => "developer"
        ]);
        $user = User::factory()->create([
            'role_id' => $role->id
        ]);
        $x = ($user->id) + 1;

        $response = $this->delete("/api/admin/users/$x", headers: [
            "Authorization" => 'Bearer ' . $token,
        ]);

        $response->assertStatus(404);
    }

    public function test_valied_create_users_too_many_requests()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $response1 = $this->post('/api/admin/users', [
            'name' => Str::random(10),
            'email' => Str::random(10) . "@gmail.com",
            'password' => Str::random(10),
            'role' => "worker"
        ], [
            "Authorization" => 'Bearer ' . $token,
        ]);

        $response2 = $this->post('/api/admin/users', [
            'name' => Str::random(10),
            'email' => Str::random(10) . "@gmail.com",
            'password' => Str::random(10),
            'role' => "worker"
        ], [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response2->assertTooManyRequests();
    }

    public function test_user_cant_get_users()
    {
        $role = Role::create([
            'name' => "developer"
        ]);

        $user = User::factory()->create([
            'role_id' => $role->id
        ]);
        $token = JWTAuth::fromUser($user);

        $response = $this->get('/api/admin/users',  [
            "Authorization" => 'Bearer ' . $token,
        ]);

        $response->assertStatus(403);
    }

    public function test_valid_final_delete_user()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $role = Role::create([
            'name' => "developer"
        ]);
        $user = User::factory()->create([
            'role_id' => $role->id
        ]);
        $user->delete();
        $response = $this->delete("/api/admin/users/$user->id/finalDelete", headers: [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(204);
    }

    public function test_falied_final_delete_user()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $role = Role::create([
            'name' => "developer"
        ]);
        $user = User::factory()->create([
            'role_id' => $role->id
        ]);
        $user->delete();

        $x = ($user->id) + 1;

        $response = $this->delete("/api/admin/users/$x/finalDelete", headers: [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(404);
    }

    public function test_valied_restore_user()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);


        $role = Role::create([
            'name' => "developer"
        ]);
        $user = User::factory()->create([
            'role_id' => $role->id
        ]);
        $user->delete();

        $response = $this->post("/api/admin/users/$user->id/restore", headers: [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(200);
    }


    public function test_failed_restore_user()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);
        $role = Role::create([
            'name' => "developer"
        ]);
        $user = User::factory()->create([
            'role_id' => $role->id
        ]);
        $user->delete();
        $x = ($user->id) + 1;

        $response = $this->post("/api/admin/users/$x/restore", headers: [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(404);
    }
    public function test_get_all_deleted_users()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $role = Role::create([
            'name' => "developer"
        ]);
        $user = User::factory()->create([
            'role_id' => $role->id
        ]);
        $user->delete();
        $response = $this->get('/api/admin/deletedUsers',  [
            "Authorization" => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
    }
}
