<?php

namespace App\Rules;

use App\Enums\TaskStatus as EnumsTaskStatus;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TaskStatus implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        $arrayOfStatus = array_column(EnumsTaskStatus::cases(), 'value');

        $status = implode(", ", array_column(EnumsTaskStatus::cases(), 'value'));

        if (!(in_array($value, $arrayOfStatus))) {
            $fail($status . " حقل :attribute  يجب ان يكون احد القيم .");
        }
        $status = implode(", ", array_column(EnumsTaskStatus::cases(), 'value'));
    }
}
