<?php

require_once 'config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Lấy thông tin đơn hàng
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: user_profile.php');
    exit;
}

// Lấy chi tiết đơn hàng
$stmt = $pdo->prepare("
    SELECT oi.*, p.name, p.image 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn hàng #<?php echo $order_id; ?> - Kingfood</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="order-detail-container">
            <h1>Chi tiết đơn hàng #<?php echo $order_id; ?></h1>
            
            <div class="order-info">
                <div class="info-section">
                    <h2>Thông tin đơn hàng</h2>
                    <div class="info-row">
                        <span>Ngày đặt:</span>
                        <span><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></span>
                    </div>
                    <div class="info-row">
                        <span>Trạng thái:</span>
                        <span class="status <?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span>
                    </div>
                    <div class="info-row">
                        <span>Phương thức thanh toán:</span>
                        <span><?php echo $order['payment_method'] === 'cod' ? 'Thanh toán khi nhận hàng' : 'Chuyển khoản ngân hàng'; ?></span>
                    </div>
                </div>
                
                <div class="info-section">
                    <h2>Thông tin giao hàng</h2>
                    <div class="info-row">
                        <span>Người nhận:</span>
                        <span><?php echo htmlspecialchars($order['customer_name']); ?></span>
                    </div>
                    <div class="info-row">
                        <span>Số điện thoại:</span>
                        <span><?php echo htmlspecialchars($order['phone']); ?></span>
                    </div>
                    <div class="info-row">
                        <span>Địa chỉ:</span>
                        <span><?php echo htmlspecialchars($order['address']); ?></span>
                    </div>
                    <?php if ($order['note']): ?>
                        <div class="info-row">
                            <span>Ghi chú:</span>
                            <span><?php echo htmlspecialchars($order['note']); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="order-items">
                <h2>Sản phẩm đã đặt</h2>
                <div class="items-list">
                    <?php foreach ($order_items as $item): ?>
                        <div class="order-item">
                            <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <div class="item-details">
                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                <div class="item-price">
                                    <?php echo number_format($item['price'], 0, ',', '.'); ?>đ x <?php echo $item['quantity']; ?>
                                </div>
                            </div>
                            <div class="item-total">
                                <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>đ
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="order-summary">
                    <div class="summary-row">
                        <span>Tạm tính:</span>
                        <span><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ</span>
                    </div>
                    <div class="summary-row">
                        <span>Phí vận chuyển:</span>
                        <span>Miễn phí</span>
                    </div>
                    <div class="summary-row total">
                        <span>Tổng cộng:</span>
                        <span><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ</span>
                    </div>
                </div>
            </div>
            
            <div class="order-actions">
                <a href="user_profile.php" class="btn">Quay lại</a>
                <?php if ($order['status'] === 'pending'): ?>
                    <button class="btn btn-danger" onclick="cancelOrder(<?php echo $order_id; ?>)">Hủy đơn hàng</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    
    <script>
        function cancelOrder(orderId) {
            if (confirm('Bạn có chắc chắn muốn hủy đơn hàng này?')) {
                // Gửi request hủy đơn hàng
                fetch('cancel_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'order_id=' + orderId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Hủy đơn hàng thành công!');
                        location.reload();
                    } else {
                        alert('Có lỗi xảy ra khi hủy đơn hàng.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi hủy đơn hàng.');
                });
            }
        }
    </script>
</body>
</html> 