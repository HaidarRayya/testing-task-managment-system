<?php

namespace App\Http\Requests\User;

use App\Rules\UserRole;
use App\Enums\UserRole as EnumsUserRole;
use App\Services\UserRequestService;
use App\Services\AuthService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class FillterUserRequest extends FormRequest
{
    protected $userRequestService;
    public function __construct(UserRequestService $userRequestService)
    {
        $this->userRequestService = $userRequestService;
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
        if (AuthService::user_role(Auth::user()->id) == EnumsUserRole::ADMIN->value) {
            return [
                'role' => ['sometimes', new UserRole],
                'user_name' => ['sometimes', 'string'],
                'availableEmployees' => ['sometimes', 'string'],
            ];
        } else if (AuthService::user_role(Auth::user()->id) == EnumsUserRole::SALES_MANAGER->value) {
            return [
                'user_name' => ['sometimes', 'string'],
            ];
        }
    }

    public function attributes(): array
    {
        return  $this->userRequestService->attributes();
    }
    public function failedValidation(Validator $validator)
    {
        $this->userRequestService->failedValidation($validator);
    }
    public function messages(): array
    {
        $messages = $this->userRequestService->messages();
        return $messages;
    }
}
