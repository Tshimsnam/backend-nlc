@echo off
echo ========================================
echo   Demarrage complet avec LocalTunnel
echo   Frontend + Backend + Tunnels
echo ========================================
echo.

REM Vérifier que LocalTunnel est installé
where lt >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo [ERREUR] LocalTunnel n'est pas installe
    echo.
    echo Installation :
    echo   npm install -g localtunnel
    echo.
    pause
    exit /b 1
)

echo [1/4] Demarrage du Backend Laravel...
start "Laravel Server" cmd /k "php artisan serve --host=192.168.241.9 --port=8000"

timeout /t 2 /nobreak >nul

echo [2/4] Demarrage de LocalTunnel Backend...
start "LocalTunnel Backend" cmd /k "lt --port 8000 --subdomain nlc-maxicash-api-rdc"

timeout /t 2 /nobreak >nul

echo [3/4] Demarrage du Frontend (npm run dev)...
REM Adapter le chemin vers votre dossier frontend
start "Frontend Dev" cmd /k "cd /d D:\choupole\Projects\Website\frontend-nlc && npm run dev"

timeout /t 5 /nobreak >nul

echo [4/4] Demarrage de LocalTunnel Frontend...
start "LocalTunnel Frontend" cmd /k "lt --port 8080 --subdomain nlc-maxicash-rdc"

echo.
echo ========================================
echo   Tous les services sont demarres !
echo ========================================
echo.
echo URLs :
echo   Frontend :
echo     - Local  : http://localhost:8080
echo     - Public : https://nlc-maxicash-rdc.loca.lt
echo.
echo   Backend :
echo     - Local  : http://192.168.241.9:8000
echo     - Public : https://nlc-maxicash-api-rdc.loca.lt
echo.
echo IMPORTANT - A faire maintenant :
echo   1. Ouvrir https://nlc-maxicash-rdc.loca.lt
echo      Cliquer "Click to Continue"
echo.
echo   2. Ouvrir https://nlc-maxicash-api-rdc.loca.lt
echo      Cliquer "Click to Continue"
echo.
echo   3. Mettre a jour .env avec :
echo      MAXICASH_NOTIFY_URL=https://nlc-maxicash-api-rdc.loca.lt/api/webhooks/maxicash
echo.
echo   4. Redemarrer Laravel (Ctrl+C dans le terminal Laravel puis relancer)
echo.
echo   5. Tester : php test-ticket-payment.php
echo.
pause
