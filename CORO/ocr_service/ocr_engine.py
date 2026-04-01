# -*- coding: utf-8 -*-
"""
OCR Engine v16.1 - Maverick-First Parallel Racing + Two-Pass Parallel SIR20
================================================================================
Strategy: Akurasi MAKSIMUM mendekati AI vision terbaik, dengan kecepatan optimal.

Model Priority (v16.1 - tanpa Gemini karena 429 di Indonesia):
  PRIMARY  → Groq Llama-4-Scout (gratis, vision, 3-5s)
  PARALLEL → OR Llama-4-Maverick / Scout / Mistral (gratis, difire bersamaan)
  WINNER   → Siapapun selesai duluan yang valid, dipakai

Strategy (v16.1):
  1. Groq + OR semua di-fire BERSAMAAN (truly parallel) dari detik 0
  2. Two-Pass OCR untuk SIR20: Pass-1=header, Pass-2=table -- kedua PARALEL
  3. Total waktu SIR20 = max(header_time, table_time) ~5-7s
  4. Preprocessing v2: CLAHE + adaptive sharpening + deskew
  5. Prompt v3: step-by-step reasoning, contoh realistis (136/137), anti-truncation
  6. Hallucination detection: KEDUANYA harus terpenuhi (sequential+same berat)

Resilient terhadap:
  - Dokumen terlipat (fold lines)
  - Foto miring/berputar (deskew + EXIF)
  - Pencahayaan buruk (CLAHE adaptive)
  - Cetakan dot-matrix pudar (sharpening agresif)
  - Tulisan tangan bercampur cetak
"""
import io, base64, time, json, logging, requests, statistics, os, re
import numpy as np, cv2
from PIL import Image, ImageEnhance, ExifTags
from concurrent.futures import ThreadPoolExecutor, as_completed, Future
from threading import Event, Thread
from typing import Optional

logger = logging.getLogger(__name__)

# ─── KEYS ────────────────────────────────────────────────────────────────────
OPENROUTER_TOKEN = os.environ.get("OPENROUTER_API_KEY", "")
GROQ_TOKEN       = os.environ.get("GROQ_API_KEY", "")

# ─── PROVIDER CONFIG ─────────────────────────────────────────────────────────
GROQ_URL   = "https://api.groq.com/openai/v1/chat/completions"
# HARUS Scout — model ini yang TERBUKTI support vision/image di Groq
# Maverick belum tentu support image_url di Groq → return kosong → lambat
GROQ_MODEL = "meta-llama/llama-4-scout-17b-16e-instruct"

OPENROUTER_URL = "https://openrouter.ai/api/v1/chat/completions"
# PRIMARY: Gemini 2.0 Flash - paling akurat untuk dokumen kompleks (gratis)
# BACKUP : Maverick > Scout sebagai fallback jika Gemini rate-limit
OR_MODELS = [
    "meta-llama/llama-4-maverick:free",   # PRIMARY -- paling kuat, 128 experts, gratis
    "meta-llama/llama-4-scout:free",      # BACKUP 1 -- stabil, gratis
    "mistralai/mistral-small-3.1-24b-instruct:free", # BACKUP 2 -- vision capable, gratis
]
# Model yg diprioritaskan bila selesai < 2 detik setelah model lain
PREFERRED_MODEL = "meta-llama/llama-4-maverick:free"

# ─── TOKEN LIMITS ────────────────────────────────────────────────────────────
MAX_TOKENS = {
    "sir20_header":  600,   # Pass-1: hanya header (hemat token, cepat)
    "sir20_table":   2200,  # Pass-2: hanya tabel (banyak baris, perlu token besar)
    "sir20":         2500,  # Single-pass fallback
    "rss1":          1800,  # RSS1 table bisa panjang
    "do":            900,
    "surat_kuasa":   1000,
}

BULAN = {
    '01':'Januari','02':'Februari','03':'Maret','04':'April','05':'Mei',
    '06':'Juni','07':'Juli','08':'Agustus','09':'September',
    '10':'Oktober','11':'November','12':'Desember'
}

# ─── JSON ENFORCEMENT ────────────────────────────────────────────────────────
JSON_STRICT = (
    "\n\nOUTPUT RULES (MANDATORY):\n"
    "- Output ONLY valid JSON. No explanation, no markdown, no prose.\n"
    "- Start with { and end with }. Nothing before or after.\n"
    "- All string values must be properly quoted.\n"
    "- null for truly unreadable fields (do NOT guess).\n"
    "- Numbers as integers/floats, NOT strings."
)

# ─── PROMPTS v5 (two-pass support + ultra-precise instructions) ──────────────

PROMPT_SIR20_HEADER = """Read ONLY the 4-field header box of this "SURAT PENGANTAR PENGIRIMAN KARET (SIR)" rubber document.
Ignore the table completely. Focus only on the top-right box area.

⚠️ ANTI-HALLUCINATION: Output null if you CANNOT read a value clearly. Never guess or invent.
⚠️ YEAR: Dot-matrix digit '6' looks like '0'. Year must be 2025 or 2026. If you see 2020→2026, 2021→2026.

FIELD LOCATIONS (top-right box, printed labels + handwritten values beside them):

[no_surat] — next to label "Nomor" or "No."
  Format: CODE/ROMAN_MONTH/YYYY.MM.SEQ  e.g. "7K17/II/2026.02.05"
  - CODE is typically 4-6 chars like "7K17" or "BKT7"
  - ROMAN_MONTH: I II III IV V VI VII VIII IX X XI XII (written as roman numerals)
  - YYYY.MM.SEQ: year (2025/2026) . month-number (01-12) . sequence (01-99)
  - CRITICAL: if year looks like 2020/2021/2022/2023 → it is almost certainly 2026 (0 misread for 6)

[tanggal] — next to label "Tanggal" or "Tgl."
  Output format: "DD MonthName YYYY"
  - DD: day (01-31), MonthName: Indonesian (Januari Februari Maret April Mei Juni Juli Agustus September Oktober November Desember), YYYY: 2025 or 2026
  - e.g. "05 Februari 2026", "30 Januari 2026"

[no_kendaraan] — next to label "No. Kendaraan" or "No Kend" or "Kend."
  Vehicle plate number, HANDWRITTEN. Format: 2-letters SPACE digits SPACE 1-3letters
  - e.g. "BD 8371 P", "BD 8763 AP", "BG 1234 AB", "BK 4455 C"
  - Common confusions: B↔8, D↔0, P↔R, I↔1, O↔0
  - Even if faint, try your best to read all parts

[nama_supir] — next to label "Nama Supir" or "Supir"
  Driver's name, HANDWRITTEN. e.g. "RUSYAM", "YOBI", "SELAMET", "SUNOTO", "ANTO"

Output ONLY this JSON:
{"no_surat":null,"tanggal":null,"no_kendaraan":null,"nama_supir":null}""" + JSON_STRICT

