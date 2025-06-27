<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // تعديل الأعمدة لتقبل القيم الفارغة
        Schema::table('course_type_skill', function (Blueprint $table) {
            $table->decimal('progress_test_max', 8, 2)->nullable()->change();
            $table->decimal('mid_max', 8, 2)->nullable()->change();
            $table->decimal('final_max', 8, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // إعادة الأعمدة كما كانت (اختياري)
        Schema::table('course_type_skill', function (Blueprint $table) {
            $table->decimal('progress_test_max', 8, 2)->nullable(false)->change();
            $table->decimal('mid_max', 8, 2)->nullable(false)->change();
            $table->decimal('final_max', 8, 2)->nullable(false)->change();
        });
    }
};