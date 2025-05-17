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
        Schema::table('progress_tests', function (Blueprint $table) {
            $table->dateTime('close_at')->nullable()->comment('Date when the test will be closed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('progress_tests', function (Blueprint $table) {
            $table->dropColumn('close_at');
        });
    }
};
