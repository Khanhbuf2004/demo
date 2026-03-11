<?php
require_once '../config.php';

// Kiểm tra đăng nhập

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Lấy danh mục cha
$stmt = $pdo->query("SELECT * FROM categories");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Xử lý form thêm danh mục
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $parent_id = $_POST['parent_id'] ?: null;
    $image_path = ''; // Mặc định không có ảnh

    // Xử lý tải ảnh lên
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/categories/'; // Thư mục lưu ảnh danh mục (đảm bảo thư mục này tồn tại và có quyền ghi)
        $file_tmp_path = $_FILES['image']['tmp_name'];
        $file_name = basename($_FILES['image']['name']);
        $new_file_name = uniqid() . '_' . $file_name; // Tạo tên file duy nhất
        $dest_path = $upload_dir . $new_file_name;

        if (move_uploaded_file($file_tmp_path, $dest_path)) {
            $image_path = 'uploads/categories/' . $new_file_name; // Đường dẫn lưu vào CSDL
        } else {
            // Xử lý lỗi tải file nếu cần
            echo "Có lỗi khi tải file lên.";
        }
    }

    $stmt = $pdo->prepare("INSERT INTO categories (name, parent_id, image) VALUES (?, ?, ?)");
    $stmt->execute([$name, $parent_id, $image_path]);

    header('Location: categories.php');
    exit;
}

// Bao gồm header admin
include 'header.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm danh mục - Admin</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <main>
        <div class="container">
            <h2>Thêm danh mục mới</h2>
            <form class="admin-form" method="POST" enctype="multipart/form-data" >
                <div class="form-group">
                    <label for="name">Tên danh mục:</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="parent_id">Danh mục cha:</label>
                    <select id="parent_id" name="parent_id">
                        <option value="">Không có</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>">
                                <?php echo $category['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="image">Ảnh danh mục:</label>
                    <input type="file" id="image" name="image" accept="image/*">
                </div>

                <button type="submit" class="btn">Thêm danh mục</button>
            </form>
        </div>
    </main>
</body>
</html>

<?php
// Bao gồm footer admin
include 'footer.php';
?> 