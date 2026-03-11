<?php

require_once 'config.php';

// Lấy thông tin giỏ hàng từ session
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

$total_price = 0;
foreach ($cart as $item) {
    $total_price += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng của bạn - Kingfood</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="cart-container">
            <h1>Giỏ hàng của bạn</h1>

            <?php if (empty($cart)): ?>
                <p>Giỏ hàng của bạn trống.</p>
            <?php else: ?>
                <div class="cart-items">
                    <?php foreach ($cart as $productId => $item): ?>
                        <div class="cart-item" data-product-id="<?php echo $productId; ?>">
                            <div class="item-image">
                                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            </div>
                            <div class="item-details">
                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                <p>Giá: <?php echo number_format($item['price'], 0, ',', '.'); ?>đ</p>
                            </div>
                            <div class="item-quantity">
                                <input type="number" class="quantity-input" value="<?php echo $item['quantity']; ?>" min="1">
                            </div>
                            <div class="item-subtotal">
                                <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>đ
                            </div>
                            <div class="item-remove">
                                <button class="remove-btn"><i class="fas fa-trash-alt"></i></button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-summary">
                    <h2>Tổng cộng</h2>
                    <div class="summary-total">
                        <span>Tổng tiền:</span>
                        <span class="total-price"><?php echo number_format($total_price, 0, ',', '.'); ?>đ</span>
                    </div>
                    <a href="checkout.php" class="btn checkout-btn">Tiến hành thanh toán</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        // JavaScript để xử lý cập nhật số lượng và xóa sản phẩm (sẽ triển khai sau)
        document.addEventListener('DOMContentLoaded', function() {
            const quantityInputs = document.querySelectorAll('.quantity-input');
            const removeButtons = document.querySelectorAll('.remove-btn');

            quantityInputs.forEach(input => {
                input.addEventListener('change', function() {
                    const productId = this.closest('.cart-item').dataset.productId;
                    const newQuantity = this.value;
                    // TODO: Gửi AJAX request để cập nhật giỏ hàng trên server
                    console.log(`Update product ${productId} quantity to ${newQuantity}`);
                });
            });

            removeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const productId = this.closest('.cart-item').dataset.productId;
                    // TODO: Gửi AJAX request để xóa sản phẩm khỏi giỏ hàng trên server
                    console.log(`Remove product ${productId}`);
                });
            });
        });
    </script>
</body>
</html> 