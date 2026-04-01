<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StockLot;
use App\Models\DailyStockSnapshot;
use App\Models\StockDetail;
use Illuminate\Support\Facades\DB;

class DailyStockSnapshotCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:snapshot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mencatat snapshot stok harian untuk forecasting';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Daily Stock Snapshot...');

        $date = now()->format('Y-m-d');

        // Snapshot per Quality Type
        $qualities = ['SIR 20 SW', 'RSS 1', 'Cutting A']; // Bisa juga query distinct

        foreach ($qualities as $quality) {
            // Hitung Closing Stock (Stock Saat Ini)
            // Note: Ini sederhana, idealnya Closing = Opening + Inbound - Outbound
            // Tapi untuk snapshot malam hari, stok saat ini dianggap closing stock hari itu.

            $currentStock = StockLot::where('quality_type', $quality)
                ->join('stock_details', 'stock_lots.id', '=', 'stock_details.stock_lot_id')
                ->sum('stock_details.net_weight_kg');

            // Hitung Inbound Hari Ini
            $inboundToday = StockLot::where('quality_type', $quality)
                ->whereDate('inbound_at', $date)
                ->join('stock_details', 'stock_lots.id', '=', 'stock_details.stock_lot_id')
                ->sum('stock_details.net_weight_kg');

            // Hitung Outbound Hari Ini (Perlu query ke Shipments join ShipmentItems join StockLots)
            $outboundToday = DB::table('shipment_items')
                ->join('shipments', 'shipment_items.shipment_id', '=', 'shipments.id')
                ->join('stock_lots', 'shipment_items.stock_lot_id', '=', 'stock_lots.id')
                ->where('stock_lots.quality_type', $quality)
                ->whereDate('shipments.dispatched_at', $date)
                ->sum('shipment_items.qty_loaded_kg');

            // Opening Stock = Closing - Inbound + Outbound (Backward calculation)
            // Atau ambil closing stock kemarin.
            $openingStock = $currentStock - $inboundToday + $outboundToday;

            DailyStockSnapshot::updateOrCreate(
                ['report_date' => $date, 'quality_type' => $quality],
                [
                    'opening_stock' => $openingStock,
                    'inbound_total' => $inboundToday,
                    'outbound_total' => $outboundToday,
                    'closing_stock' => $currentStock,
                    'updated_at' => now(),
                ]
            );

            $this->info("Snapshot saved for $quality: Closing $currentStock kg");
        }

        $this->info('Daily Stock Snapshot Completed.');
    }
}