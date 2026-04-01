<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Data User Contoh
        DB::table('users')->insert([
            'name' => 'Krani P. Baai',
            'email' => 'krani@ptpn1.co.id',
            'password' => Hash::make('password'),
            'role' => 'krani',
        ]);

        // 2. Data Kontrak Riil
        $contractId = DB::table('contracts')->insertGetId([
            'contract_number' => '1794/HO-SUPCO/SIR-L/N-I/X/2025',
            'buyer_name' => 'PT. Bitung Gunasejahtera',
            'contract_date' => '2025-09-25',
            'total_tonnage' => 221.76,
        ]);

        // 3. Data PO
        DB::table('purchase_orders')->insert([
            'contract_id' => $contractId,
            'po_number' => '014/KARET SC/2026',
            'po_date' => '2026-01-07',
            'qty_ordered_kg' => 60480,
            'qty_served_kg' => 20160,
        ]);
    }
}