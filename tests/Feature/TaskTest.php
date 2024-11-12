<?php

namespace Tests\Feature;

use App\Enums\UserPermission;
use App\Models\Permission;
use Illuminate\Support\Str;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;


class TaskTest extends TestCase
{
    use DatabaseTransactions;
    /**
     * A basic feature test example.
     */
    public function test_get_all_tasks()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        Task::factory()->create();

        $response = $this->get('/api/admin/tasks',  [
            "Authorization" => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
    }


    public function test_valid_create_task()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task = Task::factory()->create();
        $response = $this->post('/api/admin/tasks', [
            'title' => $task->title,
            'description' => $task->description,
            'priority' => $task->priority,
            'type' => $task->type,
            'due_date' => Carbon::create($task->due_date)->format('Y-m-d H:i'),
            'depends_on' => [],
        ], [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(201);
    }

    public function test_failed_create_task()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task = Task::factory()->create();
        $response = $this->post('/api/admin/tasks', [
            'title' => $task->title,
            'description' => $task->description,
            'priority' => $task->priority,
            'type' => Str::random(6),
            'due_date' => Carbon::create($task->due_date)->format('Y-m-d H:i'),
        ], [
            "Authorization" => 'Bearer ' . $token,
        ]);

        $response->assertStatus(422);
    }

    public function test_not_allowed_create_task()
    {
        $role = Role::create([
            'name' => "developer"
        ]);

        $user = User::factory()->create([
            'role_id' => $role->id
        ]);
        $token = JWTAuth::fromUser($user);

        $task = Task::factory()->create();
        $response = $this->post('/api/admin/tasks', [
            'title' => $task->title,
            'description' => $task->description,
            'priority' => $task->priority,
            'type' => $task->type,
            'due_date' => Carbon::create($task->due_date)->format('Y-m-d H:i'),
        ], [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(403);
    }

    public function test_valied_create_task_too_many_requests()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task1 = Task::factory()->create();
        $response1 = $this->post('/api/admin/tasks', [
            'title' => $task1->title,
            'description' => $task1->description,
            'priority' => $task1->priority,
            'type' => $task1->type,
            'due_date' => Carbon::create($task1->due_date)->format('Y-m-d H:i'),
        ], [
            "Authorization" => 'Bearer ' . $token,
        ]);

        $task2 = Task::factory()->create();
        $response2 = $this->post('/api/admin/tasks', [
            'title' => $task2->title,
            'description' => $task2->description,
            'priority' => $task2->priority,
            'type' => $task2->type,
            'due_date' => Carbon::create($task2->due_date)->format('Y-m-d H:i'),
        ], [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response2->assertTooManyRequests();
    }


    public function test_valid_update_task()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task = Task::factory()->create();
        $response = $this->put("/api/admin/tasks/$task->id", [
            'type' => $task->type,
        ], [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(200);
    }

    public function test_failed_update_task()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task = Task::factory()->create();
        $response = $this->put("/api/admin/tasks/$task->id", [
            'title' => $task->title,
            'description' => $task->description,
            'priority' => $task->priority,
            'type' => Str::random(6),
            'due_date' => Carbon::create($task->due_date)->format('Y-m-d H:i'),
        ], [
            "Authorization" => 'Bearer ' . $token,
        ]);

        $response->assertStatus(422);
    }

    public function test_valid_one_task()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task = Task::factory()->create();

        $response = $this->get("/api/admin/tasks/$task->id", [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(200);
    }

    public function test_failed_one_task()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);
        $task = Task::factory()->create();

        $x = ($task->id) + 1;
        $response = $this->get("/api/admin/tasks/$x", [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(404);
    }


    public function test_valid_delete_task()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task = Task::factory()->create();

        $response = $this->delete("/api/admin/tasks/$task->id", headers: [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(204);
    }

    public function test_failed_delete_task()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task = Task::factory()->create();
        $x = ($task->id) + 1;

        $response = $this->delete("/api/admin/tasks/$x", headers: [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(404);
    }

    public function test_all_deleted_tasks()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task = Task::factory()->create();
        $task->delete();
        $response = $this->get("/api/admin/deletedTasks", headers: [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(200);
    }
    public function test_valied_restore_task()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task = Task::factory()->create();
        $task->delete();
        $response = $this->post("/api/admin/tasks/$task->id/restore", headers: [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(200);
    }
    public function test_falied_restore_task()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task = Task::factory()->create();
        $task->delete();
        $x = ($task->id) + 1;

        $response = $this->post("/api/admin/tasks/$x/restore", headers: [
            "Authorization" => 'Bearer ' . $token,
        ]);

        $response->assertStatus(404);
    }
    public function test_valid_assign_employee_to_task()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task = Task::factory()->create();

        $role = Role::create([
            'name' => "developer"
        ]);

        $user = User::factory()->create([
            'role_id' => $role->id
        ]);


        $response = $this->post(
            "/api/admin/tasks/$task->id/assign",
            ['employee_id' => $user->id],
            ["Authorization" => 'Bearer ' . $token,]
        );

        $response->assertStatus(200);
    }

    public function test_falied_assign_employee_to_task_task_not_found()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task = Task::factory()->create();

        $role = Role::create([
            'name' => "developer"
        ]);

        $user = User::factory()->create([
            'role_id' => $role->id
        ]);

        $x = ($task->id) + 1;

        $response = $this->post(
            "/api/admin/tasks/$x/assign",
            ['employee_id' => $user->id],
            ["Authorization" => 'Bearer ' . $token,]
        );

        $response->assertStatus(404);
    }


    public function test_falied_assign_employee_to_task_task_assigned_employee()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task = Task::factory()->create();

        $role = Role::create([
            'name' => "developer"
        ]);

        $user1 = User::factory()->create([
            'role_id' => $role->id
        ]);

        $task->assigned_to = $user1->id;
        $task->save();

        $user2 = User::factory()->create([
            'role_id' => $role->id
        ]);
        $response = $this->post(
            "/api/admin/tasks/$task->id/assign",
            ['employee_id' => $user2->id],
            ["Authorization" => 'Bearer ' . $token,]
        );

        $response->assertStatus(422);
    }

    public function test_falied_assign_employee_to_task_cant_assign_tester_yet()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task = Task::factory()->create();

        $role = Role::create([
            'name' => "tester"
        ]);

        $user = User::factory()->create([
            'role_id' => $role->id
        ]);


        $response = $this->post(
            "/api/admin/tasks/$task->id/assign",
            ['employee_id' => $user->id],
            ["Authorization" => 'Bearer ' . $token,]
        );

        $response->assertStatus(422);
    }


    public function test_falied_assign_employee_employee_assigen_to_defernt_task()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task1 = Task::factory()->create();

        $role = Role::create([
            'name' => "tester"
        ]);

        $user = User::factory()->create([
            'role_id' => $role->id
        ]);
        $task1->assigned_to = $user->id;
        $task1->save();

        $task2 = Task::factory()->create();


        $response = $this->post(
            "/api/admin/tasks/$task2->id/assign",
            ['employee_id' => $user->id],
            ["Authorization" => 'Bearer ' . $token,]
        );

        $response->assertStatus(422);
    }


    public function test_valid_reassign_employee_to_task()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task = Task::factory()->create();

        $role = Role::create([
            'name' => "developer"
        ]);

        $user1 = User::factory()->create([
            'role_id' => $role->id
        ]);
        $task->assigned_to = $user1->id;
        $task->save();

        $user2 = User::factory()->create([
            'role_id' => $role->id
        ]);

        $response = $this->post(
            "/api/admin/tasks/$task->id/reassign",
            ['employee_id' => $user2->id],
            ["Authorization" => 'Bearer ' . $token,]
        );

        $response->assertStatus(200);
    }


    public function test_failed_reassign_employee_to_task()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task = Task::factory()->create();

        $role = Role::create([
            'name' => "developer"
        ]);

        $user1 = User::factory()->create([
            'role_id' => $role->id
        ]);
        $task->assigned_to = $user1->id;
        $task->save();
        $user2 = User::factory()->create([
            'role_id' => $role->id
        ]);
        $x = ($task->id) + 1;

        $response = $this->post(
            "/api/admin/tasks/$x/reassign",
            ['employee_id' => $user2->id],
            ["Authorization" => 'Bearer ' . $token,]
        );

        $response->assertStatus(404);
    }



    public function test_valid_end_admin_to_task()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task = Task::factory()->create();

        $task->work_status = "finished";
        $task->save();


        $response = $this->post(
            "/api/admin/tasks/$task->id/end",
            headers: ["Authorization" => 'Bearer ' . $token,]
        );

        $response->assertStatus(200);
    }


