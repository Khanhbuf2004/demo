<?php
/**
 * API endpoint để tạo mã QR code cho thanh toán
 * Gọi Python script để tạo QR code
 */

header('Content-Type: application/json');

// Kiểm tra request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Load cấu hình ngân hàng
$bank_config = require __DIR__ . '/bank_config.php';

// Lấy dữ liệu từ request
$input = json_decode(file_get_contents('php://input'), true);

$order_id = isset($input['order_id']) ? (int)$input['order_id'] : 0;
$amount = isset($input['amount']) ? (float)$input['amount'] : 0;
$bank_account = isset($input['bank_account']) ? $input['bank_account'] : $bank_config['bank_account'];
$bank_name = isset($input['bank_name']) ? $input['bank_name'] : $bank_config['bank_name'];
$account_holder = isset($input['account_holder']) ? $input['account_holder'] : $bank_config['account_holder'];
$note = isset($input['note']) ? $input['note'] : 'KF' . $order_id;

if (!$order_id || !$amount) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Thiếu thông tin order_id hoặc amount']);
    exit;
}

// Chuẩn bị dữ liệu để gửi cho Python script
$data = [
    'order_id' => $order_id,
    'amount' => $amount,
    'bank_account' => $bank_account,
    'bank_name' => $bank_name,
    'account_holder' => $account_holder,
    'note' => $note,
    'qr_format' => isset($bank_config['qr_format']) ? $bank_config['qr_format'] : 'vietqr',
    'bank_code' => isset($bank_config['bank_code']) ? $bank_config['bank_code'] : '970422',
    'merchant_name' => isset($bank_config['merchant_name']) ? $bank_config['merchant_name'] : 'KINGFOOD',
    'merchant_city' => isset($bank_config['merchant_city']) ? $bank_config['merchant_city'] : 'HANOI'
];

// Tìm đường dẫn Python (thử các đường dẫn phổ biến)
$python_paths = [
    'python',
    'python3',
    'C:\\Python\\python.exe',
    'C:\\Python3\\python.exe',
    'C:\\Users\\' . getenv('USERNAME') . '\\AppData\\Local\\Programs\\Python\\Python3*\\python.exe'
];

$python_cmd = null;
foreach ($python_paths as $path) {
    // Kiểm tra nếu là wildcard path
    if (strpos($path, '*') !== false) {
        $glob_path = str_replace('*', '', $path);
        $matches = glob($glob_path . '*\\python.exe');
        if (!empty($matches)) {
            $python_cmd = $matches[0];
            break;
        }
    } else {
        // Thử chạy lệnh để kiểm tra
        $output = [];
        $return_var = 0;
        @exec("$path --version 2>&1", $output, $return_var);
        if ($return_var === 0) {
            $python_cmd = $path;
            break;
        }
    }
}

if (!$python_cmd) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Không tìm thấy Python. Vui lòng cài đặt Python 3.']);
    exit;
}

// Đường dẫn đến script Python (cùng thư mục)
$script_path = __DIR__ . '/generate_qr.py';

if (!file_exists($script_path)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Không tìm thấy script Python']);
    exit;
}

// Chuyển đổi dữ liệu sang JSON
$json_data = json_encode($data);

// Gọi Python script
$descriptorspec = [
    0 => ['pipe', 'r'],  // stdin
    1 => ['pipe', 'w'],  // stdout
    2 => ['pipe', 'w']   // stderr
];

$process = proc_open(
    escapeshellcmd($python_cmd) . ' ' . escapeshellarg($script_path),
    $descriptorspec,
    $pipes
);

if (!is_resource($process)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Không thể khởi tạo Python process']);
    exit;
}

// Gửi dữ liệu vào stdin
fwrite($pipes[0], $json_data);
fclose($pipes[0]);

// Đọc kết quả từ stdout
$output = stream_get_contents($pipes[1]);
$error = stream_get_contents($pipes[2]);
fclose($pipes[1]);
fclose($pipes[2]);

// Đợi process kết thúc
$return_value = proc_close($process);

if ($return_value !== 0) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Lỗi khi chạy Python script: ' . $error
    ]);
    exit;
}

// Parse kết quả từ Python
$result = json_decode($output, true);

if (!$result || !isset($result['success'])) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Lỗi khi parse kết quả từ Python: ' . $output
    ]);
    exit;
}

// Trả về kết quả
echo json_encode($result);

