<?php
/**
 * API endpoint để cập nhật trạng thái đơn hàng
 * Chỉ admin mới có quyền sử dụng
 */

require_once '../config.php';

header('Content-Type: application/json');

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Kiểm tra method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Lấy dữ liệu từ POST
$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$new_status = isset($_POST['status']) ? trim($_POST['status']) : '';

// Validate input
if (!$order_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Thiếu order_id']);
    exit;
}

if (!$new_status) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Thiếu trạng thái mới']);
    exit;
}

// Validate status
$allowed_statuses = ['pending', 'paid', 'confirmed', 'processing', 'shipping', 'completed', 'cancelled'];
if (!in_array($new_status, $allowed_statuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Trạng thái không hợp lệ']);
    exit;
}

try {
    // Kiểm tra đơn hàng có tồn tại không
    $stmt = $pdo->prepare("SELECT id, status FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Không tìm thấy đơn hàng']);
        exit;
    }

    // Cập nhật trạng thái
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $order_id]);

    // Trả về kết quả
    echo json_encode([
        'success' => true,
        'message' => 'Cập nhật trạng thái thành công',
        'data' => [
            'order_id' => $order_id,
            'old_status' => $order['status'],
            'new_status' => $new_status
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Lỗi khi cập nhật trạng thái: ' . $e->getMessage()
    ]);
}

