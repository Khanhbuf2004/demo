<?php
require_once '../config.php';

// Kiểm tra đăng nhập

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Lấy ID banner cần xóa
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: banners.php');
    exit;
}

// Lấy đường dẫn ảnh để xóa file
$stmt = $pdo->prepare("SELECT image FROM banners WHERE id = ?");
$stmt->execute([$id]);
$banner = $stmt->fetch(PDO::FETCH_ASSOC);

if ($banner && $banner['image']) {
    $image_path = '../' . $banner['image'];
    if (file_exists($image_path) /*&& $banner['image'] !== 'đường/dẫn/ảnh/mac/dinh.jpg'*/) { // Đã bỏ check ảnh mặc định nếu không có
        unlink($image_path);
    }
}

// Xóa bản ghi khỏi CSDL
$stmt = $pdo->prepare("DELETE FROM banners WHERE id = ?");
$stmt->execute([$id]);

// Chuyển hướng về trang danh sách banner
header('Location: banners.php');
exit;
?>