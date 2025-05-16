<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1) Rename existing 'pending' values to 'assigned'
        DB::table('exams')
            ->where('status', 'pending')
            ->update(['status' => 'assigned']);

        // 2) Update the ENUM definition, dropping 'pending'
        Schema::table('exams', function (Blueprint $table) {
            $table->enum('status', ['new', 'assigned', 'in_progress','overdue', 'completed'])
                  ->default('assigned')
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1) Rename 'assigned' back to 'pending'
        DB::table('exams')
            ->where('status', 'assigned')
            ->update(['status' => 'pending']);

        // 2) Revert the ENUM definition to include 'pending'
        Schema::table('exams', function (Blueprint $table) {
            $table->enum('status', ['new', 'pending', 'in_progress','overdue', 'completed'])
                  ->default('pending')
                  ->change();
        });
    }
};
