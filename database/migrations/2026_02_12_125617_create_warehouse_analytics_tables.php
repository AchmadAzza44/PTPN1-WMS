<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_create_warehouse_analytics_tables.php
    public function up()
    {
        // Tabel untuk setting kapasitas maksimal gudang
        Schema::create('warehouse_capacities', function (Blueprint $table) {
            $table->id();
            $table->string('warehouse_type'); // SIR atau RSS
            $table->integer('max_capacity_units'); // Contoh: 760 untuk SIR, 1200 untuk RSS
            $table->timestamps();
        });


    }
};
