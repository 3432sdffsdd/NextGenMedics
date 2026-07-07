@echo off
title NextGen Medics - Start All
echo Starting MySQL...
start /MIN "" "C:\xampp\mysql_start.bat"
timeout /t 5 /nobreak >nul

echo Starting API on http://127.0.0.1:8080 ...
start "NGM API" cmd /k "cd /d C:\xampp\php && php.exe -c C:\xampp\php\php.ini -S 0.0.0.0:8080 -t d:\LMS\backend\public d:\LMS\backend\public\index.php"

timeout /t 2 /nobreak >nul
echo Starting Frontend on http://localhost:5173 ...
start "NGM Frontend" cmd /k "cd /d d:\LMS\frontend && npm run dev -- --host"

timeout /t 2 /nobreak >nul
echo Starting class reminder worker (WhatsApp 10 min before class)...
start /MIN "NGM Reminders" cmd /k "d:\LMS\backend\cron\run-reminders.bat"

echo.
echo ========================================
echo   NextGen Medics LMS is starting...
echo ========================================
echo   Website:  http://localhost:5173
echo   API:      http://127.0.0.1:8080
echo   Login:    admin@nextgenmedics.com
echo   Password: Admin@123
echo ========================================
pause
