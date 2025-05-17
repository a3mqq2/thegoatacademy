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
        Schema::table('course_schedules', function (Blueprint $table) {
            $table->dateTime('close_at')->nullable()->comment('Date when the course schedule will be closed');
            $table->dateTime('alert_at')->nullable()->comment('Date when the alert will be sent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_schedules', function (Blueprint $table) {
            $table->dropColumn('close_at');
            $table->dropColumn('alert_at');
        });
    }
};
