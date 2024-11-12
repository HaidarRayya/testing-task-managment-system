<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Http\Resources\PermissionResource;
use App\Jobs\SendErrorMessage;
use App\Models\Permission;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Exceptions\HttpResponseException;

class PermissionService
{
    /**
     * show all permissions
     * @param string $name 
     * @param bool $deletedPermissions 
     * @return PermissionResource $permissions 
     */
    public function allPermissions($name, $deletedPermissions)
    {
        try {
            if ($deletedPermissions) {
                $permissions = Permission::onlyTrashed()->notAdminPermission();
            } else {
                $permissions = Permission::notAdminPermission();
            }
            $permissions = $permissions
                ->byName($name)
                ->get();
            $permissions = PermissionResource::collection($permissions);

            return  $permissions;
        } catch (Exception $e) {
            Log::error("error in get all permissions"  . $e->getMessage());

            SendErrorMessage::dispatch("error in get all permissions" . $e->getMessage());

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
     * show a permission 
     * @param int $permission_id      
     * @return PermissionResource $permission 
     */
    public function onePermission($permission_id)
    {
        try {
            $permission = Permission::findOrFail($permission_id);
        } catch (ModelNotFoundException $e) {
            Log::error("error in  show a  permission"  . $e->getMessage());
            SendErrorMessage::dispatch("error in  show a  permission" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }

        try {

            $permission = PermissionResource::make($permission);
            return $permission;
        } catch (Exception $e) {
            Log::error("error in  show a  permission"  . $e->getMessage());
            SendErrorMessage::dispatch("error in  show a  permission" . $e->getMessage());

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
     * create a  new permission
     * @param  array $permissionData  
     * @return PermissionResource permission  
     */
    public function createPermission($permissionData)
    {
        try {
            $permission = Permission::create($permissionData);
            $permission  = PermissionResource::make($permission);
            return  $permission;
        } catch (Exception $e) {
            Log::error("error in create a  permission"  . $e->getMessage());
            SendErrorMessage::dispatch("error in  create a  permission" . $e->getMessage());

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
     * update a permission
     * @param int $permission_id      
     * @param  array $permissionData  
     * @return PermissionResource permission  
     */
    public function updatePermission(int $permission_id, $permissionData)
    {
        try {
            $permission = Permission::findOrFail($permission_id);
        } catch (ModelNotFoundException $e) {
            Log::error("error in   update a  permission"  . $e->getMessage());
            SendErrorMessage::dispatch("error in  update a  permission" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }

        try {

            $permission->update($permissionData);
            $permission = PermissionResource::make(Permission::find($permission->id));
            return  $permission;
        } catch (Exception $e) {
            Log::error("error in   update a  permission"  . $e->getMessage());
            SendErrorMessage::dispatch("error in  update a  permission" . $e->getMessage());

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
     *  delete a  permission
     * @param int $permission_id      
     */
    public function deletePermission(int $permission_id)
    {
        try {
            $permission = Permission::findOrFail($permission_id);
        } catch (ModelNotFoundException $e) {
            Log::error("error in soft delete a  permission"  . $e->getMessage());
            SendErrorMessage::dispatch("error in  soft delete a  permission" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }
        $allPermissions = ManagePermisionService::arrayPermissions();
        if (in_array($permission->name, $allPermissions[UserRole::ADMIN->value])) {
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "لا يمكنك القيام بهذه العملية",
                ],
                422
            ));
        }
        try {
            $permission->delete();
        } catch (Exception $e) {
            Log::error("error in  soft delete a  permission"  . $e->getMessage());
            SendErrorMessage::dispatch("error in soft delete a  permission" . $e->getMessage());

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
     * restore a permission
     * @param int $permission_id      
     * @return PermissionResource $permission
     */
    public function restorePermission($permission_id)
    {
        try {
            $permission = Permission::withTrashed()->findOrFail($permission_id);
        } catch (ModelNotFoundException $e) {
            Log::error("error in restore a permission"  . $e->getMessage());

            SendErrorMessage::dispatch("error in  restore a  permission" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }
        try {
            $permission->restore();
            return PermissionResource::make($permission);
        } catch (Exception $e) {
            Log::error("error in restore a permission"  . $e->getMessage());

            SendErrorMessage::dispatch("error in  restore a  permission" . $e->getMessage());

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
     * delete a  permission
     * @param int $permission_id  
     */
    public function forceDeletePermission($permission_id)
    {
        try {
            $permission = Permission::withTrashed()->findOrFail($permission_id);
        } catch (ModelNotFoundException $e) {
            Log::error("error in delete a  permission"  . $e->getMessage());
            SendErrorMessage::dispatch("error in  delete a  permission" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }
        $allPermissions = ManagePermisionService::arrayPermissions();
        if (in_array($permission->name, $allPermissions[UserRole::ADMIN->value])) {
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "لا يمكنك القيام بهذه العملية",
                ],
                422
            ));
        }
        try {
            $permission->forceDelete();
        } catch (Exception $e) {
            Log::error("error in delete a  permission"  . $e->getMessage());
            SendErrorMessage::dispatch("error in  delete a  permission" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        }
    }
}
