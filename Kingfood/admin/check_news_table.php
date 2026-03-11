<?php
require_once '../config.php';

try {
    // Lấy thông tin cấu trúc bảng news
    $stmt = $pdo->query("DESCRIBE news");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Cấu trúc bảng news:</h2>";
    echo "<pre>";
    print_r($columns);
    echo "</pre>";
    
    // Lấy một số dữ liệu mẫu
    $stmt = $pdo->query("SELECT * FROM news LIMIT 1");
    $sample = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h2>Dữ liệu mẫu:</h2>";
    echo "<pre>";
    print_r($sample);
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}
?> 