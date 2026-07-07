@echo off
title NextGen Medics API
cd /d C:\xampp\php
echo Starting NextGen Medics API at http://127.0.0.1:8080
echo Press Ctrl+C to stop.
php.exe -c C:\xampp\php\php.ini -S 0.0.0.0:8080 -t "d:\LMS\backend\public" "d:\LMS\backend\public\index.php"
