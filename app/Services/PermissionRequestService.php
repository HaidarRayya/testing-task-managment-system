<?php

namespace App\Services;

use Illuminate\Http\Exceptions\HttpResponseException;

class PermissionRequestService
{
    /**
     *  get array of  PermissionRequestService attributes 
     *
     * @return array   of attributes
     */
    public function attributes()
    {
        return  [
            'name' => 'اسم السماحية',
            'description' => 'الوصف',
            'permissions' => 'ألسماحيات'
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
     *  get array of  PermissionRequestService messages 
     * @return array   of messages
     */
    public function messages()
    {
        return  [
            'min' => 'حقل :attribute يجب ان  يكون على الاقل 3 محارف',
            'max' => 'حقل :attribute يجب ان  يكون على الاكثر 255 محرف',
            'unique' => 'حقل :attribute  يجب ان يكون غير مكرر ',
            'string' => 'حقل :attribute يجب ان يكون نص'
        ];
    }
}
