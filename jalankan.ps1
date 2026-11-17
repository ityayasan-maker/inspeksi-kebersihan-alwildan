Write-Host "=======================================================================" -ForegroundColor Cyan
Write-Host "  Sistem Inspeksi Kebersihan Al-Wildan Islamic School 8 Kemang Bogor" -ForegroundColor Green
Write-Host "  Developer: Yayasan Ambadar" -ForegroundColor Yellow
Write-Host "=======================================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Menjalankan local server di http://localhost:8000 ..." -ForegroundColor White
Write-Host "Silakan buka browser di: http://localhost:8000/login" -ForegroundColor Green
Write-Host ""
Set-Location -Path $PSScriptRoot
php -S localhost:8000 router.php
