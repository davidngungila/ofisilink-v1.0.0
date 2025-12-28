@echo off
echo ========================================
echo ZKTeco SDK Download Helper
echo ========================================
echo.
echo This script will open the ZKTeco SDK download page
echo.
echo Recommended Download:
echo   ZKBioModuleSDK_20210113.zip (15.54MB)
echo   - For device communication
echo   - Best for PHP/Laravel integration
echo.
echo ========================================
echo.
echo Opening ZKTeco SDK download page...
echo.

start https://www.zkteco.com/en/SDK

echo.
echo ========================================
echo Download Instructions:
echo ========================================
echo.
echo 1. On the ZKTeco website:
echo    - Navigate to: Support ^> Download Center ^> SDK
echo    - Find "ZKBioModuleSDK_20210113.zip"
echo    - Click Download
echo    - You may need to login/register first
echo.
echo 2. Save the file to:
echo    %CD%\sdk\
echo.
echo 3. Extract the ZIP file to:
echo    %CD%\sdk\zkbiomodulesdk\
echo.
echo 4. Review the documentation included
echo.
echo ========================================
echo.
echo Note: Our current PHP implementation works WITHOUT
echo the SDK. The SDK is useful for:
echo   - Reference documentation
echo   - Advanced fingerprint processing
echo   - Protocol verification
echo.
echo ========================================
echo.
pause
