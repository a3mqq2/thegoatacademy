<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseSchedulesTable extends Migration
{
    public function up()
    {
        Schema::create('course_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->string('day');
            $table->date('date');
            $table->string('from_time');
            $table->string('to_time');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('course_schedules');
    }
}