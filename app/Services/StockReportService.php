<?php
/**
 * StockReportService — Reusable stock aggregation queries with date range filtering.
 * Used by both DashboardController and ReportController for real-time data.
 */

namespace App\Services;

use App\Models\StockLot;
use App\Models\StockDetail;
use App\Models\InboundTransaction;
use App\Models\Shipment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StockReportService
{
    /**
     * Get current stock summary by quality type.
     */
    public function currentStockByQuality(): array
    {
        $data = StockLot::join('stock_details', 'stock_lots.id', '=', 'stock_details.stock_lot_id')
            ->where('stock_lots.status', '!=', 'orange')
            ->selectRaw('stock_lots.quality_type, SUM(stock_details.net_weight_kg) as total_kg, COUNT(DISTINCT stock_lots.id) as lot_count')
            ->groupBy('stock_lots.quality_type')
            ->get();

        return $data->map(fn($row) => [
            'quality_type' => $row->quality_type,
            'total_kg'     => round($row->total_kg, 2),
            'lot_count'    => $row->lot_count,
        ])->toArray();
    }

    /**
     * Get inbound/outbound trend data for a given date range.
     */
    public function trendData(string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end   = Carbon::parse($endDate)->endOfDay();

        // Generate date range
        $dates = collect();
        $d = $start->copy();
        while ($d <= $end) {
            $dates->push($d->format('Y-m-d'));
            $d->addDay();
        }

        // Inbound per day
        $inbound = InboundTransaction::selectRaw('DATE(weigh_in_at) as date, SUM(net_weight) as total')
            ->whereBetween('weigh_in_at', [$start, $end])
            ->groupBy('date')
            ->pluck('total', 'date');

        // Outbound per day
        $outbound = DB::table('shipments')
            ->join('shipment_items', 'shipments.id', '=', 'shipment_items.shipment_id')
            ->selectRaw('DATE(shipments.dispatched_at) as date, SUM(shipment_items.qty_loaded_kg) as total')
            ->whereBetween('shipments.dispatched_at', [$start, $end])
            ->groupBy('date')
            ->pluck('total', 'date');

        return [
            'labels'   => $dates->map(fn($d) => Carbon::parse($d)->format('d M'))->values()->toArray(),
            'dates'    => $dates->values()->toArray(),
            'inbound'  => $dates->map(fn($d) => round($inbound[$d] ?? 0, 2))->values()->toArray(),
            'outbound' => $dates->map(fn($d) => round($outbound[$d] ?? 0, 2))->values()->toArray(),
        ];
    }

    /**
     * Get recent inbound transactions.
     */
    public function recentInbound(int $limit = 10): array
    {
        return InboundTransaction::with('stockLot')
            ->latest('weigh_in_at')
            ->limit($limit)
            ->get()
            ->map(fn($t) => [
                'id'           => $t->id,
                'ticket'       => $t->ticket_number,
                'vehicle'      => $t->vehicle_plate,
                'driver'       => $t->driver_name,
                'net_weight'   => $t->net_weight,
                'lot_number'   => $t->stockLot->lot_number ?? '-',
                'quality'      => $t->stockLot->quality_type ?? '-',
                'time'         => $t->weigh_in_at ? Carbon::parse($t->weigh_in_at)->format('d M H:i') : '-',
            ])
            ->toArray();
    }

    /**
     * Get total summary for range.
     */
    public function rangeSummary(string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end   = Carbon::parse($endDate)->endOfDay();

        $totalInbound = InboundTransaction::whereBetween('weigh_in_at', [$start, $end])->sum('net_weight');

        $totalOutbound = DB::table('shipments')
            ->join('shipment_items', 'shipments.id', '=', 'shipment_items.shipment_id')
            ->whereBetween('shipments.dispatched_at', [$start, $end])
            ->sum('shipment_items.qty_loaded_kg');

        $inboundCount = InboundTransaction::whereBetween('weigh_in_at', [$start, $end])->count();

        return [
            'total_inbound_kg'  => round($totalInbound, 2),
            'total_outbound_kg' => round($totalOutbound, 2),
            'inbound_count'     => $inboundCount,
            'net_change_kg'     => round($totalInbound - $totalOutbound, 2),
        ];
    }

    /**
     * Calculate date range from a preset name.
     */
    public function resolveRange(string $preset, ?string $startDate = null, ?string $endDate = null): array
    {
        return match ($preset) {
            'today'   => [now()->startOfDay()->toDateString(), now()->toDateString()],
            '3d'      => [now()->subDays(2)->toDateString(), now()->toDateString()],
            '5d'      => [now()->subDays(4)->toDateString(), now()->toDateString()],
            '7d'      => [now()->subDays(6)->toDateString(), now()->toDateString()],
            'monthly' => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
            'yearly'  => [now()->startOfYear()->toDateString(), now()->endOfYear()->toDateString()],
            'custom'  => [$startDate ?? now()->subDays(6)->toDateString(), $endDate ?? now()->toDateString()],
            default   => [now()->subDays(6)->toDateString(), now()->toDateString()],
        };
    }
}
