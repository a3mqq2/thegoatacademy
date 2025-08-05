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
        Schema::table('progress_test_students', function (Blueprint $table) {
            $table->enum('status', ['present', 'absent'])
                  ->default('present')
                  ->after('student_id')
                  ->comment('Indicates whether the student was present or absent for the progress test');  
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('progress_test_students', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
