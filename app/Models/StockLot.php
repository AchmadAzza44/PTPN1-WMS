<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockLot extends Model
{
    protected $fillable = ['lot_number', 'production_year', 'quality_type', 'origin_unit', 'status', 'inbound_at'];

    // TAMBAHKAN INI:
    public function details(): HasMany
    {
        return $this->hasMany(StockDetail::class);
    }

    public function edits(): HasMany
    {
        return $this->hasMany(StockLotEdit::class);
    }

    /**
     * Mengurangi stok dari lot ini sebanyak $amountKg.
     * Mengembalikan array detail pengurangan (untuk tracking history jika perlu).
     */
    public function reduceStock($amountKg)
    {
        $remainingToDeduct = $amountKg;

        // Ambil detail yang masih punya sisa berat, urutkan FIFO (id)
        $details = $this->details()->where('net_weight_kg', '>', 0)->orderBy('id')->get();

        foreach ($details as $detail) {
            if ($remainingToDeduct <= 0)
                break;

            if ($detail->net_weight_kg >= $remainingToDeduct) {
                $detail->net_weight_kg -= $remainingToDeduct;
                $detail->save();
                $remainingToDeduct = 0;
            } else {
                $remainingToDeduct -= $detail->net_weight_kg;
                $detail->net_weight_kg = 0;
                $detail->quantity_unit = 0; // Habis
                $detail->save();
            }
        }

        $this->updateStatusBasedOnRemainingStock();
    }

    /**
     * Mengurangi stok dari lot ini berdasarkan Detail/Palet spesifik yang diplilih.
     */
    public function reduceStockByDetails(array $detailIds)
    {
        // Ambil detail yang spesifik dipilih dan masih punya sisa berat
        $details = $this->details()->whereIn('id', $detailIds)->where('net_weight_kg', '>', 0)->get();

        foreach ($details as $detail) {
            // Karena palet diambil seluruhnya secara utuh (menurut konfirmasi)
            $detail->net_weight_kg = 0;
            $detail->quantity_unit = 0;
            $detail->save();
        }

        $this->updateStatusBasedOnRemainingStock();
    }

    /**
     * Abstraction untuk update status Lot.
     */
    protected function updateStatusBasedOnRemainingStock()
    {
        $totalSisa = $this->details()->sum('net_weight_kg');
        if ($totalSisa <= 0) {
            $this->update(['status' => 'orange', 'outbound_at' => now()]);
        } else {
            if ($this->status === 'blue') {
                $this->update(['status' => 'yellow']);
            }
        }
    }

    /**
     * Menyesuaikan stok ke jumlah tertentu (Stock Opname).
     * Sederhana: Update detail pertama atau sebar proporsional?
     * Untuk MVP: Update detail pertama dan nol-kan sisanya jika berkurang, atau tambah ke detail baru jika bertambah.
     * Simplifikasi: Kita anggap 1 Lot = 1 Detail utama untuk opname, atau kita reset semua detail dan buat 1 detail baru sisa.
     */
    public function adjustStock($actualWeightKg, $reason)
    {
        $currentTotal = $this->details()->sum('net_weight_kg');
        $diff = $actualWeightKg - $currentTotal;

        if ($diff == 0)
            return; // Tidak ada perubahan

        // Strategi: Reset semua detail lama, buat 1 detail baru dengan berat aktual
        // Ini untuk menghindari kompleksitas alokasi selisih ke banyak detail.
        // Konsekuensi: Tracing per bale/fdf number mungkin hilang jika tidak hati-hati.
        // Tapi untuk Stock Opname biasanya yang dipentingkan adalah TOTAL berat Lot.

        $this->details()->delete(); // Hapus detail lama (Soft delete recommended di production, tapi disini hard delete dulu atau update)

        // Buat 1 detail baru hasil opname
        $this->details()->create([
            'fdf_number' => 'OPNAME-' . date('ymd'),
            'bale_range' => 'ADJUSTMENT',
            'quantity_unit' => ceil($actualWeightKg / 113), // Estimasi 113kg per bale
            'net_weight_kg' => $actualWeightKg,
            'gross_weight_kg' => $actualWeightKg + 0.5, // Estimasi plastik
        ]);

        // Log Transaksi (Optional, tapi disarankan ada tabel history)
        // ...

        // Update Status
        if ($actualWeightKg <= 0) {
            $this->update(['status' => 'orange', 'outbound_at' => now()]);
        } else {
            $this->update(['status' => 'yellow']); // Anggap partial/adjusted
        }
    }
}