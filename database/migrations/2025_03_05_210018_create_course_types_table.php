<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('course_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->integer('duration')->notes('Duration in Weeks');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('course_types');
    }
};
