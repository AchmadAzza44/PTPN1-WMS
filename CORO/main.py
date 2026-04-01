# -*- coding: utf-8 -*-
"""
WMS-OCR FastAPI Server
Endpoint utama untuk OCR dokumen PTPN via HuggingFace API

Jalankan: uvicorn main:app --host 0.0.0.0 --port 8000 --reload
"""
import os, sys, io, base64, time, json, logging
from typing import Optional
from fastapi import FastAPI, HTTPException, UploadFile, File, Form
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel

# Load .env dari folder CORO (GROQ_API_KEY, HF_API_KEY)
try:
    from dotenv import load_dotenv
    _env_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), '.env')
    if os.path.exists(_env_path):
        load_dotenv(_env_path)
        print(f"[INFO] Loaded .env from {_env_path}")
    else:
        print(f"[WARN] .env not found, copy .env.example to .env dan isi GROQ_API_KEY")
except ImportError:
    print("[WARN] python-dotenv tidak terinstall, jalankan: pip install python-dotenv")

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from ocr_service.ocr_engine import OCREngine


logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

app = FastAPI(
    title="WMS-OCR API",
    description="OCR untuk dokumen PTPN: SIR20, RSS1, DO, Surat Kuasa",
    version="2.0.0"
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_methods=["*"],
    allow_headers=["*"],
)

# Init OCR engine sekali saat startup
engine = OCREngine()

# ── Models ────────────────────────────────────────────────────
class OCRRequest(BaseModel):
    foto_base64: str
    jenis: str  # sir20 | rss1 | do | surat_kuasa

class OCRResponse(BaseModel):
    success: bool
    jenis: str
    waktu_s: float
    hasil: dict
    error: Optional[str] = None
    blur: Optional[dict] = None
    confidence: Optional[dict] = None
    warning: Optional[str] = None

# ── Endpoints ─────────────────────────────────────────────────
@app.get("/")
def root():
    return {
        "service": "WMS-OCR API",
        "version": "2.0.0",
        "status": "running",
        "endpoints": {
            "POST /ocr": "OCR via base64",
            "POST /ocr/upload": "OCR via file upload",
            "GET /health": "Health check",
        }
    }

@app.get("/health")
def health():
    return {"status": "ok", "engine": engine.mode}

@app.post("/ocr", response_model=OCRResponse)
async def ocr_base64(req: OCRRequest):
    """OCR dari base64 image — untuk integrasi Laravel"""
    valid_jenis = ['sir20', 'rss1', 'do', 'surat_kuasa']
    if req.jenis not in valid_jenis:
        raise HTTPException(400, f"jenis harus salah satu dari: {valid_jenis}")

    try:
        img_bytes = base64.b64decode(req.foto_base64)
        result = engine.process(img_bytes, req.jenis)
        return OCRResponse(
            success=False if result.get("error") else True,
            jenis=req.jenis,
            waktu_s=result['waktu_s'],
            hasil=result.get('hasil', {}),
            blur=result.get('blur'),
            confidence=result.get('confidence'),
            warning=result.get('warning'),
            error=result.get('error')
        )
    except Exception as e:
        logger.error(f"OCR error: {e}")
        return OCRResponse(
            success=False,
            jenis=req.jenis,
            waktu_s=0,
            hasil={},
            error=str(e)
        )

@app.post("/ocr/upload", response_model=OCRResponse)
async def ocr_upload(
    foto: UploadFile = File(...),
    jenis: str = Form(...)
):
    """OCR dari file upload langsung"""
    valid_jenis = ['sir20', 'rss1', 'do', 'surat_kuasa']
    if jenis not in valid_jenis:
        raise HTTPException(400, f"jenis harus salah satu dari: {valid_jenis}")

    try:
        img_bytes = await foto.read()
        result = engine.process(img_bytes, jenis)
        return OCRResponse(
            success=False if result.get("error") else True,
            jenis=jenis,
            waktu_s=result['waktu_s'],
            hasil=result.get('hasil', {}),
            blur=result.get('blur'),
            confidence=result.get('confidence'),
            warning=result.get('warning'),
            error=result.get('error')
        )
    except Exception as e:
        logger.error(f"OCR upload error: {e}")
        return OCRResponse(
            success=False,
            jenis=jenis,
            waktu_s=0,
            hasil={},
            error=str(e)
        )