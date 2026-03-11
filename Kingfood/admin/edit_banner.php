<?php
require_once '../config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Lấy ID banner
$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: banners.php');
    exit;
}

// Lấy thông tin banner
$stmt = $pdo->prepare("SELECT * FROM banners WHERE id = ?");
$stmt->execute([$id]);
$banner = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$banner) {
    header('Location: banners.php');
    exit;
}

$error = '';

// Xử lý form sửa banner
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $link = $_POST['link'] ?? '';
    $position = $_POST['position'] ?? 'main';
    $current_image = $banner['image'];
    $image_path = $current_image;

    // Xử lý tải ảnh lên mới
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/banners/'; // Thư mục lưu ảnh banner
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
            
            // Xóa ảnh cũ nếu tồn tại
            if ($current_image && file_exists('../' . $current_image) /*&& $current_image !== 'đường/dẫn/ảnh/mac/dinh.jpg'*/) { // Đã bỏ check ảnh mặc định nếu không có
                 unlink('../' . $current_image);
            }

        } else {
            $error = "Có lỗi khi tải file ảnh lên.";
        }
    } else if (isset($_POST['delete_image'])) { // Xử lý xóa ảnh hiện tại nếu checkbox 'delete_image' được chọn
         if ($current_image && file_exists('../' . $current_image) /*&& $current_image !== 'đường/dẫn/ảnh/mac/dinh.jpg'*/) { // Đã bỏ check ảnh mặc định nếu không có
              unlink('../' . $current_image);
              $image_path = ''; // Set đường dẫn ảnh trong CSDL thành rỗng
         } else {
              $error = "Không tìm thấy ảnh để xóa."; // Cập nhật thông báo lỗi
         }
    }

    if (empty($error)) {
        $stmt = $pdo->prepare("UPDATE banners SET image = ?, link = ?, position = ? WHERE id = ?");
        if ($stmt->execute([$image_path, $link, $position, $id])) {
            header('Location: banners.php');
            exit;
        } else {
            $error = "Có lỗi xảy ra khi cập nhật banner.";
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
    <title>Sửa Banner - Admin</title>
</head>
<body>
    <main>
        <div class="container">
            <h2>Sửa Banner</h2>

            <?php if ($error): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>

            <form class="admin-form" method="POST" enctype="multipart/form-data" class="form">
                <div class="form-group">
                    <label for="image">Ảnh Banner:</label>
                    <?php if ($banner['image']): ?>
                        <p>Ảnh hiện tại:</p>
                        <img src="../<?php echo htmlspecialchars($banner['image']); ?>" alt="Ảnh banner" width="100">
                        <label>
                            <input type="checkbox" name="delete_image"> Xóa ảnh hiện tại
                        </label>
                    <?php endif; ?>
                    <input type="file" id="image" name="image" accept="image/*">
                </div>

                <div class="form-group">
                    <label for="link">Liên kết (URL):</label>
                    <input type="url" id="link" name="link" value="<?php echo htmlspecialchars($banner['link']); ?>">
                </div>

                 <div class="form-group">
                    <label for="position">Vị trí:</label>
                    <select id="position" name="position" required>
                        <option value="main" <?php echo $banner['position'] === 'main' ? 'selected' : ''; ?>>Chính</option>
                        <option value="small" <?php echo $banner['position'] === 'small' ? 'selected' : ''; ?>>Nhỏ</option>
                    </select>
                </div>

                <button type="submit" class="btn">Cập nhật</button>
            </form>
        </div>
    </main>
</body>
</html>

<?php
// Bao gồm footer admin
include 'footer.php';
?> 