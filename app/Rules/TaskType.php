<?php

namespace App\Rules;

use App\Enums\TaskType as EnumsTaskType;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TaskType implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $arrayOfTypes = array_column(EnumsTaskType::cases(), 'value');
        $types = implode(", ", $arrayOfTypes);

        if (!(in_array($value, $arrayOfTypes))) {
            $fail($types . " حقل :attribute  يجب ان يكون احد القيم .");
        }
    }
}
