@echo off
echo ========================================
echo   Backend Laravel + LocalTunnel
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

echo [1/2] Demarrage du serveur Laravel...
start "Laravel Server" cmd /k "php artisan serve --host=192.168.241.9 --port=8000"

timeout /t 3 /nobreak >nul

echo [2/2] Demarrage de LocalTunnel (Backend)...
start "LocalTunnel Backend" cmd /k "lt --port 8000 --subdomain nlc-maxicash-api-rdc"

echo.
echo ========================================
echo   Backend demarre avec succes !
echo ========================================
echo.
echo URLs :
echo   - Backend local  : http://192.168.241.9:8000
echo   - Backend public : https://nlc-maxicash-api-rdc.loca.lt
echo.
echo IMPORTANT :
echo   1. Ouvrir https://nlc-maxicash-api-rdc.loca.lt
echo   2. Cliquer sur "Click to Continue"
echo   3. Mettre a jour .env avec :
echo      MAXICASH_NOTIFY_URL=https://nlc-maxicash-api-rdc.loca.lt/api/webhooks/maxicash
echo   4. Redemarrer Laravel (Ctrl+C puis relancer)
echo.
pause
