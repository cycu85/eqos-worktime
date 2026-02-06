<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('task_types', function (Blueprint $table) {
            $table->decimal('value', 10, 2)->nullable()->default(0)->after('active');
        });
    }

    public function down(): void
    {
        Schema::table('task_types', function (Blueprint $table) {
            $table->dropColumn('value');
        });
    }
};
