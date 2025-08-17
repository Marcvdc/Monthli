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
        Schema::create('price_ticks', function (Blueprint $table) {
            $table->id();
            $table->string('symbol');
            $table->date('date');
            $table->decimal('price', 20, 8);
            $table->timestamps();

            $table->unique(['symbol', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_ticks');
    }
};
