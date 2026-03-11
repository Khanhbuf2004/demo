#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script tạo mã QR tự động cho thanh toán
Tạo QR code chứa thông tin chuyển khoản ngân hàng
"""

import qrcode
import sys
import json
import os
import urllib.request
import urllib.parse
from datetime import datetime
from PIL import Image

def calculate_crc16(data):
    """
    Tính CRC16-CCITT cho EMV QR Code
    """
    crc = 0xFFFF
    polynomial = 0x1021
    
    for byte in data.encode('utf-8'):
        crc ^= (byte << 8)
        for _ in range(8):
            if crc & 0x8000:
                crc = (crc << 1) ^ polynomial
            else:
                crc <<= 1
            crc &= 0xFFFF
    
    return format(crc, '04X')

def create_vietqr_string(bank_code, account_number, amount, note="", merchant_name="KINGFOOD", merchant_city="HANOI", bank_name="MB"):
    """
    Tạo URL VietQR theo chuẩn VietQR.io
    Format: https://img.vietqr.io/image/{BANK_ID}-{ACCOUNT_NO}-compact2.png?amount={AMOUNT}&addInfo={NOTE}&accountName={NAME}
    
    Tham khảo: https://vietqr.io/
    """
    import urllib.parse
    
    # Map bank code sang bank ID (nếu cần)
    bank_id_map = {
        '970422': 'MB',      # Military Bank
        '970415': 'VCB',     # Vietcombank
        '970418': 'BIDV',    # BIDV
        '970419': 'TCB',     # Techcombank
        '970403': 'VTB',     # Agribank
        '970416': 'ACB',     # ACB
        '970405': 'STB',     # Sacombank
        '970407': 'VPB',     # VPBank
        '970409': 'TPB',     # TPBank
        '970411': 'HDB'      # HDBank
    }
    
    # Lấy bank_id từ map hoặc dùng bank_name
    bank_id = bank_id_map.get(str(bank_code), bank_name.upper())
    
    # Chuẩn hóa tên tài khoản (bỏ dấu, thay space bằng +)
    def remove_vietnamese_accents(text):
        """Đơn giản hóa: bỏ dấu tiếng Việt"""
        text = str(text).upper()
        # Thay thế các ký tự có dấu
        replacements = {
            'À': 'A', 'Á': 'A', 'Ạ': 'A', 'Ả': 'A', 'Ã': 'A',
            'Â': 'A', 'Ầ': 'A', 'Ấ': 'A', 'Ậ': 'A', 'Ẩ': 'A', 'Ẫ': 'A',
            'Ă': 'A', 'Ằ': 'A', 'Ắ': 'A', 'Ặ': 'A', 'Ẳ': 'A', 'Ẵ': 'A',
            'È': 'E', 'É': 'E', 'Ẹ': 'E', 'Ẻ': 'E', 'Ẽ': 'E',
            'Ê': 'E', 'Ề': 'E', 'Ế': 'E', 'Ệ': 'E', 'Ể': 'E', 'Ễ': 'E',
            'Ì': 'I', 'Í': 'I', 'Ị': 'I', 'Ỉ': 'I', 'Ĩ': 'I',
            'Ò': 'O', 'Ó': 'O', 'Ọ': 'O', 'Ỏ': 'O', 'Õ': 'O',
            'Ô': 'O', 'Ồ': 'O', 'Ố': 'O', 'Ộ': 'O', 'Ổ': 'O', 'Ỗ': 'O',
            'Ơ': 'O', 'Ờ': 'O', 'Ớ': 'O', 'Ợ': 'O', 'Ở': 'O', 'Ỡ': 'O',
            'Ù': 'U', 'Ú': 'U', 'Ụ': 'U', 'Ủ': 'U', 'Ũ': 'U',
            'Ư': 'U', 'Ừ': 'U', 'Ứ': 'U', 'Ự': 'U', 'Ử': 'U', 'Ữ': 'U',
            'Ỳ': 'Y', 'Ý': 'Y', 'Ỵ': 'Y', 'Ỷ': 'Y', 'Ỹ': 'Y',
            'Đ': 'D'
        }
        for old, new in replacements.items():
            text = text.replace(old, new)
        return text
    
    # Chuẩn hóa tên tài khoản - bỏ dấu và URL encode
    account_name_clean = remove_vietnamese_accents(merchant_name)
    account_name_clean = urllib.parse.quote(account_name_clean, safe='+')  # Giữ + cho space
    
    # Chuẩn hóa note (nội dung chuyển khoản) - URL encode
    note_clean = urllib.parse.quote(str(note), safe='+') if note else ''  # Giữ + cho space
    
    # Tạo URL VietQR
    # Format: https://img.vietqr.io/image/{BANK_ID}-{ACCOUNT_NO}-compact2.png?amount={AMOUNT}&addInfo={NOTE}&accountName={NAME}
    base_url = f"https://img.vietqr.io/image/{bank_id}-{account_number}-compact2.png"
    
    # Tham số URL
    params = []
    if amount:
        params.append(f"amount={int(amount)}")
    if note_clean:
        params.append(f"addInfo={note_clean}")
    if account_name_clean:
        params.append(f"accountName={account_name_clean}")
    
    # Build query string
    if params:
        query_string = '&'.join(params)
        vietqr_url = f"{base_url}?{query_string}"
    else:
        vietqr_url = base_url
    
    return vietqr_url

def create_url_format(bank_account, amount, note, bank_name, account_holder):
    """
    Tạo định dạng URL cho QR code
    """
    # Format URL phổ biến
    return f"bank://transfer?account={bank_account}&amount={int(amount)}&note={note}&bank={bank_name}&holder={account_holder}"

def create_text_format(bank_account, amount, note, bank_name, account_holder):
    """
    Tạo định dạng text đơn giản
    """
    return f"STK: {bank_account}\nSố tiền: {int(amount):,}đ\nNội dung: {note}\nNgân hàng: {bank_name}\nChủ TK: {account_holder}"

def generate_payment_qr(order_id, amount, bank_account, bank_name, account_holder, note="", qr_format="vietqr", bank_code="970422", merchant_name="KINGFOOD", merchant_city="HANOI"):
    """
    Tạo mã QR cho thanh toán
    
    Thông tin mặc định:
    - Số tài khoản: 230624112509
    - Ngân hàng: MB (Military Bank)
    - Mã ngân hàng: 970422
    
    Args:
        order_id: Mã đơn hàng
        amount: Số tiền cần thanh toán
        bank_account: Số tài khoản ngân hàng
        bank_name: Tên ngân hàng
        account_holder: Tên chủ tài khoản
        note: Nội dung chuyển khoản
        qr_format: Định dạng QR code ('vietqr', 'url', 'text')
        bank_code: Mã ngân hàng theo VietQR
        merchant_name: Tên merchant
        merchant_city: Thành phố merchant
    
    Returns:
        Đường dẫn file QR code đã tạo
    """
    # Tạo thư mục lưu QR code nếu chưa có
    script_dir = os.path.dirname(os.path.abspath(__file__))
    parent_dir = os.path.dirname(script_dir)
    qr_dir = os.path.join(parent_dir, "uploads", "qr_codes")
    
    if not os.path.exists(qr_dir):
        os.makedirs(qr_dir, exist_ok=True)
    
    # Tạo filename
    filename = f"qr_order_{order_id}_{datetime.now().strftime('%Y%m%d_%H%M%S')}.png"
    filepath = os.path.join(qr_dir, filename)
    
    # Tạo nội dung QR code theo định dạng được chọn
    if qr_format == 'vietqr' or qr_format == 'emv':
        # Định dạng VietQR - Tải hình ảnh QR từ VietQR.io API
        vietqr_url = create_vietqr_string(bank_code, bank_account, amount, note, merchant_name, merchant_city, bank_name)
        
        try:
            # Tải hình ảnh QR code từ VietQR.io (hình ảnh đã là QR code sẵn)
            response = urllib.request.urlopen(vietqr_url, timeout=10)
            img_data = response.read()
            
            # Lưu hình ảnh QR code
            with open(filepath, 'wb') as f:
                f.write(img_data)
            
            # Kiểm tra hình ảnh hợp lệ (mở lại sau khi verify)
            try:
                img = Image.open(filepath)
                img.load()  # Load image để kiểm tra
            except Exception as img_error:
                raise Exception(f"Hình ảnh QR code không hợp lệ: {img_error}")
            
        except Exception as e:
            # Nếu không tải được, tạo QR code chứa URL (fallback)
            print(f"Warning: Không thể tải QR từ VietQR.io: {e}", file=sys.stderr)
            qr = qrcode.QRCode(
                version=1,
                error_correction=qrcode.constants.ERROR_CORRECT_L,
                box_size=10,
                border=4,
            )
            qr.add_data(vietqr_url)
            qr.make(fit=True)
            img = qr.make_image(fill_color="black", back_color="white")
            img.save(filepath)
            
    elif qr_format == 'url':
        # Định dạng URL
        qr_content = create_url_format(bank_account, amount, note, bank_name, account_holder)
        qr = qrcode.QRCode(
            version=1,
            error_correction=qrcode.constants.ERROR_CORRECT_L,
            box_size=10,
            border=4,
        )
        qr.add_data(qr_content)
        qr.make(fit=True)
        img = qr.make_image(fill_color="black", back_color="white")
        img.save(filepath)
    else:
        # Định dạng text (mặc định)
        qr_content = create_text_format(bank_account, amount, note, bank_name, account_holder)
        qr = qrcode.QRCode(
            version=1,
            error_correction=qrcode.constants.ERROR_CORRECT_L,
            box_size=10,
            border=4,
        )
        qr.add_data(qr_content)
        qr.make(fit=True)
        img = qr.make_image(fill_color="black", back_color="white")
        img.save(filepath)
    
    # Trả về đường dẫn tương đối cho web
    relative_path = os.path.join("uploads", "qr_codes", filename).replace("\\", "/")
    
    return filepath, filename, relative_path

def main():
    """Hàm chính xử lý request từ PHP"""
    try:
        # Đọc dữ liệu từ stdin (JSON)
        input_data = sys.stdin.read()
        data = json.loads(input_data)
        
        order_id = data.get('order_id')
        amount = data.get('amount')
        bank_account = data.get('bank_account', '230624112509')  # Số tài khoản MB
        bank_name = data.get('bank_name', 'MB')  # Ngân hàng MB
        account_holder = data.get('account_holder', 'CÔNG TY TNHH KINGFOOD')
        note = data.get('note', f'KF{order_id}')
        qr_format = data.get('qr_format', 'vietqr')
        bank_code = data.get('bank_code', '970422')  # Mã ngân hàng MB
        merchant_name = data.get('merchant_name', 'KINGFOOD')
        merchant_city = data.get('merchant_city', 'HANOI')
        
        if not order_id or not amount:
            result = {
                'success': False,
                'error': 'Thiếu thông tin order_id hoặc amount'
            }
            print(json.dumps(result))
            sys.exit(1)
        
        # Tạo QR code
        filepath, filename, relative_path = generate_payment_qr(
            order_id, amount, bank_account, bank_name, account_holder, note, 
            qr_format, bank_code, merchant_name, merchant_city
        )
        
        # Trả về kết quả dạng JSON
        result = {
            'success': True,
            'filepath': filepath,
            'filename': filename,
            'url': relative_path
        }
        
        print(json.dumps(result))
        
    except Exception as e:
        result = {
            'success': False,
            'error': str(e)
        }
        print(json.dumps(result))
        sys.exit(1)

if __name__ == '__main__':
    main()

