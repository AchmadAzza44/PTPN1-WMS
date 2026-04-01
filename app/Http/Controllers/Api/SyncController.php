<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    //
    public function index()
    {
        // Data Utama untuk Sinkronisasi Offline Android
        return response()->json([
            'stocks' => \App\Models\StockLot::with('details')->get(),
            'contracts' => \App\Models\Contract::with('purchaseOrders')->get(),
            'buyers' => \App\Models\Buyer::all(),
            'timestamp' => now(),
        ]);
    }
}
