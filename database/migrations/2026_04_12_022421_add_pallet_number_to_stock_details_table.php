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
        Schema::table('stock_details', function (Blueprint $table) {
            $table->string('pallet_number')->nullable()->after('fdf_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_details', function (Blueprint $table) {
            $table->dropColumn('pallet_number');
        });
    }
};
