@echo off
title NextGen Medics - FRESH install (WARNING)
echo.
echo  WARNING: For EMPTY database only.
echo  If you already added teachers/students, DO NOT run this.
echo  Use run-migrations.bat instead.
echo.
set /p CONFIRM="Type YES to continue with fresh install: "
if /i not "%CONFIRM%"=="YES" (
  echo Cancelled. Your data was not touched.
  pause
  exit /b 0
)
cd /d C:\xampp\php
php.exe -c C:\xampp\php\php.ini "d:\LMS\backend\database\install.php"
pause
