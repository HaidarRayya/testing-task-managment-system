<?php

namespace App\Services;


use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    /**
     * login a user
     * @param array $credentials 
     * @return array  token and UserResource user
     * 
     */
    public function login(array $credentials)
    {
        try {
        $user = '';
        $token = JWTAuth::attempt($credentials);
        if ($token) {
            $user = UserResource::make(auth()->user());
        }
        return [
            'token' => $token,
            'user' => $user
        ];
        } catch (Exception $e) {
            Log::error("error in get login" . $e->getMessage());
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        }
    }

    /**
     * get a role of a user
     * @param  int $id 
     * @return string  role
     * 
     */
    public static function  user_role($id)
    {
        $user = User::find($id);
        $user->load('role');
        return  $user->role->name;
    }

    /**
     * get  a user permissions
     * @param  $registerData 
     * @return array  permissions
     * 
     */
    public static function  user_permissions($role)
    {

        $permissions = Role::where('name', '=', $role)->with(['permissions' => function ($q) {
            $q->select('name');
        }])->first();
        $permissions = $permissions->permissions;
        $temp = [];
        foreach ($permissions as $permission) {
            array_push($temp, $permission->name);
        }
        $permissions = $temp;
        return  $permissions;
    }


    /**
     * check if user can do this operation
     * @param string  $operation 
     * 
     */
    public static function  canDo($operation)
    {
        $permissions = self::user_permissions(self::user_role(Auth::user()->id));
        if (!in_array($operation, $permissions)) {
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' =>   "لا يمكنك القيام بهذه العملية حاليا . لا تمتك السماحيات المناسبة",
                ],
                403
            ));
        }
    }
}