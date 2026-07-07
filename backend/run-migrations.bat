@echo off
title NextGen Medics - Safe migrations (keeps your data)
echo.
echo  SAFE UPDATE - does NOT delete teachers, students, or enrollments.
echo  Only adds new tables/columns if needed.
echo.
cd /d C:\xampp\php
php.exe -c C:\xampp\php\php.ini "d:\LMS\backend\database\migrate.php"
echo.
pause
