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
        // add dedupe_status column to result_events table
        Schema::table('result_events', function (Blueprint $table) {
            $table->after('notes', function (Blueprint $table) {
                $table->string('dedupe_status')->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('result_events', function (Blueprint $table) {
            $table->dropColumn('dedupe_status');
        });
    }
};
