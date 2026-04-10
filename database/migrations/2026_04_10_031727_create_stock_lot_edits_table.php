<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_lot_edits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_lot_id')->constrained('stock_lots')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('field_changed');       // 'lot_number', 'quality_type', etc
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->string('reason');               // Wajib diisi operator
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_lot_edits');
    }
};
