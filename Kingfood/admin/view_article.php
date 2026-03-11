<?php
require_once '../config.php';

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$article = null;
$error = null;
$success = null;

// Lấy các giá trị enum cho cột 'type'
$type_options = ['news', 'recipe']; // Lấy trực tiếp từ cấu trúc DB nếu phức tạp hơn

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $article_id = filter_input(INPUT_POST, 'article_id', FILTER_VALIDATE_INT);
    $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);
    
    if ($article_id) {
        try {
            if ($action === 'approve' || $action === 'reject') {
                // Xử lý duyệt/từ chối
                $new_status = ($action === 'approve') ? 'approved' : 'rejected';
                $stmt = $pdo->prepare("UPDATE news SET status = :status WHERE id = :id");
                $stmt->bindParam(':status', $new_status);
                $stmt->bindParam(':id', $article_id);
                if ($stmt->execute()) {
                    $success = "Đã " . (($action === 'approve') ? 'duyệt' : 'từ chối') . " bài viết thành công!";
                    header('Location: moderate_content.php?success=' . urlencode($success));
                    exit;
                }
            } else {
                // Xử lý cập nhật thông tin bài viết
                $title = trim($_POST['title']);
                $content = trim($_POST['content']);
                $type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);
                $summary = trim($_POST['summary']);
                
                // Xử lý upload hình ảnh
                $image_path = null;
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = '../uploads/news/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                    
                    if (in_array($file_extension, $allowed_extensions)) {
                        $new_filename = uniqid() . '.' . $file_extension;
                        $upload_path = $upload_dir . $new_filename;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                            $image_path = 'uploads/news/' . $new_filename;
                        }
                    }
                }
                
                // Cập nhật thông tin bài viết
                $sql = "UPDATE news SET title = :title, content = :content, type = :type, summary = :summary";
                $params = [
                    ':title' => $title,
                    ':content' => $content,
                    ':type' => $type,
                    ':summary' => $summary,
                    ':id' => $article_id
                ];
                
                if ($image_path) {
                    $sql .= ", image = :image";
                    $params[':image'] = $image_path;
                }
                
                $sql .= " WHERE id = :id";
                
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute($params)) {
                    $success = "Cập nhật bài viết thành công!";
                }
            }
        } catch (PDOException $e) {
            $error = "Lỗi cơ sở dữ liệu: " . $e->getMessage();
        }
    }
}

// Lấy thông tin bài viết để hiển thị
$article_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$article_id) {
    $error = "ID bài viết không hợp lệ.";
} else {
    try {
        $stmt = $pdo->prepare("SELECT * FROM news WHERE id = :id");
        $stmt->bindParam(':id', $article_id);
        $stmt->execute();
        $article = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$article) {
            $error = "Không tìm thấy bài viết.";
        }
    } catch (PDOException $e) {
        $error = "Lỗi khi tải thông tin bài viết: " . $e->getMessage();
    }
}
?>

<?php include 'header.php'; ?>

<div class="admin-container">
    <?php if (isset($error)): ?>
        <div class="error-message">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <div class="success-message">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <?php if ($article): ?>
        <div class="article-detail">
            <h1>Chỉnh sửa bài viết</h1>
            
            <form method="POST" action="" enctype="multipart/form-data" class="edit-form">
                <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                
                <div class="form-group">
                    <label for="title">Tiêu đề:</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($article['title'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="summary">Tóm tắt:</label>
                    <textarea id="summary" name="summary" rows="4"><?php echo htmlspecialchars($article['summary'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="type">Loại bài viết:</label>
                    <select id="type" name="type" required>
                        <?php foreach ($type_options as $option): ?>
                            <option value="<?php echo $option; ?>" <?php echo ($article['type'] == $option) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(ucfirst($option)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="content">Nội dung:</label>
                    <textarea id="content" name="content" rows="10" required><?php echo htmlspecialchars($article['content']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="image">Hình ảnh:</label>
                    <?php if (!empty($article['image'])): ?>
                        <div class="current-image">
                            <img src="../<?php echo htmlspecialchars($article['image']); ?>" alt="Hình ảnh hiện tại" style="max-width: 200px;">
                            <p>Hình ảnh hiện tại</p>
                        </div>
                    <?php endif; ?>
                    <input type="file" id="image" name="image" accept="image/*">
                    <small>Để trống nếu không muốn thay đổi hình ảnh</small>
                </div>

                <div class="form-actions">
                    <button type="submit" class="save-btn">
                        <i class="fas fa-save"></i> Lưu thay đổi
                    </button>
                    
                    <div class="moderation-actions">
                        <button type="submit" name="action" value="approve" class="approve-btn">
                            <i class="fas fa-check"></i> Duyệt bài viết
                        </button>
                        <button type="submit" name="action" value="reject" class="reject-btn">
                            <i class="fas fa-times"></i> Từ chối bài viết
                        </button>
                    </div>
                </div>
            </form>
        </div>
    <?php elseif (!isset($error)): ?>
        <p class="no-articles">Không tìm thấy bài viết hoặc ID không hợp lệ.</p>
    <?php endif; ?>

    <div class="back-link">
        <a href="moderate_content.php">
            <i class="fas fa-arrow-left"></i> Quay lại danh sách chờ kiểm duyệt
        </a>
    </div>
</div>

<style>
    .admin-container {
        max-width: 800px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    .article-detail {
        background: white;
        padding: 2rem;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .article-detail h1 {
        text-align: center;
        margin-bottom: 2rem;
        color: #333;
    }

    .edit-form {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .form-group label {
        font-weight: 600;
        color: #333;
    }

    .form-group input[type="text"],
    .form-group select,
    .form-group textarea {
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 1rem;
    }

    .form-group textarea {
        resize: vertical;
        min-height: 200px;
    }

    .form-group textarea#summary {
        min-height: 100px;
    }

    .current-image {
        margin: 1rem 0;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 4px;
        text-align: center;
    }

    .current-image img {
        max-width: 100%;
        height: auto;
        border-radius: 4px;
    }

    .form-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid #eee;
    }

    .moderation-actions {
        display: flex;
        gap: 1rem;
    }

    button {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 1rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s;
    }

    button:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }

    .save-btn {
        background-color: #007bff;
        color: white;
    }

    .approve-btn {
        background-color: #28a745;
        color: white;
    }

    .reject-btn {
        background-color: #dc3545;
        color: white;
    }

    .error-message {
        background-color: #f8d7da;
        color: #721c24;
        padding: 1rem;
        margin-bottom: 1rem;
        border-radius: 4px;
        text-align: center;
        border: 1px solid #f5c6cb;
    }

    .success-message {
        background-color: #d4edda;
        color: #155724;
        padding: 1rem;
        margin-bottom: 1rem;
        border-radius: 4px;
        text-align: center;
        border: 1px solid #c3e6cb;
    }

    .back-link {
        text-align: center;
        margin-top: 2rem;
    }

    .back-link a {
        color: #007bff;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .back-link a:hover {
        text-decoration: underline;
    }
</style>

<?php include 'footer.php'; ?> 