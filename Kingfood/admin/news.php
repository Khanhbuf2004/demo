<?php
require_once '../config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Lấy danh sách tin tức/công thức
$stmt = $pdo->query("SELECT * FROM news ORDER BY created_at DESC");
$news_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'header.php'; ?>

<div class="admin-container">
    <h2>Danh sách Tin tức & Công thức</h2>
    <a href="add_news.php" class="btn">Thêm mới</a>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Ảnh</th>
                <th>Tiêu đề</th>
                <th>Loại</th>
                <th>Ngày tạo</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($news_items as $item): ?>
                <tr>
                    <td><?php echo $item['id']; ?></td>
                    <td><img src="../<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" width="50"></td>
                    <td><?php echo htmlspecialchars($item['title']); ?></td>
                    <td><?php echo htmlspecialchars($item['type']); ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($item['created_at'])); ?></td>
                    <td>
                        <a href="edit_news.php?id=<?php echo $item['id']; ?>" class="btn">Sửa</a>
                        <a href="delete_news.php?id=<?php echo $item['id']; ?>" class="btn" onclick="return confirm('Bạn có chắc chắn muốn xóa?')">Xóa</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?> 