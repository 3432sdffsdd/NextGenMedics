@echo off
title NextGen Medics - Import LIVE database dump
echo.
echo  This replaces your LOCAL database with the live dump.
echo  Your live/hosted database is NOT changed.
echo.
echo  Source: database\imports\nextgenmedics-live.sql
echo  Target: nextgen_medics (localhost)
echo.
set /p CONFIRM="Type YES to import and replace local data: "
if /i not "%CONFIRM%"=="YES" (
  echo Cancelled.
  pause
  exit /b 0
)

set MYSQL=C:\xampp\mysql\bin\mysql.exe
set PHP=C:\xampp\php\php.exe
set SQL=d:\LMS\backend\database\imports\nextgenmedics-live.sql

if not exist "%SQL%" (
  echo ERROR: Dump file not found: %SQL%
  echo Place your phpMyAdmin export at that path and run again.
  pause
  exit /b 1
)

echo.
echo [1/3] Recreating database nextgen_medics ...
"%MYSQL%" -u root -e "DROP DATABASE IF EXISTS nextgen_medics; CREATE DATABASE nextgen_medics CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if errorlevel 1 (
  echo ERROR: Could not create database. Is XAMPP MySQL running?
  pause
  exit /b 1
)

echo [2/3] Importing live dump (may take a minute) ...
"%MYSQL%" -u root nextgen_medics < "%SQL%"
if errorlevel 1 (
  echo ERROR: Import failed.
  pause
  exit /b 1
)

echo [3/3] Applying any new migrations from the codebase ...
"%PHP%" -c C:\xampp\php\php.ini "d:\LMS\backend\database\migrate.php"

echo.
echo Done. Local .env should use DB_NAME=nextgen_medics
echo Login examples from your live data:
echo   Admin: admin3@nextgenmedics.info
echo   Teacher Talha: talhanazeer3@gmail.com
echo   Teacher Sidrah: sskhan.pk@gmail.com
echo.
pause
