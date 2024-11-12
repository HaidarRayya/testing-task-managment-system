<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Http\Resources\TaskResource;
use App\Http\Resources\UserResource;
use App\Jobs\SendErrorMessage;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;


class UserService
{
    /**
     * get all  users
     * @param array $fillter
     * @param bool $deletedUsers
     * @return  UserResource $users
     */
    public function allUsers(array $fillter, $deletedUsers)
    {
        try {
            if ($fillter['role'] == null) {
                $op = "!=";
                $val = UserRole::ADMIN->value;
            } else {
                $op = "=";
                $val = $fillter['role'];
            }
            if ($deletedUsers) {
                $users = Cache::remember('deleted_users', 3600, function () use ($fillter) {
                    return User::onlyTrashed()->with('role')
                        ->byUserName($fillter['user_name'])
                        ->get();;
                });
            } else {
                $users = Cache::remember('users', 3600, function () use ($op, $val, $fillter) {
                    return User::with('role')
                        ->whereRelation('role', 'name', $op, $val)
                        ->byUserName($fillter['user_name'])
                        ->get();
                });
            }
            $users = UserResource::collection($users);
            return $users;
        } catch (Exception $e) {
            Log::error("error in get all users" . $e->getMessage());

            SendErrorMessage::dispatch("error in get all users"  . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        } catch (RelationNotFoundException $e) {
            Log::error("error in get all users" . $e->getMessage());
            SendErrorMessage::dispatch("error in get all users"  . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any relation",
                ],
                404
            ));
        }
    }

    /**
     * create  a  new user
     * @param array $data 
     * @return   UserResource $user
     */
    public function createUser($data)
    {

        try {
            $user = new User();
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->password = $data['password'];
            $role = '';
            if ($data['role'] == "developer") {
                $role = Role::userRole(UserRole::DEVELOPER->value)->first();
            } else if ($data['role'] == "tester") {
                $role = Role::userRole(UserRole::TESTER->value)->first();
            }
            $user->role_id = $role->id;
            $user->save();

            Cache::forget('users');
            $user = UserResource::make($user->load('role'));
            return $user;
        } catch (Exception $e) {
            Log::error("error in create user" . $e->getMessage());
            SendErrorMessage::dispatch("error in create user" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        } catch (RelationNotFoundException $e) {
            Log::error("error in  create a user" . $e->getMessage());
            SendErrorMessage::dispatch("error in create user" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any relation",
                ],
                404
            ));
        }
    }
    /**
     * show  a  user
     * @param User $user 
     * @return  array of  TaskResource $tasks and  UserResource $user 
     */
    public function oneUser($user_id)
    {
        try {
            $user =  User::findOrFail($user_id);
        } catch (ModelNotFoundException $e) {
            Log::error("error in get a user" . $e->getMessage());
            SendErrorMessage::dispatch("error in get a user" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }
        try {
            $user = Cache::remember('user_' . $user_id, 600, function () use ($user) {
                return   $user->load(['tasks', 'role']);
            });
            $user = UserResource::make($user);
            $tasks = TaskResource::collection($user->tasks);
            return [
                'user' => $user,
                'tasks' => $tasks,
            ];
        } catch (Exception $e) {
            Log::error("error in get a user" . $e->getMessage());
            SendErrorMessage::dispatch("error in get a user" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        } catch (RelationNotFoundException $e) {
            Log::error("error in get a user" . $e->getMessage());
            SendErrorMessage::dispatch("error in get a user" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any relation",
                ],
                404
            ));
        }
    }

    /**
     * update  a  user
     * @param array $data 
     * @param User $user 
     * @return  UserResource $user
     */
    public function updateUser($data, $user_id)
    {
        try {
            $user =  User::findOrFail($user_id);
        } catch (ModelNotFoundException $e) {
            Log::error("error in update a user" . $e->getMessage());
            SendErrorMessage::dispatch("error in get a user" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }
        try {
            $user = $user->load('role');
            $user->update($data);
            if (Cache::has('user_' . $user_id)) {
                $user = Cache::remember('user_' . $user_id, 600, function () use ($user) {
                    return  $user->load('tasks');
                });
            }
            Cache::forget('users');
            $user = UserResource::make($user);
            return $user;
        } catch (Exception $e) {
            Log::error("error in update a user"  . $e->getMessage());
            SendErrorMessage::dispatch("error in update a user" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        } catch (RelationNotFoundException $e) {
            Log::error("error in update a user"  . $e->getMessage());
            SendErrorMessage::dispatch("error in update a user" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any relation",
                ],
                404
            ));
        }
    }
    /**
     * delete  a user
     * @param int $user_id 
     */
    public function deleteUser($user_id)
    {
        try {
            $user =  User::findOrFail($user_id);
        } catch (ModelNotFoundException $e) {
            Log::error("error in get a user" . $e->getMessage());
            SendErrorMessage::dispatch("error in get a user" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }
        try {
            $user->delete();
            Cache::forget('user_' . $user_id);
            Cache::forget('users');
        } catch (Exception $e) {
            Log::error("error in delete a user"  . $e->getMessage());
            SendErrorMessage::dispatch("error in delete a user" . $e->getMessage());

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
     * restore a user
     * @param int $user_id      
     * @return UserResource $user
     */
    public function restoreUser($user_id)
    {
        try {
            $user = User::withTrashed()->findOrFail($user_id);
        } catch (ModelNotFoundException $e) {
            Log::error("error in restore a user"  . $e->getMessage());
            SendErrorMessage::dispatch("error in restore a user" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }
        try {
            $user->restore();
            $user->load('role');
            return UserResource::make($user);
        } catch (Exception $e) {
            Log::error("error in restore a user"  . $e->getMessage());
            SendErrorMessage::dispatch("error in restore a user" . $e->getMessage());

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
     * final delelte a user
     * @param int $user_id      
     */
    public function forceDeleteUser($user_id)
    {
        try {
            $user = User::withTrashed()->findOrFail($user_id);
        } catch (ModelNotFoundException $e) {
            Log::error("error in final delete a user" . $e->getMessage());
            SendErrorMessage::dispatch("error in final delete a user" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }
        try {
            $user->forceDelete();
        } catch (Exception $e) {
            Log::error("error in final delete a user" . $e->getMessage());
            SendErrorMessage::dispatch("error in final delete a user" . $e->getMessage());

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