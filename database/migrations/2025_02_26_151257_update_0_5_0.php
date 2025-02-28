<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Event;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $gain = Event::create([
            'event'      => 'gain_possession',
            'additional' => null,
        ]);
        $lose = Event::create([
            'event'      => 'lose_possession',
            'additional' => null,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
