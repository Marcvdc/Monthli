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
            if (! Schema::hasColumn('transactions', 'import_batch_id')) {
                $table->uuid('import_batch_id')->nullable()->after('id');
            }

            if (! Schema::hasColumn('transactions', 'source_file_name')) {
                $table->string('source_file_name')->nullable()->after('import_batch_id');
            }

            if (! Schema::hasColumn('transactions', 'source_file_hash')) {
                $table->string('source_file_hash', 64)->nullable()->after('source_file_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'import_batch_id',
                'source_file_name',
                'source_file_hash',
            ]);
        });
    }
};
