<?php

namespace App\Http\Controllers;

use App\Http\Requests\Role\AddPermissionsRequest;
use App\Http\Requests\Role\RemovePermissionsRequest;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    protected $roleService;
    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }
    /**
     * Display a listing of the resource.
     */
    /**
     * get all  roles
     * 
     * @param Request  $request 
     *
     * @return response  of the status of operation : roles 
     */
    public function index(Request $request)
    {
        $name = $request->input('name');
        $roles = $this->roleService->allRoles($name, false);
        return response()->json([
            'status' => 'success',
            'data' => [
                'roles' => $roles
            ]
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * create a new role
     * 
     * @param StoreRoleRequest  $request 
     *
     * @return response  of the status of operation : role
     */
    public function store(StoreRoleRequest $request)
    {
        $roleData = $request->validated();

        $role = $this->roleService->createRole($roleData);

        return response()->json([
            'status' => 'success',
            'data' => [
                'role' => $role,
            ]
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    /**
     * get a specified role
     * 
     * @param StoreRoleRequest  $request 
     *
     * @return response  of the status of operation : role and  her permissions
     */
    public function show(int $role_id)
    {

        $data = $this->roleService->oneRole($role_id);

        return response()->json([
            'status' => 'success',
            'data' => [
                'role' => $data['role'],
                'permissions' => $data['permissions']
            ]
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */

    /**
     * update a specified role
     * @param int  $role_id 
     * @param UpdateRoleRequest  $request 
     *
     * @return response  of the status of operation : role
     */
    public function update(UpdateRoleRequest $request, int $role_id)
    {
        $roleData = $request->validated();
        $role = $this->roleService->updateRole($role_id,  $roleData);
        return response()->json([
            'status' => 'success',
            'data' => [
                'role' => $role
            ]
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */

    /**
     * delete a specified role
     * @param int  $role_id 
     *
     * @return response  of the status of operation 
     */

    public function destroy(int $role_id)
    {
        $this->roleService->deleteRole($role_id);
        return response()->json(status: 204);
    }

    /**
     * show all  deleted roles
     *
     * @param Request $request 
     *
     * @return response  of the status of operation : and the roles
     */
    public function deletedRoles(Request $request)
    {
        $name = $request->input('name');

        $roles = $this->roleService->allRoles($name, true);

        return response()->json([
            'status' => 'success',
            'data' => [
                'roles' => $roles
            ]
        ], 200);
    }

    /**
     * restore a  role
     *
     * @param int $role_id 
     *
     * @return response  of the status of operation and the role
     */
    public function restoreRole($role_id)
    {
        $role = $this->roleService->restoreRole($role_id);
        return response()->json([
            'status' => 'success',
            'data' => [
                'role' => $role
            ]
        ], 200);
    }
    /**
     * force delete a role
     * 
     * @param int $role_id 
     *
     * @return response  of the status of operation 
     */

    public function forceDeleteRole($role_id)
    {
        $this->roleService->forceDeleteRole($role_id);
        return response()->json(status: 204);
    }
    /**
     * add a permission to role
     * @param AddPermissionsRequest $request
     * @param int  $role_id 
     *
     * @return response  of the status of operation and the role
     */
    public function addPermissionToRole(AddPermissionsRequest $request, int $role_id)
    {
        $permissionsData = $request->validated();
        $this->roleService->addPermissionToRole($role_id, $permissionsData);
        return response()->json([
            'status' => 'success',
        ], 200);
    }
    /**
     * remove a permission from role
     * @param RemovePermissionsRequest $request
     * @param int $role_id 
     *
     * @return response  of the status of operation and the role
     */
    public function removePermissionFromRole(RemovePermissionsRequest $request, int $role_id)
    {
        $permissionsData = $request->validated();

        $this->roleService->removePermissionFromRole($role_id, $permissionsData);
        return response()->json([
            'status' => 'success',
        ], 200);
    }
}
