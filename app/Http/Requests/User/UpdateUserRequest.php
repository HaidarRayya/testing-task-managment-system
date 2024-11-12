<?php

namespace App\Http\Requests\User;

use App\Models\User;
use App\Services\UserRequestService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
        $user_id = $this->route('user');

        return [
            'first_name' => ['sometimes',  'min:3', 'max:15', 'string'],
            'last_name' => ['sometimes',  'min:3', 'max:15', 'string'],
            'email' =>    ['sometimes', 'email', "unique:users,email,$user_id"],
            'password' => [
                'sometimes',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&]/',
            ],
        ];
    }
    public function validateWithCasts()
    {
        $user_id = $this->route('user');
        $user = User::findOrFail($user_id);
        $full_name = explode(" ", $user->name);

        if ($this->first_name != null ||  $this->last_name != null) {
            if ($this->first_name == null) {
                return  $this->merge([
                    'name' => $full_name[0] . ' ' . $this->last_name,
                ]);
            } else if ($this->last_name == null) {
                return  $this->merge([
                    'name' => $this->first_name . ' ' . $full_name[1],
                ]);
            } else {
                return   $this->merge([
                    'name' => $this->first_name  . ' ' . $this->last_name,
                ]);
            }
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
        return $this->userRequestService->messages();
    }
}