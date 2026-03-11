<?php
require_once '../config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// File này hiện tại là trang chủ admin, có thể thêm dashboard sau

// Lấy thống kê
try {
    // Đếm số bài viết chờ duyệt
    $stmt = $pdo->query("SELECT COUNT(*) FROM news WHERE status = 'pending'");
    $pending_articles = $stmt->fetchColumn();

    // Đếm số đơn hàng mới
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
    $pending_orders = $stmt->fetchColumn();

    // Đếm tổng số sản phẩm
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    $total_products = $stmt->fetchColumn();

    // Lấy doanh thu hôm nay
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE DATE(created_at) = :today AND status != 'cancelled'");
    $stmt->execute(['today' => $today]);
    $today_revenue = $stmt->fetchColumn();

} catch (PDOException $e) {
    $error = "Lỗi khi tải thống kê: " . $e->getMessage();
}

?>

<?php include 'header.php'; ?>

<div class="admin-dashboard">
    <div class="dashboard-header">
        <h1>Bảng điều khiển</h1>
        <p>Chào mừng đến trang quản trị Kingfood!</p>
    </div>

    <?php if (isset($error)): ?>
        <div class="error-message">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <div class="dashboard-stats">
        <div class="stat-card">
            <i class="fas fa-tasks"></i>
            <div class="stat-info">
                <h3>Bài viết chờ duyệt</h3>
                <p><?php echo $pending_articles; ?></p>
                <a href="moderate_content.php" class="stat-link">Xem chi tiết</a>
            </div>
        </div>

        <div class="stat-card">
            <i class="fas fa-shopping-cart"></i>
            <div class="stat-info">
                <h3>Đơn hàng mới</h3>
                <p><?php echo $pending_orders; ?></p>
                <a href="manage_orders.php" class="stat-link">Xem chi tiết</a>
            </div>
        </div>

        <div class="stat-card">
            <i class="fas fa-box"></i>
            <div class="stat-info">
                <h3>Tổng sản phẩm</h3>
                <p><?php echo $total_products; ?></p>
                <a href="manage_products.php" class="stat-link">Xem chi tiết</a>
            </div>
        </div>

        <div class="stat-card">
            <i class="fas fa-money-bill-wave"></i>
            <div class="stat-info">
                <h3>Doanh thu hôm nay</h3>
                <p><?php echo number_format($today_revenue, 0, ',', '.'); ?> VNĐ</p>
                <a href="manage_orders.php" class="stat-link">Xem chi tiết</a>
            </div>
        </div>
    </div>

    <div class="quick-actions">
        <h2>Thao tác nhanh</h2>
        <div class="action-buttons">
            <a href="moderate_content.php" class="action-button">
                <i class="fas fa-tasks"></i>
                Kiểm duyệt bài viết
            </a>
            <a href="manage_products.php" class="action-button">
                <i class="fas fa-box"></i>
                Quản lý sản phẩm
            </a>
            <a href="manage_orders.php" class="action-button">
                <i class="fas fa-shopping-cart"></i>
                Quản lý đơn hàng
            </a>
        </div>
    </div>
</div>

<style>
    .admin-dashboard {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    .dashboard-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .dashboard-header h1 {
        color: #333;
        margin-bottom: 0.5rem;
    }

    .dashboard-header p {
        color: #666;
    }

    .dashboard-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .stat-card i {
        font-size: 2rem;
        color: #1a7f37;
    }

    .stat-info h3 {
        margin: 0;
        font-size: 1rem;
        color: #666;
    }

    .stat-info p {
        margin: 0.5rem 0;
        font-size: 1.5rem;
        font-weight: bold;
        color: #333;
    }

    .stat-link {
        color: #1a7f37;
        text-decoration: none;
        font-size: 0.9rem;
    }

    .stat-link:hover {
        text-decoration: underline;
    }

    .quick-actions {
        background: white;
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .quick-actions h2 {
        margin-top: 0;
        margin-bottom: 1rem;
        color: #333;
    }

    .action-buttons {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .action-button {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 4px;
        color: #333;
        text-decoration: none;
        transition: background-color 0.3s;
    }

    .action-button:hover {
        background: #e9ecef;
    }

    .action-button i {
        color: #1a7f37;
    }

    @media (max-width: 768px) {
        .dashboard-stats {
            grid-template-columns: 1fr;
        }

        .action-buttons {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php include 'footer.php'; ?> 