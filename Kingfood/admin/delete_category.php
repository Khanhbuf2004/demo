<?php
require_once '../config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Lấy ID danh mục
$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: categories.php');
    exit;
}

// Xóa danh mục
$stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
$stmt->execute([$id]);

header('Location: categories.php');
exit; 