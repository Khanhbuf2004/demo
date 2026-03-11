<?php
require_once '../config.php';

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$order = null;
$order_items = [];
$error = null;

$order_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$order_id) {
    $error = "ID đơn hàng không hợp lệ.";
} else {
    try {
        // Lấy thông tin đơn hàng
        $stmt = $pdo->prepare("SELECT o.*, u.username FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = :id");
        $stmt->bindParam(':id', $order_id);
        $stmt->execute();
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            $error = "Không tìm thấy đơn hàng với ID này.";
        } else {
            // Lấy danh sách sản phẩm trong đơn hàng
            $stmt_items = $pdo->prepare("SELECT oi.*, p.name as product_name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = :order_id");
            $stmt_items->bindParam(':order_id', $order_id);
            $stmt_items->execute();
            $order_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
        }

    } catch (PDOException $e) {
        $error = "Lỗi khi tải thông tin đơn hàng: " . $e->getMessage();
    }
}

?>

<?php include 'header.php'; ?>

<div class="admin-container">
    <h1>Chi tiết đơn hàng #<?php echo htmlspecialchars($order_id); ?></h1>

    <?php if (isset($error)): ?>
        <div class="error-message">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <?php if ($order && !$error): ?>
        <div class="order-detail">
            <h2>Thông tin đơn hàng</h2>
            <div class="info-row">
                <span class="label">ID đơn hàng:</span>
                <span class="value"><?php echo htmlspecialchars($order['id']); ?></span>
            </div>
            <div class="info-row">
                <span class="label">Khách hàng:</span>
                <span class="value"><?php echo htmlspecialchars($order['username'] ?? 'Khách vãng lai'); ?></span>
            </div>
             <div class="info-row">
                <span class="label">Tên người nhận:</span>
                <span class="value"><?php echo htmlspecialchars($order['customer_name']); ?></span>
            </div>
             <div class="info-row">
                <span class="label">Email:</span>
                <span class="value"><?php echo htmlspecialchars($order['email']); ?></span>
            </div>
             <div class="info-row">
                <span class="label">Điện thoại:</span>
                <span class="value"><?php echo htmlspecialchars($order['phone']); ?></span>
            </div>
            <div class="info-row">
                <span class="label">Địa chỉ nhận hàng:</span>
                <span class="value"><?php echo htmlspecialchars($order['address']); ?></span>
            </div>
             <div class="info-row">
                <span class="label">Ghi chú:</span>
                <span class="value"><?php echo htmlspecialchars($order['note']); ?></span>
            </div>
            <div class="info-row">
                <span class="label">Tổng tiền:</span>
                <span class="value"><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> VNĐ</span>
            </div>
            <div class="info-row">
                <span class="label">Trạng thái:</span>
                <span class="value"><?php echo htmlspecialchars($order['status']); ?></span>
            </div>
            <div class="info-row">
                <span class="label">Phương thức thanh toán:</span>
                <span class="value"><?php echo htmlspecialchars($order['payment_method']); ?></span>
            </div>
            <div class="info-row">
                <span class="label">Ngày đặt:</span>
                <span class="value"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></span>
            </div>

            <h2 style="margin-top: 2rem;">Sản phẩm trong đơn hàng</h2>
            <?php if (empty($order_items)): ?>
                <p>Không có sản phẩm nào trong đơn hàng này.</p>
            <?php else: ?>
                <div class="item-list">
                    <table>
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Số lượng</th>
                                <th>Giá</th>
                                <th>Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                    <td><?php echo number_format($item['price'], 0, ',', '.'); ?> VNĐ</td>
                                    <td><?php echo number_format($item['quantity'] * $item['price'], 0, ',', '.'); ?> VNĐ</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Các nút hành động (ví dụ: Cập nhật trạng thái) có thể thêm ở đây -->

        </div>
    <?php elseif (!$error): // Chỉ hiển thị nếu ID không hợp lệ hoặc không tìm thấy đơn hàng ?>
         <p class="no-items">Không tìm thấy thông tin đơn hàng.</p>
    <?php endif; ?>

    <div class="back-link" style="text-align: center; margin-top: 20px;">
        <a href="manage_orders.php">
             <i class="fas fa-arrow-left"></i> Quay lại quản lý đơn hàng
        </a>
    </div>

</div>

<?php include 'footer.php'; ?>

<style>
    .admin-container {
        max-width: 900px;
        margin: 2rem auto;
        padding: 0 1rem;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        padding: 2rem;
    }
    .admin-container h1 {
        text-align: center;
        margin-bottom: 2rem;
        color: #333;
    }
    .order-detail h2 {
        margin-top: 0;
        margin-bottom: 1.5rem;
        color: #333;
        border-bottom: 1px solid #eee;
        padding-bottom: 0.5rem;
    }
    .info-row {
        display: flex;
        margin-bottom: 0.75rem;
        padding: 0.5rem;
        background-color: #f8f9fa;
        border-radius: 4px;
    }
    .info-row .label {
        font-weight: 600;
        min-width: 180px;
        color: #555;
    }
    .info-row .value {
        flex-grow: 1;
        color: #333;
    }
     .item-list table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
        background: white; /* Table background if needed */
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
         border-radius: 8px;
         overflow: hidden;
    }
    .item-list th, .item-list td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid #eee;
    }
     .item-list th {
        background-color: #e9ecef; /* Light grey for header */
        font-weight: 600;
        color: #333;
    }
    .item-list tr:last-child td {
        border-bottom: none;
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
     .no-items {
        text-align: center;
        padding: 2rem;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        color: #666;
    }
    .back-link {
        text-align: center;
        margin-top: 2rem;
    }

    .back-link a {
        color: #1a7f37;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        background-color: #e9ecef;
        transition: background-color 0.3s;
    }

    .back-link a:hover {
        background-color: #dee2e6;
    }

     @media (max-width: 768px) {
        .info-row {
            flex-direction: column;
            align-items: flex-start;
        }
        .info-row .label {
            min-width: auto;
            margin-bottom: 0.25rem;
        }
     }

</style> 