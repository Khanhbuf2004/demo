@echo off
echo ====================================
echo Cai dat thu vien Python cho QR Code
echo ====================================
echo.

REM Kiểm tra Python
python --version >nul 2>&1
if %errorlevel% neq 0 (
    echo [LOI] Python chua duoc cai dat hoac khong co trong PATH
    echo Vui long cai dat Python 3 tu python.org
    pause
    exit /b 1
)

echo [OK] Tim thay Python
python --version
echo.

echo Dang cai dat cac thu vien...
echo.

REM Cài đặt qrcode và Pillow
python -m pip install --upgrade pip
python -m pip install qrcode[pil]
python -m pip install Pillow

echo.
echo ====================================
echo Cai dat hoan tat!
echo ====================================
echo.
echo Ban co the test bang cach chay:
echo python payment\generate_qr.py
echo.
pause

