<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * formations.name has to stay purely numeric (433, 4231, ...) because the
     * formation drawer reads each digit as a row's player count. Variants of the
     * same shape (433 defensive vs 433 attack) get told apart by a free-text
     * description instead.
     */
    public function up(): void
    {
        Schema::table('formations', function (Blueprint $table) {
            $table->string('description')->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('formations', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
