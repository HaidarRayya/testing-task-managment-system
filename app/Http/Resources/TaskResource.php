<?php

namespace App\Http\Resources;

use App\Enums\UserRole;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if (AuthService::user_role(Auth::user()->id) == UserRole::ADMIN->value) {
            if (!$this->relationLoaded('employee')) {
                return [
                    'id' => $this->id,
                    'title' => $this->title,
                    'description' => $this->description,
                    'priority' => $this->priority,
                    'type' => $this->type,
                    'due_date' => $this->due_date,
                    'status' => $this->status,
                    'work_status' => $this->work_status,
                    'employeeName' =>  '',
                ];
            } else {
                $data = [];
                if ($this->employee != null) {
                    $data['employeeName'] = $this->employee->name;
                    $data['employeeRole'] = $this->employee->role->name;
                }
                return [
                    'id' => $this->id,
                    'title' => $this->title,
                    'description' => $this->description,
                    'priority' => $this->priority,
                    'type' => $this->type,
                    'due_date' => $this->due_date,
                    'status' => $this->status,
                    'work_status' => $this->work_status,
                    ...$data
                ];
            }
        } else {
            return [
                'id' => $this->id,
                'title' => $this->title,
                'description' => $this->description,
                'priority' => $this->priority,
                'type' => $this->type,
                'due_date' => $this->due_date,
                'status' => $this->status,
                'work_status' => $this->work_status,
            ];
        }
    }
}