PROMPT_SIR20_TABLE = """You are reading the DATA TABLE inside a "SURAT PENGANTAR PENGIRIMAN KARET (SIR)" document.
Table type: dot-matrix print, may be faded, folded, or photographed at an angle.

YOUR TASK: Extract ALL rows from the LEFT "Dikirim" table. The RIGHT "Diterima" table = IGNORE.

=== TABLE COLUMNS (left to right) ===
  1. Nomor Peti  → no_peti
  2. Jenis Mutu  → jenis_mutu
  3. Nomor Lot   → no_lot   ← MOST CRITICAL (narrow, handwritten)
  4. Berat Kg    → berat_kg
  5. Jml Balle   → jml_bale

── COLUMN: no_peti ──
  Format: 3 uppercase letters + space + numbers. e.g. "PDF 306", "FDF 309", "ABC 1234"
  - Never digits alone. Estimate from context if faint. null if impossible.

── COLUMN: no_lot ── ⚠️ READ EVERY DIGIT WITH EXTREME CARE ⚠️
  This column is VERY NARROW and HANDWRITTEN. It contains a short integer only.
  Valid range: 1 to 999 (1 digit, 2 digits, or 3 digits: "2", "39", "136", "137")
  - "136" means one-hundred-thirty-six. It has 3 digits. Read ALL of them.
  - "069" means lot 69 (leading zero is padding, ignore it).

  DITTO MARKS — symbols meaning "same lot as row above":
    Symbols: " v u , - ` ~ HI H.I. II s/d idem = ditto
  
  DITTO RESOLUTION STEPS:
    1. Find first row with a real INTEGER in no_lot column → this is your anchor
    2. For every following row with a ditto symbol → copy the anchor number
    3. When you find a NEW integer → it becomes the new anchor for rows below
    4. If NO integer exists anywhere in the column → all no_lot = null
  
  Realistic example (⚠️ DO NOT COPY THESE VALUES — read what's in YOUR image):
    FDF 306 | SIR20 | 069 | 1.200 | 30  →  no_lot=69  (first anchor)
    FDF 309 | SIR20 |  v  | 1.200 | 36  →  no_lot=69  (v = ditto)
    FDF 313 | SIR20 | 040 | 1.200 | 36  →  no_lot=40  (new anchor)
    FDF 319 | SIR20 | HI  | 1.200 | 36  →  no_lot=40  (HI = ditto)
    FDF 316 | SIR20 |  v  | 1.260 | 36  →  no_lot=40  (v = ditto)

  NEVER output the string "HI", "II", "v", "s/d", "idem" as no_lot value.

── COLUMN: berat_kg ──
  Dot is thousand separator: "1.200"=1200, "1.260"=1260. Valid: 800-2000.
  If you read a value <100 → multiply by 1000 (decimal misread as kg).

── COLUMN: jml_bale ──
  Integer, typically 30-38. DO NOT include the bottom "Jumlah" summary row in baris.

── TOTAL ROW ──
  The last "Jumlah" row gives totals. Extract:
    total_kg   (dot notation: "7.560"=7560)
    total_bale (e.g. 216, 180, 144)

Output ONLY this JSON:
{"baris":[{"no_peti":null,"jenis_mutu":null,"no_lot":null,"berat_kg":0,"jml_bale":0}],"total_kg":0,"total_bale":0}""" + JSON_STRICT

PROMPT_SIR20_FULL = """You are reading a "SURAT PENGANTAR PENGIRIMAN KARET (SIR)" rubber shipment document.
Print: dot-matrix or photocopy, mixed handwritten. May be folded, angled, or low-contrast.

⚠️ RULE 1 — NEVER HALLUCINATE: Output null for any field you cannot clearly read. Do NOT invent values.
⚠️ RULE 2 — YEAR CORRECTION: All docs are 2025 or 2026. Dot-matrix '6' ≈ '0'. Correct 2020/2021→2026.
⚠️ RULE 3 — DO NOT COPY EXAMPLES: Read the ACTUAL image, not these example values!

═══ STEP 1: HEADER BOX (top-right area) ═══
Find printed labels with handwritten values:
  "Nomor"         → no_surat       e.g. "7K17/II/2026.02.05"
  "Tanggal"       → tanggal        e.g. "05 Februari 2026"
  "No. Kendaraan" → no_kendaraan   e.g. "BD 8371 P"
  "Nama Supir"    → nama_supir     e.g. "RUSYAM"

═══ STEP 2: LEFT "Dikirim" TABLE — ignore the right "Diterima" table ═══
Columns: no_peti | jenis_mutu | no_lot | berat_kg | jml_bale

no_peti    → "ABC 1234" format (3 uppercase letters + space + digits). null if unreadable.
jenis_mutu → rubber grade from column header (SIR20, SIR 20, RSS 1 etc.)
no_lot     → NARROW HANDWRITTEN INTEGER COLUMN (1-3 digits: e.g. 2, 39, 69, 136, 137, 140)
             Ditto marks (", v, u, HI, II, s/d, idem, ~) = use previous row's lot number.
             RESOLVE dittos — never output "HI", "v", "idem" as the value.
berat_kg   → dot=thousands ("1.200"=1200), range 800-2000
jml_bale   → integer 20-50

Row "Jumlah" = summary → put in total_kg & total_bale, DO NOT add to baris.

Output RAW JSON:
{"no_surat":null,"tanggal":null,"no_kendaraan":null,"nama_supir":null,"baris":[{"no_peti":null,"jenis_mutu":null,"no_lot":null,"berat_kg":0,"jml_bale":0}],"total_kg":0,"total_bale":0}""" + JSON_STRICT


