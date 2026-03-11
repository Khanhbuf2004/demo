<?php
/**
 * Cấu hình thông tin tài khoản ngân hàng
 * Sửa thông tin ở đây để thay đổi cho toàn bộ hệ thống
 */

return [
    'bank_account' => '230624112509',           // Số tài khoản ngân hàng
    'bank_name' => 'MB',                         // Tên ngân hàng
    'account_holder' => 'CÔNG TY TNHH KINGFOOD', // Tên chủ tài khoản
    
    // Định dạng QR code: 'vietqr', 'url', 'text', 'emv'
    // - 'vietqr': Định dạng VietQR chuẩn (EMV QR Code)
    // - 'url': Định dạng URL (một số ngân hàng hỗ trợ)
    // - 'text': Định dạng text đơn giản
    // - 'emv': Định dạng EMV QR Code đầy đủ
    'qr_format' => 'vietqr',
    
    // Mã ngân hàng theo VietQR (nếu dùng định dạng vietqr/emv)
    // Danh sách: https://www.vietqr.io/danh-sach-ngan-hang
    'bank_code' => '970422',  // Mã ngân hàng MB (Military Bank)
    
    // Thông tin bổ sung cho VietQR
    'merchant_name' => 'CÔNG TY TNHH KINGFOOD',
    'merchant_city' => 'Hà Nội',
];

