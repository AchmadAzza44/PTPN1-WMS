# WMS OCR System — Dokumentasi Lengkap

## Struktur Proyek

```
wms-ocr/
├── preprocessing/
│   └── image_processor.py      ← Preprocessing gambar (shadow, perspektif, CLAHE)
├── ocr_service/
│   ├── ocr_engine.py           ← Hybrid OCR: PaddleOCR + TrOCR
│   └── classifier.py           ← Identifikasi jenis dokumen otomatis
├── extractors/
│   ├── surat_pengantar_sir20.py ← Ekstraksi field SIR20
│   ├── surat_pengantar_rss1.py  ← Ekstraksi field RSS1
│   └── other_documents.py       ← Nota Timbang, DO, Surat Kuasa, Jaminan Mutu
├── validators/
│   └── document_validator.py    ← Validasi business rules
├── utils/
│   └── text_normalizer.py       ← Normalisasi angka, tanggal, plat
├── api/
│   └── main.py                  ← FastAPI service
├── laravel/
│   ├── OcrService.php           ← Client PHP untuk Laravel
│   └── InboundOcrController.php ← Controller dengan OCR integration
└── requirements.txt
```

---

## Cara Instalasi

### 1. Setup Python Environment

```bash
# Buat virtual environment
python -m venv venv
source venv/bin/activate          # Linux/Mac
venv\Scripts\activate             # Windows

# Install dependencies
pip install -r requirements.txt

# Jika ada error numpy/paddle conflict:
pip install paddlepaddle --break-system-packages
pip install paddleocr --break-system-packages
pip install transformers torch --break-system-packages
```

### 2. Download TrOCR Model (sekali saja, ~340MB)

Model akan otomatis didownload saat pertama kali dijalankan dan
disimpan di `./models/trocr-handwritten/` untuk offline berikutnya.

Atau download manual:
```bash
python -c "
from transformers import TrOCRProcessor, VisionEncoderDecoderModel
TrOCRProcessor.from_pretrained('microsoft/trocr-base-handwritten').save_pretrained('./models/trocr-handwritten')
VisionEncoderDecoderModel.from_pretrained('microsoft/trocr-base-handwritten').save_pretrained('./models/trocr-handwritten')
print('Model saved!')
"
```

### 3. Jalankan OCR Service

```bash
cd wms-ocr
python api/main.py
# Atau dengan uvicorn langsung:
uvicorn api.main:app --host 0.0.0.0 --port 8001 --workers 1
```

Service berjalan di: http://localhost:8001
Dokumentasi API: http://localhost:8001/docs

### 4. Konfigurasi Laravel

Tambahkan ke `.env`:
```
OCR_SERVICE_URL=http://localhost:8001
OCR_SERVICE_TIMEOUT=30
```

Tambahkan ke `config/services.php`:
```php
'ocr' => [
    'url'     => env('OCR_SERVICE_URL', 'http://localhost:8001'),
    'timeout' => env('OCR_SERVICE_TIMEOUT', 30),
],
```

Tambahkan ke `app/Providers/AppServiceProvider.php`:
```php
$this->app->singleton(\App\Services\OcrService::class);
```

Route di `routes/api.php`:
```php
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/inbound/scan',  [InboundOcrController::class, 'scanDocument']);
    Route::post('/inbound/save',  [InboundOcrController::class, 'saveInbound']);
});
```

---

## Alur Kerja Sistem

```
Petugas Gudang
     │
     │ 1. Foto dokumen di aplikasi Android/PWA
     ▼
Laravel API
     │
     │ 2. Forward ke OCR Service
     ▼
Python FastAPI (OCR Service)
     │
     ├─ Preprocessing (shadow removal, deskew, CLAHE)
     ├─ PaddleOCR → teks cetak (confidence ≥ 0.80 → langsung pakai)
     ├─ TrOCR → teks tulisan tangan (confidence < 0.80 → re-read)
     ├─ Classifier → identifikasi jenis surat
     ├─ Extractor → ambil field spesifik + tabel
     └─ Validator → cek business rules
     │
     │ 3. Return JSON terstruktur
     ▼
Laravel API
     │
     │ 4. Tampilkan form pre-filled di UI
     ▼
Petugas Review & Edit (WAJIB sebelum simpan)
     │
     │ 5. Submit data yang sudah diverifikasi
     ▼
Database MySQL (via Laravel)
```

