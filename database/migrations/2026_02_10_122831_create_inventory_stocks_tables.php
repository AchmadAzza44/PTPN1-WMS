<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 4. STOCK LOTS (Induk Stok untuk Heatmap)
        // Referensi: Kartu Persediaan
        Schema::create('stock_lots', function (Blueprint $table) {
            $table->id();
            $table->string('lot_number')->index(); // Contoh: 039
            $table->year('production_year');
            
            // Jenis Mutu
            $table->enum('quality_type', ['SIR 20 SW', 'RSS 1', 'RSS 2', 'Cutting A', 'Cutting B']);
            
            // Asal Unit
            $table->string('origin_unit'); // Contoh: Unit Pawi, Unit Ketahun
            
            // Logika Warna Dashboard
            // White: Rencana, Blue: Di Gudang, Yellow: Keluar Ganjil, Orange: Keluar Genap
            $table->enum('status', ['white', 'blue', 'yellow', 'orange'])->default('white');
            
            $table->timestamp('inbound_at')->nullable(); // Waktu masuk
            $table->timestamp('outbound_at')->nullable(); // Waktu keluar
            $table->timestamps();
        });

        // 5. STOCK DETAILS (Detil Fisik: Palet atau Bale)
        // Referensi: Beda SIR 20 vs RSS
        Schema::create('stock_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_lot_id')->constrained()->onDelete('cascade');
            
            // Tipe bungkusan
            $table->enum('packaging_type', ['pallet', 'bale']);
            
            // Identitas Unik
            $table->string('fdf_number')->nullable(); // Khusus SIR 20 (Contoh: FDF 306)
            $table->string('bale_range')->nullable(); // Khusus RSS (Contoh: 7458-7470)
            
            $table->integer('quantity_unit'); // Jumlah bale/palet
            $table->decimal('net_weight_kg', 10, 2); // Berat (Contoh: 1260 kg)
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_details');
        Schema::dropIfExists('stock_lots');
    }
};