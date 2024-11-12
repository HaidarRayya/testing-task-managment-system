<?php

namespace App\Rules;

use App\Enums\UserPermission as EnumsUserPermission;
use App\Enums\UserRole;
use App\Services\ManagePermisionService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UserPermission implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $all_permossions = ManagePermisionService::arrayPermissions();
        $developer_permossions = $all_permossions[UserRole::DEVELOPER->value];
        $tester_permossions = $all_permossions[UserRole::TESTER->value];

        $arrayOfPermission = array_unique(array_merge($developer_permossions, $tester_permossions));

        $permissions = implode(", ", $arrayOfPermission);

        if (!(in_array($value, $arrayOfPermission))) {
            $fail($permissions . " حقل :attribute  يجب ان يكون احد القيم .");
        }
    }
}
