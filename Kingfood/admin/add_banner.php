<?php
require_once '../config.php';

// Kiểm tra đăng nhập

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$error = '';

// Xử lý form thêm mới banner
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $link = $_POST['link'] ?? '';
    $position = $_POST['position'] ?? 'main';
    $image_path = '';

    // Xử lý tải ảnh lên
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/banners/'; // Thư mục lưu ảnh banner (đảm bảo tồn tại và có quyền ghi)
         // Tạo thư mục nếu chưa có
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_tmp_path = $_FILES['image']['tmp_name'];
        $file_name = basename($_FILES['image']['name']);
        $new_file_name = uniqid() . '_' . $file_name; // Tạo tên file duy nhất
        $dest_path = $upload_dir . $new_file_name;

        if (move_uploaded_file($file_tmp_path, $dest_path)) {
            $image_path = 'uploads/banners/' . $new_file_name; // Đường dẫn lưu vào CSDL
        } else {
            $error = "Có lỗi khi tải file ảnh lên.";
        }
    } else {
         $error = "Vui lòng chọn ảnh cho banner.";
    }

    if (empty($error) && $image_path) {
        $stmt = $pdo->prepare("INSERT INTO banners (image, link, position) VALUES (?, ?, ?)");
        if ($stmt->execute([$image_path, $link, $position])) {
            header('Location: banners.php');
            exit;
        } else {
            $error = "Có lỗi xảy ra khi thêm mới banner.";
        }
    }
}

// Bao gồm header admin
include 'header.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm mới Banner - Admin</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <main>
        <div class="container">
            <h2>Thêm mới Banner</h2>
            
            <?php if ($error): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>

            <form class="admin-form" method="POST" enctype="multipart/form-data" >
                <div class="form-group">
                    <label for="image">Ảnh Banner:</label>
                    <input type="file" id="image" name="image" accept="image/*" required>
                </div>

                <div class="form-group">
                    <label for="link">Liên kết (URL):</label>
                    <input type="url" id="link" name="link">
                </div>

                 <div class="form-group">
                    <label for="position">Vị trí:</label>
                    <select id="position" name="position" required>
                        <option value="main">Chính</option>
                        <option value="small">Nhỏ</option>
                    </select>
                </div>

                <button type="submit" class="btn">Thêm mới</button>
            </form>
        </div>
    </main>
</body>
</html>

<?php
// Bao gồm footer admin
include 'footer.php';
?> 