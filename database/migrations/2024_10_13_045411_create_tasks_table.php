<?php

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Enums\TaskType;
use App\Enums\TaskWorkStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('description');
            $table->enum('priority', array_column(TaskPriority::cases(), 'value'));
            $table->enum('type', array_column(TaskType::cases(), 'value'));
            $table->enum('status', array_column(TaskStatus::cases(), 'value'))->default(TaskStatus::OPEN->value);
            $table->enum('work_status', array_column(TaskWorkStatus::cases(), 'value'))->default(TaskWorkStatus::IDLE->value);
            $table->timestamp('due_date');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->foreign('assigned_to')->references('id')->on('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_tasks');
    }
};