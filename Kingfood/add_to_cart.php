<?php

require_once 'config.php';

// Kiểm tra request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Lấy thông tin sản phẩm
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

if (!$product_id) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

try {
    // Lấy thông tin sản phẩm từ database
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        throw new Exception('Product not found');
    }

    // Khởi tạo giỏ hàng nếu chưa có
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Kiểm tra sản phẩm đã có trong giỏ hàng chưa
    if (isset($_SESSION['cart'][$product_id])) {
        // Cập nhật số lượng
        $_SESSION['cart'][$product_id]['quantity'] += $quantity;
    } else {
        // Thêm sản phẩm mới vào giỏ hàng
        $_SESSION['cart'][$product_id] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'image' => $product['image'],
            'quantity' => $quantity
        ];
    }

    // Tính tổng số sản phẩm trong giỏ hàng
    $total_items = array_sum(array_column($_SESSION['cart'], 'quantity'));

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Đã thêm sản phẩm vào giỏ hàng',
        'total_items' => $total_items
    ]);

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 