<?php

require_once 'config.php';

// Kiểm tra nếu request là POST và có nội dung chia sẻ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['share_content'])) {
    $share_content = trim($_POST['share_content']);
    // Bỏ phần lấy user_id

    if (!empty($share_content)) {
        try {
            // Chèn dữ liệu vào bảng news với status là 'pending'
            // Sử dụng 'community_share' làm type cho bài viết từ người dùng
            $share_type = 'community_share';

            // Cập nhật câu lệnh INSERT để không còn cột user_id
            $stmt = $pdo->prepare("INSERT INTO news (content, type, status, created_at) VALUES (?, ?, ?, NOW())");

            // Thực thi câu lệnh, không truyền user_id
            $stmt->execute([$share_content, $share_type, 'pending']);

            // Chuyển hướng về trang chủ hoặc trang thông báo thành công
            header('Location: index.php?share_status=success');
            exit;

        } catch (PDOException $e) {
            // Ghi log lỗi thay vì hiển thị trực tiếp trên production
            error_log("Error submitting share: " . $e->getMessage());
            // Chuyển hướng về trang chủ với thông báo lỗi
            header('Location: index.php?share_status=error');
            exit;
        }
    } else {
        // Nội dung chia sẻ rỗng
        header('Location: index.php?share_status=empty');
        exit;
    }
} else {
    // Truy cập trực tiếp hoặc không có dữ liệu POST
    header('Location: index.php');
    exit;
} 