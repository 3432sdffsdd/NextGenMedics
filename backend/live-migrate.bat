@echo off
REM ============================================================
REM NextGen Medics LMS — LIVE server safe database update (Windows)
REM ============================================================
REM SAFE: Does NOT delete teachers, students, courses, or enrollments.
REM
REM HOW TO RUN:
REM   Double-click this file, OR from cmd:
REM   cd D:\path\to\your\backend
REM   live-migrate.bat
REM ============================================================

title NextGen Medics - Live DB Update (Safe)
cd /d "%~dp0"

echo.
echo NextGen Medics - Live database update
echo =====================================
echo Folder: %CD%
echo.

if not exist ".env" (
  echo ERROR: .env not found. Set your LIVE database credentials in .env first.
  pause
  exit /b 1
)

if not exist "database\migrate.php" (
  echo ERROR: database\migrate.php not found. Upload the full backend folder first.
  pause
  exit /b 1
)

REM Adjust php path if your live server uses a different PHP location
set PHP=C:\xampp\php\php.exe
if not exist "%PHP%" set PHP=php

echo Using: %PHP%
echo.
"%PHP%" database\migrate.php
echo.
echo Live database update finished.
echo Remember: also upload backend PHP files + frontend dist\ if you have not yet.
echo.
pause
