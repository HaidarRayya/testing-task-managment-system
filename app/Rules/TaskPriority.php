<?php

namespace App\Rules;

use App\Enums\TaskPriority as EnumsTaskPriority;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TaskPriority implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $arrayOfPriority = array_column(EnumsTaskPriority::cases(), 'value');
        $priority = implode(", ", $arrayOfPriority);

        if (!(in_array($value, $arrayOfPriority))) {
            $fail($priority . " حقل :attribute  يجب ان يكون احد القيم .");
        }
    }
}
