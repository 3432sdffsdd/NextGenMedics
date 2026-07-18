@echo off
title NGM AI Engine Worker
cd /d F:\LMS\backend
:loop
"C:\xampp\php\php.exe" -c "C:\xampp\php\php.ini" "F:\LMS\backend\scripts\ai-engine-daemon.php"
timeout /t 3 /nobreak >nul
goto loop
