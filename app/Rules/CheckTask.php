<?php

namespace App\Rules;

use App\Models\Task;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckTask implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $tasks_depends = $value;
        $tasks = Task::pluck('id')->toArray();
        foreach ($tasks_depends as $taskId) {
            if (!in_array($taskId, $tasks)) {
                $fail("المهمة رقم {$taskId} غير موجودة");
            }
        }
    }
}