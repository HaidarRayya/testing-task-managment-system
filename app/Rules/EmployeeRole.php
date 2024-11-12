<?php

namespace App\Rules;

use App\Enums\UserRole as EnumsUserRole;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EmployeeRole  implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $roles = array_column(EnumsUserRole::cases(), 'value');
        $arrayOfRoles = [];
        foreach ($roles as $r) {
            if (
                $r != EnumsUserRole::ADMIN->value
            ) {
                array_push($arrayOfRoles, $r);
            }
        }
        $roles = $arrayOfRoles;
        $roles = implode(", ", $roles);

        if (!(in_array($value, $arrayOfRoles))) {
            $fail($roles . " حقل :attribute  يجب ان يكون احد القيم .");
        }
    }
}