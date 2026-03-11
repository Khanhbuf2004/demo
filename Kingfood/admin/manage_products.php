<?php
require_once '../config.php';

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$products = [];
$error = null;

// Lấy danh sách sản phẩm
try {
    $stmt = $pdo->query("SELECT id, name, price, stock, created_at FROM products ORDER BY created_at DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Lỗi khi tải danh sách sản phẩm: " . $e->getMessage();
}

// Lấy thông báo thành công từ URL nếu có
$success = isset($_GET['success']) ? $_GET['success'] : null;

?>

<?php include 'header.php'; ?>

<div class="admin-container">
    <h1>Quản lý sản phẩm</h1>

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

    <div class="action-bar">
        <a href="add_product.php" class="add-button"><i class="fas fa-plus"></i> Thêm sản phẩm mới</a>
    </div>

    <?php if (empty($products)): ?>
        <p class="no-items">Chưa có sản phẩm nào trong cơ sở dữ liệu.</p>
    <?php else: ?>
        <div class="item-list">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên sản phẩm</th>
                        <th>Giá</th>
                        <th>Số lượng tồn</th>
                        <th>Ngày tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo number_format($product['price'], 0, ',', '.'); ?> VNĐ</td>
                            <td><?php echo $product['stock']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($product['created_at'])); ?></td>
                            <td class="actions">
                                <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="edit-link"><i class="fas fa-edit"></i> Sửa</a>
                                <a href="delete_product.php?id=<?php echo $product['id']; ?>" class="delete-link" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này không?');"><i class="fas fa-trash-alt"></i> Xóa</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>

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
    .action-bar {
        margin-bottom: 1.5rem;
        text-align: right;
    }
    .add-button {
        display: inline-block;
        padding: 0.75rem 1.5rem;
        background-color: #1a7f37;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        transition: background-color 0.3s ease;
         display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    .add-button:hover {
        background-color: #155724;
    }
    .item-list table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border-radius: 8px;
        overflow: hidden;
    }
    .item-list th, .item-list td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid #eee;
    }
    .item-list th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #333;
    }
    .item-list tr:last-child td {
        border-bottom: none;
    }
    .item-list tr:hover {
        background-color: #f8f9fa;
    }
    .item-list .actions a {
        color: #1a7f37;
        text-decoration: none;
        margin-right: 10px;
        transition: color 0.3s ease;
    }
     .item-list .actions a:hover {
         color: #155724;
     }
     .item-list .actions .delete-link {
         color: #dc3545;
     }
      .item-list .actions .delete-link:hover {
         color: #c82333;
     }
      .no-items {
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