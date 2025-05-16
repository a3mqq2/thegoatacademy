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
            $table->date('done_at')->nullable()->comment('Date when the test was completed');
            $table->date('done_by')->nullable()->comment('Date when the test was marked as done');
            $table->dateTime('will_alert_at')->nullable()->comment('Date when the alert will be sent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('progress_tests', function (Blueprint $table) {
            $table->dropColumn('done_at');
            $table->dropColumn('done_by');
            $table->dropColumn('will_alert_at');
        });
    }
};
