<?php
require_once 'config.php';


// Kiểm tra xem có ID sản phẩm hoặc bài viết được truyền vào không
if (!isset($_GET['id'])) {
    // Chuyển hướng về trang chủ hoặc trang lỗi nếu không có ID
    header('Location: index.php');
    exit();
}

$item_id = $_GET['id'];
$item_type = isset($_GET['type']) ? $_GET['type'] : 'product'; // Mặc định là product

$item_data = null;

if ($item_type === 'product') {
    // Lấy thông tin sản phẩm
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name 
                          FROM products p 
                          LEFT JOIN categories c ON p.category_id = c.id 
                          WHERE p.id = ?");
    $stmt->execute([$item_id]);
    $item_data = $stmt->fetch(PDO::FETCH_ASSOC);

} elseif ($item_type === 'article') {
    // Lấy thông tin bài viết (news)
    $stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
    $stmt->execute([$item_id]);
    $item_data = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Nếu không tìm thấy dữ liệu
if (!$item_data) {
    // Có thể trả về lỗi JSON nếu là AJAX, hoặc chuyển hướng nếu không
    if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
        header('Content-Type: application/json');
        echo json_encode(null); // Trả về null hoặc mảng rỗng
        exit();
    } else {
        header('Location: index.php'); // Hoặc trang 404
        exit();
    }
}

// Nếu là AJAX request, trả về JSON
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    header('Content-Type: application/json');
    echo json_encode($item_data);
    exit();
}

// --- Hiển thị trang chi tiết đầy đủ (chỉ cho sản phẩm) ---
// Nếu không phải AJAX request và là sản phẩm, hiển thị trang chi tiết sản phẩm
if ($item_type === 'product') {
    $product = $item_data;

    // Lấy các sản phẩm liên quan (cùng danh mục)
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? LIMIT 4");
    $stmt->execute([$product['category_id'], $product['id']]);
    $related_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Bao gồm header
    include 'template.php';
    ?>

    <div class="container">
        <div class="product-detail">
            <div class="product-images">
                <div class="main-image">
                    <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                </div>
                <?php if (!empty($product['gallery'])): ?>
                <div class="thumbnail-images">
                    <?php 
                    $gallery = json_decode($product['gallery'], true);
                    foreach ($gallery as $image): 
                    ?>
                    <img src="<?php echo htmlspecialchars($image); ?>" alt="Thumbnail">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="product-info">
                <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                <div class="product-meta">
                    <span class="category">Danh mục: <?php echo htmlspecialchars($product['category_name'] ?? 'Không phân loại'); ?></span>
                    <span class="sku">Mã sản phẩm: <?php echo htmlspecialchars($product['sku'] ?? 'Đang cập nhật'); ?></span>
                </div>
                
                <div class="product-price">
                    <?php if (isset($product['sale_price']) && $product['sale_price']): ?>
                        <span class="sale-price"><?php echo number_format($product['sale_price']); ?>đ</span>
                        <span class="original-price"><?php echo number_format($product['price']); ?>đ</span>
                    <?php else: ?>
                        <span class="price"><?php echo number_format($product['price']); ?>đ</span>
                    <?php endif; ?>
                </div>

                <div class="product-description">
                    <?php echo nl2br(htmlspecialchars($product['description'] ?? 'Chưa có mô tả sản phẩm')); ?>
                </div>

                <div class="product-actions">
                    <div class="quantity-selector">
                        <button onclick="updateQuantity(-1)">-</button>
                        <input type="number" id="quantity" value="1" min="1">
                        <button onclick="updateQuantity(1)">+</button>
                    </div>
                    <button class="add-to-cart-btn" onclick="addToCart(<?php echo $product['id']; ?>, document.getElementById('quantity').value)">
                        <i class="fas fa-shopping-cart"></i> Thêm vào giỏ hàng
                    </button>
                    <button class="add-to-wishlist-btn" onclick="addToWishlist(<?php echo $product['id']; ?>)">
                        <i class="far fa-heart"></i> Yêu thích
                    </button>
                </div>
            </div>
        </div>

        <?php if (!empty($related_products)): ?>
        <div class="related-products">
            <h2>Sản phẩm liên quan</h2>
            <div class="product-grid">
                <?php foreach ($related_products as $related): ?>
                <div class="product-card">
                    <a href="product_detail.php?id=<?php echo $related['id']; ?>">
                        <img src="<?php echo htmlspecialchars($related['image']); ?>" alt="<?php echo htmlspecialchars($related['name']); ?>">
                        <h3><?php echo htmlspecialchars($related['name']); ?></h3>
                        <div class="price">
                            <?php if (isset($related['sale_price']) && $related['sale_price']): ?>
                                <span class="sale-price"><?php echo number_format($related['sale_price']); ?>đ</span>
                                <span class="original-price"><?php echo number_format($related['price']); ?>đ</span>
                            <?php else: ?>
                                <span class="price"><?php echo number_format($related['price']); ?>đ</span>
                            <?php endif; ?>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
    function updateQuantity(change) {
        const input = document.getElementById('quantity');
        const newValue = parseInt(input.value) + change;
        if (newValue >= 1) {
            input.value = newValue;
        }
    }

    function addToWishlist(productId) {
        fetch('add_to_wishlist.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${productId}`
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi thêm vào danh sách yêu thích');
        });
    }
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>
<?php } // End if $item_type === 'product' ?> 