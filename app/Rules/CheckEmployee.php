<?php

namespace App\Rules;

use App\Enums\TaskWorkStatus;
use App\Models\Task;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckEmployee implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $tasks = Task::where('assigned_to', '=', $value)
            ->whereIn('work_status', [TaskWorkStatus::ACTIVE->value, TaskWorkStatus::IDLE->value])
            ->get();
        if ($tasks->isNotEmpty()) {
            $fail("هذا الموظف لديه مهمة اخرى موكله اليه");
        }
    }
}