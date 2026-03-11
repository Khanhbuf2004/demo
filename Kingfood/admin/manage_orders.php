<?php
require_once '../config.php';

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$orders = [];
$error = null;

// Lấy danh sách đơn hàng
try {
    // Truy vấn lấy thông tin đơn hàng và tên khách hàng (nếu có user_id)
    $stmt = $pdo->query("SELECT o.id, o.user_id, u.username, o.total_amount, o.status, o.created_at, o.payment_method FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Lỗi khi tải danh sách đơn hàng: " . $e->getMessage();
}

// Lấy thông báo thành công từ URL nếu có
$success = isset($_GET['success']) ? $_GET['success'] : null;

?>

<?php include 'header.php'; ?>

<div class="admin-container">
    <h1>Quản lý đơn hàng</h1>

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

    <?php if (empty($orders)): ?>
        <p class="no-items">Chưa có đơn hàng nào trong hệ thống.</p>
    <?php else: ?>
        <div class="item-list">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Khách hàng</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Phương thức TT</th>
                        <th>Ngày đặt</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr id="order-row-<?php echo $order['id']; ?>">
                            <td><?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['username'] ?? 'Khách vãng lai'); ?></td>
                            <td><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> VNĐ</td>
                            <td class="order-status" id="status-<?php echo $order['id']; ?>"><?php echo htmlspecialchars($order['status']); ?></td>
                            <td><?php echo htmlspecialchars($order['payment_method']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                            <td class="actions">
                                <!-- Liên kết đến trang xem chi tiết đơn hàng -->
                                <a href="view_order.php?id=<?php echo $order['id']; ?>" class="view-link"><i class="fas fa-eye"></i> Xem</a>
                                <!-- Nút xác nhận - chỉ hiển thị khi status = pending -->
                                <?php if ($order['status'] === 'pending'): ?>
                                    <button type="button" 
                                            class="confirm-btn" 
                                            onclick="confirmOrder(<?php echo $order['id']; ?>, this)"
                                            data-order-id="<?php echo $order['id']; ?>">
                                        <i class="fas fa-check"></i> Xác nhận
                                    </button>
                                <?php endif; ?>
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
    /* Sử dụng lại các style chung từ manage_products.php nếu cần */
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
    .confirm-btn {
        background-color: #28a745;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.9rem;
        transition: background-color 0.3s ease;
        margin-left: 10px;
    }
    .confirm-btn:hover {
        background-color: #218838;
    }
    .confirm-btn:disabled {
        background-color: #6c757d;
        cursor: not-allowed;
    }
    .confirm-btn i {
        margin-right: 5px;
    }
</style>

<script>
function confirmOrder(orderId, buttonElement) {
    // Xác nhận trước khi thực hiện
    if (!confirm('Bạn có chắc chắn muốn xác nhận đơn hàng #' + orderId + '?')) {
        return;
    }

    // Disable button và hiển thị loading
    buttonElement.disabled = true;
    const originalText = buttonElement.innerHTML;
    buttonElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';

    // Tạo FormData để gửi POST request
    const formData = new FormData();
    formData.append('order_id', orderId);
    formData.append('status', 'completed');

    // Gọi API
    fetch('update_order_status.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Cập nhật trạng thái trong bảng
            const statusCell = document.getElementById('status-' + orderId);
            if (statusCell) {
                statusCell.textContent = 'completed';
            }

            // Ẩn nút xác nhận
            buttonElement.style.display = 'none';

            // Hiển thị thông báo thành công
            showMessage('Xác nhận đơn hàng #' + orderId + ' thành công!', 'success');
        } else {
            // Hiển thị lỗi
            showMessage('Lỗi: ' + (data.error || 'Không thể cập nhật trạng thái'), 'error');
            buttonElement.disabled = false;
            buttonElement.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Lỗi khi kết nối đến server', 'error');
        buttonElement.disabled = false;
        buttonElement.innerHTML = originalText;
    });
}

function showMessage(message, type) {
    // Tạo thông báo
    const messageDiv = document.createElement('div');
    messageDiv.className = type === 'success' ? 'success-message' : 'error-message';
    messageDiv.textContent = message;
    messageDiv.style.position = 'fixed';
    messageDiv.style.top = '20px';
    messageDiv.style.right = '20px';
    messageDiv.style.zIndex = '9999';
    messageDiv.style.minWidth = '300px';
    messageDiv.style.padding = '1rem';
    messageDiv.style.borderRadius = '4px';
    messageDiv.style.boxShadow = '0 2px 8px rgba(0,0,0,0.2)';

    // Thêm vào body
    document.body.appendChild(messageDiv);

    // Tự động xóa sau 3 giây
    setTimeout(() => {
        messageDiv.style.opacity = '0';
        messageDiv.style.transition = 'opacity 0.3s ease';
        setTimeout(() => {
            document.body.removeChild(messageDiv);
        }, 300);
    }, 3000);
}
</script> 