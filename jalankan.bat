@echo off
title Inspeksi Kebersihan - Al-Wildan Islamic School 8 Kemang Bogor
echo =======================================================================
echo   Sistem Inspeksi Kebersihan Al-Wildan Islamic School 8 Kemang Bogor
echo   Developer: Yayasan Ambadar
echo =======================================================================
echo.
echo Menjalankan local server di http://localhost:8000 ...
echo Silakan buka browser di: http://localhost:8000/login
echo.
echo Tekan Ctrl+C untuk menghentikan server.
echo.
cd /d "%~dp0"
php -S localhost:8000 router.php
pause
