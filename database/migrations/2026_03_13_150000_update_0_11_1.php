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
        Schema::table('results', function (Blueprint $table) {
            $table->string('live_period')->nullable()->after('live');
            $table->dateTime('live_timer_started_at')->nullable()->after('live_period');
            $table->integer('live_timer_offset')->nullable()->after('live_timer_started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('results', function (Blueprint $table) {
            $table->dropColumn(['live_period', 'live_timer_started_at', 'live_timer_offset']);
        });
    }
};