PROMPT_RSS1 = """Read a "BUKTI PENGANTAR PENGIRIMAN PRODUKSI JADI" rubber shipment document from PT Perkebunan Nusantara I Regional 7.
Document: printed (dot-matrix or photocopy) + some handwritten values. Photo may be angled.

⚠️ ANTI-HALLUCINATION: Output null for any field you CANNOT clearly read. NEVER invent data.
⚠️ DO NOT COPY EXAMPLES — read actual values from the document image!

=== DOCUMENT LAYOUT (top to bottom) ===
1. Letter head: "PERKEBUNAN NUSANTARA I Regional 7" + logo (top)
2. Bold kebun name: e.g. "KEBUN KETAHUN" or "KEBUN MUKOMUKO"
3. Address line(s)
4. Title: "BUKTI PENGANTAR PENGIRIMAN PRODUKSI JADI" (centered, bold)
5. HEADER STRIP — a row of labeled boxes/cells (MOST IMPORTANT FOR no_dokumen):
     ┌──────────┬────────────┬──────────┬──────────┬───────────────────────────┬──────────┐
     │DIKIRIM KE│  BANYAKNYA │ NOMOR SP │ TANGGAL  │ PENGANGKUT/TRUCK          │ NO.KEND  │
     │(destination)│ (qty)   │ (doc no) │ (date)   │ (carrier name)            │ (plate)  │
     └──────────┴────────────┴──────────┴──────────┴───────────────────────────┴──────────┘
6. Bale number range table (multiple rows)
7. Summary row: JUMLAH with total bale count and weight
8. Footer: signatures, JENIS MUTU label, BERAT NETTO TOTAL

=== FIELD EXTRACTION ===

[no_dokumen] ← "NOMOR SP" box in the header strip (Step 5 above)
  THIS IS A CRITICAL FIELD — look carefully at the cell under "NOMOR SP" label.
  Format: N/SP/KEBUN-ABBR/TYPE/SUB/YEAR or similar coded format
  Examples: "11/SP/KETA/RSS/1/2026", "08/SP/KETA/RSS/1/2026", "03/SP/MUKO/RSS/1/2026"
  - N = sequence number (usually 2 digits)
  - SP = always "SP"
  - KEBUN-ABBR = kebun short code (KETA=Ketahun, MUKO=Mukomuko, etc.)
  - TYPE = RSS or SIR
  - SUB = grade number (1, 20, etc.)
  - YEAR = 4-digit year (2025 or 2026)
  - If you cannot fully read it, output whatever partial text you CAN read (e.g. "11/SP/KETA/RSS/1/2026")
  - null ONLY if the entire cell is blank or completely illegible

[tanggal] ← "TANGGAL" box in the header strip, or from the signature area at the bottom
  Output format: "DD MonthName YYYY" e.g. "30 Januari 2026", "15 Februari 2026"

[kebun] ← Bold name on line 2 (after letterhead)
  Full name: "KEBUN KETAHUN", "KEBUN MUKOMUKO", "KEBUN TALO TRANSMIGRAN"

[mutu] ← From the "JENIS MUTU" label (near footer or header strip)
  Usually "RSS 1". ONE value only. If you see comma-separated → take first only.

[jumlah_bale] ← Integer from the JUMLAH row of the bale table
  e.g. 72, 58, 144, 36

[berat_netto_total] ← Total weight. DOT = thousand separator: "8.136"=8136, "6.552"=6552
  Look for label "BERAT NETTO" or "JUMLAH" near the bottom.

[pengangkut] ← Under "PENGANGKUT/TRUCK" in header strip
  Full text including company name in parentheses if present.
  e.g. "Juki (PT. Persero Varuna Tirta Prakasya)", "BUDI (CV. MAJU JAYA)"

[no_kendaraan] ← Under "NO.KEND" or "NO. KENDARAAN" in header strip
  Vehicle plate format: 2-letters SPACE digits SPACE letters  e.g. "BD 8506 C", "BG 1234 AB"

[nomor_bale] ← Array of range strings from the bale table
  Each row has a bale number RANGE like "7458-7470" in format "START-END"
  Output as array: ["7458-7470", "7471-7515", "7516-7529"]
  Do NOT include the JUMLAH summary row.

[nomor_urut_bale] ← Expand each range to individual numbers
  "7458-7460" → [7458, 7459, 7460]. Concatenate all ranges into one flat array.
  ONLY output if you can read ranges clearly. If too faint → []

Output ONLY this JSON:
{"no_dokumen":null,"tanggal":null,"kebun":null,"mutu":null,"jumlah_bale":0,"berat_netto_total":0,"pengangkut":null,"no_kendaraan":null,"nomor_bale":[],"nomor_urut_bale":[]}""" + JSON_STRICT


PROMPT_DO = """You are reading a printed Sales Order from PT Perkebunan Nusantara I Regional VII (Eks PTPN VII).

ALL text is PRINTED (computer-generated). Read carefully, mark null only if truly invisible.

TOP-RIGHT box "Sales Order":
  no_so_internal      ← "No.SO Internal" e.g. "1316018648"
  tanggal_so          ← "Tanggal" e.g. "07.01.2026"
  no_kontrak_internal ← "No.Kontrak Internal" e.g. "1116013277"

LEFT "Kepada"/"Pembeli":
  nama_pembeli   ← company name e.g. "PT. BITUNG GUNASEJAHTERA"
  alamat_pembeli ← full address (multi-line ok)

RIGHT "Informasi" box:
  no_po           ← "No.PO" e.g. "014/KARET SC/2026"
  tanggal_po      ← "Tgl.PO" e.g. "07.01.2026"
  no_kontrak      ← "No.Kontrak" e.g. "1794/HO-SUPCO/SIR-L/N-I/IX/2025"
  tanggal_kontrak ← "Tgl.Kontrak" e.g. "25.09.2025"
  incoterms       ← e.g. "Free on board"
  lokasi          ← location after incoterms e.g. "IPMG Bengkulu"

Product table (1 row):
  no_material ← e.g. "11003903"
  deskripsi   ← e.g. "SIR 20 (PAWI)"
  volume      ← DOT=thousands: "60.480"=60480 (integer)
  terbilang   ← e.g. "ENAM PULUH RIBU EMPAT RATUS DELAPAN PULUH"

Output RAW JSON only:
{"no_so_internal":null,"tanggal_so":null,"no_kontrak_internal":null,"no_po":null,"tanggal_po":null,"no_kontrak":null,"tanggal_kontrak":null,"incoterms":null,"lokasi":null,"nama_pembeli":null,"alamat_pembeli":null,"no_material":null,"deskripsi":null,"volume":0,"terbilang":null}""" + JSON_STRICT


PROMPT_SURAT_KUASA = """Read a "SURAT KUASA" (Power of Attorney) document from a rubber export company.
Layout: letterhead → title → fields → signatures. Mix of printed and handwritten text.

⚠️ ANTI-HALLUCINATION: null if you cannot read a field. Never invent.

[no_surat_kuasa] ← Document code typically below the "SURAT KUASA" title
  Format: LOCXXX-YYMM-NNNN e.g. "LOCBGS-2601-0014", "LOCBGS-2602-0007"
  Also may appear as alphanumeric near the title.

[tanggal] ← Date, often in bottom-left area: "City, DD MonthName YYYY"
  Extract ONLY the date part: "8 Januari 2026", "15 Februari 2026"

[nama_pemberi] ← After label "NAMA" or "Yang bertanda tangan": "TIMMIE MELVIN", "BUDI SANTOSO"

[perusahaan_pemberi] ← After label "PERUSAHAAN": "PT. BITUNG GUNASEJAHTERA"

[alamat_pemberi] ← After label "ALAMAT" (may be multi-line):
  e.g. "Jl. Syech Nawawi Al-Bantani No.33 Bengkulu"

[nama_penerima] ← Near "Penerima Kuasa" (usually bottom-right signature area):
  e.g. "RELA SUMADIYANA", "AHMAD FAUZI"

[jenis_karet] ← Rubber type in document body: "SIR 20 SEP", "SIR 20", "RSS 1"

[jumlah_kg] ← Weight in kg, DOT=thousands: "60.480"=60480

[jumlah_pallet] ← Pallet count (integer): 48, 36, 24

[no_do] ← DO or PO reference number: "014/KARET SC/2026"

[trucking] ← Full text after "Trucking:" label

[stuffing] ← Full text after "Stuffing:" label

[tujuan] ← Full text after "Dengan tujuan" or "Tujuan:" label

Output ONLY this JSON:
{"no_surat_kuasa":null,"tanggal":null,"nama_pemberi":null,"perusahaan_pemberi":null,"alamat_pemberi":null,"nama_penerima":null,"jenis_karet":null,"jumlah_kg":0,"jumlah_pallet":0,"no_do":null,"trucking":null,"stuffing":null,"tujuan":null}""" + JSON_STRICT


