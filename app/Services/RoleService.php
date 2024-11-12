<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Http\Resources\RoleResource;
use App\Http\Resources\PermissionResource;
use App\Jobs\SendErrorMessage;
use App\Models\Permission;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Models\Role;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;

class RoleService
{
    /**
     * show all roles
     * @param string $name  
     * @return RoleResource $roles 
     */
    public function allRoles($name, $deletedRole)
    {
        try {
            if ($deletedRole) {
                $roles = Role::onlyTrashed()->notAdminRole();
            } else {
                $roles = Role::notAdminRole();
            }
            $roles = $roles
                ->byName($name)
                ->get();
            $roles = RoleResource::collection($roles);
            return  $roles;
        } catch (Exception $e) {
            Log::error("error in get all roles"  . $e->getMessage());

            SendErrorMessage::dispatch("error in get all roles" . $e->getMessage());
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
     * show a role and all  her permissions
     * @param  int $role  
     * @return array RoleResource $role and PermissionResource $permissions
     */
    public function oneRole($role_id)
    {
        try {
            $role = Role::findOrFail($role_id);
        } catch (ModelNotFoundException $e) {
            Log::error("error in  show a role" . $e->getMessage());
            SendErrorMessage::dispatch("error in  show a role" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }
        if ($role->name == UserRole::ADMIN->value) {
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "لا يمكنك القيام بهذه العملية",
                ],
                422
            ));
        }
        try {
            $permissions = $role->load('permissions')->permissions;
            $role = RoleResource::make($role);

            $permissions = $permissions->isNotEmpty() ? PermissionResource::collection($permissions) : [];

            return [
                'role' => $role,
                'permissions' =>  $permissions
            ];
        } catch (Exception $e) {
            Log::error("error in  show a  role"  . $e->getMessage());

            SendErrorMessage::dispatch("error in  show a role" . $e->getMessage());

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
     * create a  new role
     * @param  array $roleData  
     * @return RoleResource role  
     */
    public function createRole($roleData)
    {
        try {
            $role = Role::create($roleData);
            $role  = RoleResource::make($role);
            return  $role;
        } catch (Exception $e) {
            Log::error("error in create a  role"  . $e->getMessage());
            SendErrorMessage::dispatch("error in  create a role" . $e->getMessage());

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
     * update a role
     * @param int $role_id  
     * @param  array $roleData  
     * @return RoleResource role  
     */
    public function updateRole(int $role_id, $roleData)
    {
        try {
            $role = Role::findOrFail($role_id);
        } catch (ModelNotFoundException $e) {
            Log::error("error in  update a role" . $e->getMessage());
            SendErrorMessage::dispatch("error in  update a role" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }
        if ($role->name == UserRole::ADMIN->value) {
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "لا يمكنك القيام بهذه العملية",
                ],
                422
            ));
        }
        try {
            $role->update($roleData);
            $role = RoleResource::make(Role::find($role->id));
            return  $role;
        } catch (Exception $e) {
            Log::error("error in   update a  role"  . $e->getMessage());
            SendErrorMessage::dispatch("error in  update a role" . $e->getMessage());

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
     *  delete a  role
     * @param int $role_id 
     */
    public function deleteRole(int $role_id)
    {
        try {
            $role = Role::findOrFail($role_id);
        } catch (ModelNotFoundException $e) {
            Log::error("error in  delete a  role" . $e->getMessage());
            SendErrorMessage::dispatch("error in soft  delete a role" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }
        if ($role->name == UserRole::ADMIN->value) {
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "لا يمكنك القيام بهذه العملية",
                ],
                422
            ));
        }
        try {
            $role->delete();
        } catch (Exception $e) {
            Log::error("error in  soft delete a  role"  . $e->getMessage());
            SendErrorMessage::dispatch("error in soft delete a role" . $e->getMessage());

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
     * restore a role
     * @param int $role_id      
     * @return RoleResource $role
     */
    public function restoreRole($role_id)
    {
        try {
            $role = Role::withTrashed()->findOrFail($role_id);
        } catch (ModelNotFoundException $e) {
            Log::error("error in restore a role"  . $e->getMessage());
            SendErrorMessage::dispatch("error in  restore a role" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }

        try {
            $role->restore();
            return RoleResource::make($role);
        } catch (Exception $e) {
            Log::error("error in restore a role"  . $e->getMessage());
            SendErrorMessage::dispatch("error in  restore a role" . $e->getMessage());

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
     * delete a  role
     * @param int $role  
     */
    public function forceDeleteRole($role_id)
    {
        try {
            $role = Role::withTrashed()->findOrFail($role_id);
        } catch (ModelNotFoundException $e) {
            Log::error("error in  final delete a  role" . $e->getMessage());
            SendErrorMessage::dispatch("error in  final delete a role" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }
        if ($role->name == UserRole::ADMIN->value) {
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "لا يمكنك القيام بهذه العملية",
                ],
                422
            ));
        }
        try {
            $role->forceDelete();
        } catch (Exception $e) {
            Log::error("error in  final delete a  role" . $e->getMessage());
            SendErrorMessage::dispatch("error in  final delete a role" . $e->getMessage());

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
     *  add permission to role
     * @param int $role_id  
     *  @param array permissionsData
     */
    public function addPermissionToRole($role_id, $permissionsData)
    {
        try {
            $role = Role::findOrFail($role_id);
        } catch (ModelNotFoundException $e) {
            Log::error("error in add permission to role" . $e->getMessage());
            SendErrorMessage::dispatch("error in  add permission to role" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }
        if ($role->name == UserRole::ADMIN->value) {
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "لا يمكنك القيام بهذه العملية",
                ],
                422
            ));
        }
        try {
            $permissionsData = array_unique(array_filter($permissionsData, function ($num) {
                return $num > 0;
            }));

            foreach ($permissionsData as $i) {
                $role->permissions()->attach(Permission::find($i));
            }
        } catch (Exception $e) {
            Log::error("error in add permission to role" . $e->getMessage());
            SendErrorMessage::dispatch("error in  add permission to role" . $e->getMessage());

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
     *  remove permission from role
     * @param int $role_id  
     *  @param array permissionsData
     */
    public function removePermissionFromRole($role_id, $permissionsData)
    {
        try {
            $role = Role::findOrFail($role_id);
        } catch (ModelNotFoundException $e) {
            Log::error("error in remove permission from role" . $e->getMessage());
            SendErrorMessage::dispatch("error in  remove permission from role" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }
        if ($role->name == UserRole::ADMIN->value) {
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "لا يمكنك القيام بهذه العملية",
                ],
                422
            ));
        }
        try {
            $permissionsData = array_unique(array_filter($permissionsData, function ($num) {
                return $num > 0;
            }));
            foreach ($permissionsData as $i) {
                $role->permissions()->detach(Permission::find($i));
            }
        } catch (Exception $e) {
            Log::error("error in remove permission from role" . $e->getMessage());
            SendErrorMessage::dispatch("error in  remove permission from role" . $e->getMessage());

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
