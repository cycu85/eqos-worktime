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
        Schema::table('task_work_logs', function (Blueprint $table) {
            $table->tinyInteger('completed_tasks_count')->default(0)->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_work_logs', function (Blueprint $table) {
            $table->dropColumn('completed_tasks_count');
        });
    }
};
