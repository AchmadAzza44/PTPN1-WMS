<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\StockLot;
use App\Models\StockDetail;
use App\Models\PurchaseOrder;
use App\Models\Contract;

class PartialOutboundTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_reduce_stock_partially_and_update_status()
    {
        // 1. Setup Data
        $user = User::factory()->create();

        $contract = Contract::create([
            'contract_number' => '1794/HO-SUPCO/SIR-L/N-I/X/2025',
            'buyer_name' => 'PT. Bitung Gunasejahtera',
            'contract_date' => now(),
            'total_tonnage' => 1000,
        ]);

        $po = PurchaseOrder::create([
            'contract_id' => $contract->id,
            'po_number' => 'PO-TEST',
            'po_date' => now(),
            'qty_ordered_kg' => 100000, // 100 ton
        ]);

        $lot = StockLot::create([
            'lot_number' => 'L001',
            'production_year' => 2026,
            'quality_type' => 'SIR 20 SW',
            'origin_unit' => 'Unit Test',
            'status' => 'blue', // Penuh
            'inbound_at' => now(),
        ]);

        // Detail 1: 500kg
        StockDetail::create([
            'stock_lot_id' => $lot->id,
            'packaging_type' => 'pallet',
            'quantity_unit' => 1,
            'net_weight_kg' => 500,
        ]);

        // Detail 2: 500kg
        StockDetail::create([
            'stock_lot_id' => $lot->id,
            'packaging_type' => 'pallet',
            'quantity_unit' => 1,
            'net_weight_kg' => 500,
        ]);

        // Total 1000kg

        // 2. Act: Kirim 400kg
        $response = $this->actingAs($user)->post(route('shipments.store'), [
            'transporter_name' => 'TRUCK-01',
            'driver_name' => 'Budi',
            'vehicle_plate' => 'B 1234 XX',
            'items' => [
                [
                    'stock_lot_id' => $lot->id,
                    'qty_loaded_kg' => 400,
                ]
            ]
        ]);

        // 3. Assert
        $response->assertRedirect(route('stocks.index'));

        $lot->refresh();
        $this->assertEquals('yellow', $lot->status); // Harusnya jadi Partial
        $this->assertEquals(600, $lot->details()->sum('net_weight_kg')); // 1000 - 400 = 600

        // Detail 1 sisa 100, Detail 2 utuh 500 (FIFO)
        $d1 = $lot->details()->orderBy('id')->first();
        $this->assertEquals(100, $d1->net_weight_kg);
    }

    public function test_can_reduce_stock_fully_and_update_status_to_orange()
    {
        // 1. Setup Data
        $user = User::factory()->create();

        $contract = Contract::create([
            'contract_number' => 'CTR-002',
            'buyer_name' => 'Tester 2',
            'contract_date' => now(),
            'total_tonnage' => 500,
        ]);

        $po = PurchaseOrder::create([
            'contract_id' => $contract->id,
            'po_number' => 'PO-TEST-2',
            'po_date' => now(),
            'qty_ordered_kg' => 50000
        ]);

        $lot = StockLot::create([
            'lot_number' => 'L002',
            'production_year' => 2026,
            'quality_type' => 'SIR 20 SW',
            'origin_unit' => 'Unit Test',
            'status' => 'blue',
            'inbound_at' => now(),
        ]);

        StockDetail::create([
            'stock_lot_id' => $lot->id,
            'packaging_type' => 'pallet',
            'quantity_unit' => 1,
            'net_weight_kg' => 1000,
        ]);

        // 2. Act: Kirim semua 1000kg
        $this->actingAs($user)->post(route('shipments.store'), [
            'transporter_name' => 'TRUCK-02',
            'driver_name' => 'Wawan',
            'vehicle_plate' => 'B 5678 XX',
            'items' => [
                [
                    'stock_lot_id' => $lot->id,
                    'qty_loaded_kg' => 1000,
                ]
            ]
        ]);

        // 3. Assert
        $lot->refresh();
        $this->assertEquals('orange', $lot->status); // Kosong
        $this->assertEquals(0, $lot->details()->sum('net_weight_kg'));
    }
}
