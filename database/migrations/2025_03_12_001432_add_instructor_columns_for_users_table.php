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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('age')->nullable()->after('name');
            $table->enum('gender', ['male', 'female'])->nullable()->after('age');
            $table->string('nationality')->nullable()->after('gender');
            $table->string('video')->nullable()->after('nationality');
            $table->text('notes')->nullable()->after('video');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['age', 'gender', 'nationality', 'video', 'notes']);
        });
    }
};