# ─── EMPTY TEMPLATES ─────────────────────────────────────────────────────────
EMPTY = {
    "sir20":       {"no_surat":None,"tanggal":None,"no_kendaraan":None,"nama_supir":None,"baris":[],"total_kg":0,"total_bale":0},
    "rss1":        {"no_dokumen":None,"tanggal":None,"kebun":None,"mutu":None,"jumlah_bale":0,"berat_netto_total":0,"pengangkut":None,"no_kendaraan":None,"nomor_bale":[],"nomor_urut_bale":[]},
    "do":          {"no_so_internal":None,"tanggal_so":None,"no_kontrak_internal":None,"no_po":None,"tanggal_po":None,"no_kontrak":None,"tanggal_kontrak":None,"incoterms":None,"lokasi":None,"nama_pembeli":None,"alamat_pembeli":None,"no_material":None,"deskripsi":None,"volume":0,"terbilang":None},
    "surat_kuasa": {"no_surat_kuasa":None,"tanggal":None,"nama_pemberi":None,"perusahaan_pemberi":None,"alamat_pemberi":None,"nama_penerima":None,"jenis_karet":None,"jumlah_kg":0,"jumlah_pallet":0,"no_do":None,"trucking":None,"stuffing":None,"tujuan":None},
}

# ─── ENGINE ──────────────────────────────────────────────────────────────────

