<?php
require_once 'config.php'; // Include database connection

// Get category ID from URL
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;

$products = [];
$category_name = 'Tất cả sản phẩm';

// Fetch products based on category ID
if ($category_id > 0) {
    try {
        // Get category name
        $stmt_cat = $pdo->prepare("SELECT name FROM categories WHERE id = ? LIMIT 1");
        $stmt_cat->execute([$category_id]);
        $category = $stmt_cat->fetch(PDO::FETCH_ASSOC);
        if ($category) {
            $category_name = htmlspecialchars($category['name']);
        }

        // Fetch products belonging to this category
        $stmt_products = $pdo->prepare("SELECT * FROM products WHERE category_id = ?");
        $stmt_products->execute([$category_id]);
        $products = $stmt_products->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        // Handle database error
        echo "Database error: " . $e->getMessage();
    }
} else {
    // If no category ID, maybe show all products or a default message
    // For now, we'll just show a message if no category is selected
    // Or you could fetch all products if that's the desired behavior
}

require_once 'header.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $category_name; ?> - Kingfood</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <div class="container product-section">
        <h2><?php echo $category_name; ?></h2>
        
        <?php if (!empty($products)): ?>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <!-- Product Card structure (similar to index.php) -->
                    <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="product-card">
                         <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                         <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                         <div class="price"><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</div>
                         <?php if (isset($product['is_new']) && $product['is_new']): ?>
                             <div class="new">Mới</div>
                         <?php endif; ?>
                         <div class="sales">Đã bán <?php echo isset($product['sold']) ? $product['sold'] : 0; ?></div>
                         <div class="rating">
                             <?php
                             $rating = isset($product['rating']) ? $product['rating'] : 4;
                             for ($i = 1; $i <= 5; $i++):
                                 if ($i <= $rating): ?>
                                     <i class="fas fa-star filled"></i>
                                 <?php else: ?>
                                     <i class="far fa-star empty"></i>
                                 <?php endif; ?>
                             <?php endfor; ?>
                         </div>
                         
                         <div class="add-to-cart">Thêm vào giỏ</div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Không tìm thấy sản phẩm nào trong danh mục này.</p>
        <?php endif; ?>

    </div>

    <!-- Product Modal Structure -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <div id="modalProductDetails">
                <!-- Product details will be loaded here via AJAX -->
            </div>
        </div>
    </div>

    <script src="js/main.js"></script>

    <?php
    // Include footer
    // require_once 'footer.php'; // Assuming you have a footer file
    ?>

</body>
</html> 