    public function test_failed_end_employee_to_task_task_not_found()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task = Task::factory()->create();

        $task->work_status = "finished";
        $task->save();

        $x = ($task->id) + 1;

        $response = $this->post(
            "/api/admin/tasks/$x/end",
            headers: ["Authorization" => 'Bearer ' . $token]
        );

        $response->assertStatus(404);
    }

    public function test_failed_end_admin_to_task_task_not_complete()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task = Task::factory()->create();

        $response = $this->post(
            "/api/admin/tasks/$task->id/end",
            headers: ["Authorization" => 'Bearer ' . $token,]
        );

        $response->assertStatus(422);
    }

    public function test_vailed_start_work_task()
    {
        $role = Role::create([
            'name' => "developer"
        ]);
        $user = User::factory()->create([
            'role_id' => $role->id
        ]);
        $token = JWTAuth::fromUser($user);

        $task = Task::factory()->create();

        $task->assigned_to = $user->id;
        $task->save();
        $permission = Permission::create(['name' => UserPermission::START_WORK_TASK->value]);

        $role->permissions()->attach($permission->id);

        $response = $this->post(
            "/api/developer/tasks/$task->id/startWork",
            headers: ["Authorization" => 'Bearer ' . $token]
        );

        $response->assertStatus(200);
    }


    public function test_falied_start_work_task_user_dont_have_permission()
    {
        $role = Role::create([
            'name' => "developer"
        ]);
        $user = User::factory()->create([
            'role_id' => $role->id
        ]);
        $token = JWTAuth::fromUser($user);

        $task = Task::factory()->create();

        $task->assigned_to = $user->id;
        // $task->work_status = "active";
        $task->save();

        $response = $this->post(
            "/api/developer/tasks/$task->id/startWork",
            headers: ["Authorization" => 'Bearer ' . $token]
        );

        $response->assertStatus(403);
    }

    public function test_falied_start_work_task_this_task_dont_belong_to_user()
    {
        $role = Role::create([
            'name' => "developer"
        ]);
        $user = User::factory()->create([
            'role_id' => $role->id
        ]);

        $task = Task::factory()->create();

        $task->assigned_to = $user->id;
        $task->save();

        $permission = Permission::create(['name' => UserPermission::START_WORK_TASK->value]);

        $role->permissions()->attach($permission->id);

        $user2 = User::factory()->create([
            'role_id' => $role->id
        ]);
        $token = JWTAuth::fromUser($user2);

        $response = $this->post(
            "/api/developer/tasks/$task->id/startWork",
            headers: ["Authorization" => 'Bearer ' . $token]
        );

        $response->assertStatus(403);
    }

    public function test_falied_start_work_task_task_not_found()
    {
        $role = Role::create([
            'name' => "developer"
        ]);
        $user = User::factory()->create([
            'role_id' => $role->id
        ]);
        $token = JWTAuth::fromUser($user);

        $task = Task::factory()->create();

        $task->assigned_to = $user->id;
        $task->save();
        $permission = Permission::create(['name' => UserPermission::START_WORK_TASK->value]);

        $role->permissions()->attach($permission->id);
        $x = ($task->id) + 1;

        $response = $this->post(
            "/api/developer/tasks/$x/startWork",
            headers: ["Authorization" => 'Bearer ' . $token]
        );

        $response->assertStatus(404);
    }


    public function test_falied_start_work_task_task_not_idel()
    {
        $role = Role::create([
            'name' => "developer"
        ]);
        $user = User::factory()->create([
            'role_id' => $role->id
        ]);
        $token = JWTAuth::fromUser($user);

        $task = Task::factory()->create();

        $task->assigned_to = $user->id;
        $task->work_status = "active";
        $task->save();
        $permission = Permission::create(['name' => UserPermission::START_WORK_TASK->value]);

        $role->permissions()->attach($permission->id);

        $response = $this->post(
            "/api/developer/tasks/$task->id/startWork",
            headers: ["Authorization" => 'Bearer ' . $token]
        );

        $response->assertStatus(422);
    }


    public function test_vailed_end_work_task()
    {
        $role = Role::create([
            'name' => "developer"
        ]);
        $user = User::factory()->create([
            'role_id' => $role->id
        ]);
        $token = JWTAuth::fromUser($user);

        $task = Task::factory()->create();

        $task->assigned_to = $user->id;
        $task->work_status = "active";
        $task->save();
        $permission = Permission::create(['name' => UserPermission::END_WORK_TASK->value]);

        $role->permissions()->attach($permission->id);

        $response = $this->post(
            "/api/developer/tasks/$task->id/endWork",
            headers: ["Authorization" => 'Bearer ' . $token]
        );

        $response->assertStatus(200);
    }


    public function test_falied_end_work_task_user_dont_have_permission()
    {
        $role = Role::create([
            'name' => "developer"
        ]);
        $user = User::factory()->create([
            'role_id' => $role->id
        ]);
        $token = JWTAuth::fromUser($user);

        $task = Task::factory()->create();

        $task->assigned_to = $user->id;
        $task->work_status = "active";
        $task->save();

        $response = $this->post(
            "/api/developer/tasks/$task->id/endWork",
            headers: ["Authorization" => 'Bearer ' . $token]
        );

        $response->assertStatus(403);
    }

    public function test_falied_end_work_task_this_task_dont_belong_to_user()
    {
        $role = Role::create([
            'name' => "developer"
        ]);
        $user = User::factory()->create([
            'role_id' => $role->id
        ]);

        $task = Task::factory()->create();

        $task->assigned_to = $user->id;
        $task->work_status = "active";
        $task->save();

        $permission = Permission::create(['name' => UserPermission::END_WORK_TASK->value]);

        $role->permissions()->attach($permission->id);

        $user2 = User::factory()->create([
            'role_id' => $role->id
        ]);
        $token = JWTAuth::fromUser($user2);

        $response = $this->post(
            "/api/developer/tasks/$task->id/endWork",
            headers: ["Authorization" => 'Bearer ' . $token]
        );

        $response->assertStatus(403);
    }

    public function test_falied_end_work_task_task_not_found()
    {
        $role = Role::create([
            'name' => "developer"
        ]);
        $user = User::factory()->create([
            'role_id' => $role->id
        ]);
        $token = JWTAuth::fromUser($user);

        $task = Task::factory()->create();

        $task->assigned_to = $user->id;
        $task->work_status = "active";
        $task->save();
        $permission = Permission::create(['name' => UserPermission::END_WORK_TASK->value]);

        $role->permissions()->attach($permission->id);
        $x = ($task->id) + 1;
        $response = $this->post(
            "/api/developer/tasks/$x/endWork",
            headers: ["Authorization" => 'Bearer ' . $token]
        );

        $response->assertStatus(404);
    }
    public function test_falied_end_work_task_task_not_active()
    {
        $role = Role::create([
            'name' => "developer"
        ]);
        $user = User::factory()->create([
            'role_id' => $role->id
        ]);
        $token = JWTAuth::fromUser($user);

        $task = Task::factory()->create();

        $task->assigned_to = $user->id;
        $task->save();
        $permission = Permission::create(['name' => UserPermission::END_WORK_TASK->value]);

        $role->permissions()->attach($permission->id);

        $response = $this->post(
            "/api/developer/tasks/$task->id/endWork",
            headers: ["Authorization" => 'Bearer ' . $token]
        );

        $response->assertStatus(422);
    }
    ///////////////////////////////////


    ///---------------------
    public function test_vailed_start_test_task()
    {
        $role = Role::create([
            'name' => "tester"
        ]);
        $user = User::factory()->create([
            'role_id' => $role->id
        ]);
        $token = JWTAuth::fromUser($user);

        $task = Task::factory()->create();

        $task->assigned_to = $user->id;
        $task->save();
        $permission = Permission::create(['name' => UserPermission::START_TEST_TASK->value]);

        $role->permissions()->attach($permission->id);

        $response = $this->post(
            "/api/tester/tasks/$task->id/startTest",
            headers: ["Authorization" => 'Bearer ' . $token]
        );

        $response->assertStatus(200);
    }


    public function test_falied_start_test_task_user_dont_have_permission()
    {
        $role = Role::create([
            'name' => "tester"
        ]);
        $user = User::factory()->create([
            'role_id' => $role->id
        ]);
        $token = JWTAuth::fromUser($user);

        $task = Task::factory()->create();

        $task->assigned_to = $user->id;
        $task->save();

        $response = $this->post(
            "/api/tester/tasks/$task->id/startTest",
            headers: ["Authorization" => 'Bearer ' . $token]
        );

        $response->assertStatus(403);
    }

    public function test_falied_start_test_task_this_task_dont_belong_to_user()
    {
        $role = Role::create([
            'name' => "tester"
        ]);
        $user = User::factory()->create([
            'role_id' => $role->id
        ]);

        $task = Task::factory()->create();

        $task->assigned_to = $user->id;
        $task->save();

        $permission = Permission::create(['name' => UserPermission::START_TEST_TASK->value]);

        $role->permissions()->attach($permission->id);

        $user2 = User::factory()->create([
            'role_id' => $role->id
        ]);
        $token = JWTAuth::fromUser($user2);

        $response = $this->post(
            "/api/tester/tasks/$task->id/startTest",
            headers: ["Authorization" => 'Bearer ' . $token]
        );

        $response->assertStatus(403);
    }

    public function test_falied_start_test_task_task_not_found()
    {
        $role = Role::create([
            'name' => "tester"
        ]);
        $user = User::factory()->create([
            'role_id' => $role->id
        ]);
        $token = JWTAuth::fromUser($user);

        $task = Task::factory()->create();

        $task->assigned_to = $user->id;
        $task->save();
        $permission = Permission::create(['name' => UserPermission::START_TEST_TASK->value]);

        $role->permissions()->attach($permission->id);
        $x = ($task->id) + 1;

        $response = $this->post(
            "/api/tester/tasks/$x/startTest",
            headers: ["Authorization" => 'Bearer ' . $token]
        );

        $response->assertStatus(404);
    }


    public function test_falied_start_test_task_task_not_idel()
    {
        $role = Role::create([
            'name' => "tester"
        ]);
        $user = User::factory()->create([
            'role_id' => $role->id
        ]);
        $token = JWTAuth::fromUser($user);

        $task = Task::factory()->create();

        $task->assigned_to = $user->id;
        $task->work_status = "active";
        $task->save();
        $permission = Permission::create(['name' => UserPermission::START_TEST_TASK->value]);

        $role->permissions()->attach($permission->id);

        $response = $this->post(
            "/api/tester/tasks/$task->id/startTest",
            headers: ["Authorization" => 'Bearer ' . $token]
        );

        $response->assertStatus(422);
    }



    public function test_vailed_end_test_task()
    {
        $role = Role::create([
            'name' => "tester"
        ]);
        $user = User::factory()->create([
            'role_id' => $role->id
        ]);
        $token = JWTAuth::fromUser($user);

        $task = Task::factory()->create();

        $task->assigned_to = $user->id;
        $task->work_status = "active";
        $task->save();
        $permission = Permission::create(['name' => UserPermission::END_TEST_TASK->value]);

        $role->permissions()->attach($permission->id);

        $response = $this->post(
            "/api/tester/tasks/$task->id/endTest",
            headers: ["Authorization" => 'Bearer ' . $token]
        );

        $response->assertStatus(200);
    }

    public function test_falied_end_test_task_user_dont_have_permission()
    {
        $role = Role::create([
            'name' => "tester"
        ]);
        $user = User::factory()->create([
            'role_id' => $role->id
        ]);
        $token = JWTAuth::fromUser($user);

        $task = Task::factory()->create();

        $task->assigned_to = $user->id;
        $task->work_status = "active";
        $task->save();

        $response = $this->post(
            "/api/tester/tasks/$task->id/endTest",
            headers: ["Authorization" => 'Bearer ' . $token]
        );

        $response->assertStatus(403);
    }

    public function test_falied_end_test_task_this_task_dont_belong_to_user()
    {
        $role = Role::create([
            'name' => "tester"
        ]);
        $user = User::factory()->create([
            'role_id' => $role->id
        ]);

        $task = Task::factory()->create();

        $task->assigned_to = $user->id;
        $task->work_status = "active";
        $task->save();

        $permission = Permission::create(['name' => UserPermission::END_WORK_TASK->value]);

        $role->permissions()->attach($permission->id);

        $user2 = User::factory()->create([
            'role_id' => $role->id
        ]);
        $token = JWTAuth::fromUser($user2);

        $response = $this->post(
            "/api/tester/tasks/$task->id/endTest",
            headers: ["Authorization" => 'Bearer ' . $token]
        );

        $response->assertStatus(403);
    }

    public function test_falied_end_test_task_task_not_found()
    {
        $role = Role::create([
            'name' => "tester"
        ]);
        $user = User::factory()->create([
            'role_id' => $role->id
        ]);
        $token = JWTAuth::fromUser($user);

        $task = Task::factory()->create();

        $task->assigned_to = $user->id;
        $task->work_status = "active";
        $task->save();
        $permission = Permission::create(['name' => UserPermission::END_TEST_TASK->value]);

        $role->permissions()->attach($permission->id);
        $x = ($task->id) + 1;
        $response = $this->post(
            "/api/tester/tasks/$x/endTest",
            headers: ["Authorization" => 'Bearer ' . $token]
        );

        $response->assertStatus(404);
    }
    public function test_falied_end_test_task_task_not_active()
    {
        $role = Role::create([
            'name' => "tester"
        ]);
        $user = User::factory()->create([
            'role_id' => $role->id
        ]);
        $token = JWTAuth::fromUser($user);

        $task = Task::factory()->create();

        $task->assigned_to = $user->id;
        $task->save();
        $permission = Permission::create(['name' => UserPermission::END_TEST_TASK->value]);

        $role->permissions()->attach($permission->id);

        $response = $this->post(
            "/api/tester/tasks/$task->id/endTest",
            headers: ["Authorization" => 'Bearer ' . $token]
        );

        $response->assertStatus(422);
    }

    public function test_valid_get_daily_tasks()
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $response = $this->get(
            "/api/admin/dailyReports",
            headers: ["Authorization" => 'Bearer ' . $token]
        );

        $response->assertStatus(200);
    }
    public function test_falied_get_daily_tasks()
    {
        $role = Role::create([
            'name' => "tester"
        ]);
        $user = User::factory()->create([
            'role_id' => $role->id
        ]);
        $token = JWTAuth::fromUser($user);

        $response = $this->get(
            "/api/admin/dailyReports",
            headers: ["Authorization" => 'Bearer ' . $token]
        );

        $response->assertStatus(403);
    }
}
