# PTPN1 Warehouse Management System (WMS) & LLM OCR

Modern Warehouse Management System (WMS) yang dibangun khusus untuk PTPN1. Dilengkapi dengan microservice AI OCR berbasis LLM untuk ekstraksi data dokumen pengiriman secara otomatis, sistem antrian (queue) yang efisien, dan notifikasi Web Push (VAPID) realtime ke perangkat Android staf gudang.

## 🚀 Fitur Utama

- **Inbound & Outbound Management**: Lacak status penerimaan dan pengeluaran barang di gudang.
- **Smart LLM OCR (Multi-Pass)**: Ekstraksi otomatis dari foto dokumen ke field form (didukung model LLM via Groq API). Mendukung format dokumen:
  - Surat Pengantar SIR20
  - Surat Pengantar RSS1
  - Delivery Order (DO)
  - Surat Kuasa
- **Web Push Notifications**: Notifikasi verifikasi pengiriman real-time kepada staf gudang di mobile/Android.
- **Human-in-the-loop Verification**: Integrasi cerdas di mana hasil OCR disetujui / direvisi staf sebelum masuk DB.
- **Reporting & Export**: Dukungan cetak PDF (via DOMPDF) dan ekspor Excel.

## 🛠️ Tech Stack

### Web Application (Monolith)
- **Framework**: Laravel 12 (PHP 8.2+)
- **Frontend**: Blade + TailwindCSS (Vite)
- **Database**: MySQL/MariaDB
- **Ekstraksi/Librari Tambahan**: DomPDF, Maatwebsite Excel, WebPush

### OCR Microservice
- **Framework**: Python FastAPI
- **Engine**: Groq LLM API (Multi-pass extraction)
- **Image Processing**: OpenCV, Pillow

---

## 📁 Struktur Proyek (Ringkasan)

```text
PTPN1-WMS/
├── app/                  # Logic utama backend Laravel (Controllers, Services)
│   └── Services/         # OcrService.php, AIService.php, Notification, dsb
├── CORO/                 # Python OCR Microservice
│   ├── main.py           # FastAPI Server
│   ├── ocr_service/      # Logic pemrosesan gambar & klasifikasi AI
│   └── requirements.txt  # Dependencies Python
├── resources/            # Views (Blade), CSS, JS
├── routes/               # Routes Laravel
└── start-all.bat         # Script cepat untuk menjalankan service (Windows)
```

---

## 💻 Cara Instalasi & Menjalankan

### Persiapan
Pastikan environment Anda sudah memiliki:
- PHP >= 8.2
- Composer
- Node.js & NPM
- Python >= 3.9
- MySQL / MariaDB

### 1. Setup Laravel Server
```bash
# Clone repo & masuk folder
git clone <url-repo-anda>
cd PTPN1-WMS

# Install dependensi PHP
composer install

# Copy .env configuration
cp .env.example .env
php artisan key:generate

# Konfigurasi Database di .env (Sesuaikan nama DB, user, password)
# DB_DATABASE=ptpn1_wms

# Jalankan migrasi database
php artisan migrate

# Install & Build frontend assets
npm install
npm run build
```

### 2. Setup Python OCR Microservice
```bash
# Pindah ke folder microservice
cd CORO

# Buat virtual environment (opsional namun direkomendasikan)
python -m venv venv
# Aktifkan (Windows)
venv\Scripts\activate
# Aktifkan (Mac/Linux)
# source venv/bin/activate

# Install dependensi
pip install -r requirements.txt

# Copy configurasi microservice
cp .env.example .env

# **PENTING**: Masukkan GROQ_API_KEY di file CORO/.env
```

### 3. Menjalankan Seluruh Service
Aplikasi membutuhkan 3 service yang berjalan bersamaan: Laravel Web Server, Laravel Queue Worker, dan Python OCR Server.

#### Opsi 1: Menggunakan Script (Windows)
Cukup jalankan file batch yang sudah disediakan dari CMD di direktori utama:
```cmd
start-all.bat
```
Script tersebut akan otomatis membuka terminal terpisah untuk masing-masing service di port yang tepat (`8000` untuk Laravel, `8001` untuk OCR API).

#### Opsi 2: Manual (Semua OS)
Buka 3 terminal terpisah:

**Terminal 1 (Laravel Server):**
```bash
php artisan serve --port=8000
```
**Terminal 2 (Laravel Queue):**
```bash
php artisan queue:work --sleep=3 --tries=2 --timeout=150
```
**Terminal 3 (FastAPI OCR):**
```bash
cd CORO
uvicorn main:app --host 0.0.0.0 --port 8001
```

Akses sistem Utama di: `http://localhost:8000`
Akses API OCR (Internal) di: `http://localhost:8001/docs`

---

## 🔄 Alur Integrasi OCR (Inbound)

1. **Upload Gambar:** User mengunggah foto dokumen via Front-end.
2. **Forwarding:** Laravel `InboundOcrController` memanggil `OcrService` (client) mengubah file menjadi format `base64`.
3. **AI Processing:** `OcrService` melakukan HTTP Request (POST `/ocr`) ke server FastAPI yang berjalan di port `8001` (`CORO/main.py`).
4. **LLM Extraction:** FastAPI mendeteksi jenis file (contoh: SIR20, RSS1) lalu membangun instruksi dinamis menggunakan model AI (Groq API) melalui sistem multi-pass.
5. **Return Data Response:** Data yang berhasil diekstraksi ke JSON dikembalikan ke Laravel.
6. **Verifikasi:** User melakukan *review human-in-the-loop* melalui antarmuka Form UI yang terisi otomatis (Pre-filled form), setelah terkonfirmasi, data disimpan secara permanen di database.

---

## 🌐 Catatan API (FastAPI)

- Server wajib berjalan untuk fitur pemindai. Response API dijamin memiliki *Schema* struktur data universal: `success`, `jenis`, `waktu_s`, `hasil`, dan error response jika terjadi kegagalan/blur.
- Batasan `Upload`: Pastikan dokumen foto dapat terbaca dan tidak tertutup kilatan lampu flash dominan untuk meminimalisir kesalahan pembacaan LLM.
