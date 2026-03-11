<?php

require_once '../config.php';

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Lấy tên file hiện tại để active menu
$current_page = basename($_SERVER['PHP_SELF']);

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Kingfood</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css"> <!-- Sử dụng style.css chung -->
    <!-- Bạn có thể thêm các CSS riêng cho admin tại đây nếu cần -->
    <style>
        /* Style cho header admin */
        .admin-header {
            background-color: #1a7f37;
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .admin-header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem; /* Thêm padding ngang cho container */
            flex-wrap: wrap; /* Cho phép xuống dòng trên màn hình nhỏ */
        }
        .admin-logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: white;
            text-decoration: none;
            margin-right: 1rem; /* Khoảng cách với nav */
        }
        .admin-nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap; /* Cho phép các mục nav xuống dòng */
        }
         .admin-nav li {
             margin: 0;
         }
        .admin-nav a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: background-color 0.3s;
            display: flex; /* Để icon và text cùng hàng */
            align-items: center;
            gap: 0.5rem;
        }
        .admin-nav a:hover {
            background-color: rgba(255,255,255,0.1);
        }
        .admin-nav a.active {
            background-color: rgba(255,255,255,0.2);
        }
        .admin-user {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-left: 1rem; /* Khoảng cách với nav */
        }
         .admin-user i {
             color: white;
         }
        .admin-user a {
            color: white;
            text-decoration: none;
        }
        .admin-user a:hover {
            text-decoration: underline;
        }

        /* Style cho main content area */
        .admin-main {
             padding: 1rem 0;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .admin-header .container {
                flex-direction: column;
                align-items: flex-start;
            }
            .admin-logo {
                margin-bottom: 1rem;
            }
            .admin-nav ul {
                flex-direction: column;
                gap: 0.5rem;
                margin-bottom: 1rem;
            }
             .admin-user {
                 margin-left: 0;
                 width: 100%; /* Đảm bảo user info chiếm toàn bộ chiều ngang */
                 justify-content: center; /* Căn giữa user info */
             }
        }

        /* Dropdown styles */
        .dropdown {
            position: relative;
            display: inline-block; /* Changed from flex item to allow dropdown positioning */
        }

        .dropdown-content {
            display: none; /* Hide the dropdown content by default */
            position: absolute;
            background-color: #f9f9f9; /* Light background */
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            border-radius: 4px;
            overflow: hidden; /* Hide sharp corners on child elements */
        }

        .dropdown-content a {
            color: black; /* Dark text for visibility */
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            text-align: left; /* Align text to the left */
            transition: background-color 0.2s;
        }

        .dropdown-content a:hover {
            background-color: #e9e9e9; /* Slightly darker on hover */
        }

        .dropdown:hover .dropdown-content {
            display: block; /* Show the dropdown menu on hover */
        }

    </style>
</head>
<body>
    <header class="admin-header">
        <div class="container">
            <a href="index.php" class="admin-logo">Kingfood Admin</a>
            <nav class="admin-nav">
                <ul>
                    <li><a href="index.php" <?php echo ($current_page == 'index.php') ? 'class="active"' : ''; ?>>
                        <i class="fas fa-home"></i> Trang chủ
                    </a></li>
                    <li><a href="manage_products.php" <?php echo ($current_page == 'products.php' || $current_page == 'add_product.php' || $current_page == 'edit_product.php' || $current_page == 'delete_product.php') ? 'class="active"' : ''; ?>>
                        <i class="fas fa-box"></i> Sản phẩm
                    </a></li>
                    <li><a href="categories.php" <?php echo ($current_page == 'categories.php' || $current_page == 'add_category.php' || $current_page == 'edit_category.php' || $current_page == 'delete_category.php') ? 'class="active"' : ''; ?>>
                        <i class="fas fa-list"></i> Danh mục
                    </a></li>
                    <li class="dropdown">
                        <a href="news.php" <?php echo ($current_page == 'news.php' || $current_page == 'add_news.php' || $current_page == 'moderate_content.php' || $current_page == 'view_article.php') ? 'class="active"' : ''; ?>>
                            <i class="fas fa-newspaper"></i> Tin tức & Công thức
                        </a>
                        <div class="dropdown-content">
                            <a href="news.php">Danh sách</a>
                            <a href="add_news.php">Thêm mới</a>
                            <a href="moderate_content.php">Duyệt bài</a>
                        </div>
                    </li>
                    <li><a href="banners.php" <?php echo ($current_page == 'banners.php' || $current_page == 'add_banner.php' || $current_page == 'edit_banner.php' || $current_page == 'delete_banner.php') ? 'class="active"' : ''; ?>>
                        <i class="fas fa-image"></i> Banner
                    </a></li>
                    <li><a href="logout.php">Đăng xuất</a></li>
                </ul>
            </nav>
            <div class="admin-user">
                <i class="fas fa-user-circle"></i> <!-- Icon người dùng đẹp hơn -->
                <a href="profile.php"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></a>
                <span>|</span>
                <a href="logout.php">Đăng xuất</a>
            </div>
        </div>
    </header>
    <main class="admin-main"> 