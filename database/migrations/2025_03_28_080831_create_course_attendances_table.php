<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseAttendancesTable extends Migration
{
    public function up()
    {
        Schema::create('course_attendances', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('course_schedule_id');

            $table->enum('attendance', ['present', 'absent'])->default('absent');
            $table->boolean('homework_submitted')->default(false);
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['course_id', 'student_id', 'course_schedule_id'], 'course_attendance_unique');

            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('course_schedule_id')->references('id')->on('course_schedules')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('course_attendances');
    }
}

