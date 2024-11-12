<?php

namespace App\Services;

use App\Enums\UserPermission;
use App\Enums\UserRole;
use App\Models\Permission;
use Exception;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class ManagePermisionService
{
    /**
     * get a all permissions
     * @return array  permissions
     * 
     */
    public static function arrayPermissions()
    {
        return [
            UserRole::ADMIN->value => [
                UserPermission::GET_COMMENT->value,
                UserPermission::CREATE_COMMENT->value,
                UserPermission::UPDATE_COMMENT->value,
                UserPermission::DELETE_COMMENT->value,
                UserPermission::GET_ATTACHMENT->value,
                UserPermission::CREATE_ATTACHMENT->value,
                UserPermission::UPDATE_ATTACHMENT->value,
                UserPermission::DELETE_ATTACHMENT->value,
                UserPermission::DOWNLOAD_ATTACHMENT->value,
                UserPermission::GET_TASK->value,
                UserPermission::CREATE_TASK->value,
                UserPermission::UPDATE_TASK->value,
                UserPermission::DELETE_TASK->value,
                UserPermission::ASSIGN_TASK->value,
                UserPermission::REASSIGN_TASK->value,
                UserPermission::CREATE_REPORTS->value,
                UserPermission::END_TASK->value,
                UserPermission::RESTORE_TASK->value,
                UserPermission::GET_DELELTED_TASK->value,
                UserPermission::GET_USER->value,
                UserPermission::CREATE_USER->value,
                UserPermission::UPDATE_USER->value,
                UserPermission::DELETE_USER->value,
                UserPermission::RESTORE_USER->value,
                UserPermission::GET_DELELTED_USER->value,
                UserPermission::FINAL_DELETE_USER->value,
            ],
            UserRole::DEVELOPER->value => [
                UserPermission::GET_COMMENT->value,
                UserPermission::GET_ATTACHMENT->value,
                UserPermission::CREATE_ATTACHMENT->value,
                UserPermission::UPDATE_ATTACHMENT->value,
                UserPermission::DELETE_ATTACHMENT->value,
                UserPermission::DOWNLOAD_ATTACHMENT->value,
                UserPermission::GET_TASK->value,
                UserPermission::START_WORK_TASK->value,
                UserPermission::END_WORK_TASK->value,
            ],
            UserRole::TESTER->value => [
                UserPermission::GET_COMMENT->value,
                UserPermission::CREATE_COMMENT->value,
                UserPermission::UPDATE_COMMENT->value,
                UserPermission::DELETE_COMMENT->value,
                UserPermission::GET_ATTACHMENT->value,
                UserPermission::DOWNLOAD_ATTACHMENT->value,
                UserPermission::GET_TASK->value,
                UserPermission::START_TEST_TASK->value,
                UserPermission::END_TEST_TASK->value,
            ],
        ];
    }
    /**
     * check if permission can assign to role 
     *  @param  array $valid_permissions
     *  @param  array $entred_permissions
     * @return string  message
     */
    public static function checkPermissions($valid_permissions, $entred_permissions)
    {
        try {
            $valid_permissions_id = Permission::whereIn('name', $valid_permissions)
                ->select('id')->get();

            $permissions_id = [];
            foreach ($valid_permissions_id as $i) {
                array_push($permissions_id, $i->id);
            }
            $message = "";
            foreach ($entred_permissions as $i) {
                if (!in_array($i, $permissions_id)) {
                    $p = Permission::find($i)->name;
                    $message .=  '\n' . " الى هذا الدور " . $p  . " لا يمكنك اضافة السماحية";
                }
            }
            return   $message;
        } catch (Exception $e) {
            Log::error("error " . $e->getMessage());
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' =>   "يرجى التأكد من السماحيات المدخلة"
                ],
                422
            ));
        }
    }
}
