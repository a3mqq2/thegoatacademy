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
        Schema::table('course_type_skill', function (Blueprint $table) {
            $table->decimal('progress_test_max', 5, 2)->default(0)->after('skill_id')->comment('Maximum score for progress test');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_type_skill', function (Blueprint $table) {
            $table->dropColumn('progress_test_max');
        });
    }
};
