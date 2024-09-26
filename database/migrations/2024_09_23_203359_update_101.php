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
        // Add managed column to players table
        Schema::table('players', function (Blueprint $player) {
            $player->after('photo', function (Blueprint $table) {
                $table->boolean('managed');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop managed column
        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn('managed');
        });
    }
};
