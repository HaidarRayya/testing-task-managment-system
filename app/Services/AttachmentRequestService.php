<?php

namespace App\Services;

use Illuminate\Http\Exceptions\HttpResponseException;

class AttachmentRequestService
{
    /**
     *  get array of  AttachmentRequestService attributes 
     *
     * @return array   of attributes
     */
    public function attributes()
    {
        return  [
            'file' => 'الملف',
        ];
    }
    /**
     *  
     * @param $validator
     *
     * throw a exception
     */
    public function failedValidation($validator)
    {
        throw new HttpResponseException(response()->json(
            [
                'status' => 'error',
                'message' => "فشل التحقق يرجى التأكد من صحة القيم مدخلة",
                'errors' => $validator->errors()
            ],
            422
        ));
    }
    /**
     *  get array of  CommentRequestService messages 
     * @return array   of messages
     */
    public function messages()
    {
        return [
            'file' => 'حقل :attribute يجب ان  يكون ملف   ',
            'mimes' => 'pdf,docx,doc' . 'حقل :attribute يجب ان يكون ملف  من نوع ',
            'max' => 'حقل :attribute يجب ان  يكون حجمه اصغر من 10 ميغابايت   ',
        ];
    }
}
