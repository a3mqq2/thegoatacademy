<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseTypeSkillTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('course_type_skill', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to course_types table
            $table->foreignId('course_type_id')->constrained()->onDelete('cascade');
            
            // Foreign key to skills table
            $table->foreignId('skill_id')->constrained()->onDelete('cascade');
            
            // Additional fields for the grades
            $table->decimal('mid_max', 8, 2);
            $table->decimal('final_max', 8, 2);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('course_type_skill');
    }
}
