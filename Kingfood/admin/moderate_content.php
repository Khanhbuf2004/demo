<?php
require_once '../config.php'; // Điều chỉnh đường dẫn nếu cần

// Kiểm tra xem người dùng có phải là admin và đã đăng nhập chưa
// (Bạn cần có một cơ chế kiểm tra đăng nhập admin)
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php'); // Chuyển hướng về trang đăng nhập admin
    exit;
}

// Lấy danh sách các bài viết chờ kiểm duyệt
try {
    $stmt = $pdo->prepare("SELECT id, title, content, type, created_at FROM news WHERE status = 'pending' ORDER BY created_at DESC");
    $stmt->execute();
    $pending_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Lỗi khi tải danh sách bài viết: " . $e->getMessage();
    $pending_articles = []; // Gán mảng rỗng để tránh lỗi vòng lặp
}

// Lấy thông báo thành công từ URL nếu có
$success = isset($_GET['success']) ? $_GET['success'] : null;
?>

<?php include 'header.php'; ?>

<div class="admin-container">
    <h1>Bài viết chờ kiểm duyệt</h1>

    <div class="admin-actions" style="margin-bottom: 20px;">
        <a href="add_article.php" class="btn btn-primary">Đăng bài viết mới</a>
    </div>
    
    <?php if (isset($error)): ?>
        <div class="error-message">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <div class="success-message">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($pending_articles)): ?>
        <p class="no-articles">Không có bài viết nào chờ kiểm duyệt.</p>
    <?php else: ?>
        <div class="article-list">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tiêu đề / Nội dung</th>
                        <th>Loại</th>
                        <th>Ngày gửi</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_articles as $article): ?>
                        <tr>
                            <td><?php echo $article['id']; ?></td>
                            <td><?php echo htmlspecialchars(!empty($article['title']) ? $article['title'] : substr($article['content'], 0, 100) . '...'); ?></td>
                            <td><?php echo htmlspecialchars($article['type']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($article['created_at'])); ?></td>
                            <td class="actions">
                                <a href="view_article.php?id=<?php echo $article['id']; ?>">Xem & Duyệt</a>
                                <!-- <a href="#">Xóa</a> -->
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<style>
    .admin-container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 1rem;
    }
    .admin-container h1 {
        text-align: center;
        margin-bottom: 2rem;
        color: #333;
    }
    .article-list table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border-radius: 8px;
        overflow: hidden;
    }
    .article-list th, .article-list td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid #eee;
    }
    .article-list th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #333;
    }
    .article-list tr:last-child td {
        border-bottom: none;
    }
    .article-list tr:hover {
        background-color: #f8f9fa;
    }
    .article-list .actions a {
        color: #1a7f37;
        text-decoration: none;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        background-color: #e9ecef;
        transition: background-color 0.3s;
    }
    .article-list .actions a:hover {
        background-color: #dee2e6;
    }
    .no-articles {
        text-align: center;
        padding: 2rem;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        color: #666;
    }
    .error-message, .success-message {
        padding: 1rem;
        margin-bottom: 1rem;
        border-radius: 4px;
        text-align: center;
    }
    .error-message {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    .success-message {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
</style>

<?php include 'footer.php'; ?> 