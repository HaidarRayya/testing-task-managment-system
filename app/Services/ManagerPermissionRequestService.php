<?php

namespace App\Services;

use Illuminate\Http\Exceptions\HttpResponseException;

class ManagerPermissionRequestService
{
    /**
     *  get array of  PermissionRequestService attributes 
     *
     * @return array   of attributes
     */
    public function attributes()
    {
        return  [
            'permissions' => 'السماحيات ',
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
            'required' => 'حقل :attribute هو حقل اجباري ',
            'array' => 'حقل :attribute يجب ان يكون مصفوفة',
        ];
    }
}