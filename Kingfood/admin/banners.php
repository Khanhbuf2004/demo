<?php
require_once '../config.php';

// Kiểm tra đăng nhập

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Lấy danh sách banner
$stmt = $pdo->query("SELECT * FROM banners");
$banners = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Bao gồm header admin
include 'header.php';
?>

<div class="admin-container">
    <h2>Danh sách Banner</h2>
    <a href="add_banner.php" class="btn">Thêm banner</a>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ảnh</th>
                        <th>Liên kết</th>
                        <th>Vị trí</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($banners as $banner): ?>
                        <tr>
                            <td><?php echo $banner['id']; ?></td>
                            <td><img src="../<?php echo htmlspecialchars($banner['image']); ?>" alt="Banner" width="100"></td>
                            <td><?php echo htmlspecialchars($banner['link']); ?></td>
                            <td><?php echo htmlspecialchars($banner['position']); ?></td>
                            <td>
                                <a href="edit_banner.php?id=<?php echo $banner['id']; ?>" class="btn">Sửa</a>
                                <a href="delete_banner.php?id=<?php echo $banner['id']; ?>" class="btn" onclick="return confirm('Bạn có chắc chắn muốn xóa?')">Xóa</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
</div>

<?php include 'footer.php'; ?> 