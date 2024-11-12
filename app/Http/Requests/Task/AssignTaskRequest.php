<?php

namespace App\Http\Requests\Task;

use App\Rules\CheckEmployee;
use App\Services\TaskRequestService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class AssignTaskRequest extends FormRequest
{
    protected $taskRequestService;
    public function __construct(TaskRequestService $taskRequestService)
    {
        $this->taskRequestService = $taskRequestService;
    }
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:users,id', new CheckEmployee],
        ];
    }

    public function attributes(): array
    {
        return  $this->taskRequestService->attributes();
    }
    public function failedValidation(Validator $validator)
    {
        $this->taskRequestService->failedValidation($validator);
    }
    public function messages(): array
    {
        return $this->taskRequestService->messages();
    }
}
