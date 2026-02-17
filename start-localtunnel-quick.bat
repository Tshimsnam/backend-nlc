@echo off
echo ========================================
echo   Configuration LocalTunnel MaxiCash
echo ========================================
echo.

echo Etape 1: Verification de LocalTunnel...
where lt >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERREUR] LocalTunnel n'est pas installe!
    echo.
    echo Installez-le avec: npm install -g localtunnel
    echo.
    pause
    exit /b 1
)
echo [OK] LocalTunnel est installe
echo.

echo Etape 2: Demarrage du backend Laravel...
echo Commande: php artisan serve --host=192.168.58.9 --port=8000
echo.
echo IMPORTANT: Ouvrez un nouveau terminal et executez:
echo   cd %CD%
echo   php artisan serve --host=192.168.58.9 --port=8000
echo.
pause

echo.
echo Etape 3: Exposition du backend avec LocalTunnel...
echo Commande: lt --port 8000 --subdomain nlc-maxicash-api-rdc
echo.
echo IMPORTANT: Ouvrez un nouveau terminal et executez:
echo   lt --port 8000 --subdomain nlc-maxicash-api-rdc
echo.
echo Vous obtiendrez: https://nlc-maxicash-api-rdc.loca.lt
echo.
pause

echo.
echo Etape 4: Demarrage du frontend...
echo.
echo IMPORTANT: Ouvrez un nouveau terminal et executez:
echo   cd ..\frontend-nlc
echo   npm run dev
echo.
pause

echo.
echo Etape 5: Exposition du frontend avec LocalTunnel...
echo Commande: lt --port 8080 --subdomain nlc-maxicash-rdc
echo.
echo IMPORTANT: Ouvrez un nouveau terminal et executez:
echo   lt --port 8080 --subdomain nlc-maxicash-rdc
echo.
echo Vous obtiendrez: https://nlc-maxicash-rdc.loca.lt
echo.
pause

echo.
echo Etape 6: Autorisation LocalTunnel...
echo.
echo Ouvrez dans votre navigateur et cliquez "Continue":
echo   - https://nlc-maxicash-rdc.loca.lt
echo   - https://nlc-maxicash-api-rdc.loca.lt
echo.
pause

echo.
echo Etape 7: Mise a jour du fichier .env...
echo.
echo Ajoutez ces lignes dans votre fichier .env:
echo.
echo MAXICASH_SUCCESS_URL=https://nlc-maxicash-rdc.loca.lt/paiement/success
echo MAXICASH_FAILURE_URL=https://nlc-maxicash-rdc.loca.lt/paiement/failure
echo MAXICASH_CANCEL_URL=https://nlc-maxicash-rdc.loca.lt/paiement/cancel
echo MAXICASH_NOTIFY_URL=https://nlc-maxicash-api-rdc.loca.lt/api/webhooks/maxicash
echo.
pause

echo.
echo Etape 8: Redemarrage de Laravel...
echo.
echo Dans le terminal du backend Laravel:
echo   1. Appuyez sur Ctrl+C pour arreter
echo   2. Executez: php artisan config:clear
echo   3. Executez: php artisan serve --host=192.168.58.9 --port=8000
echo.
pause

echo.
echo ========================================
echo   Configuration terminee!
echo ========================================
echo.
echo Testez maintenant l'inscription depuis:
echo   - Local: http://192.168.58.9:8080/evenements/1
echo   - Public: https://nlc-maxicash-rdc.loca.lt/evenements/1
echo.
echo La page MaxiCash devrait maintenant s'afficher sans erreur!
echo.
pause
