<?php
// Lấy tất cả danh mục sản phẩm
$stmt = $pdo->query("SELECT * FROM categories");
$all_categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hàm để xây dựng cây danh mục
function buildCategoryTree($categories, $parent_id = NULL) {
    $tree = array();
    foreach ($categories as $category) {
        if ($category['parent_id'] == $parent_id) {
            $children = buildCategoryTree($categories, $category['id']);
            if ($children) {
                $category['children'] = $children;
            }
            $tree[] = $category;
        }
    }
    return $tree;
}

$categories = buildCategoryTree($all_categories);

// Tính tổng số sản phẩm trong giỏ hàng
$cart_total = 0;
if (isset($_SESSION['cart'])) {
    $cart_total = array_sum(array_column($_SESSION['cart'], 'quantity'));
}
?>
<header>
    <div class="top-bar">
        <div class="contact">
            <span><i class="fas fa-phone"></i>Hotline: 0123456789</span>
            <span><i class="fas fa-truck"></i>Miễn phí vận chuyển từ 500K</span>
        </div>
        <div class="support">
            <a href="track_order.php"><i class="fas fa-box"></i>Theo dõi đơn hàng</a>
            <a href="support.php"><i class="fas fa-question-circle"></i>Hỗ trợ</a>
        </div>
    </div>
    <div class="main-nav">
        <a href="index.php" class="logo">Kingfood</a>
        <div class="search-bar">
            <input type="text" placeholder="Tìm kiếm sản phẩm...">
            <i class="fas fa-search"></i>
        </div>
        <div class="nav-icons">
            <a href="<?php echo isset($_SESSION['user_id']) ? 'user_profile.php' : 'login.php'; ?>"><i class="fas fa-user"></i>Tài khoản</a>
            <a href="wishlist.php"><i class="far fa-heart"></i>Yêu thích</a>
            <a href="cart.php" class="cart-link">
                <i class="fas fa-shopping-cart"></i>Giỏ hàng
                <?php if ($cart_total > 0): ?>
                    <span class="cart-count"><?php echo $cart_total; ?></span>
                <?php endif; ?>
            </a>
        </div>
    </div>
</header>

<div class="categories">
    <?php foreach ($categories as $category): ?>
        <div class="category">
            <a href="products.php?category=<?php echo $category['id']; ?>">
                <?php echo htmlspecialchars($category['name']); ?>
            </a>
            <i class="fas fa-chevron-down"></i>
            <div class="dropdown">
                <div class="sub-items">
                    <?php
                    // Fetch products for the current category
                    $stmt_products = $pdo->prepare("SELECT * FROM products WHERE category_id = ?");
                    $stmt_products->execute([$category['id']]);
                    $category_products = $stmt_products->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <?php if (!empty($category_products)): ?>
                        <?php foreach ($category_products as $dropdown_product): ?>
                            <a href="product_detail.php?id=<?php echo $dropdown_product['id']; ?>" data-img="<?php echo htmlspecialchars($dropdown_product['image']); ?>">
                                <?php echo htmlspecialchars($dropdown_product['name']); ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="dropdown-img-preview">
                    <img src="<?php echo htmlspecialchars($category['image']); ?>" alt="Preview">
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
// Hàm thêm vào giỏ hàng
function addToCart(productId, quantity = 1) {
    fetch('add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}&quantity=${quantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Cập nhật số lượng sản phẩm trong giỏ hàng
            const cartCount = document.querySelector('.cart-count');
            if (cartCount) {
                cartCount.textContent = data.total_items;
            } else {
                const cartLink = document.querySelector('.cart-link');
                cartLink.innerHTML += `<span class="cart-count">${data.total_items}</span>`;
            }
            
            // Hiển thị thông báo thành công
            alert(data.message);
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra khi thêm vào giỏ hàng');
    });
}
</script>

<script src="js/main.js"></script>