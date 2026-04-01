<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 6. INBOUND TRANSACTIONS (Penerimaan Barang)
        // Referensi: Nota Timbang
        Schema::create('inbound_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_lot_id')->constrained(); // Relasi ke stok yang dibuat
            
            $table->string('ticket_number')->unique(); // No. Seri Nota (AMCO3P...)
            $table->string('vehicle_plate'); // No. Kendaraan
            $table->string('driver_name');
            
            // Data Timbangan Presisi
            $table->decimal('gross_weight', 10, 2);
            $table->decimal('tare_weight', 10, 2);
            $table->decimal('net_weight', 10, 2);
            
            // Efisiensi Waktu Muat
            $table->dateTime('weigh_in_at'); // Tgl Masuk
            $table->dateTime('weigh_out_at'); // Tgl Keluar
            
            // Menyimpan hasil bacaan AI OCR (Raw JSON) untuk audit
            $table->json('ai_ocr_data')->nullable(); 
            $table->string('photo_path')->nullable(); // Foto Nota Timbang
            
            $table->timestamps();
        });

        // 7. SHIPMENTS (Pengiriman / Checklist Truk)
        // Referensi: Bukti Penyerahan & Checklist
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained(); // Link ke PO untuk potong Sisa DO
            
            // Data Logistik
            $table->string('do_number_manual')->nullable(); // Jika ada no manual
            $table->string('transporter_name'); // Contoh: PT. SRL
            $table->string('driver_name'); // Contoh: Aris Sinaga
            $table->string('vehicle_plate'); // Contoh: BD 8704 CK
            
            // Checklist Lapangan (JSON agar fleksibel jika poin checklist bertambah)
            // Isi: {"terpal": true, "alas_bak": true, "kebersihan": "baik"}
            $table->json('vehicle_checklist'); 
            
            $table->string('weather_condition'); // Cerah/Hujan
            $table->dateTime('dispatched_at'); // Waktu berangkat
            
            // Dokumen PDF yang digenerate otomatis
            $table->string('generated_ba_path')->nullable(); // Path PDF Berita Acara
            $table->string('generated_sjt_path')->nullable(); // Path PDF Surat Jalan
            
            $table->timestamps();
        });
        
        // 8. SHIPMENT ITEMS (Pivot: Barang apa saja yang naik ke Truk X)
        Schema::create('shipment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->onDelete('cascade');
            $table->foreignId('stock_lot_id')->constrained(); // Lot mana yang diambil
            $table->decimal('qty_loaded_kg', 10, 2); // Berapa kg dari lot ini
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_items');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('inbound_transactions');
    }
};