---

## Dokumen yang Didukung

| Dokumen | Jenis | Field Utama |
|---------|-------|-------------|
| Surat Pengantar SIR20 | Inbound | no_surat, tanggal, no_kendaraan, tabel lot (no_peti, no_lot, berat, bale) |
| Surat Pengantar RSS1 | Inbound | no_surat, tanggal, no_kendaraan, tabel lot dengan nomor urut bale |
| Surat Pengantar Cutting | Inbound | (sama seperti SIR20, perlu sampel dokumen) |
| Nota Timbang | Inbound (opsional) | no_nota, kendaraan, supir, bruto, tara, netto |
| Surat Jaminan Mutu | Inbound (opsional) | no_surat, tabel mutu per palet |
| Sales Order / DO | Outbound | no_so, no_kontrak, pembeli, volume_kg |
| Surat Kuasa | Outbound | no_surat_kuasa, nama, no_do, jenis, kg, palet |

---

## Validasi Business Rules

### Nota Timbang
- ✅ Netto = Bruto - Tara (toleransi ±5 kg)
- ✅ Tara dalam range 500–15.000 kg (berat truk kosong)
- ✅ Format plat kendaraan Indonesia (AB 1234 CD)

### Surat Pengantar
- ✅ Total bale = jumlah dari semua baris tabel
- ✅ Total berat dalam range 500–30.000 kg
- ✅ Nomor surat harus ada

### Surat Kuasa (KRITIS)
- ✅ Nomor surat kuasa wajib ada
- ✅ Nomor DO wajib ada (untuk verifikasi otorisasi)

---

## Flow Human Review di Frontend

Setelah OCR, tampilkan form dengan:
1. **Highlight kuning** → field yang confidence rendah atau tidak ditemukan
2. **Badge "Perlu Cek"** → jika ada warning validasi
3. **Tombol simpan disabled** → sampai user klik "Saya sudah verifikasi"
4. **Tabel lot** → tampilkan per baris, user bisa edit/tambah/hapus baris

---

## Catatan Penting

### Mengapa Human Review Selalu Wajib?
Data dari OCR langsung ke database = BERBAHAYA untuk dokumen berat dan keuangan.
Satu angka yang salah (1,200 vs 12,00) bisa berdampak pada laporan stok.
Human review adalah safety net yang tidak bisa dihilangkan.

### Offline Mode
- PaddleOCR: ✅ Offline setelah download model (~15MB)
- TrOCR: ✅ Offline setelah download model (~340MB, sekali saja)
- Semua model disimpan lokal di `./models/`
- Tidak ada ketergantungan cloud saat runtime

### GPU vs CPU
- GPU (RTX 4050): PaddleOCR ~100–300ms, TrOCR ~50–150ms per dokumen
- CPU saja: PaddleOCR ~500ms–2s, TrOCR ~2–5s per dokumen
- Untuk production di server LAN, GPU sangat disarankan

### Jika Foto Kurang dari 300
Dengan <300 foto, tetap bisa build sistem yang baik dengan:
1. PaddleOCR pretrained (tidak perlu dataset untuk teks cetak)
2. TrOCR pretrained (cukup baik untuk tulisan tangan standar)
3. Fine-tuning hanya jika ada error spesifik yang berulang (misal: angka lot tertentu selalu salah baca)
4. Tambah dataset augmentasi: rotate, brightness, contrast variation dari foto yang ada

---

## Langkah Selanjutnya

**Sekarang (Fase 4):**
1. Test file `api/main.py` dengan foto dokumen asli
2. Lihat field mana yang masih salah ekstraksi
3. Perbaiki regex di file extractor yang relevan

**Berikutnya (Fase 5–6):**
1. Buat UI form review di Laravel/Blade
2. Test dengan minimal 20 foto berbeda per jenis dokumen
3. Catat error patterns → perbaiki regex atau tambah TrOCR

**Jika Akurasi Masih Kurang:**
1. Kumpulkan foto yang gagal
2. Fine-tune TrOCR dengan foto tulisan tangan spesifik (perlu ~50–100 sampel per karakter)
3. Atau tambahkan LLM fallback (Groq API gratis) hanya untuk field yang masih sering salah
