<?php
require_once '../config.php';

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') { // Sử dụng user_role
    header('Location: login.php');
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    // Chuyển hướng về trang quản lý sản phẩm nếu không có ID hợp lệ
    header('Location: manage_products.php?error=' . urlencode('ID sản phẩm không hợp lệ!'));
    exit;
}

try {
    // Xóa sản phẩm
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);

    // Chuyển hướng về trang quản lý sản phẩm với thông báo thành công
    header('Location: manage_products.php?success=' . urlencode('Đã xóa sản phẩm thành công!'));
    exit;
} catch (PDOException $e) {
    // Xử lý lỗi khi xóa
    header('Location: manage_products.php?error=' . urlencode('Lỗi khi xóa sản phẩm: ' . $e->getMessage()));
    exit;
}
?>