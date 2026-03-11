<?php

require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Kiểm tra thông tin đăng nhập
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        // Đăng nhập thành công
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];
        
        // Chuyển hướng dựa vào vai trò
        if ($user['role'] === 'admin') {
            header('Location: admin/index.php');
        } else {
            header('Location: index.php');
        }
        exit;
    } else {
        $error = "Email hoặc mật khẩu không đúng.";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Kingfood</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="auth-container">
            <h1>Đăng nhập</h1>
            
            <?php if ($error): ?>
                <div class="error-message">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="login.php" class="auth-form">
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Mật khẩu *</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-container">
                        <input type="checkbox" name="remember">
                        <span class="checkmark"></span>
                        Ghi nhớ đăng nhập
                    </label>
                </div>
                
                <button type="submit" class="btn">Đăng nhập</button>
            </form>
            
            <div class="auth-links">
                <p>Chưa có tài khoản? <a href="register.php">Đăng ký</a></p>
                <p><a href="forgot_password.php">Quên mật khẩu?</a></p>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html> 