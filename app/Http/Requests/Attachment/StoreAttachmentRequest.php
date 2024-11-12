<?php

namespace App\Http\Requests\Attachment;

use App\Services\AttachmentRequestService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreAttachmentRequest extends FormRequest
{
    protected $attachmentRequestService;
    public function __construct(AttachmentRequestService $attachmentRequestService)
    {
        $this->attachmentRequestService = $attachmentRequestService;
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
            'file' => ['required',  'file', 'mimes:pdf,docx,doc', 'max:10240'],
        ];
    }
    public function attributes(): array
    {
        return  $this->attachmentRequestService->attributes();
    }
    public function failedValidation(Validator $validator)
    {
        $this->attachmentRequestService->failedValidation($validator);
    }
    public function messages(): array
    {
        $messages = $this->attachmentRequestService->messages();
        $messages['required'] = 'حقل :attribute هو حقل اجباري ';
        return $messages;
    }
}