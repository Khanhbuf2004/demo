<?php
/**
 * Script để khách hàng kiểm tra trạng thái thanh toán của đơn hàng
 * Sử dụng AJAX polling để kiểm tra định kỳ
 */

session_start();
require_once dirname(__DIR__) . '/config.php';

header('Content-Type: application/json');

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

// Kiểm tra quyền truy cập (chỉ chủ đơn hàng hoặc admin)
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];

if (!$is_admin && $order['user_id'] != $user_id) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Không có quyền truy cập']);
    exit;
}

// Trả về trạng thái đơn hàng
echo json_encode([
    'success' => true,
    'data' => [
        'order_id' => $order_id,
        'status' => $order['status'],
        'payment_method' => $order['payment_method'],
        'total_amount' => $order['total_amount'],
        'is_paid' => in_array($order['status'], ['paid', 'confirmed', 'processing', 'completed']),
        'status_text' => getStatusText($order['status'])
    ]
]);

function getStatusText($status) {
    $statuses = [
        'pending' => 'Chờ thanh toán',
        'paid' => 'Đã thanh toán',
        'confirmed' => 'Đã xác nhận',
        'processing' => 'Đang xử lý',
        'shipping' => 'Đang giao hàng',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy'
    ];
    return $statuses[$status] ?? $status;
}

