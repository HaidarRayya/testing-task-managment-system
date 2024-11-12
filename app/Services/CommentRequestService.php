<?php

namespace App\Services;

use Illuminate\Http\Exceptions\HttpResponseException;

class CommentRequestService
{
    /**
     *  get array of  CommentRequestService attributes 
     *
     * @return array   of attributes
     */
    public function attributes()
    {
        return  [
            'description' => 'الوصف',
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
            'min' => 'حقل :attribute يجب ان  يكون على الاقل 3 محرف',
            'string' => 'حقل :attribute يجب ان يكون نص'
        ];
    }
}