<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabela pivot task_task_type (wiele do wielu)
        Schema::create('task_task_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->foreignId('task_type_id')->constrained()->onDelete('cascade');
            $table->unique(['task_id', 'task_type_id']);
        });

        // 2. Migruj istniejące dane z tasks.task_type_id do tabeli pivot
        DB::statement('
            INSERT INTO task_task_type (task_id, task_type_id)
            SELECT id, task_type_id
            FROM tasks
            WHERE task_type_id IS NOT NULL
        ');

        // 3. Usuń kolumnę task_type_id z tabeli tasks
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['task_type_id']);
            $table->dropColumn('task_type_id');
        });

        // 4. Dodaj task_type_id do task_work_logs
        Schema::table('task_work_logs', function (Blueprint $table) {
            $table->foreignId('task_type_id')->nullable()->after('task_id')
                  ->constrained()->onDelete('set null');
        });
    }

    public function down(): void
    {
        // Odwróć zmiany w task_work_logs
        Schema::table('task_work_logs', function (Blueprint $table) {
            $table->dropForeign(['task_type_id']);
            $table->dropColumn('task_type_id');
        });

        // Przywróć kolumnę task_type_id w tasks
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('task_type_id')->nullable()->after('description')
                  ->constrained()->onDelete('set null');
        });

        // Migruj dane z powrotem (tylko pierwsza wartość z pivotu)
        DB::statement('
            UPDATE tasks t
            JOIN task_task_type ttt ON ttt.task_id = t.id
            SET t.task_type_id = ttt.task_type_id
            WHERE ttt.id = (
                SELECT MIN(id) FROM task_task_type WHERE task_id = t.id
            )
        ');

        Schema::dropIfExists('task_task_type');
    }
};
