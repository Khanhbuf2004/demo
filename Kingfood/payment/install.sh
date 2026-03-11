#!/bin/bash

echo "===================================="
echo "Cài đặt thư viện Python cho QR Code"
echo "===================================="
echo ""

# Kiểm tra Python
if ! command -v python3 &> /dev/null; then
    echo "[LỖI] Python 3 chưa được cài đặt hoặc không có trong PATH"
    echo "Vui lòng cài đặt Python 3:"
    echo "  Ubuntu/Debian: sudo apt-get install python3 python3-pip"
    echo "  Mac: brew install python3"
    exit 1
fi

echo "[OK] Tìm thấy Python"
python3 --version
echo ""

echo "Đang cài đặt các thư viện..."
echo ""

# Cài đặt qrcode và Pillow
python3 -m pip install --upgrade pip
python3 -m pip install qrcode[pil]
python3 -m pip install Pillow

echo ""
echo "===================================="
echo "Cài đặt hoàn tất!"
echo "===================================="
echo ""
echo "Bạn có thể test bằng cách chạy:"
echo "python3 payment/generate_qr.py"
echo ""

