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
        Schema::table('positions', function (Blueprint $table) {
            // Drop the unique constraint first
            $table->dropUnique(['portfolio_id', 'symbol']);
            
            // Make symbol nullable
            $table->string('symbol')->nullable()->change();
            
            // Add new unique constraint that handles nullable symbol
            $table->unique(['portfolio_id', 'symbol', 'isin'], 'positions_portfolio_symbol_isin_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            // Drop the new unique constraint
            $table->dropUnique('positions_portfolio_symbol_isin_unique');
            
            // Make symbol not nullable again
            $table->string('symbol')->nullable(false)->change();
            
            // Restore original unique constraint
            $table->unique(['portfolio_id', 'symbol']);
        });
    }
};
