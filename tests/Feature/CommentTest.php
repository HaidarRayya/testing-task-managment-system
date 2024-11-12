<?php

namespace Tests\Feature;

use App\Enums\UserPermission;
use App\Models\Permission;
use Illuminate\Support\Str;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class CommentTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * A basic feature test example.
     */
    public function test_get_all_comments(): void
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task = Task::factory()->create();

        $task->comments()->create([
            'user_id' => $admin->id,
            'description' => Str::random(30)
        ]);
        $response = $this->get("/api/admin/tasks/$task->id/comments",  [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(200);
    }

    public function test_valied_create_comments(): void
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task = Task::factory()->create();
        $response = $this->post(
            "/api/admin/tasks/$task->id/comments",
            [
                'description' => Str::random(30)
            ],
            [
                "Authorization" => 'Bearer ' . $token,
            ]
        );
        $response->assertStatus(201);
    }

    public function test_falied_create_comments_developer_not_have_permission(): void
    {
        $role = Role::create([
            'name' => "developer"
        ]);

        $user = User::factory()->create([
            'role_id' => $role->id
        ]);
        $token = JWTAuth::fromUser($user);

        $task = Task::factory()->create();
        $response = $this->post(
            "/api/admin/tasks/$task->id/comments",
            [
                'description' => Str::random(30)
            ],
            [
                "Authorization" => 'Bearer ' . $token,
            ]
        );
        $response->assertStatus(403);
    }

    public function test_falied_create_comments_too_many_requests(): void
    {
        $role = Role::create([
            'name' => "developer"
        ]);

        $user = User::factory()->create([
            'role_id' => $role->id
        ]);
        $token = JWTAuth::fromUser($user);

        $task = Task::factory()->create();
        $this->post(
            "/api/admin/tasks/$task->id/comments",
            [
                'description' => Str::random(30)
            ],
            [
                "Authorization" => 'Bearer ' . $token,
            ]
        );

        $response2 = $this->post(
            "/api/admin/tasks/$task->id/comments",
            [
                'description' => Str::random(30)
            ],
            [
                "Authorization" => 'Bearer ' . $token,
            ]
        );
        $response2->assertTooManyRequests();
    }
    public function test_valied_show_comment(): void
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task = Task::factory()->create();

        $commnet = $task->comments()->create([
            'user_id' => $admin->id,
            'description' => Str::random(30)
        ]);
        $response = $this->get(
            "/api/admin/tasks/$task->id/comments/$commnet->id",
            [
                "Authorization" => 'Bearer ' . $token,
            ]
        );
        $response->assertStatus(200);
    }

    public function test_failed_show_comment(): void
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task = Task::factory()->create();

        $commnet = $task->comments()->create([
            'user_id' => $admin->id,
            'description' => Str::random(30)
        ]);
        $x = ($commnet->id) + 1;
        $response = $this->get(
            "/api/admin/tasks/$task->id/comments/$x",
            [
                "Authorization" => 'Bearer ' . $token,
            ]
        );
        $response->assertStatus(404);
    }

    public function test_valied_update_comment(): void
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task = Task::factory()->create();

        $commnet = $task->comments()->create([
            'user_id' => $admin->id,
            'description' => Str::random(30)
        ]);
        $response = $this->put(
            "/api/admin/tasks/$task->id/comments/$commnet->id",
            [
                'description' => Str::random(30)
            ],
            [
                "Authorization" => 'Bearer ' . $token,
            ]
        );
        $response->assertStatus(200);
    }
    public function test_falied_update_comment_this_comment_not_belong_to_user(): void
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();

        $task = Task::factory()->create();

        $commnet = $task->comments()->create([
            'user_id' => $admin->id,
            'description' => Str::random(30)
        ]);


        $role = Role::create([
            'name' => "developer"
        ]);

        $user = User::factory()->create([
            'role_id' => $role->id
        ]);
        $token = JWTAuth::fromUser($user);
        $permission = Permission::userPermission(UserPermission::UPDATE_COMMENT->value)->first();

        $role->permissions()->attach($permission->id);
        $response = $this->put(
            "/api/admin/tasks/$task->id/comments/$commnet->id",
            [
                'description' => Str::random(30)
            ],
            [
                "Authorization" => 'Bearer ' . $token,
            ]
        );
        $response->assertStatus(403);
    }
    public function test_falied_update_comment_not_found(): void
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task = Task::factory()->create();

        $commnet = $task->comments()->create([
            'user_id' => $admin->id,
            'description' => Str::random(30)
        ]);
        $x = ($commnet->id) + 1;

        $response = $this->put(
            "/api/admin/tasks/$task->id/comments/$x",
            [
                'description' => Str::random(30)
            ],
            [
                "Authorization" => 'Bearer ' . $token,
            ]
        );
        $response->assertStatus(404);
    }

    public function test_valied_delete_comment(): void
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task = Task::factory()->create();

        $commnet = $task->comments()->create([
            'user_id' => $admin->id,
            'description' => Str::random(30)
        ]);
        $response = $this->delete(
            "/api/admin/tasks/$task->id/comments/$commnet->id",
            headers: [
                "Authorization" => 'Bearer ' . $token,
            ]
        );
        $response->assertStatus(204);
    }

    public function test_falied_delete_comment_this_comment_not_belong_to_user(): void
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();

        $task = Task::factory()->create();

        $commnet = $task->comments()->create([
            'user_id' => $admin->id,
            'description' => Str::random(30)
        ]);

        $role = Role::create([
            'name' => "developer"
        ]);

        $user = User::factory()->create([
            'role_id' => $role->id
        ]);
        $token = JWTAuth::fromUser($user);
        $permission = Permission::userPermission(UserPermission::DELETE_COMMENT->value)->first();

        $role->permissions()->attach($permission->id);
        $response = $this->delete(
            "/api/admin/tasks/$task->id/comments/$commnet->id",
            headers: [
                "Authorization" => 'Bearer ' . $token,
            ]
        );
        $response->assertStatus(403);
    }
    public function test_falied_delete_comment_not_found(): void
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task = Task::factory()->create();

        $commnet = $task->comments()->create([
            'user_id' => $admin->id,
            'description' => Str::random(30)
        ]);
        $x = ($commnet->id) + 1;

        $response = $this->delete(
            "/api/admin/tasks/$task->id/comments/$x",
            headers: [
                "Authorization" => 'Bearer ' . $token,
            ]
        );
        $response->assertStatus(404);
    }
}
