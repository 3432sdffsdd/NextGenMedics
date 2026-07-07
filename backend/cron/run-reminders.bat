@echo off
title NGM Class Reminders
cd /d C:\xampp\php
:loop
php.exe -c C:\xampp\php\php.ini -r "file_get_contents('http://127.0.0.1:8080/cron/class-reminders?key=ngm-cron-local-dev');"
timeout /t 60 /nobreak >nul
goto loop
