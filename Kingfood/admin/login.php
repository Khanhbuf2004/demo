<?php
require_once '../config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = 'admin'");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // --- TẠM THỜI: BỎ QUA KIỂM TRA MẬT KHẨU MÃ HÓA ---n    // --- CẢNH BÁO: ĐÂY LÀ RỦI RO BẢO MẬT NGHIÊM TRỌNG ---n    
    // if ($user && $password === $user['password']) { // Chỉ sử dụng kiểm tra mật khẩu không mã hóa tạm thời
    // --- KẾT THÚC PHẦN TẠM THỜI ---n

    // Sử dụng password_verify() để kiểm tra mật khẩu đã mã hóa
    if ($user && password_verify($password, $user['password'])) {

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_username'] = $user['username'];
        header('Location: index.php');
        exit;
    } else {
        $error = 'Tên đăng nhập hoặc mật khẩu không đúng';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            font-family: sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }
        .login-container h1 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #333;
        }
        .login-container .error {
            color: #dc3545;
            margin-bottom: 15px;
        }
        .login-container div {
            margin-bottom: 15px;
            text-align: left;
        }
        .login-container label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        .login-container input[type="text"],
        .login-container input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .login-container .btn {
            width: 100%;
            padding: 10px;
            background-color: #1a7f37;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        .login-container .btn:hover {
            background-color: #155724;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Đăng nhập</h1>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form action="login.php" method="post">
            <div>
                <label for="username">Tên đăng nhập:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div>
                <label for="password">Mật khẩu:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Đăng nhập</button>
        </form>
    </div>
</body>
</html> 