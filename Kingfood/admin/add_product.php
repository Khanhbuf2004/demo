<?php

require_once '../config.php';

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') { // Sử dụng user_role
    header('Location: login.php');
    exit;
}

// Lấy danh mục sản phẩm
$stmt = $pdo->query("SELECT * FROM categories");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ form và làm sạch
    $name = trim($_POST['name']);
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $description = trim($_POST['description']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_new = isset($_POST['is_new']) ? 1 : 0;
    $is_flash_sale = isset($_POST['is_flash_sale']) ? 1 : 0;
    $stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT); // Thêm số lượng tồn kho

    // Kiểm tra dữ liệu bắt buộc
    if (empty($name) || $category_id === false || $price === false || empty($description) || $stock === false) {
        $error = "Vui lòng điền đầy đủ và chính xác các thông tin bắt buộc (Tên, Danh mục, Giá, Mô tả, Số lượng tồn).";
    } else {
        // Upload ảnh
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $target_dir = '../uploads/products/'; // Thư mục riêng cho ảnh sản phẩm
            // Tạo thư mục nếu chưa tồn tại
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $image_name = uniqid() . '_' . basename($_FILES['image']['name']); // Tên file duy nhất
            $target_file = $target_dir . $image_name;
            $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Kiểm tra loại file (chỉ cho phép ảnh)
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($image_file_type, $allowed_types)) {
                $error = "Chỉ cho phép tải lên các file ảnh JPG, JPEG, PNG, GIF.";
            // Kiểm tra kích thước file (ví dụ: dưới 5MB)
            } elseif ($_FILES['image']['size'] > 5000000) {
                $error = "Kích thước file ảnh quá lớn, tối đa 5MB.";
            } else {
                 // Di chuyển file
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image = 'uploads/products/' . $image_name;
                } else {
                    $error = "Có lỗi khi tải lên file ảnh.";
                }
            }
        } else {
             $error = "Vui lòng chọn ảnh sản phẩm.";
        }

        // Nếu không có lỗi upload ảnh hoặc các trường khác, tiến hành thêm vào DB
        if (!$error) {
            try {
                $stmt = $pdo->prepare("INSERT INTO products (name, category_id, image, price, description, stock, is_featured, is_new, is_flash_sale, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$name, $category_id, $image, $price, $description, $stock, $is_featured, $is_new, $is_flash_sale]);

                // Chuyển hướng về trang quản lý sản phẩm với thông báo thành công
                header('Location: manage_products.php?success=' . urlencode('Đã thêm sản phẩm thành công!'));
                exit;
            } catch (PDOException $e) {
                $error = "Lỗi cơ sở dữ liệu khi thêm sản phẩm: " . $e->getMessage();
            }
        }
    }
}
?>

<?php include 'header.php'; ?>

<div class="admin-container">
    <h1>Thêm sản phẩm mới</h1>

    <?php if (isset($error)): ?>
        <div class="error-message">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form action="add_product.php" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">Tên sản phẩm:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="category_id">Danh mục:</label>
            <select id="category_id" name="category_id" required>
                <option value="">-- Chọn danh mục --</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>" <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="image">Ảnh sản phẩm:</label>
            <input type="file" id="image" name="image" accept="image/*" required>
        </div>

        <div class="form-group">
            <label for="price">Giá:</label>
            <input type="number" id="price" name="price" value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" step="0.01" required min="0">
        </div>

         <div class="form-group">
            <label for="stock">Số lượng tồn:</label>
            <input type="number" id="stock" name="stock" value="<?php echo htmlspecialchars($_POST['stock'] ?? ''); ?>" required min="0">
        </div>

        <div class="form-group">
            <label for="description">Mô tả:</label>
            <textarea id="description" name="description" rows="6" required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
        </div>

        <div class="form-group form-checkbox">
            <label>
                <input type="checkbox" name="is_featured" <?php echo (isset($_POST['is_featured']) && $_POST['is_featured']) ? 'checked' : ''; ?>> Sản phẩm nổi bật
            </label>
        </div>

        <div class="form-group form-checkbox">
            <label>
                <input type="checkbox" name="is_new" <?php echo (isset($_POST['is_new']) && $_POST['is_new']) ? 'checked' : ''; ?>> Sản phẩm mới
            </label>
        </div>

        <div class="form-group form-checkbox">
            <label>
                <input type="checkbox" name="is_flash_sale" <?php echo (isset($_POST['is_flash_sale']) && $_POST['is_flash_sale']) ? 'checked' : ''; ?>> Flash sale
            </label>
        </div>

        <div class="form-group">
            <button type="submit" class="btn add-button"><i class="fas fa-plus"></i> Thêm sản phẩm</button>
        </div>
    </form>

    <div class="back-link" style="text-align: center; margin-top: 20px;">
        <a href="manage_products.php">
             <i class="fas fa-arrow-left"></i> Quay lại quản lý sản phẩm
        </a>
    </div>

</div>

<?php include 'footer.php'; ?>

<style>
    .admin-container {
        max-width: 800px;
        margin: 2rem auto;
        padding: 0 1rem;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        padding: 2rem;
    }
    .admin-container h1 {
        text-align: center;
        margin-bottom: 2rem;
        color: #333;
    }
    .form-group {
        margin-bottom: 1.5rem;
    }
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #555;
    }
    .form-group input[type="text"],
    .form-group input[type="number"],
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box;
        font-size: 1rem;
    }
    .form-group input[type="file"] {
         padding: 0.5rem;
         border: 1px solid #ddd;
         border-radius: 4px;
         background-color: #f8f9fa;
    }
    .form-group textarea {
        resize: vertical;
    }
    .form-checkbox label {
        font-weight: normal;
        display: inline-block;
        margin-top: 0.5rem;
    }
     .form-checkbox input[type="checkbox"] {
         margin-right: 0.5rem;
     }
    .btn.add-button {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        background-color: #1a7f37;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        transition: background-color 0.3s ease;
        border: none;
        cursor: pointer;
        font-size: 1rem;
    }
    .btn.add-button:hover {
        background-color: #155724;
    }
     .error-message {
        background-color: #f8d7da;
        color: #721c24;
        padding: 1rem;
        margin-bottom: 1rem;
        border-radius: 4px;
        text-align: center;
        border: 1px solid #f5c6cb;
    }
    .back-link a {
        color: #1a7f37;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        background-color: #e9ecef;
        transition: background-color 0.3s;
    }

    .back-link a:hover {
        background-color: #dee2e6;
    }

</style> 