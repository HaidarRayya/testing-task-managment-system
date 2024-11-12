<?php

namespace App\Http\Requests\User;

use App\Rules\EmployeeRole;
use App\Services\UserRequestService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
        return [
            'first_name' => ['required',  'min:3', 'max:15', 'string'],
            'last_name' => ['required',  'min:3', 'max:15', 'string'],
            'email' =>    ['required', 'email', 'unique:users,email'],
            'password' => [
                'required',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&]/',
            ],
            'role' => ['required',  new EmployeeRole]
        ];
    }

    public function validateWithCasts()
    {
        return $this->merge([
            'name' => $this->first_name . ' ' . $this->last_name,
        ]);
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
        $messages['required'] = 'حقل :attribute هو حقل اجباري ';
        return $messages;
    }
}
