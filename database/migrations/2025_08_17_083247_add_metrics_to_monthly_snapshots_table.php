<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monthly_snapshots', function (Blueprint $table) {
            $table->decimal('mom', 20, 8)->nullable()->after('value');
            $table->decimal('ytd', 20, 8)->nullable()->after('mom');
            $table->decimal('drawdown', 20, 8)->nullable()->after('ytd');
            $table->decimal('volatility', 20, 8)->nullable()->after('drawdown');
        });
    }

    public function down(): void
    {
        Schema::table('monthly_snapshots', function (Blueprint $table) {
            $table->dropColumn(['mom', 'ytd', 'drawdown', 'volatility']);
        });
    }
};
