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
            $table->unsignedBigInteger('done_by')->change()->nullable()->comment('User who marked the test as done');
            $table->foreign('done_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('progress_tests', function (Blueprint $table) {
            $table->date('done_by')->nullable()->comment('Date when the test was marked as done');
        });
    }
};
