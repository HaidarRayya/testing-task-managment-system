<?php

namespace App\Http\Requests\Comment;

use App\Services\CommentRequestService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    protected $commentRequestService;
    public function __construct(CommentRequestService $commentRequestService)
    {
        $this->commentRequestService = $commentRequestService;
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
            'description' => ['required',  'min:3', 'string'],
        ];
    }
    public function attributes(): array
    {
        return  $this->commentRequestService->attributes();
    }
    public function failedValidation(Validator $validator)
    {
        $this->commentRequestService->failedValidation($validator);
    }
    public function messages(): array
    {
        $messages = $this->commentRequestService->messages();
        $messages['required'] = 'حقل :attribute هو حقل اجباري ';
        return $messages;
    }
}