<?php
require_once '../config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Lấy danh sách sản phẩm cùng với tên danh mục
$stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Bao gồm header admin
include 'header.php';
?>

<div class="admin-container">
    <h2>Danh sách sản phẩm</h2>
    <a href="add_product.php" class="btn">Thêm sản phẩm</a>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ảnh</th>
                        <th>Tên</th>
                        <th>Danh mục</th>
                        <th>Giá</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td><img src="../<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" width="50"></td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                            <td><?php echo number_format($product['price'], 0, ',', '.'); ?> VNĐ</td>
                            <td>
                                <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn">Sửa</a>
                                <a href="delete_product.php?id=<?php echo $product['id']; ?>" class="btn" onclick="return confirm('Bạn có chắc chắn muốn xóa?')">Xóa</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
</div>

<?php include 'footer.php'; ?> 