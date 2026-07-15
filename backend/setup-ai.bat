@echo off
setlocal EnableDelayedExpansion
title NextGen Medics - Free AI Setup (Groq)
color 0B
set "ROOT=%~dp0"
set "ROOT=%ROOT:~0,-1%"

echo.
echo ============================================================
echo   FREE AI Setup — Groq (no credit card)
echo ============================================================
echo.
echo 1. Browser will open: https://console.groq.com/keys
echo 2. Sign up free (Google/GitHub/email)
echo 3. Click "Create API Key" — copy the gsk_... key
echo 4. Paste it below
echo.
echo Free tier: thousands of requests/day — enough for study tools.
echo.
pause
start https://console.groq.com/keys
echo.
set /p APIKEY=Paste your Groq API key (gsk_...): 
if "%APIKEY%"=="" (
    echo No key entered. Cancelled.
    pause
    exit /b 1
)

powershell -NoProfile -ExecutionPolicy Bypass -File "%ROOT%\scripts\update-ai-env.ps1" -EnvFile "%ROOT%\.env" -ApiKey "%APIKEY%"

if errorlevel 1 (
    echo Failed to update .env
    pause
    exit /b 1
)

where php >nul 2>&1
if errorlevel 1 (
    if exist "C:\xampp\php\php.exe" (set "PHP=C:\xampp\php\php.exe") else (set "PHP=php")
) else (
    set "PHP=php"
)

echo.
echo Testing API key...
"%PHP%" -f "%ROOT%\scripts\test-ai-key.php"
if errorlevel 1 (
    echo.
    echo Key test FAILED. Check the key at console.groq.com and run this again.
    pause
    exit /b 1
)

echo.
echo ============================================================
echo   SUCCESS — AI is ready
echo ============================================================
echo Restart the API server (close NGM API window, run start-api.bat)
echo Then try Generate study resources in Study tools tab.
echo.
pause
