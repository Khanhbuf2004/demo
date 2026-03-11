<?php

require_once 'config.php';

// Kiểm tra giỏ hàng
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL;
$user_info = null;

// Lấy thông tin người dùng nếu đã đăng nhập
if ($user_id) {
    $stmt = $pdo->prepare("SELECT username, email, phone, address FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_info = $stmt->fetch();
}

// Xử lý đặt hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $note = $_POST['note'];
    $payment_method = $_POST['payment_method'];
    
    try {
        $pdo->beginTransaction();
        
        // Tạo đơn hàng mới
        $stmt = $pdo->prepare("INSERT INTO orders (customer_name, email, phone, address, note, payment_method, total_amount, status, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $phone, $address, $note, $payment_method, $_SESSION['cart_total'], 'pending', $user_id]);
        $order_id = $pdo->lastInsertId();
        
        // Thêm chi tiết đơn hàng
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        foreach ($_SESSION['cart'] as $item) {
            $stmt->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
        }
        
        $pdo->commit();
        
        // Xóa giỏ hàng
        unset($_SESSION['cart']);
        unset($_SESSION['cart_total']);
        
        // Luôn chuyển đến trang cảm ơn (nếu banking sẽ hiển thị modal)
        header('Location: thank_you.php?order_id=' . $order_id);
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        // Hiển thị thông báo lỗi chi tiết để debug
        $error = "Có lỗi xảy ra khi đặt hàng: " . $e->getMessage();
        // Bạn nên log lỗi này thay vì hiển thị trực tiếp trên production
        // error_log("Order placement failed: " . $e->getMessage());
    }
}

// Tính tổng tiền
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['quantity'];
}
$_SESSION['cart_total'] = $total;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán - Kingfood</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <h1>Thanh toán</h1>
        
        <?php if (isset($error)): ?>
            <div class="error-message">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="checkout-container">
            <div class="checkout-form">
                <form method="post" action="checkout.php">
                    <h2>Thông tin giao hàng</h2>
                    
                    <div class="form-group">
                        <label for="name">Họ và tên *</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user_info['username'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_info['email'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Số điện thoại *</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user_info['phone'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Địa chỉ giao hàng *</label>
                        <textarea id="address" name="address" required><?php echo htmlspecialchars($user_info['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="note">Ghi chú</label>
                        <textarea id="note" name="note"></textarea>
                    </div>
                    
                    <h2>Phương thức thanh toán</h2>
                    <div class="payment-methods">
                        <div class="payment-method">
                            <input type="radio" id="cod" name="payment_method" value="cod" checked>
                            <label for="cod">Thanh toán khi nhận hàng (COD)</label>
                        </div>
                        <div class="payment-method">
                            <input type="radio" id="banking" name="payment_method" value="banking">
                            <label for="banking">Chuyển khoản ngân hàng</label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn checkout-btn">Đặt hàng</button>
                </form>
            </div>
            
            <div class="order-summary">
                <h2>Đơn hàng của bạn</h2>
                <div class="order-items">
                    <?php foreach ($_SESSION['cart'] as $item): ?>
                        <div class="order-item">
                            <div class="item-info">
                                <span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                <span class="item-quantity">x<?php echo $item['quantity']; ?></span>
                            </div>
                            <span class="item-price"><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>đ</span>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="order-total">
                    <div class="total-row">
                        <span>Tạm tính:</span>
                        <span><?php echo number_format($total, 0, ',', '.'); ?>đ</span>
                    </div>
                    <div class="total-row">
                        <span>Phí vận chuyển:</span>
                        <span>Miễn phí</span>
                    </div>
                    <div class="total-row final">
                        <span>Tổng cộng:</span>
                        <span><?php echo number_format($total, 0, ',', '.'); ?>đ</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html> 