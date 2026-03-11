<?php
require_once '../config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$news_id = $_GET['id'] ?? null;
$news_item = null;
$message = '';

// Fetch news item data
if ($news_id) {
    $stmt = $pdo->prepare("SELECT * FROM news WHERE id = :id");
    $stmt->execute(['id' => $news_id]);
    $news_item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$news_item) {
        $message = "Không tìm thấy bài viết/công thức.";
        $news_id = null; // Invalidate news_id if not found
    }
}

// Handle form submission for update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $news_id) {
    $title = trim($_POST['title']);
    $summary = trim($_POST['summary']);
    $content = trim($_POST['content']);
    $type = $_POST['type'];
    $current_image = $_POST['current_image'] ?? ''; // Hidden input for current image path
    $image_path = $current_image;

    // Handle image upload for update
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "../uploads/";
        $image_name = uniqid() . '_' . basename($_FILES['image']['name']);
        $target_file = $target_dir . $image_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $check = getimagesize($_FILES['image']['tmp_name']);
        if ($check !== false) {
            if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
                && $imageFileType != "gif") {
                $message = "Chỉ cho phép file JPG, JPEG, PNG & GIF.";
            } else {
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image_path = 'uploads/' . $image_name; // Path to save in DB
                    // Optional: Delete old image file if it exists and is not the default/placeholder
                    if (!empty($current_image) && file_exists('../' . $current_image)) {
                         // unlink('../' . $current_image); // Uncomment to delete old file
                    }
                } else {
                    $message = "Có lỗi khi tải ảnh mới lên.";
                }
            }
        } else {
            $message = "File tải lên không phải là ảnh.";
        }
    }

    if (empty($message)) {
        // Update database
        $sql = "UPDATE news SET title = :title, summary = :summary, content = :content, image = :image, type = :type WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute(['title' => $title, 'summary' => $summary, 'content' => $content, 'image' => $image_path, 'type' => $type, 'id' => $news_id])) {
            $message = "Cập nhật bài viết/công thức thành công!";
            // Refresh news item data after update
            $stmt = $pdo->prepare("SELECT * FROM news WHERE id = :id");
            $stmt->execute(['id' => $news_id]);
            $news_item = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $message = "Có lỗi xảy ra khi cập nhật CSDL.";
        }
    }
}

?>

<?php include 'header.php'; ?>

<div class="admin-container">
    <h2><?php echo $news_item ? 'Sửa Tin tức & Công thức' : 'Thông báo'; ?></h2>

    <?php if (!empty($message)): ?>
        <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>

    <?php if ($news_item): ?>
        <form action="edit_news.php?id=<?php echo $news_item['id']; ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($news_item['image']); ?>">

            <div class="form-group">
                <label for="title">Tiêu đề:</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($news_item['title']); ?>" required>
            </div>

            <div class="form-group">
                <label for="summary">Tóm tắt:</label>
                <textarea id="summary" name="summary" rows="3" required><?php echo htmlspecialchars($news_item['summary']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="type">Loại:</label>
                <select id="type" name="type" required>
                    <option value="news" <?php echo ($news_item['type'] == 'news') ? 'selected' : ''; ?>>Tin tức</option>
                    <option value="recipe" <?php echo ($news_item['type'] == 'recipe') ? 'selected' : ''; ?>>Công thức</option>
                </select>
            </div>

            <div class="form-group">
                <label for="content">Nội dung:</label>
                <textarea id="content" name="content" rows="10" required><?php echo htmlspecialchars($news_item['content']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="image">Ảnh đại diện:</label>
                <input type="file" id="image" name="image" accept="image/*">
                <?php if (!empty($news_item['image'])): ?>
                    <p>Ảnh hiện tại: <img src="../<?php echo htmlspecialchars($news_item['image']); ?>" alt="Current Image" width="100"></p>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Cập nhật</button>
                <a href="news.php" class="btn btn-secondary">Quay về</a>
            </div>

        </form>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?> 