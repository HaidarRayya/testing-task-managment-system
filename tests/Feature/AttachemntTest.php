<?php

namespace Tests\Feature;

use App\Enums\UserPermission;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AttachemntTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * A basic feature test example.
     */
    
    
    public function test_get_all_attachemnts(): void
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task = Task::factory()->create();
        $file =  UploadedFile::fake()->create(
            'document.pdf',
            300,
            'application/pdf'
        );

        $fileName = Str::random(32);
        $mime_type = $file->getClientMimeType();
        $extension = $file->getClientOriginalExtension();
        $path =  Storage::putFileAs('Attachments', $file, $fileName . '.' . $extension);

        $url = asset($path);
        $task->attachments()->create([
            'name' => $file->getFilename(),
            'path' => $url,
            'mime_type' =>  $mime_type,
            'user_id' => $admin->id
        ]);
        $response = $this->get("/api/admin/tasks/$task->id/attachments",  [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(200);
    }

    public function test_valied_create_attachments(): void
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task = Task::factory()->create();
        $file =  UploadedFile::fake()->create(
            'document.pdf',
            300,
            'application/pdf'
        );

        $response = $this->post(
            "/api/admin/tasks/$task->id/attachments",
            [
                "file" => $file
            ],
            [
                "Authorization" => 'Bearer ' . $token,
            ]
        );
        $response->assertStatus(201);
    }


    public function test_failed_create_attachments_file_type_not_avilabel(): void
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task = Task::factory()->create();
        $file =  UploadedFile::fake()->create(
            'document.txt',
            300,
            'text/plain'
        );

        $response = $this->post(
            "/api/admin/tasks/$task->id/attachments",
            [
                "file" => $file
            ],
            [
                "Authorization" => 'Bearer ' . $token,
            ]
        );
        $response->assertStatus(422);
    }

    public function test_failed_create_attachments_file_very_big(): void
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task = Task::factory()->create();
        $file =  UploadedFile::fake()->create(
            'document.pdf',
            300000,
            'application/pdf'
        );
        $response = $this->post(
            "/api/admin/tasks/$task->id/attachments",
            [
                "file" => $file
            ],
            [
                "Authorization" => 'Bearer ' . $token,
            ]
        );
        $response->assertStatus(422);
    }

    public function test_failed_create_attachments_file_name_is_invalied(): void
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task = Task::factory()->create();
        $file =  UploadedFile::fake()->create(
            'document.php.pdf',
            300,
            'application/pdf'
        );
        $response = $this->post(
            "/api/admin/tasks/$task->id/attachments",
            [
                "file" => $file
            ],
            [
                "Authorization" => 'Bearer ' . $token,
            ]
        );
        $response->assertStatus(422);
    }

    public function test_falied_create_attacment_tester_not_have_permission(): void
    {
        $role = Role::create([
            'name' => "tester"
        ]);

        $user = User::factory()->create([
            'role_id' => $role->id
        ]);
        $token = JWTAuth::fromUser($user);
        $task = Task::factory()->create();

        $file =  UploadedFile::fake()->create(
            'document.pdf',
            300,
            'application/pdf'
        );

        $response = $this->post(
            "/api/admin/tasks/$task->id/attachments",
            [
                "file" => $file
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
        $file1 =  UploadedFile::fake()->create(
            'document.pdf',
            300,
            'application/pdf'
        );

        $this->post(
            "/api/admin/tasks/$task->id/attachments",
            [
                "file" => $file1
            ],
            [
                "Authorization" => 'Bearer ' . $token,
            ]
        );
        $file2 =  UploadedFile::fake()->create(
            'document.pdf',
            300,
            'application/pdf'
        );
        $response2 = $this->post(
            "/api/admin/tasks/$task->id/attachments",
            [
                "file" => $file2
            ],
            [
                "Authorization" => 'Bearer ' . $token,
            ]
        );
        $response2->assertTooManyRequests();
    }



    public function test_valied_get_attachment(): void
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task = Task::factory()->create();
        $file =  UploadedFile::fake()->create(
            'document.pdf',
            300,
            'application/pdf'
        );

        $fileName = Str::random(32);
        $mime_type = $file->getClientMimeType();
        $extension = $file->getClientOriginalExtension();
        $path =  Storage::putFileAs('Attachments', $file, $fileName . '.' . $extension);

        $url = asset($path);
        $attachment = $task->attachments()->create([
            'name' => $file->getFilename(),
            'path' => $url,
            'mime_type' =>  $mime_type,
            'user_id' => $admin->id
        ]);

        $response = $this->get(
            "/api/admin/tasks/$task->id/attachments/$attachment->id",
            headers: [
                "Authorization" => 'Bearer ' . $token,
            ]
        );
        $response->assertStatus(200);
    }

    public function test_falied_get_attachment(): void
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task = Task::factory()->create();
        $file =  UploadedFile::fake()->create(
            'document.pdf',
            300,
            'application/pdf'
        );

        $fileName = Str::random(32);
        $mime_type = $file->getClientMimeType();
        $extension = $file->getClientOriginalExtension();
        $path =  Storage::putFileAs('Attachments', $file, $fileName . '.' . $extension);

        $url = asset($path);
        $attachment = $task->attachments()->create([
            'name' => $file->getFilename(),
            'path' => $url,
            'mime_type' =>  $mime_type,
            'user_id' => $admin->id
        ]);

        $x = ($attachment->id) + 1;
        $response = $this->get(
            "/api/admin/tasks/$task->id/attachments/$x",
            headers: [
                "Authorization" => 'Bearer ' . $token,
            ]
        );
        $response->assertStatus(404);
    }
    public function test_valied_update_attachment(): void
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task = Task::factory()->create();
        $file =  UploadedFile::fake()->create(
            'document.pdf',
            300,
            'application/pdf'
        );

        $fileName = Str::random(32);
        $mime_type = $file->getClientMimeType();
        $extension = $file->getClientOriginalExtension();
        $path =  Storage::putFileAs('Attachments', $file, $fileName . '.' . $extension);

        $url = asset($path);
        $attachment = $task->attachments()->create([
            'name' => $file->getFilename(),
            'path' => $url,
            'mime_type' =>  $mime_type,
            'user_id' => $admin->id
        ]);

        $file2 =  UploadedFile::fake()->create(
            'document2.pdf',
            300,
            'application/pdf'
        );
        $response = $this->put(
            "/api/admin/tasks/$task->id/attachments/$attachment->id",
            [
                'file' => $file2
            ],
            [
                "Authorization" => 'Bearer ' . $token,
            ]
        );
        $response->assertStatus(200);
    }

    public function test_falied_update_attachment_not_found(): void
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task = Task::factory()->create();
        $file =  UploadedFile::fake()->create(
            'document.pdf',
            300,
            'application/pdf'
        );

        $fileName = Str::random(32);
        $mime_type = $file->getClientMimeType();
        $extension = $file->getClientOriginalExtension();
        $path =  Storage::putFileAs('Attachments', $file, $fileName . '.' . $extension);

        $url = asset($path);
        $attachment = $task->attachments()->create([
            'name' => $file->getFilename(),
            'path' => $url,
            'mime_type' =>  $mime_type,
            'user_id' => $admin->id
        ]);

        $file2 =  UploadedFile::fake()->create(
            'document2.pdf',
            300,
            'application/pdf'
        );
        $x = ($attachment->id) + 1;
        $response = $this->put(
            "/api/admin/tasks/$task->id/attachments/$x",
            [
                'file' => $file2
            ],
            [
                "Authorization" => 'Bearer ' . $token,
            ]
        );
        $response->assertStatus(404);
    }



    public function test_falied_update_attachment_file_not_belong_to_user(): void
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();

        $task = Task::factory()->create();
        $file =  UploadedFile::fake()->create(
            'document.pdf',
            300,
            'application/pdf'
        );

        $fileName = Str::random(32);
        $mime_type = $file->getClientMimeType();
        $extension = $file->getClientOriginalExtension();
        $path =  Storage::putFileAs('Attachments', $file, $fileName . '.' . $extension);

        $url = asset($path);
        $attachment = $task->attachments()->create([
            'name' => $file->getFilename(),
            'path' => $url,
            'mime_type' =>  $mime_type,
            'user_id' => $admin->id
        ]);

        $role = Role::create([
            'name' => "developer"
        ]);

        $user = User::factory()->create([
            'role_id' => $role->id
        ]);
        $token = JWTAuth::fromUser($user);

        $permission = Permission::userPermission(UserPermission::UPDATE_ATTACHMENT->value)->first();

        $role->permissions()->attach($permission->id);

        $file2 =  UploadedFile::fake()->create(
            'document2.pdf',
            300,
            'application/pdf'
        );
        $response = $this->put(
            "/api/admin/tasks/$task->id/attachments/$attachment->id",
            [
                'file' => $file2
            ],
            [
                "Authorization" => 'Bearer ' . $token,
            ]
        );
        $response->assertStatus(403);
    }

    public function test_valied_delete_attachment(): void
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task = Task::factory()->create();
        $file =  UploadedFile::fake()->create(
            'document.pdf',
            300,
            'application/pdf'
        );

        $fileName = Str::random(32);
        $mime_type = $file->getClientMimeType();
        $extension = $file->getClientOriginalExtension();
        $path =  Storage::putFileAs('Attachments', $file, $fileName . '.' . $extension);

        $url = asset($path);
        $attachment = $task->attachments()->create([
            'name' => $file->getFilename(),
            'path' => $url,
            'mime_type' =>  $mime_type,
            'user_id' => $admin->id
        ]);

        $file2 =  UploadedFile::fake()->create(
            'document2.pdf',
            300,
            'application/pdf'
        );
        $response = $this->delete(
            "/api/admin/tasks/$task->id/attachments/$attachment->id",
            headers: [
                "Authorization" => 'Bearer ' . $token,
            ]
        );
        $response->assertStatus(204);
    }

    public function test_falied_delete_attachment_not_found(): void
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task = Task::factory()->create();
        $file =  UploadedFile::fake()->create(
            'document.pdf',
            300,
            'application/pdf'
        );

        $fileName = Str::random(32);
        $mime_type = $file->getClientMimeType();
        $extension = $file->getClientOriginalExtension();
        $path =  Storage::putFileAs('Attachments', $file, $fileName . '.' . $extension);

        $url = asset($path);
        $attachment = $task->attachments()->create([
            'name' => $file->getFilename(),
            'path' => $url,
            'mime_type' =>  $mime_type,
            'user_id' => $admin->id
        ]);

        $file2 =  UploadedFile::fake()->create(
            'document2.pdf',
            300,
            'application/pdf'
        );
        $x = ($attachment->id) + 1;
        $response = $this->delete(
            "/api/admin/tasks/$task->id/attachments/$x",

            headers: [
                "Authorization" => 'Bearer ' . $token,
            ]
        );
        $response->assertStatus(404);
    }



    public function test_falied_delete_attachment_file_not_belong_to_user(): void
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();

        $task = Task::factory()->create();
        $file =  UploadedFile::fake()->create(
            'document.pdf',
            300,
            'application/pdf'
        );

        $fileName = Str::random(32);
        $mime_type = $file->getClientMimeType();
        $extension = $file->getClientOriginalExtension();
        $path =  Storage::putFileAs('Attachments', $file, $fileName . '.' . $extension);

        $url = asset($path);
        $attachment = $task->attachments()->create([
            'name' => $file->getFilename(),
            'path' => $url,
            'mime_type' =>  $mime_type,
            'user_id' => $admin->id
        ]);

        $role = Role::create([
            'name' => "developer"
        ]);

        $user = User::factory()->create([
            'role_id' => $role->id
        ]);
        $token = JWTAuth::fromUser($user);
        $permission = Permission::userPermission(UserPermission::DELETE_ATTACHMENT->value)->first();

        $role->permissions()->attach($permission->id);
        $response = $this->delete(
            "/api/admin/tasks/$task->id/attachments/$attachment->id",
            headers: [
                "Authorization" => 'Bearer ' . $token,
            ]
        );
        $response->assertStatus(403);
    }


    public function test_valied_download_attachment(): void
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task = Task::factory()->create();
        $file =  UploadedFile::fake()->create(
            'document.pdf',
            300,
            'application/pdf'
        );

        $fileName = Str::random(32);
        $mime_type = $file->getClientMimeType();
        $extension = $file->getClientOriginalExtension();
        $path =  Storage::putFileAs('Attachments', $file, $fileName . '.' . $extension);

        $url = asset($path);
        $attachment = $task->attachments()->create([
            'name' => $file->getFilename(),
            'path' => $url,
            'mime_type' =>  $mime_type,
            'user_id' => $admin->id
        ]);

        $response = $this->get(
            "/api/admin/tasks/$task->id/attachments/$attachment->id/download",
            headers: [
                "Authorization" => 'Bearer ' . $token,
            ]
        );
        $response->assertDownload();
    }

    public function test_falied_download_attachment(): void
    {
        $admin = User::where('role_id', '=', Role::userRole('admin')->first()->id)->first();
        $token = JWTAuth::fromUser($admin);

        $task = Task::factory()->create();
        $file =  UploadedFile::fake()->create(
            'document.pdf',
            300,
            'application/pdf'
        );

        $fileName = Str::random(32);
        $mime_type = $file->getClientMimeType();
        $extension = $file->getClientOriginalExtension();
        $path =  Storage::putFileAs('Attachments', $file, $fileName . '.' . $extension);

        $url = asset($path);
        $attachment = $task->attachments()->create([
            'name' => $file->getFilename(),
            'path' => $url,
            'mime_type' =>  $mime_type,
            'user_id' => $admin->id
        ]);

        $x = ($attachment->id) + 1;
        $response = $this->get(
            "/api/admin/tasks/$task->id/attachments/$x/download",
            headers: [
                "Authorization" => 'Bearer ' . $token,
            ]
        );
        $response->assertStatus(404);
    }
}