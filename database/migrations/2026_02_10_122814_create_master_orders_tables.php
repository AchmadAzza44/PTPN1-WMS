<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {

        // 2. CONTRACTS (Data Induk dari Kandir)
        // Referensi: Dokumen Berita Acara
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number')->unique(); // Contoh: 1794/HO-SUPCO...
            $table->string('buyer_name'); // Contoh: PT. Bitung Gunasejahtera
            $table->date('contract_date');
            $table->decimal('total_tonnage', 10, 2); // Total kontrak (Ton)
            $table->timestamps();
        });

        // 3. PURCHASE ORDERS (Instruksi Pengiriman & Smart DO Tracker)
        // Referensi: Buku Catatan Pelayanan
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->onDelete('cascade');
            $table->string('po_number')->unique(); // Contoh: 014/KARET SC/2026
            $table->date('po_date');

            // Smart DO Tracking Logic
            $table->decimal('qty_ordered_kg', 12, 2); // Jumlah diminta (misal 60.480 kg)
            $table->decimal('qty_served_kg', 12, 2)->default(0); // Jumlah yang sudah keluar

            // Kolom Virtual/Computed bisa dihandle di Model: sisa_do = qty_ordered - qty_served

            $table->enum('status', ['open', 'completed', 'closed'])->default('open');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('contracts');
    }
};