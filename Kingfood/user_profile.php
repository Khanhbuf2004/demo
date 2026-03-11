<?php

require_once 'config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Lấy thông tin người dùng
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
        
        try {
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, phone = ?, address = ? WHERE id = ?");
            $stmt->execute([$name, $email, $phone, $address, $user_id]);
            $success = "Cập nhật thông tin thành công!";
            
            // Cập nhật session
            $_SESSION['user_name'] = $name;
            
            // Refresh thông tin người dùng
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
        } catch (PDOException $e) {
            $error = "Có lỗi xảy ra khi cập nhật thông tin.";
        }
    } elseif (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (password_verify($current_password, $user['password'])) {
            if ($new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $user_id]);
                $success = "Đổi mật khẩu thành công!";
            } else {
                $error = "Mật khẩu mới không khớp.";
            }
        } else {
            $error = "Mật khẩu hiện tại không đúng.";
        }
    }
}

// Lấy lịch sử đơn hàng
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tài khoản của tôi - Kingfood</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="profile-container">
            <h1>Tài khoản của tôi</h1>
            
            <?php if ($success): ?>
                <div class="success-message">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="error-message">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <div class="profile-content">
                <div class="profile-sidebar">
                    <div class="user-info">
                        <i class="fas fa-user-circle"></i>
                        <h3><?php echo htmlspecialchars($user['username']); ?></h3>
                    </div>
                    <ul class="profile-menu">
                        <li class="active"><a href="#profile">Thông tin tài khoản</a></li>
                        <li><a href="#orders">Đơn hàng của tôi</a></li>
                        <li><a href="#change-password">Đổi mật khẩu</a></li>
                        <li><a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
                    </ul>
                </div>
                
                <div class="profile-main">
                    <!-- Thông tin tài khoản -->
                    <section id="profile" class="profile-section">
                        <h2>Thông tin tài khoản</h2>
                        <form method="post" action="user_profile.php" class="profile-form">
                            <div class="form-group">
                                <label for="name">Họ và tên</label>
                                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Số điện thoại</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="address">Địa chỉ</label>
                                <textarea id="address" name="address"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>
                            
                            <button type="submit" name="update_profile" class="btn">Cập nhật thông tin</button>
                        </form>
                    </section>
                    
                    <!-- Đơn hàng -->
                    <section id="orders" class="profile-section">
                        <h2>Đơn hàng của tôi</h2>
                        <?php if (empty($orders)): ?>
                            <p>Bạn chưa có đơn hàng nào.</p>
                        <?php else: ?>
                            <div class="orders-list">
                                <?php foreach ($orders as $order): ?>
                                    <div class="order-item">
                                        <div class="order-header">
                                            <span class="order-id">Đơn hàng #<?php echo $order['id']; ?></span>
                                            <span class="order-date"><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></span>
                                            <span class="order-status <?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span>
                                        </div>
                                        <div class="order-total">
                                            Tổng tiền: <?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ
                                        </div>
                                        <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-small">Chi tiết</a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>
                    
                    <!-- Đổi mật khẩu -->
                    <section id="change-password" class="profile-section">
                        <h2>Đổi mật khẩu</h2>
                        <form method="post" action="user_profile.php" class="profile-form">
                            <div class="form-group">
                                <label for="current_password">Mật khẩu hiện tại</label>
                                <input type="password" id="current_password" name="current_password" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password">Mật khẩu mới</label>
                                <input type="password" id="new_password" name="new_password" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Xác nhận mật khẩu mới</label>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                            </div>
                            
                            <button type="submit" name="change_password" class="btn">Đổi mật khẩu</button>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    
    <script>
        // Xử lý menu profile
        document.querySelectorAll('.profile-menu a').forEach(link => {
            link.addEventListener('click', function(e) {
                // Kiểm tra nếu là link logout thì không ngăn chặn hành động mặc định
                if (this.classList.contains('logout-btn')) {
                    return; // Cho phép link logout hoạt động bình thường
                }
                
                e.preventDefault();
                const targetId = this.getAttribute('href').substring(1);
                
                // Ẩn tất cả các section
                document.querySelectorAll('.profile-section').forEach(section => {
                    section.style.display = 'none';
                });
                
                // Hiển thị section được chọn
                document.getElementById(targetId).style.display = 'block';
                
                // Cập nhật active state
                document.querySelectorAll('.profile-menu li').forEach(item => {
                    item.classList.remove('active');
                });
                this.parentElement.classList.add('active');
            });
        });
    </script>
</body>
</html> 