<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoursesTable extends Migration
{
    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('group_type_id')->constrained()->onDelete('cascade');
            // If your 'instructor_id' references the 'users' table, use 'constrained('users')':
            $table->foreignId('instructor_id')->constrained('users')->onDelete('cascade');
            $table->date('start_date');
            $table->date('mid_exam_date');
            $table->date('final_exam_date');
            $table->date('end_date');
            $table->enum('status', ['ongoing','canceled','completed','upcoming'])->default('upcoming');
            $table->unsignedInteger('student_capacity');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('courses');
    }
}




