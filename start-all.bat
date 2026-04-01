@echo off
title WMS - All Servers
echo ================================================
echo  WMS - Starting All Services
echo ================================================
echo.

:: [1/3] Laravel Queue Worker
echo [1/3] Starting Laravel Queue Worker...
start "WMS Queue Worker" cmd /k "cd /d D:\laragon\www\Kerjapraktik && php artisan queue:work --sleep=3 --tries=2 --timeout=150 -v"

:: Tunggu sebentar biar queue ready
timeout /t 2 /nobreak >nul

:: [2/3] Python OCR Server
echo [2/3] Starting Python OCR Server (port 8001)...
start "WMS OCR Server" cmd /k "cd /d D:\laragon\www\Kerjapraktik\CORO && python -m uvicorn main:app --host 0.0.0.0 --port 8001"

:: Tunggu OCR server ready
timeout /t 3 /nobreak >nul

:: [3/3] Laravel Web Server — window terpisah agar tidak blocking
echo [3/3] Starting Laravel Web Server (port 8000)...
start "WMS Laravel" cmd /k "cd /d D:\laragon\www\Kerjapraktik && php artisan serve --port=8000"

echo.
echo ================================================
echo  Semua server berjalan di window terpisah!
echo.
echo  Laravel  : http://localhost:8000
echo  OCR API  : http://localhost:8001/health
echo  Queue    : berjalan di background
echo.
echo  JANGAN TUTUP window Queue Worker dan OCR Server!
echo  Tutup window ini aman - service tetap jalan.
echo ================================================
echo.
echo Tekan tombol apapun untuk menutup window ini...
pause >nul