class OCREngine:
    def __init__(self):
        self.groq_ok = bool(GROQ_TOKEN)
        self.or_ok   = bool(OPENROUTER_TOKEN)

        if self.groq_ok and self.or_ok: self.mode = "racing_groq_or"
        elif self.groq_ok:              self.mode = "groq_only"
        elif self.or_ok:                self.mode = "or_only"
        else:                           self.mode = "no_api"

        logger.info(f"OCREngine v15.1: mode={self.mode} groq={self.groq_ok} or={self.or_ok}")

    # ── post-processing ───────────────────────────────────────────────────────

    def _fix_roman(self, s):
        try:
            p = s.split('/')
            if len(p) != 3: return s
            dp = p[2].split('.')
            if len(dp) < 2: return s
            m = int(dp[1])
            r2i = {'I':1,'II':2,'III':3,'IV':4,'V':5,'VI':6,'VII':7,'VIII':8,'IX':9,'X':10,'XI':11,'XII':12}
            i2r = {v:k for k,v in r2i.items()}
            if r2i.get(p[1].upper()) != m:
                s2 = f"{p[0]}/{i2r.get(m,p[1])}/{p[2]}"
                logger.info(f"Roman fix: {s}->{s2}"); return s2
        except: pass
        return s

    def _fix_year_no_surat(self, s: str) -> str:
        """
        Koreksi tahun di no_surat jika < 2024.
        Masalah umum: digit '6' dibaca sebagai '0' di dokumen dot-matrix.
        2020 → 2026, 2021 → 2026, 2023 → 2025, dll.
        """
        if not s: return s
        try:
            p = s.split('/')
            if len(p) != 3: return s
            dp = p[2].split('.')
            if not dp[0].isdigit(): return s
            yr = int(dp[0])
            if yr >= 2024: return s  # sudah benar
            import datetime
            current_year = datetime.datetime.now().year  # 2026
            # Heuristic: jika selisih antara tahun terbaca dan current year adalah 4-8
            # (artinya digit terakhir salah), coba ganti digit terakhir
            if 2018 <= yr <= 2023:
                corrected = current_year  # gunakan tahun sekarang (2026) sebagai koreksi
                dp[0] = str(corrected)
                p[2] = '.'.join(dp)
                fixed = '/'.join(p)
                logger.warning(f"Year fix no_surat: {s} -> {fixed} (year {yr}->{corrected})")
                return fixed
        except Exception as e:
            logger.warning(f"_fix_year_no_surat error: {e}")
        return s


    def _infer_tanggal(self, code):
        try:
            for part in code.split('/'):
                dp = part.split('.')
                if len(dp) >= 2 and dp[0].isdigit() and len(dp[0]) == 4:
                    nama = BULAN.get(dp[1].zfill(2))
                    if nama: return f"?? {nama} {dp[0]}"
        except: pass
        return None

    def _normalize_tanggal(self, s):
        if not s: return s
        s = s.strip()
        abbr = {
            'jan':'Januari','feb':'Februari','mar':'Maret','apr':'April',
            'mei':'Mei','may':'Mei','jun':'Juni','jul':'Juli',
            'ags':'Agustus','aug':'Agustus','sep':'September','okt':'Oktober',
            'oct':'Oktober','nov':'November','des':'Desember','dec':'Desember',
            'januari':'Januari','februari':'Februari','maret':'Maret','april':'April',
            'juni':'Juni','juli':'Juli','agustus':'Agustus','september':'September',
            'oktober':'Oktober','november':'November','desember':'Desember',
            'january':'Januari','february':'Februari','march':'Maret',
            'august':'Agustus','october':'Oktober','december':'Desember',
        }
        m = re.match(r"(\d{1,2})\s+([a-zA-Z']+)[^a-zA-Z0-9]*(\d{4})", s)
        if m:
            day, mon_raw, yr = m.group(1), m.group(2), m.group(3)
            mon_clean = re.sub(r"[^a-zA-Z].*", '', mon_raw).lower()
            nama = abbr.get(mon_clean)
            if nama: return f"{int(day):02d} {nama} {yr}"
        m = re.match(r'(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})', s)
        if m:
            day, mon_num, yr = m.group(1), m.group(2).zfill(2), m.group(3)
            nama = BULAN.get(mon_num)
            if nama: return f"{int(day):02d} {nama} {yr}"
        return s

    def _validate_tanggal_sir20(self, tanggal, no_surat):
        if not no_surat: return tanggal
        try:
            parts = no_surat.split('/')
            if len(parts) < 3: return tanggal
            dp = parts[2].split('.')
            if len(dp) < 2: return tanggal
            tahun_surat, bulan_surat_num = dp[0], dp[1].zfill(2)
            bulan_surat_nama = BULAN.get(bulan_surat_num)
            if not bulan_surat_nama or not tahun_surat.isdigit(): return tanggal
            m = re.match(r'(\d{1,2})\s+(\S+)\s+(\d{4})', str(tanggal or ''))
            if not m: return tanggal
            day, mon_nama, yr = m.group(1), m.group(2), m.group(3)
            changed = False
            if mon_nama.lower() != bulan_surat_nama.lower():
                logger.warning(f"Tanggal bulan fix: {mon_nama}->{bulan_surat_nama}")
                mon_nama = bulan_surat_nama; changed = True
            if not yr.isdigit() or int(yr) < 2024 or abs(int(yr) - int(tahun_surat)) > 1:
                logger.warning(f"Tanggal tahun fix: {yr}->{tahun_surat}")
                yr = tahun_surat; changed = True
            if changed: return f"{int(day):02d} {mon_nama} {yr}"
        except Exception as e:
            logger.warning(f"_validate_tanggal_sir20: {e}")
        return tanggal

    def _resolve_ditto(self, baris):
        """
        Aturan ditto yang benar:
          - Jika no_lot = null/kosong/simbol ditto → COPY dari baris di ATASNYA.
          - Jika no_lot = angka (int, float, atau string angka) → angka itu VALID, simpan sebagai last.
          - 'HI', 'II', 'v', '"', ',' dll → ditto mark (salin dari atas).
          - Angka berapa pun (1 digit, 2 digit, 3 digit: 136, 157) → SELALU valid.
        """
        DITTO_STRINGS = {
            'v', 'u', '"', ',', '-', "'", '`', '~', 'idem', 'sd', 's/d', '=',
            '//', 'do.', 'ditto', 'hi', 'h.i', 'h.i.', 'ii', 'id', 'sd.', '--',
            '==', 'dito', 'idem.', 'ij', 'ij.', 's', 'ss'
        }
        last = None
        for row in baris:
            lot = row.get('no_lot')

            # Angka integer/float langsung = nilai lot valid
            if isinstance(lot, (int, float)):
                clean = str(int(lot))
                row['no_lot'] = clean
                last = clean
                continue

            # String: normalisasi
            lot_str = str(lot).strip() if lot is not None else ''
            lot_lower = lot_str.lower()

            if not lot_str:
                # null/kosong → salin dari atas
                if last is not None:
                    row['no_lot'] = last
                    logger.info(f"Ditto(null)->{last}")
                continue

            # Cek apakah string angka murni ("136", "157", "2", dll)
            clean_digits = lot_str.replace('.', '').replace(',', '').strip()
            if clean_digits.isdigit():
                # Ini angka valid, bukan ditto
                row['no_lot'] = clean_digits
                last = clean_digits
                continue

            # Cek apakah termasuk simbol/kata ditto
            is_ditto = lot_lower in DITTO_STRINGS
            if not is_ditto:
                # Jika hanya 1-2 karakter non-digit → anggap ditto
                is_ditto = len(lot_str) <= 2 and not lot_str.isdigit()

            if is_ditto:
                if last is not None:
                    row['no_lot'] = last
                    logger.info(f"Ditto('{lot_str}')->{last}")
                else:
                    row['no_lot'] = None
                    logger.warning(f"Ditto('{lot_str}') di baris pertama tanpa referensi -> null")
            else:
                # String panjang tidak dikenali — simpan apa adanya dan jadikan last
                row['no_lot'] = lot_str
                last = lot_str
                logger.warning(f"no_lot tidak dikenal: '{lot_str}' (disimpan apa adanya)")

        return baris


    def _validate_no_peti(self, baris):
        pattern = re.compile(r'^[A-Z]{3}\s+\d+$')
        for row in baris:
            np_ = row.get('no_peti')
            if np_ and not pattern.match(str(np_).strip()):
                cleaned = re.sub(r'\s+', ' ', str(np_).strip().upper())
                m = re.match(r'([A-Z]{2,4})\s*(\d+)', cleaned)
                if m:
                    letters = m.group(1)[:3].ljust(3,'X')
                    row['no_peti'] = f"{letters} {m.group(2)}"
                    logger.info(f"no_peti fix: '{np_}'->'{row['no_peti']}'")
        return baris

    def _validate_berat(self, baris):
        for row in baris:
            bk = row.get('berat_kg', 0)
            try:
                bk = float(str(bk).replace('.','').replace(',','.')) if isinstance(bk, str) else float(bk)
                if 0.4 <= bk <= 3.0:  # terbaca sebagai 1.2 bukannya 1200
                    bk = bk * 1000
                    logger.info(f"berat_kg scale fix: {row.get('berat_kg')}->{bk}")
                row['berat_kg'] = int(bk) if bk > 0 else 0
            except: pass
        return baris

    def _expand_urut(self, h):
        if not h.get('nomor_urut_bale') and h.get('nomor_bale'):
            out = []
            for r in h['nomor_bale']:
                try:
                    a, b = map(int, str(r).split('-'))
                    out.extend(range(a, b+1))
                except: pass
            if out: h['nomor_urut_bale'] = out; logger.info(f"Urut: {len(out)} bales")
        return h

    def _detect_hallucination(self, baris: list) -> list:
        """
        Deteksi halusinasi pada baris SIR20 dan bersihkan data palsu.
        Halusinasi umum dari LLM saat gambar terlalu buram:
          1. no_peti angka berurutan sempurna (1095,1096,1097,...)
          2. SEMUA berat_kg identik (semua 1200 atau semua 1260)
          3. Kombinasi 1+2 = hampir pasti fabricated

        PENTING: Berat karet real JUGA bisa semua sama (tiap bale memang ~1200kg).
        Oleh karena itu, clear baris HANYA jika KEDUANYA sequential DAN berat identik.
        Jika hanya berat identik tanpa sequential no_peti → biarkan (mungkin data real).
        """
        if not baris or len(baris) < 2:
            return baris

        # Ekstrak digit dari no_peti untuk cek sequential
        peti_nums = []
        for row in baris:
            np_ = str(row.get('no_peti') or '').strip()
            m = re.search(r'(\d+)$', np_)
            if m:
                peti_nums.append(int(m.group(1)))

        berats = [row.get('berat_kg', 0) for row in baris]

        # Cek sequential no_peti (selisih selalu 1 = fabricated)
        is_sequential = False
        if len(peti_nums) >= 3:
            diffs = [abs(peti_nums[i+1] - peti_nums[i]) for i in range(len(peti_nums)-1)]
            if all(d == 1 for d in diffs):
                is_sequential = True
                logger.warning(f"HALLUCINATION: no_peti sequential {peti_nums}")

        # Cek semua berat identik (dan > 3 baris — 3 baris bisa kebetulan sama)
        all_berat_same = len(set(berats)) == 1 and len(berats) > 3 and berats[0] > 0
        if all_berat_same:
            logger.warning(f"HALLUCINATION: all berat_kg identical ({berats[0]})")

        # Clear HANYA jika KEDUANYA terpenuhi (sequential + berat identik)
        # Berat real karet memang sering sama semua (~1200kg), jadi tidak cukup 1 indikator
        if is_sequential and all_berat_same:
            logger.warning("Baris cleared: BOTH sequential peti AND identical berat detected.")
            return []

        # Jika hanya sequential tapi berat beragam → biarkan
        # Jika hanya berat identik tapi peti tidak sequential → biarkan (data real)
        if is_sequential and not all_berat_same:
            logger.warning("Sequential peti detected but berat varies → keeping data (may be real)")
        if all_berat_same and not is_sequential:
            logger.warning("Identical berat detected but peti not sequential → keeping data (real rubber weight)")

        return baris



    def _prep(self, img_bytes: bytes, max_dim: int = 1400) -> Image.Image:
        img = Image.open(io.BytesIO(img_bytes)).convert("RGB")
        img = self._exif_rotate(img)
        img = self._to_portrait(img)
        img = self._autocrop(img)
        img = self._deskew(img)
        img = self._enhance_clahe(img)
        return self._resize(img, max_dim)

    def _exif_rotate(self, img):
        try:
            exif = img._getexif()
            if exif:
                for tag, val in exif.items():
                    if ExifTags.TAGS.get(tag) == 'Orientation':
                        if val == 3: return img.rotate(180, expand=True)
                        if val == 6: return img.rotate(270, expand=True)
                        if val == 8: return img.rotate(90,  expand=True)
        except: pass
        return img

    def _to_portrait(self, img):
        w, h = img.size
        if w > h:
            img = img.rotate(90, expand=True)
            logger.info("Auto-rotate landscape→portrait")
        return img

    def _deskew(self, img):
        try:
            arr = np.array(img)
            gray = cv2.cvtColor(arr, cv2.COLOR_RGB2GRAY)
            _, th = cv2.threshold(gray, 0, 255, cv2.THRESH_BINARY_INV + cv2.THRESH_OTSU)
            edges = cv2.Canny(th, 50, 150, apertureSize=3)
            lines = cv2.HoughLines(edges, 1, np.pi / 180, 120)
            if lines is None or len(lines) < 4: return img
            angles = [np.degrees(l[0][1]) - 90 for l in lines if -20 < np.degrees(l[0][1]) - 90 < 20]
            if not angles: return img
            skew = float(np.median(angles))
            if abs(skew) < 0.3: return img
            logger.info(f"Deskew {skew:.2f}deg")
            return img.rotate(-skew, expand=True, resample=Image.BICUBIC, fillcolor=(255, 255, 255))
        except Exception as e:
            logger.warning(f"Deskew skip: {e}"); return img

    def _enhance_clahe(self, img):
        """
        CLAHE (Contrast Limited Adaptive Histogram Equalization).
        Jauh lebih baik dari brightness/contrast biasa untuk:
        - Pencahayaan tidak merata, bayangan, flash terlalu terang.
        """
        try:
            arr = np.array(img)
            lab = cv2.cvtColor(arr, cv2.COLOR_RGB2LAB)
            l_channel, a, b = cv2.split(lab)
            clahe = cv2.createCLAHE(clipLimit=2.5, tileGridSize=(8, 8))
            l_enhanced = clahe.apply(l_channel)
            enhanced_lab = cv2.merge([l_enhanced, a, b])
            enhanced_rgb = cv2.cvtColor(enhanced_lab, cv2.COLOR_LAB2RGB)
            result = Image.fromarray(enhanced_rgb)
            result = ImageEnhance.Sharpness(result).enhance(1.4)
            return result
        except Exception as e:
            logger.warning(f"CLAHE skip: {e}")
            avg = statistics.mean(list(img.convert('L').getdata()))
            if avg < 100: img = ImageEnhance.Brightness(img).enhance(min(140 / max(avg, 10), 2.5))
            return ImageEnhance.Contrast(img).enhance(1.35)

    def _resize(self, img, max_dim):
        w, h = img.size
        if max(w, h) > max_dim:
            s = max_dim / max(w, h)
            img = img.resize((int(w * s), int(h * s)), Image.LANCZOS)
        return img

    def _autocrop(self, img):
        try:
            arr = np.array(img)
            gray = cv2.cvtColor(arr, cv2.COLOR_RGB2GRAY)
            h_img, w_img = gray.shape
            img_area = w_img * h_img

            blurred = cv2.GaussianBlur(gray, (5, 5), 0)
            edges = cv2.Canny(blurred, 25, 80)
            kernel_e = cv2.getStructuringElement(cv2.MORPH_RECT, (20, 20))
            edges_d = cv2.morphologyEx(edges, cv2.MORPH_CLOSE, kernel_e)
            cnts_e, _ = cv2.findContours(edges_d, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
            best_edge = self._best_doc_contour(cnts_e, img_area)

            _, mask_w = cv2.threshold(gray, 170, 255, cv2.THRESH_BINARY)
            kernel_w = cv2.getStructuringElement(cv2.MORPH_RECT, (40, 40))
            mask_w = cv2.morphologyEx(mask_w, cv2.MORPH_CLOSE, kernel_w)
            mask_w = cv2.morphologyEx(mask_w, cv2.MORPH_OPEN, kernel_w)
            cnts_w, _ = cv2.findContours(mask_w, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
            best_white = self._best_doc_contour(cnts_w, img_area)

            candidates = [c for c in [best_edge, best_white] if c is not None]
            if not candidates: return img

            def doc_score(bbox):
                x, y, w, h = bbox
                aspect = w / h if h > 0 else 0
                return -abs(aspect - 0.68) + (w * h / img_area) * 0.5

            best = max(candidates, key=doc_score)
            x, y, w, h = best
            pad = int(min(w_img, h_img) * 0.012)
            cropped = img.crop((max(0, x-pad), max(0, y-pad),
                                min(w_img, x+w+pad), min(h_img, y+h+pad)))
            if cropped.width * cropped.height < img_area * 0.95:
                logger.info(f"Autocrop: {img.size}->{cropped.size}")
                return cropped
            return img
        except Exception as e:
            logger.warning(f"Autocrop skip: {e}"); return img

    def _best_doc_contour(self, cnts, img_area, min_ratio=0.15, max_ratio=0.92):
        best, best_area = None, 0
        for c in cnts:
            area = cv2.contourArea(c)
            ratio = area / img_area
            if ratio < min_ratio or ratio > max_ratio: continue
            x, y, w, h = cv2.boundingRect(c)
            if area > best_area: best_area = area; best = (x, y, w, h)
        return best

    def _b64(self, img, q=85):
        buf = io.BytesIO()
        img.save(buf, format='JPEG', quality=q)
        return base64.b64encode(buf.getvalue()).decode()

    # ── API calls ─────────────────────────────────────────────────────────────

    def _groq_call(self, b64: str, prompt: str, max_tokens: int) -> dict:
        """Groq Llama-4-Scout — CONFIRMED vision support. Timeout 9s."""
        if not self.groq_ok: return {}
        payload = {
            "model": GROQ_MODEL, "temperature": 0, "max_tokens": max_tokens,
            "messages": [{"role": "user", "content": [
                {"type": "image_url", "image_url": {"url": f"data:image/jpeg;base64,{b64}"}},
                {"type": "text", "text": prompt}
            ]}]
        }
        try:
            t0 = time.time()
            r = requests.post(
                GROQ_URL,
                headers={"Authorization": f"Bearer {GROQ_TOKEN}", "Content-Type": "application/json"},
                json=payload, timeout=9
            )
            elapsed = round(time.time() - t0, 1)
            logger.info(f"Groq {r.status_code} {elapsed}s")
            if r.status_code == 200:
                return self._parse(r.json()['choices'][0]['message']['content'])
            if r.status_code == 429:
                logger.warning("Groq 429 rate-limit")
            else:
                logger.error(f"Groq {r.status_code}: {r.text[:100]}")
        except requests.Timeout:
            logger.warning("Groq timeout 9s → OR will handle")
        except Exception as e:
            logger.error(f"Groq error: {e}")
        return {}

    def _or_call(self, b64: str, prompt: str, max_tokens: int, model: str) -> dict:
        """Single OpenRouter model call. Timeout 18s."""
        if not self.or_ok: return {}
        payload = {
            "model": model, "temperature": 0, "max_tokens": max_tokens,
            "messages": [{"role": "user", "content": [
                {"type": "image_url", "image_url": {"url": f"data:image/jpeg;base64,{b64}"}},
                {"type": "text", "text": prompt}
            ]}]
        }
        headers = {
            "Authorization": f"Bearer {OPENROUTER_TOKEN}",
            "Content-Type": "application/json",
            "X-Title": "WMS-OCR-PTPN",
            "HTTP-Referer": "http://localhost:8000",
        }
        try:
            t0 = time.time()
            r = requests.post(OPENROUTER_URL, headers=headers, json=payload, timeout=18)
            elapsed = round(time.time() - t0, 1)
            model_short = model.split('/')[-1][:20]
            logger.info(f"OR[{model_short}] {r.status_code} {elapsed}s")
            if r.status_code == 200:
                return self._parse(r.json()['choices'][0]['message']['content'])
            logger.warning(f"OR[{model_short}] {r.status_code}")
        except requests.Timeout:
            logger.warning(f"OR[{model.split('/')[-1][:20]}] timeout 18s")
        except Exception as e:
            logger.error(f"OR error: {e}")
        return {}

    def _call_parallel(self, b64: str, prompt: str, max_tokens: int) -> dict:
        """
        TRUE PARALLEL RACING (v16) — Groq + OR semua di-fire BERSAMAAN dari detik 0.

        Kenapa tidak staggered lagi?
        - Tanpa Gemini, Groq Scout dan Maverick kira-kira sama cepatnya
        - Staggered 3s hanya membuang waktu jika Groq = OR kecepatannya setara
        - Full parallel: ambil hasil pertama yang valid, cancel sisanya

        Timeout: Groq 9s, OR 20s, overall 22s
        """
        result_holder = [{}]
        finish_time   = [None]  # waktu selesai per provider
        done_event = Event()

        def groq_task():
            r = self._groq_call(b64, prompt, max_tokens)
            if r and not done_event.is_set():
                result_holder[0] = r
                finish_time[0] = time.time()
                done_event.set()
                logger.info("Winner: Groq")
            return r

        def or_task(model: str):
            r = self._or_call(b64, prompt, max_tokens, model)
            if r and not done_event.is_set():
                result_holder[0] = r
                finish_time[0] = time.time()
                done_event.set()
                short = model.split('/')[-1][:25]
                logger.info(f"Winner: OR[{short}]")
            return r

        # Fire semua sekaligus
        tasks = []
        with ThreadPoolExecutor(max_workers=1 + len(OR_MODELS)) as pool:
            if self.groq_ok:
                tasks.append(pool.submit(groq_task))
            if self.or_ok:
                for m in OR_MODELS:
                    tasks.append(pool.submit(or_task, m))
            # Tunggu winner atau timeout 22s
            done_event.wait(timeout=22)
            # Cancel semua yang belum selesai
            for t in tasks:
                t.cancel()

        if not result_holder[0]:
            logger.error("Semua provider gagal / timeout")
        return result_holder[0]

    # Alias untuk backward compatibility
    def _call_staggered(self, b64: str, prompt: str, max_tokens: int) -> dict:
        return self._call_parallel(b64, prompt, max_tokens)

    def _parse(self, text: str) -> dict:
        if not text: return {}
        c = text.strip()
        for marker in ["```json", "```"]:
            if marker in c:
                parts = c.split(marker)
                if len(parts) >= 2:
                    c = parts[1].split("```")[0].strip()
                    break
        start = c.find('{')
        if start == -1:
            logger.error(f"No JSON found: {c[:150]}")
            return {}
        c = c[start:]
        end = c.rfind('}')
        if end == -1:
            logger.warning("Truncated JSON, repairing...")
            c = self._repair_json(c)
        else:
            c = c[:end + 1]
        try:
            return json.loads(c)
        except json.JSONDecodeError:
            repaired = self._repair_json(c)
            try:
                return json.loads(repaired)
            except Exception as e:
                logger.error(f"JSON parse fail ({e}): {c[:200]}")
                return {}

    def _repair_json(self, s: str) -> str:
        in_string = False
        escape_next = False
        open_braces = 0
        open_brackets = 0
        result = []
        for ch in s:
            if escape_next: escape_next = False; result.append(ch); continue
            if ch == '\\' and in_string: escape_next = True; result.append(ch); continue
            if ch == '"' and not in_string: in_string = True; result.append(ch); continue
            if ch == '"' and in_string: in_string = False; result.append(ch); continue
            if not in_string:
                if ch == '{': open_braces += 1
                elif ch == '}': open_braces -= 1
                elif ch == '[': open_brackets += 1
                elif ch == ']': open_brackets -= 1
            result.append(ch)
        if in_string: result.append('"')
        repaired = ''.join(result).rstrip().rstrip(',')
        repaired += ']' * max(0, open_brackets)
        repaired += '}' * max(0, open_braces)
        logger.info("JSON repaired: +%d] +%d}" % (max(0, open_brackets), max(0, open_braces)))
        return repaired

    # ── blur detection ────────────────────────────────────────────────────────

    def _check_blur(self, img_bytes: bytes) -> dict:
        """Laplacian variance blur detection. Threshold diturunkan agar toleran foto tulisan tipis."""
        try:
            img = Image.open(io.BytesIO(img_bytes)).convert("L")
            w, h = img.size
            if max(w, h) > 800:
                s = 800 / max(w, h)
                img = img.resize((int(w * s), int(h * s)), Image.LANCZOS)
            arr = np.array(img, dtype=np.float64)
            lap_var = float(cv2.Laplacian(arr.astype(np.uint8), cv2.CV_64F).var())
            if lap_var >= 80:   status = "ok"
            elif lap_var >= 40: status = "warning"
            else:               status = "blur"
            logger.info(f"Blur: laplacian={lap_var:.1f} status={status}")
            return {"score": round(lap_var, 1), "status": status}
        except Exception as e:
            logger.warning(f"Blur check error: {e}")
            return {"score": 999, "status": "ok"}

    # ── confidence scoring ────────────────────────────────────────────────────

    def _confidence_score(self, h: dict, jenis: str) -> dict:
        CRITICAL = {
            "sir20":       ["no_surat", "tanggal", "no_kendaraan", "nama_supir", "baris"],
            "rss1":        ["no_dokumen", "tanggal", "kebun", "jumlah_bale", "nomor_bale"],
            "do":          ["no_so_internal", "tanggal_so", "nama_pembeli", "volume"],
            "surat_kuasa": ["no_surat_kuasa", "tanggal", "nama_pemberi", "jumlah_kg"],
        }
        fields = CRITICAL.get(jenis, [])
        if not fields: return {"score": 100, "level": "high", "missing": []}
        missing = [f for f in fields if not h.get(f) or h.get(f) in [0, [], ""]]
        warnings = []
        if jenis == "sir20" and h.get("baris") and len(h["baris"]) > 1:
            lots  = [str(b.get("no_lot","")) for b in h["baris"]]
            berats = [b.get("berat_kg",0) for b in h["baris"]]
            if len(set(lots)) == 1: warnings.append("semua no_lot identik")
            if len(set(berats)) == 1 and len(berats) > 2: warnings.append("semua berat_kg identik")
        score = max(0, 100 - len(missing)*20 - len(warnings)*15)
        level = "high" if score>=80 else ("medium" if score>=50 else "low")
        if warnings: logger.warning(f"Confidence [{jenis}]: {warnings}")
        return {"score": score, "level": level, "missing": missing, "warnings": warnings}

    # ── main ──────────────────────────────────────────────────────────────────

    def process(self, img_bytes: bytes, jenis: str) -> dict:
        t0 = time.time()
        logger.info(f"=== START {jenis.upper()} {len(img_bytes)//1024}KB ===")

        blur = self._check_blur(img_bytes)
        if blur["status"] == "blur":
            logger.warning(f"Foto terlalu blur (score={blur['score']})")
            return {
                "hasil": EMPTY.get(jenis, {}).copy(),
                "waktu_s": round(time.time() - t0, 2),
                "blur": blur,
                "confidence": {"score":0,"level":"low","missing":[],"warnings":["foto terlalu blur"]},
                "error": "Foto terlalu buram. Letakkan dokumen di permukaan datar dengan pencahayaan cukup, lalu foto ulang."
            }

        try:
            if jenis == 'sir20':
                img = self._prep(img_bytes, max_dim=2000)
                b64_img = self._b64(img, q=88)

                # == TWO-PASS OCR PARALEL: Header & Tabel difire bersamaan ==
                # Keduanya jalan paralel → total waktu = max(pass1, pass2) bukan pass1+pass2
                logger.info("SIR20 Two-pass PARALLEL: header + table...")
                h_header_holder = [{}]
                h_table_holder  = [{}]

                def run_header():
                    h_header_holder[0] = self._call_parallel(
                        b64_img, PROMPT_SIR20_HEADER, MAX_TOKENS['sir20_header'])

                def run_table():
                    h_table_holder[0] = self._call_parallel(
                        b64_img, PROMPT_SIR20_TABLE, MAX_TOKENS['sir20_table'])

                with ThreadPoolExecutor(max_workers=2) as p:
                    ft1 = p.submit(run_header)
                    ft2 = p.submit(run_table)
                    try:
                        ft1.result(timeout=30)
                        ft2.result(timeout=30)
                    except Exception as e:
                        logger.error(f"Two-pass error: {e}")

                h_header = h_header_holder[0]
                h_table  = h_table_holder[0]

                # Gabungkan hasil dua pass
                h = EMPTY['sir20'].copy()
                if h_header:
                    h['no_surat']     = h_header.get('no_surat')
                    h['tanggal']      = h_header.get('tanggal')
                    h['no_kendaraan'] = h_header.get('no_kendaraan')
                    h['nama_supir']   = h_header.get('nama_supir')
                if h_table:
                    h['baris']      = h_table.get('baris', [])
                    h['total_kg']   = h_table.get('total_kg', 0)
                    h['total_bale'] = h_table.get('total_bale', 0)

                # Fallback: jika keduanya gagal, coba single-pass
                if not h_header and not h_table:
                    logger.warning("Two-pass failed, fallback to single-pass")
                    h = self._call_parallel(b64_img, PROMPT_SIR20_FULL, MAX_TOKENS['sir20'])
                    if not h: h = EMPTY['sir20'].copy()
                if h.get('no_surat'): h['no_surat'] = self._fix_year_no_surat(h['no_surat'])
                if h.get('no_surat'): h['no_surat'] = self._fix_roman(h['no_surat'])
                if h.get('tanggal'):  h['tanggal']  = self._normalize_tanggal(h['tanggal'])
                if not h.get('tanggal') and h.get('no_surat'):
                    h['tanggal'] = self._infer_tanggal(h['no_surat'])
                if h.get('tanggal') and h.get('no_surat'):
                    h['tanggal'] = self._validate_tanggal_sir20(h['tanggal'], h['no_surat'])
                if h.get('baris'):
                    h['baris'] = self._resolve_ditto(h['baris'])
                    h['baris'] = self._validate_no_peti(h['baris'])
                    h['baris'] = self._validate_berat(h['baris'])
                    h['baris'] = self._detect_hallucination(h['baris'])  # v15.2: clear fake data

            elif jenis == 'rss1':
                img = self._prep(img_bytes, max_dim=1400)
                h = self._call_staggered(self._b64(img, q=85), PROMPT_RSS1, MAX_TOKENS['rss1'])
                if not h: h = EMPTY['rss1'].copy()
                if isinstance(h.get('mutu'), str) and ',' in h['mutu']:
                    h['mutu'] = h['mutu'].split(',')[0].strip()
                if h.get('tanggal'): h['tanggal'] = self._normalize_tanggal(h['tanggal'])
                if not h.get('tanggal') and h.get('no_dokumen'):
                    h['tanggal'] = self._infer_tanggal(h['no_dokumen'])
                if 'nomor_urut_bale' not in h: h['nomor_urut_bale'] = []
                h = self._expand_urut(h)

            elif jenis == 'do':
                img = self._prep(img_bytes, max_dim=1300)
                h = self._call_staggered(self._b64(img, q=85), PROMPT_DO, MAX_TOKENS['do'])
                if not h: h = EMPTY['do'].copy()

            elif jenis == 'surat_kuasa':
                img = self._prep(img_bytes, max_dim=1400)
                h = self._call_staggered(self._b64(img, q=85), PROMPT_SURAT_KUASA, MAX_TOKENS['surat_kuasa'])
                if not h: h = EMPTY['surat_kuasa'].copy()

            else:
                raise ValueError(f"Jenis tidak dikenal: {jenis}")

            if not h: h = EMPTY.get(jenis, {}).copy()

        except Exception as e:
            logger.error(f"Process error [{jenis}]: {e}", exc_info=True)
            h = EMPTY.get(jenis, {}).copy()

        waktu = round(time.time() - t0, 2)
        confidence = self._confidence_score(h, jenis)
        logger.info(f"=== DONE {jenis.upper()} {waktu}s confidence={confidence['score']} ({confidence['level']}) ===")
        result = {"hasil": h, "waktu_s": waktu, "blur": blur, "confidence": confidence}
        if blur["status"] == "warning":
            result["warning"] = "Foto agak buram. Periksa semua field sebelum menyimpan."
        return result