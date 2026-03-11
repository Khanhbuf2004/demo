<?php
require_once '../config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Lấy ID tin tức/công thức cần xóa
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: news.php');
    exit;
}

// Lấy đường dẫn ảnh để xóa file
$stmt = $pdo->prepare("SELECT image FROM news WHERE id = ?");
$stmt->execute([$id]);
$news_item = $stmt->fetch(PDO::FETCH_ASSOC);

if ($news_item && $news_item['image']) {
    $image_path = '../' . $news_item['image'];
    if (file_exists($image_path) && $news_item['image'] !== 'đường/dẫn/ảnh/mac/dinh.jpg') { // Thay placeholder
        unlink($image_path);
    }
}

// Xóa bản ghi khỏi CSDL
$stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
$stmt->execute([$id]);

// Chuyển hướng về trang danh sách tin tức
header('Location: news.php');
exit;
?> 