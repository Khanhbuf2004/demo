<?php

require_once 'config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;

if (!$order_id) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

try {
    // Kiểm tra đơn hàng có thuộc về người dùng và có thể hủy không
    $stmt = $pdo->prepare("SELECT status FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch();
    
    if (!$order) {
        throw new Exception('Order not found');
    }
    
    if ($order['status'] !== 'pending') {
        throw new Exception('Order cannot be cancelled');
    }
    
    // Cập nhật trạng thái đơn hàng
    $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
    $stmt->execute([$order_id]);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Order cancelled successfully']);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 