<?php
require_once '../config.php';

// Kiểm tra đăng nhập

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Lấy danh mục sản phẩm (không cần JOIN vì bảng categories đã có cột image)
$stmt = $pdo->query("SELECT * FROM categories");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Cần lấy tên danh mục cha nếu parent_id không null
// Cách đơn giản tạm thời: Duyệt qua danh mục và lấy tên danh mục cha
// Cách hiệu quả hơn: Sử dụng recursive query hoặc lấy tất cả danh mục và xây dựng cây
// Tạm thời sử dụng cách đơn giản để hiển thị tên danh mục cha
$parent_categories = array();
if (!empty($categories)) {
    $parent_ids = array_filter(array_column($categories, 'parent_id'));
    if (!empty($parent_ids)) {
        $placeholders = implode(',', array_fill(0, count($parent_ids), '?'));
        $stmt_parents = $pdo->prepare("SELECT id, name FROM categories WHERE id IN ($placeholders)");
        $stmt_parents->execute(array_values($parent_ids));
        $parent_categories_data = $stmt_parents->fetchAll(PDO::FETCH_KEY_PAIR);
        foreach ($categories as &$category) {
            if ($category['parent_id'] !== NULL && isset($parent_categories_data[$category['parent_id']])) {
                $category['parent_name'] = $parent_categories_data[$category['parent_id']];
            } else {
                $category['parent_name'] = ''; // Hoặc 'Không có'
            }
        }
        unset($category); // Break the reference
    }
}

// Bao gồm header admin
include 'header.php';
?>

<div class="admin-container">
    <h2>Danh mục sản phẩm</h2>
    <a href="add_category.php" class="btn">Thêm danh mục</a>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ảnh</th>
                        <th>Tên</th>
                        <th>Danh mục cha</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?php echo $category['id']; ?></td>
                            <td><img src="../<?php echo htmlspecialchars($category['image']); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>" width="50"></td>
                            <td><?php echo htmlspecialchars($category['name']); ?></td>
                            <td><?php echo htmlspecialchars($category['parent_name'] ?? ''); ?></td>
                            <td>
                                <a href="edit_category.php?id=<?php echo $category['id']; ?>" class="btn">Sửa</a>
                                <a href="delete_category.php?id=<?php echo $category['id']; ?>" class="btn" onclick="return confirm('Bạn có chắc chắn muốn xóa?')">Xóa</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
</div>

<?php include 'footer.php'; ?> 