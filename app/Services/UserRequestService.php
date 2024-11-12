<?php

namespace App\Services;

use Illuminate\Http\Exceptions\HttpResponseException;

class UserRequestService
{
    /**
     *  get array of  UserRequestService attributes 
     *
     * @return array   of attributes
     */
    public function attributes()
    {
        return  [
            'first_name' => 'الاسم الاول',
            'last_name' => ' الاسم الاخير',
            'email' => 'الايميل',
            'password' => 'كلمة السر',
            'role' => 'دور المستخدم',
            'user_name' => 'اسم المستخدم'
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
     *  get array of  UserRequestService messages 
     * @return array   of messages
     */
    public function messages()
    {
        return  [
            'email' => 'حقل :attribute يجب ان  يكون ايميل  ',
            'min' => 'حقل :attribute يجب ان  يكون على الاقل 3 محرف',
            'max' => 'حقل :attribute يجب ان  يكون على الاكثر 15 محرف',
            'unique' => 'حقل :attribute  مكرر ',
            'string' => 'حقل :attribute  يجب ان يكون نص ',
            'regex' => 'حقل :attribute  يجب ان يكون يحتوي على حرف صغير وحرف كبير ورمز ورقم واحد عالاقل ',
        ];
    }
}