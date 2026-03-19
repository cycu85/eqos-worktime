<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('task_work_logs', function (Blueprint $table) {
            $table->dropUnique('task_work_logs_task_id_work_date_unique');
        });
    }

    public function down(): void
    {
        Schema::table('task_work_logs', function (Blueprint $table) {
            $table->unique(['task_id', 'work_date']);
        });
    }
};
