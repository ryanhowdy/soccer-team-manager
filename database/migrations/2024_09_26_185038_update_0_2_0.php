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
        // add against column to result_events table
        Schema::table('result_events', function (Blueprint $events) {
            $events->after('player_id', function (Blueprint $table) {
                $table->boolean('against')->default(0);
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // drop against column
        Schema::table('result_events', function (Blueprint $table) {
            $table->dropColumn('against');
        });
    }
};
