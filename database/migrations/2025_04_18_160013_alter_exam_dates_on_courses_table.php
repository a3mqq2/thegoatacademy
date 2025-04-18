<?php

/**
 * 2025_04_18_160000_alter_exam_dates_on_courses_table.php
 *
 * php artisan make:migration alter_exam_dates_on_courses_table --table=courses
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // 🟢 ensure all exam-date columns are pure DATE & nullable
            $table->date('pre_test_date')->nullable()->change();
            $table->date('mid_exam_date')->nullable()->change();
            $table->date('final_exam_date')->nullable()->change();
            $table->date('end_date')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // revert to DATETIME if that was the old type
            $table->dateTime('pre_test_date')->nullable()->change();
            $table->dateTime('mid_exam_date')->nullable()->change();
            $table->dateTime('final_exam_date')->nullable()->change();
            $table->dateTime('end_date')->nullable()->change();
        });
    }
};
