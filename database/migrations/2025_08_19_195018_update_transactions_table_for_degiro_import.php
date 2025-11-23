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
        Schema::table('transactions', function (Blueprint $table) {
            // DEGIRO-specific fields
            $table->string('symbol')->nullable()->after('position_id');
            $table->string('isin')->nullable()->after('symbol');
            $table->string('currency', 3)->default('EUR')->after('price');
            $table->decimal('total_amount', 20, 8)->nullable()->after('currency');
            $table->decimal('exchange_rate', 10, 6)->default(1)->after('total_amount');
            $table->decimal('fees', 10, 2)->default(0)->after('exchange_rate');
            $table->string('venue')->nullable()->after('fees');
            $table->text('description')->nullable()->after('venue');
            $table->json('raw_data')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'symbol',
                'isin',
                'currency',
                'total_amount',
                'exchange_rate',
                'fees',
                'venue',
                'description',
                'raw_data',
            ]);
        });
    }
};
