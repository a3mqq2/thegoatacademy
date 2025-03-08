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
        Schema::create('quality_settings', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // نوع التقييم (Progress Test, Attendance, Homework)
            $table->integer('red_threshold');   // الحد الأدنى للون الأحمر
            $table->integer('yellow_threshold'); // الحد الأدنى للون الأصفر
            $table->integer('green_threshold'); // الحد الأدنى للون الأخضر
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_settings');
    }
};
