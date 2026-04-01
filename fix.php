<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\App\Models\StockDetail::whereHas('stockLot', function ($q) {
    $q->where('origin_unit', 'RSS'); })->get()->each(function ($d) {
        if ($d->quantity_unit > 0 && $d->net_weight_kg != $d->quantity_unit * 113) {
            $d->net_weight_kg = $d->quantity_unit * 113;
            $d->save();
            \App\Models\InboundTransaction::where('stock_lot_id', $d->stock_lot_id)->update(['net_weight' => $d->net_weight_kg, 'gross_weight' => $d->net_weight_kg]);
        }
    });

\App\Models\StockLot::where('origin_unit', 'RSS')->get()->each(function ($l) {
    $l->lot_number = str_replace('LOT-', 'RSS-', $l->lot_number);
    $l->save();
});

echo "Fix completed.\n";
