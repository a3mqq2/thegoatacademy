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
        Schema::table('courses', function (Blueprint $table) {
            $table->integer('warn_absent')->nullable();
            $table->integer('warn_homework')->nullable();
            $table->integer('stop_absent')->nullable();
            $table->integer('stop_homework')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('warn_absent');
            $table->dropColumn('warn_homework');
            $table->dropColumn('stop_absent');
            $table->dropColumn('stop_homework');
        });
    }
};
