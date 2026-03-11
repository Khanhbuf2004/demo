<?php

require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Kiểm tra email đã tồn tại chưa
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        $error = "Email này đã được sử dụng. Vui lòng chọn email khác.";
    } else {
        // Kiểm tra mật khẩu
        if ($password !== $confirm_password) {
            $error = "Mật khẩu xác nhận không khớp.";
        } else {
            // Mã hóa mật khẩu
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Thêm người dùng mới
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
            if ($stmt->execute([$name, $email, $hashed_password])) {
                $success = "Đăng ký thành công! Bạn có thể đăng nhập ngay bây giờ.";
            } else {
                $error = "Có lỗi xảy ra khi đăng ký. Vui lòng thử lại sau.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - Kingfood</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="auth-container">
            <h1>Đăng ký tài khoản</h1>
            
            <?php if ($error): ?>
                <div class="error-message">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message">
                    <?php echo $success; ?>
                    <p>Bạn có thể <a href="login.php">đăng nhập</a> ngay bây giờ.</p>
                </div>
            <?php else: ?>
                <form method="post" action="register.php" class="auth-form">
                    <div class="form-group">
                        <label for="name">Họ và tên *</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Mật khẩu *</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Xác nhận mật khẩu *</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" class="btn">Đăng ký</button>
                </form>
                
                <div class="auth-links">
                    <p>Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html> 