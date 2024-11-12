<?php

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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // drop the keys
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
        });

        // drop the actual columns
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role_id');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('name');
        });
    }
};
