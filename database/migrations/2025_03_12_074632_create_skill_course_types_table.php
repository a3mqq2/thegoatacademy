<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSkillCourseTypesTable extends Migration
{
    public function up()
    {
        Schema::create('skill_course_types', function (Blueprint $table) {
            $table->unsignedBigInteger('skill_id');
            $table->unsignedBigInteger('course_type_id');

            $table->foreign('skill_id')->references('id')->on('skills')->onDelete('cascade');
            $table->foreign('course_type_id')->references('id')->on('course_types')->onDelete('cascade');

            $table->primary(['skill_id', 'course_type_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('skill_course_types');
    }
}
