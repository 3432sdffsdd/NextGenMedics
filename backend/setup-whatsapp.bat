@echo off
setlocal EnableDelayedExpansion
title NextGen Medics - WhatsApp API Setup
color 0A
set "ROOT=%~dp0"
set "ROOT=%ROOT:~0,-1%"

echo.
echo ============================================================
echo   WhatsApp API Setup (Meta Cloud API)
echo ============================================================
echo.
echo This opens Meta Developer in your browser.
echo Copy your Phone Number ID and Access Token, then paste below.
echo.
echo WHAT YOU NEED from Meta ^> WhatsApp ^> API Setup:
echo   - Phone number ID
echo   - Temporary access token
echo.
pause
start https://developers.facebook.com/apps/
echo.
echo --- QUICK STEPS ---
echo 1. Create App ^> Other ^> Business
echo 2. Add product: WhatsApp
echo 3. WhatsApp ^> API Setup ^> copy Phone number ID + Token
echo 4. Add YOUR phone under test recipients and verify the code
echo.
set /p PHONE_ID=Paste Phone Number ID: 
set /p TOKEN=Paste Access Token: 
set /p TEST_PHONE=Your phone for test (e.g. 03218902931): 
echo.

powershell -NoProfile -ExecutionPolicy Bypass -File "%ROOT%\scripts\update-whatsapp-env.ps1" -EnvFile "%ROOT%\.env" -PhoneNumberId "%PHONE_ID%" -AccessToken "%TOKEN%"

if errorlevel 1 (
    echo Failed to update .env
    pause
    exit /b 1
)

echo.
echo .env updated. Sending test message...
echo.

where php >nul 2>&1
if errorlevel 1 (
    if exist "C:\xampp\php\php.exe" (
        set "PHP=C:\xampp\php\php.exe"
    ) else (
        echo PHP not found. Install XAMPP or add php to PATH.
        echo You can test later: php "%ROOT%\scripts\test-whatsapp.php" %TEST_PHONE%
        pause
        exit /b 1
    )
) else (
    set "PHP=php"
)

"%PHP%" -f "%ROOT%\scripts\test-whatsapp.php" "%TEST_PHONE%"

echo.
echo ============================================================
echo   DONE
echo ============================================================
echo - Restart API: close "NGM API" window and run start-lms.bat
echo - Guide: %ROOT%\docs\WHATSAPP_SETUP.md
echo - Log: %ROOT%\storage\logs\whatsapp.log
echo.
pause
