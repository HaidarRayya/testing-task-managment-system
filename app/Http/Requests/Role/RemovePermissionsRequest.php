<?php

namespace App\Http\Requests\Role;

use App\Services\ManagerPermissionRequestService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class RemovePermissionsRequest extends FormRequest
{

    protected $managerPermissionRequestService;
    public function __construct(ManagerPermissionRequestService $managerPermissionRequestService)
    {
        $this->managerPermissionRequestService = $managerPermissionRequestService;
    }
    /**
     * Determine if the user is authorized to make this request.
     */
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
            'permissions' => ['required', 'array']
        ];
    }

    public function attributes(): array
    {
        return  $this->managerPermissionRequestService->attributes();
    }
    public function failedValidation(Validator $validator)
    {
        $this->managerPermissionRequestService->failedValidation($validator);
    }
    public function messages(): array
    {
        $messages = $this->managerPermissionRequestService->messages();
        return $messages;
    }
}
