<?php
require_once '../config.php';

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') { // Sử dụng user_role
    header('Location: login.php');
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$product = null;
$categories = [];
$error = null;
$success = null;

// Lấy thông tin sản phẩm
if ($id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            $error = "Không tìm thấy sản phẩm với ID này.";
        }
    } catch (PDOException $e) {
        $error = "Lỗi khi tải thông tin sản phẩm: " . $e->getMessage();
    }
} else {
    $error = "ID sản phẩm không hợp lệ.";
}

// Lấy danh mục sản phẩm
try {
    $stmt = $pdo->query("SELECT * FROM categories");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
     // Nếu có lỗi tải danh mục nhưng không có lỗi sản phẩm thì ghi nhận lỗi danh mục
    if (!$error) $error = "Lỗi khi tải danh mục sản phẩm: " . $e->getMessage();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && $product && !$error) {
     // Lấy dữ liệu từ form và làm sạch
    $name = trim($_POST['name']);
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $description = trim($_POST['description']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_new = isset($_POST['is_new']) ? 1 : 0;
    $is_flash_sale = isset($_POST['is_flash_sale']) ? 1 : 0;
    $stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);

    // Kiểm tra dữ liệu bắt buộc
    if (empty($name) || $category_id === false || $price === false || empty($description) || $stock === false) {
        $error = "Vui lòng điền đầy đủ và chính xác các thông tin bắt buộc (Tên, Danh mục, Giá, Mô tả, Số lượng tồn).";
    } else {

        // Upload ảnh mới (nếu có)
        $image = $product['image']; // Giữ ảnh cũ mặc định
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $target_dir = '../uploads/products/'; // Thư mục riêng cho ảnh sản phẩm
             // Tạo thư mục nếu chưa tồn tại
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $image_name = uniqid() . '_' . basename($_FILES['image']['name']); // Tên file duy nhất
            $target_file = $target_dir . $image_name;
            $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Kiểm tra loại file và kích thước
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($image_file_type, $allowed_types)) {
                $error = "Chỉ cho phép tải lên các file ảnh JPG, JPEG, PNG, GIF.";
            } elseif ($_FILES['image']['size'] > 5000000) {
                $error = "Kích thước file ảnh quá lớn, tối đa 5MB.";
            } else {
                 // Di chuyển file mới
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image = 'uploads/products/' . $image_name;
                    // Xóa ảnh cũ nếu tồn tại và khác ảnh mặc định/mới
                    if (!empty($product['image']) && $product['image'] !== 'uploads/products/' . $image_name && file_exists('../' . $product['image'])) {
                         unlink('../' . $product['image']);
                    }
                } else {
                    $error = "Có lỗi khi tải lên file ảnh mới.";
                }
            }
        }

         // Nếu không có lỗi, tiến hành cập nhật vào DB
        if (!$error) {
            try {
                $stmt = $pdo->prepare("UPDATE products SET name = ?, category_id = ?, image = ?, price = ?, description = ?, stock = ?, is_featured = ?, is_new = ?, is_flash_sale = ? WHERE id = ?");
                $stmt->execute([$name, $category_id, $image, $price, $description, $stock, $is_featured, $is_new, $is_flash_sale, $id]);

                // Chuyển hướng về trang quản lý sản phẩm với thông báo thành công
                header('Location: manage_products.php?success=' . urlencode('Đã cập nhật sản phẩm thành công!'));
                exit;
            } catch (PDOException $e) {
                $error = "Lỗi cơ sở dữ liệu khi cập nhật sản phẩm: " . $e->getMessage();
            }
        }
    }
}
?>

<?php include 'header.php'; ?>

<div class="admin-container">
    <h1>Sửa sản phẩm</h1>

    <?php if (isset($error)): ?>
        <div class="error-message">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <?php if ($product && empty($error)): // Chỉ hiển thị form nếu tìm thấy sản phẩm và không có lỗi ban đầu ?>
        <form action="edit_product.php?id=<?php echo $id; ?>" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Tên sản phẩm:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="category_id">Danh mục:</label>
                <select id="category_id" name="category_id" required>
                     <option value="">-- Chọn danh mục --</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" <?php echo ($category['id'] == $product['category_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="image">Ảnh sản phẩm:</label>
                <input type="file" id="image" name="image" accept="image/*">
                 <?php if (!empty($product['image'])): ?>
                    <p style="margin-top: 10px;">Ảnh hiện tại:</p>
                    <img src="../<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="max-width: 150px; height: auto; display: block; margin-top: 5px; border: 1px solid #ddd; padding: 5px; background-color: #fff;">
                 <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="price">Giá:</label>
                <input type="number" id="price" name="price" value="<?php echo htmlspecialchars($product['price'] ?? ''); ?>" step="0.01" required min="0">
            </div>

             <div class="form-group">
                <label for="stock">Số lượng tồn:</label>
                <input type="number" id="stock" name="stock" value="<?php echo htmlspecialchars($product['stock'] ?? ''); ?>" required min="0">
            </div>

            <div class="form-group">
                <label for="description">Mô tả:</label>
                <textarea id="description" name="description" rows="6" required><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-group form-checkbox">
                <label>
                    <input type="checkbox" name="is_featured" <?php echo ($product['is_featured']) ? 'checked' : ''; ?>> Sản phẩm nổi bật
                </label>
            </div>

            <div class="form-group form-checkbox">
                <label>
                    <input type="checkbox" name="is_new" <?php echo ($product['is_new']) ? 'checked' : ''; ?>> Sản phẩm mới
                </label>
            </div>

            <div class="form-group form-checkbox">
                <label>
                    <input type="checkbox" name="is_flash_sale" <?php echo ($product['is_flash_sale']) ? 'checked' : ''; ?>> Flash sale
                </label>
            </div>

            <div class="form-group">
                <button type="submit" class="btn add-button"><i class="fas fa-save"></i> Cập nhật sản phẩm</button>
            </div>
        </form>
    <?php elseif (!$error): // Thông báo nếu không tìm thấy sản phẩm và không có lỗi hệ thống ?>
         <p class="no-items">Không tìm thấy thông tin sản phẩm.</p>
    <?php endif; ?>

    <div class="back-link" style="text-align: center; margin-top: 20px;">
        <a href="manage_products.php">
             <i class="fas fa-arrow-left"></i> Quay lại quản lý sản phẩm
        </a>
    </div>

</div>

<?php include 'footer.php'; ?>

<style>
    /* Sử dụng lại các style từ add_product.php */
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
    .no-items {
        text-align: center;
        padding: 2rem;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        color: #666;
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