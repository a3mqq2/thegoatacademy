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
        Schema::create('progress_test_student_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('progress_test_student_id')->constrained('progress_test_students')->onDelete('cascade');
            $table->foreignId('course_type_skill_id')->constrained('course_type_skill')->onDelete('cascade');
            $table->decimal('progress_test_grade', 5, 2)->default(0)->comment('Grade for the progress test');
            $table->decimal('max_grade', 5, 2)->default(0)->comment('Grade for the midterm exam');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progress_test_student_grades');
    }
};
