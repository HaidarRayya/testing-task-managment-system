<?php

namespace App\Rules;

use App\Enums\UserRole;
use App\Models\Role;
use App\Services\ManagePermisionService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class checkValidPermission implements ValidationRule
{
    protected $role;
    public function __construct($role_id)
    {
        $this->role = Role::findOrFail($role_id);
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $message = "";
        $permissions = ManagePermisionService::arrayPermissions();
        if ($this->role->name == UserRole::DEVELOPER->value) {
            $message = ManagePermisionService::checkPermissions(
                $permissions[UserRole::DEVELOPER->value],
                array_unique(array_filter($value, function ($num) {
                    return $num > 0;
                }))
            );
        } else if ($this->role->name == UserRole::TESTER->value) {
            $message = ManagePermisionService::checkPermissions(
                $permissions[UserRole::TESTER->value],
                array_unique(array_filter($value, function ($num) {
                    return $num > 0;
                }))
            );
        }
        if ($message != "") {
            $fail($message);
        }
    }
}
