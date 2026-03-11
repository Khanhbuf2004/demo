<?php
require_once '../config.php';
// TODO: Thêm kiểm tra đăng nhập admin

$message = '';
$error = '';

// Xử lý khi form được submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $type = $_POST['type'] ?? 'news'; // Mặc định là 'news'
    $image_path = '';

    // Xử lý upload ảnh
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = $_FILES['image'];
        $upload_dir = '../uploads/news/'; // Thư mục lưu ảnh bài viết
        $image_name = uniqid() . '_' . basename($image['name']);
        $target_file = $upload_dir . $image_name;
        $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Kiểm tra định dạng file
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
        if (!in_array($image_file_type, $allowed_types)) {
            $error = "Chỉ cho phép file JPG, JPEG, PNG & GIF.";
        } else {
            // Di chuyển file
            if (move_uploaded_file($image['tmp_name'], $target_file)) {
                $image_path = 'uploads/news/' . $image_name; // Lưu đường dẫn tương đối vào DB
            } else {
                $error = "Có lỗi khi upload ảnh.";
            }
        }
    }

    // Nếu không có lỗi upload ảnh, tiến hành lưu vào DB
    if (empty($error)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO news (title, content, image, type, status, created_at) VALUES (?, ?, ?, ?, 'approved', NOW())");
            $stmt->execute([$title, $content, $image_path, $type]);
            $message = "Bài viết mới đã được đăng thành công!";
            // Tùy chọn: Chuyển hướng sau khi lưu thành công
            // header('Location: moderate_content.php?status=approved');
            // exit();
        } catch (PDOException $e) {
            $error = "Lỗi cơ sở dữ liệu: " . $e->getMessage();
             // Xóa file ảnh vừa upload nếu lưu DB thất bại
            if (!empty($image_path) && file_exists('../' . $image_path)) {
                 unlink('../' . $image_path);
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Bài Viết Mới - Admin</title>
    <link rel="stylesheet" href="../css/style.css"> <!-- Đảm bảo đường dẫn đúng -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?> <!-- Bao gồm header admin -->

    <div class="admin-main">
        <div class="container">
            <div class="admin-form">
                <h1>Đăng Bài Viết Mới</h1>

                <?php if ($message): ?>
                    <div class="success-message"><?php echo $message; ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>

                <form action="add_article.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title">Tiêu đề:</label>
                        <input type="text" id="title" name="title" required>
                    </div>

                    <div class="form-group">
                        <label for="type">Loại bài viết:</label>
                        <select id="type" name="type">
                            <option value="news">Tin tức</option>
                            <option value="recipe">Công thức</option>
                        </select>
                    </div>

                     <div class="form-group">
                        <label for="image">Hình ảnh:</label>
                        <input type="file" id="image" name="image" accept="image/*">
                    </div>

                    <div class="form-group">
                        <label for="content">Nội dung:</label>
                        <textarea id="content" name="content" rows="15" required></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Đăng Bài Viết</button>
                        <a href="moderate_content.php" class="btn btn-secondary">Hủy</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?> <!-- Bao gồm footer admin -->
</body>
</html> 