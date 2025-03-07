<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('course_students', function (Blueprint $table) {
            $table->unsignedBigInteger('exclude_reason_id')->nullable()->after('student_id');
            $table->unsignedBigInteger('withdrawn_reason_id')->nullable()->after('exclude_reason_id');

            $table->foreign('exclude_reason_id')
                  ->references('id')->on('exclude_reasons')
                  ->onDelete('set null');

            $table->foreign('withdrawn_reason_id')
                  ->references('id')->on('withdrawn_reasons')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('course_students', function (Blueprint $table) {
            $table->dropForeign(['exclude_reason_id']);
            $table->dropForeign(['withdrawn_reason_id']);
            $table->dropColumn(['exclude_reason_id','withdrawn_reason_id']);
        });
    }
};
