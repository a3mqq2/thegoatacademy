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
        Schema::create('exam_student_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_student_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('course_type_skill_id');
            $table->foreign('course_type_skill_id')
                  ->references('id')
                  ->on('course_type_skill') // التعديل هنا إلى الاسم الصحيح
                  ->onDelete('cascade');            
            $table->decimal('grade', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_student_grades');
    }
};
