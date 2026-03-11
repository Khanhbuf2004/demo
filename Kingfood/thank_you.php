<?php

require_once 'config.php';

// Load cấu hình ngân hàng
$bank_config = require __DIR__ . '/payment/bank_config.php';

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if (!$order_id) {
    header('Location: index.php');
    exit;
}

// Lấy thông tin đơn hàng
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cảm ơn bạn đã đặt hàng - Kingfood</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="thank-you-container">
            <div class="thank-you-layout">
                <div class="thank-you-main-column">
                    <div class="thank-you-content">
                        <i class="fas fa-check-circle"></i>
                        <h1>Cảm ơn bạn đã đặt hàng!</h1>
                        <p>Đơn hàng của bạn đã được đặt thành công.</p>
                        <div class="order-info">
                            <p>Mã đơn hàng: <strong>#<?php echo $order_id; ?></strong></p>
                            <p>Tổng tiền: <strong><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ</strong></p>
                        </div>
                    </div>

                    <div class="next-steps">
                        <h2>Các bước tiếp theo</h2>
                        <ol>
                            <li>Chúng tôi sẽ xác nhận đơn hàng của bạn trong thời gian sớm nhất.</li>
                            <li>Bạn sẽ nhận được email xác nhận đơn hàng.</li>
                            <li>Đơn hàng sẽ được giao trong vòng 2-3 ngày làm việc.</li>
                        </ol>
                    </div>

                    <div class="action-buttons">
                        <a href="index.php" class="btn">Tiếp tục mua sắm</a>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="order_detail.php?id=<?php echo $order_id; ?>" class="btn btn-outline">Xem chi tiết đơn hàng</a>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    
    <!-- Modal thanh toán (chỉ hiển thị nếu payment_method = banking và status = pending) -->
    <?php if ($order['payment_method'] === 'banking' && $order['status'] === 'pending'): ?>
    <div id="paymentModal" class="payment-modal">
        <div class="payment-modal-content">
            <div class="payment-modal-header">
                <h2>Thanh toán chuyển khoản</h2>
                <button class="payment-modal-close" onclick="closePaymentModal()">&times;</button>
            </div>
            <div class="payment-modal-body">
                <!-- Thông tin ngân hàng -->
                <div class="bank-info-section">
                    <h3>Thông tin chuyển khoản</h3>
                    <div class="bank-details-list">
                        <div class="bank-detail-row">
                            <span class="bank-label">Ngân hàng:</span>
                            <span class="bank-value"><?php echo htmlspecialchars($bank_config['bank_name']); ?></span>
                        </div>
                        <div class="bank-detail-row">
                            <span class="bank-label">Số tài khoản:</span>
                            <span class="bank-value"><?php echo htmlspecialchars($bank_config['bank_account']); ?></span>
                        </div>
                        <div class="bank-detail-row">
                            <span class="bank-label">Chủ tài khoản:</span>
                            <span class="bank-value"><?php echo htmlspecialchars($bank_config['account_holder']); ?></span>
                        </div>
                        <div class="bank-detail-row">
                            <span class="bank-label">Số tiền:</span>
                            <span class="bank-value amount"><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ</span>
                        </div>
                        <div class="bank-detail-row">
                            <span class="bank-label">Nội dung:</span>
                            <span class="bank-value note">KF<?php echo $order_id; ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- QR Code -->
                <div class="qr-code-section">
                    <h3>Quét mã QR để thanh toán</h3>
                    <div class="qr-code-container" id="qrCodeContainer">
                        <div class="qr-loading">Đang tạo mã QR...</div>
                    </div>
                    <p class="qr-instruction">Quét mã QR bằng ứng dụng ngân hàng để thanh toán nhanh chóng</p>
                </div>
                
                <!-- Kiểm tra thanh toán -->
                <div class="payment-status-check">
                    <div class="status-message" id="statusMessage">
                        <i class="fas fa-clock"></i>
                        <span>Đang chờ thanh toán...</span>
                    </div>
                    <button type="button" class="btn btn-check-payment" id="checkPaymentBtn" onclick="checkPaymentStatus()">
                        <i class="fas fa-sync-alt"></i> Kiểm tra thanh toán
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <script>
        const orderId = <?php echo $order_id; ?>;
        const totalAmount = <?php echo $order['total_amount']; ?>;
        const paymentMethod = '<?php echo $order['payment_method']; ?>';
        
        // Thông tin ngân hàng từ config
        const bankConfig = {
            bank_account: '<?php echo addslashes($bank_config['bank_account']); ?>',
            bank_name: '<?php echo addslashes($bank_config['bank_name']); ?>',
            account_holder: '<?php echo addslashes($bank_config['account_holder']); ?>'
        };
        
        // Hiển thị modal khi trang load (nếu là banking và pending)
        <?php if ($order['payment_method'] === 'banking' && $order['status'] === 'pending'): ?>
        let paymentCheckInterval = null;
        
        document.addEventListener('DOMContentLoaded', function() {
            // Hiển thị modal
            const modal = document.getElementById('paymentModal');
            if (modal) {
                modal.style.display = 'flex';
                // Tạo QR code
                generateQRCode();
                // Tự động kiểm tra thanh toán mỗi 30 giây
                paymentCheckInterval = setInterval(checkPaymentStatus, 30000);
            }
        });
        
        // Hàm dừng kiểm tra tự động
        function stopAutoCheck() {
            if (paymentCheckInterval) {
                clearInterval(paymentCheckInterval);
                paymentCheckInterval = null;
            }
        }
        
        function closePaymentModal() {
            const modal = document.getElementById('paymentModal');
            if (modal) {
                modal.style.display = 'none';
            }
        }
        
        // Đóng modal khi click bên ngoài
        window.onclick = function(event) {
            const modal = document.getElementById('paymentModal');
            if (event.target === modal) {
                closePaymentModal();
            }
        }
        
        function generateQRCode() {
            const qrContainer = document.getElementById('qrCodeContainer');
            if (!qrContainer) return;
            
            qrContainer.innerHTML = '<div class="qr-loading">Đang tạo mã QR...</div>';
            
            fetch('payment/qr_generator.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    order_id: orderId,
                    amount: totalAmount,
                    bank_account: bankConfig.bank_account,
                    bank_name: bankConfig.bank_name,
                    account_holder: bankConfig.account_holder,
                    note: 'KF' + orderId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    qrContainer.innerHTML = `
                        <img src="${data.url}" alt="QR Code" class="qr-code-image" />
                    `;
                } else {
                    qrContainer.innerHTML = `
                        <div class="qr-error">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p>Không thể tạo mã QR: ${data.error || 'Lỗi không xác định'}</p>
                            <p class="qr-fallback">Vui lòng chuyển khoản thủ công theo thông tin bên trên</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                qrContainer.innerHTML = `
                    <div class="qr-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Lỗi khi tạo mã QR. Vui lòng chuyển khoản thủ công theo thông tin bên trên</p>
                    </div>
                `;
            });
        }
        
        function checkPaymentStatus() {
            const statusMessage = document.getElementById('statusMessage');
            const checkBtn = document.getElementById('checkPaymentBtn');
            
            // Kiểm tra element tồn tại
            if (!statusMessage || !checkBtn) {
                console.error('Không tìm thấy element statusMessage hoặc checkPaymentBtn');
                return;
            }
            
            // Disable button và hiển thị loading
            checkBtn.disabled = true;
            checkBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang kiểm tra...';
            
            fetch(`payment/check_payment.php?order_id=${orderId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (!data.success) {
                    // Xử lý lỗi từ API
                    statusMessage.innerHTML = `
                        <i class="fas fa-exclamation-triangle" style="color: #dc3545;"></i>
                        <span style="color: #dc3545;">Lỗi: ${data.error || 'Không thể kiểm tra trạng thái'}</span>
                    `;
                    statusMessage.classList.remove('status-success');
                    checkBtn.disabled = false;
                    checkBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Kiểm tra thanh toán';
                    return;
                }
                
                // Kiểm tra nếu đã thanh toán (is_paid hoặc status = completed)
                const isPaid = data.data.is_paid || data.data.status === 'completed';
                
                if (isPaid) {
                    // Dừng kiểm tra tự động
                    stopAutoCheck();
                    
                    // Đã thanh toán - đóng modal và chuyển về trang index
                    statusMessage.innerHTML = `
                        <i class="fas fa-check-circle" style="color: #28a745;"></i>
                        <span style="color: #28a745; font-weight: bold;">Đã thanh toán thành công! Đang chuyển hướng...</span>
                    `;
                    statusMessage.classList.add('status-success');
                    
                    // Đóng modal trước
                    const modal = document.getElementById('paymentModal');
                    if (modal) {
                        setTimeout(() => {
                            modal.style.display = 'none';
                        }, 1000);
                    }
                    
                    // Chuyển về trang index sau 2 giây
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 2000);
                } else {
                    // Chưa thanh toán - hiển thị trạng thái hiện tại
                    const statusText = data.data.status_text || data.data.status || 'Chờ thanh toán';
                    statusMessage.innerHTML = `
                        <i class="fas fa-clock" style="color: #ffc107;"></i>
                        <span>Trạng thái: ${statusText}</span>
                        <br><small style="color: #6c757d; font-size: 0.85em;">Nếu bạn đã chuyển khoản, vui lòng đợi admin xác nhận hoặc liên hệ hỗ trợ.</small>
                    `;
                    statusMessage.classList.remove('status-success');
                    checkBtn.disabled = false;
                    checkBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Kiểm tra thanh toán';
                }
            })
            .catch(error => {
                console.error('Error khi kiểm tra thanh toán:', error);
                statusMessage.innerHTML = `
                    <i class="fas fa-exclamation-triangle" style="color: #dc3545;"></i>
                    <span style="color: #dc3545;">Lỗi khi kiểm tra trạng thái</span>
                    <br><small style="color: #6c757d; font-size: 0.85em;">Vui lòng thử lại sau hoặc liên hệ hỗ trợ.</small>
                `;
                statusMessage.classList.remove('status-success');
                checkBtn.disabled = false;
                checkBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Kiểm tra thanh toán';
            });
        }
        <?php endif; ?>
    </script>
    
    <style>
        /* Payment Modal Styles */
        .payment-modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
        }
        
        .payment-modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 0;
            border: none;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            animation: modalFadeIn 0.3s;
        }
        
        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .payment-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid #dee2e6;
            background-color: #1a7f37;
            color: white;
            border-radius: 12px 12px 0 0;
        }
        
        .payment-modal-header h2 {
            margin: 0;
            font-size: 1.5rem;
        }
        
        .payment-modal-close {
            background: none;
            border: none;
            color: white;
            font-size: 2rem;
            font-weight: bold;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background-color 0.3s;
        }
        
        .payment-modal-close:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        .payment-modal-body {
            padding: 2rem;
        }
        
        .bank-info-section {
            margin-bottom: 2rem;
        }
        
        .bank-info-section h3 {
            color: #1a7f37;
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }
        
        .bank-details-list {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        
        .bank-detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        .bank-detail-row:last-child {
            border-bottom: none;
        }
        
        .bank-label {
            font-weight: 600;
            color: #666;
            min-width: 140px;
        }
        
        .bank-value {
            color: #333;
            font-weight: 500;
            text-align: right;
        }
        
        .bank-value.amount {
            color: #1a7f37;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .bank-value.note {
            color: #1a7f37;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .qr-code-section {
            margin-top: 2rem;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 8px;
            text-align: center;
        }
        
        .qr-code-section h3 {
            color: #1a7f37;
            margin-bottom: 1rem;
        }
        
        .qr-code-container {
            margin: 1rem 0;
            min-height: 250px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .qr-code-image {
            max-width: 280px;
            width: 100%;
            height: auto;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 1rem;
            background: white;
        }
        
        .qr-loading {
            color: #666;
            font-style: italic;
        }
        
        .qr-error {
            color: #dc3545;
            padding: 1rem;
        }
        
        .qr-error i {
            font-size: 2em;
            margin-bottom: 0.5rem;
        }
        
        .qr-instruction {
            color: #666;
            font-size: 0.9em;
            margin-top: 10px;
        }
        
        .qr-fallback {
            margin-top: 10px;
            font-size: 0.85em;
            color: #555;
        }
        
        .payment-status-check {
            margin-top: 2rem;
            padding: 1.5rem;
            background: #fff;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            text-align: center;
        }
        
        .status-message {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 4px;
            min-height: 60px;
        }
        
        .status-message.status-success {
            background: #d4edda;
            color: #155724;
        }
        
        .btn-check-payment {
            width: 100%;
            padding: 0.75rem 2rem;
            background: #1a7f37;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s ease;
        }
        
        .btn-check-payment:hover:not(:disabled) {
            background: #155724;
        }
        
        .btn-check-payment:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
    </style>
</body>
</html> 