<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * seasons.season was a free-text varchar(50) fed by a text input, so
     * casing and spelling could drift into a column the high school grade
     * calculation has to read exactly (a Fall/Spring mixup shifts every grade by
     * a year). Constrain it at the database instead of defending against it in
     * PHP forever.
     *
     * No data change: all existing rows are already exactly 'Fall' or 'Spring'.
     */
    public function up(): void
    {
        Schema::table('seasons', function (Blueprint $table) {
            $table->enum('season', ['Fall', 'Spring'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seasons', function (Blueprint $table) {
            $table->string('season')->change();
        });
    }
};
