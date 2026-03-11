<?php
/**
 * Script xác thực thanh toán
 * Kiểm tra xem khách hàng đã thanh toán chưa
 * Có thể được gọi định kỳ hoặc qua webhook
 */

session_start();
require_once dirname(__DIR__) . '/config.php';

header('Content-Type: application/json');

// Kiểm tra quyền truy cập (chỉ admin hoặc API key)
$api_key = isset($_GET['api_key']) ? $_GET['api_key'] : '';
$is_admin = isset($_SESSION['user_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'];

// API key mặc định (nên thay đổi trong production)
$valid_api_key = 'your_secret_api_key_here';

if (!$is_admin && $api_key !== $valid_api_key) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Lấy order_id từ request
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if (!$order_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Thiếu order_id']);
    exit;
}

// Lấy thông tin đơn hàng
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Không tìm thấy đơn hàng']);
    exit;
}

// Kiểm tra trạng thái thanh toán
$payment_status = [
    'order_id' => $order_id,
    'current_status' => $order['status'],
    'payment_method' => $order['payment_method'],
    'total_amount' => $order['total_amount'],
    'is_paid' => ($order['status'] === 'paid' || $order['status'] === 'confirmed'),
    'payment_verified' => false
];

// Nếu đơn hàng đã được thanh toán
if ($payment_status['is_paid']) {
    $payment_status['payment_verified'] = true;
    echo json_encode([
        'success' => true,
        'data' => $payment_status,
        'message' => 'Đơn hàng đã được thanh toán'
    ]);
    exit;
}

// Nếu là phương thức banking, có thể kiểm tra thêm
if ($order['payment_method'] === 'banking') {
    // TODO: Tích hợp với API ngân hàng để kiểm tra giao dịch
    // Hoặc kiểm tra trong database nếu có bảng payment_transactions
    
    // Ví dụ: Kiểm tra trong bảng payment_transactions (nếu có)
    try {
        $stmt = $pdo->prepare("SELECT * FROM payment_transactions WHERE order_id = ? AND status = 'completed'");
        $stmt->execute([$order_id]);
        $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($transaction) {
            // Cập nhật trạng thái đơn hàng
            $stmt = $pdo->prepare("UPDATE orders SET status = 'paid' WHERE id = ?");
            $stmt->execute([$order_id]);
            
            $payment_status['payment_verified'] = true;
            $payment_status['current_status'] = 'paid';
            $payment_status['transaction_id'] = $transaction['id'];
            
            echo json_encode([
                'success' => true,
                'data' => $payment_status,
                'message' => 'Thanh toán đã được xác thực'
            ]);
            exit;
        }
    } catch (PDOException $e) {
        // Bảng payment_transactions có thể chưa tồn tại, bỏ qua
    }
}

// Trả về trạng thái chưa thanh toán
echo json_encode([
    'success' => true,
    'data' => $payment_status,
    'message' => 'Đơn hàng chưa được thanh toán'
]);

