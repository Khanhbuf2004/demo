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

// Lấy thông tin danh mục
$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$id]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    header('Location: categories.php');
    exit;
}

// Lấy danh mục cha (loại trừ danh mục hiện tại)
$stmt = $pdo->prepare("SELECT * FROM categories WHERE id != ?");
$stmt->execute([$id]);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';

// Xử lý form sửa danh mục
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $parent_id = $_POST['parent_id'] ?: null;
    $current_image = $category['image']; // Lấy đường dẫn ảnh hiện tại
    $image_path = $current_image; // Mặc định giữ nguyên ảnh cũ

    // Xử lý tải ảnh lên mới
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/categories/'; // Thư mục lưu ảnh danh mục
        $file_tmp_path = $_FILES['image']['tmp_name'];
        $file_name = basename($_FILES['image']['name']);
        $new_file_name = uniqid() . '_' . $file_name; // Tạo tên file duy nhất
        $dest_path = $upload_dir . $new_file_name;

        if (move_uploaded_file($file_tmp_path, $dest_path)) {
            $image_path = 'uploads/categories/' . $new_file_name; // Đường dẫn lưu vào CSDL
            
            // Xóa ảnh cũ nếu tồn tại và khác ảnh mặc định/placeholder
            if ($current_image && file_exists('../' . $current_image) /*&& $current_image !== 'đường/dẫn/ảnh/mac/dinh.jpg'*/) { // Đã bỏ check ảnh mặc định nếu không có
                 unlink('../' . $current_image);
            }

        } else {
            $error = "Có lỗi khi tải file ảnh lên.";
        }
    }

    if (empty($error)) {
         $stmt = $pdo->prepare("UPDATE categories SET name = ?, parent_id = ?, image = ? WHERE id = ?");
         if ($stmt->execute([$name, $parent_id, $image_path, $id])) {
              header('Location: categories.php');
              exit;
         } else {
              $error = "Có lỗi xảy ra khi cập nhật danh mục.";
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
    <title>Sửa danh mục - Admin</title>
</head>
<body>
    <main>
        <div class="container">
            <h2>Sửa danh mục</h2>
            
            <?php if ($error): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>

            <form class="admin-form" method="POST" enctype="multipart/form-data" > <!-- Thêm enctype -->
                <div class="form-group">
                    <label for="name">Tên danh mục:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="parent_id">Danh mục cha:</label>
                    <select id="parent_id" name="parent_id">
                        <option value="">Không có</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $cat['id'] == $category['parent_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group"> <!-- Thêm trường ảnh -->
                    <label for="image">Ảnh danh mục:</label>
                    <?php if ($category['image']): ?>
                        <p>Ảnh hiện tại:</p>
                        <img src="../<?php echo htmlspecialchars($category['image']); ?>" alt="Ảnh danh mục" width="100">
                    <?php endif; ?>
                    <input type="file" id="image" name="image" accept="image/*">
                </div>

                <button type="submit" class="btn">Cập nhật danh mục</button>
                <div class="back-link" style="text-align: center; margin-top: 20px;">
                    <a href="categories.php">
                        <i class="fas fa-arrow-left"></i> Quay lại quản lý danh mục
                    </a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>

<?php
// Bao gồm footer admin
include 'footer.php';
?> 