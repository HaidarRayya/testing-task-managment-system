<?php

namespace App\Http\Requests\Role;

use App\Rules\UserRole;
use App\Services\RoleRequestService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class   StoreRoleRequest extends FormRequest
{

    protected $roleRequestService;
    public function __construct(RoleRequestService $roleRequestService)
    {
        $this->roleRequestService = $roleRequestService;
    }
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
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
            'name' => ['required', 'string', 'min:3', 'max:255', 'unique:roles,name', new UserRole],
            'description' => 'sometimes|nullable|string|min:3|max:255',
        ];
    }

    public function attributes(): array
    {
        return  $this->roleRequestService->attributes();
    }
    public function failedValidation(Validator $validator)
    {
        $this->roleRequestService->failedValidation($validator);
    }
    public function messages(): array
    {
        $messages = $this->roleRequestService->messages();
        $messages['required'] = 'حقل :attribute هو حقل اجباري ';
        return $messages;
    }
}