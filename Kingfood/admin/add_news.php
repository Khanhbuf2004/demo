<?php
require_once '../config.php';

// Kiểm tra đăng nhập

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $type = $_POST['type']; // 'news' or 'recipe'
    $summary = trim($_POST['summary']); // Lấy dữ liệu tóm tắt
    $image_path = '';

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "../uploads/";
        $image_name = uniqid() . '_' . basename($_FILES['image']['name']);
        $target_file = $target_dir . $image_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES['image']['tmp_name']);
        if ($check !== false) {
            // Allow certain file formats
            if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
                && $imageFileType != "gif") {
                $message = "Chỉ cho phép file JPG, JPEG, PNG & GIF.";
            } else {
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image_path = 'uploads/' . $image_name; // Path to save in DB
                } else {
                    $message = "Có lỗi khi tải ảnh lên.";
                }
            }
        } else {
            $message = "File không phải là ảnh.";
        }
    }

    if (empty($message)) {
        // Insert into database
        $sql = "INSERT INTO news (title, content, image, type, status, summary) VALUES (:title, :content, :image, :type, :status, :summary)";
        $stmt = $pdo->prepare($sql);
        $status = 'approved'; // New articles added by admin are approved by default
        if ($stmt->execute(['title' => $title, 'content' => $content, 'image' => $image_path, 'type' => $type, 'status' => $status, 'summary' => $summary])) {
            $message = "Thêm bài viết/công thức thành công!";
            // Clear form fields after successful insertion
            $title = '';
            $content = '';
            $summary = ''; // Clear summary field
            $image_path = '';
        } else {
            $message = "Có lỗi xảy ra khi thêm vào CSDL.";
        }
    }
}
?>

<?php include 'header.php'; ?>

<div class="admin-container">
    <h2>Thêm mới Tin tức & Công thức</h2>

    <?php if (!empty($message)): ?>
        <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>

    <form action="add_news.php" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Tiêu đề:</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="type">Loại:</label>
            <select id="type" name="type" required>
                <option value="news" <?php echo (isset($type) && $type == 'news') ? 'selected' : ''; ?>>Tin tức</option>
                <option value="recipe" <?php echo (isset($type) && $type == 'recipe') ? 'selected' : ''; ?>>Công thức</option>
            </select>
        </div>

        <div class="form-group">
            <label for="summary">Tóm tắt:</label>
            <textarea id="summary" name="summary" rows="3" required><?php echo htmlspecialchars($summary ?? ''); ?></textarea>
        </div>

        <div class="form-group">
            <label for="content">Nội dung:</label>
            <textarea id="content" name="content" rows="10" required><?php echo htmlspecialchars($content ?? ''); ?></textarea>
        </div>

        <div class="form-group">
            <label for="image">Ảnh đại diện:</label>
            <input type="file" id="image" name="image" accept="image/*">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Thêm</button>
            <a href="news.php" class="btn btn-secondary">Quay về</a>
        </div>

    </form>
</div>

<?php include 'footer.php'; ?> 