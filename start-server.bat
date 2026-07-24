@echo off
cd /d C:\xampp\htdocs\erapor
echo Starting E-Rapor local server...
echo URL: http://127.0.0.1:8000/
echo.
C:\xampp\php\php.exe -S 127.0.0.1:8000 -t public router.php
pause
