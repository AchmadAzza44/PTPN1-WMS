<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipment_groups', function (Blueprint $table) {
            $table->id();
            $table->string('ba_number')->nullable()->unique(); // Nomor Berita Acara (auto-generated)
            $table->string('buyer_name')->nullable();           // e.g. PT. Bitung Gunasejahtera
            $table->string('transporter_name')->nullable();     // Jasa ekspedisi / pengangkut
            $table->string('driver_name')->nullable();
            $table->string('vehicle_plate')->nullable();
            $table->string('weather_condition')->default('Cerah');
            $table->dateTime('dispatched_at')->nullable();

            // Status & Verification
            $table->enum('status', ['draft', 'verified', 'completed'])->default('draft');
            $table->dateTime('verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->string('krani_name')->nullable();
            $table->string('manager_name')->nullable();

            // Dokumen
            $table->string('signed_document_path')->nullable();

            $table->timestamps();
        });

        // Add shipment_group_id and surat_kuasa_number to shipments
        Schema::table('shipments', function (Blueprint $table) {
            $table->foreignId('shipment_group_id')->nullable()->after('id')->constrained('shipment_groups')->onDelete('cascade');
            $table->string('surat_kuasa_number')->nullable()->after('do_number_manual');
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropForeign(['shipment_group_id']);
            $table->dropColumn(['shipment_group_id', 'surat_kuasa_number']);
        });
        Schema::dropIfExists('shipment_groups');
    }
};
