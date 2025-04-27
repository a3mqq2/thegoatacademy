<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExamsTable extends Migration
{
    public function up()
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->enum('exam_type', ['pre', 'mid', 'final']);
            $table->date('exam_date');
            $table->unsignedBigInteger('examiner_id')->nullable();
            $table->enum('status', ['new', 'assigned', 'completed','overdue','cancelled','paused'])->default('new');
            $table->time('time')->nullable();
            $table->timestamps();
            $table->foreign('course_id')
                  ->references('id')->on('courses')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('exams');
    }
}
