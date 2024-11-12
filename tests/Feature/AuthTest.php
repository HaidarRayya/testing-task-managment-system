<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function  test_valid_login()
    {

        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();

        $response = $this->post('/api/login', [
            'email' => $admin->email,
            'password' => 'admin123123!!',
        ]);

        $response->assertStatus(200);
    }

    public function  test_failed_login()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();

        $response = $this->post('/api/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertStatus(422);
    }

    public function  test_valid_logout()
    {

        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $response = $this->post('/api/logout', [], [
            "Authorization" => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
    }

    public function test_valid_refresh()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $response = $this->post('/api/refresh', [], [
            "Authorization" => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
    }
}
