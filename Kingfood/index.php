<?php
require_once 'config.php';

// Lấy danh mục sản phẩm
$stmt = $pdo->query("SELECT * FROM categories WHERE parent_id IS NULL");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy sản phẩm nổi bật
$stmt = $pdo->query("SELECT * FROM products WHERE is_featured = 1 LIMIT 8");
$featured_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy sản phẩm mới
$stmt = $pdo->query("SELECT * FROM products WHERE is_new = 1 LIMIT 8");
$new_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy sản phẩm flash sale
$stmt = $pdo->query("SELECT * FROM products WHERE is_flash_sale = 1 LIMIT 8");
$flash_sale_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy tin tức đã được phê duyệt
$stmt = $pdo->prepare("SELECT * FROM news WHERE type = 'news' AND status = 'approved' LIMIT 4");
$stmt->execute();
$news = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy công thức đã được phê duyệt
$stmt = $pdo->prepare("SELECT * FROM news WHERE type = 'recipe' AND status = 'approved' LIMIT 4");
$stmt->execute();
$recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy banner
$stmt = $pdo->query("SELECT * FROM banners WHERE position = 'main' LIMIT 1");
$main_banner = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT * FROM banners WHERE position = 'small' LIMIT 3");
$small_banners = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Bao gồm template (sẽ include header và footer)
include 'template.php';
?>

<?php /* Nội dung chính của trang index */ ?>

<!-- Banner chính -->
<?php if ($main_banner): ?>
    <div class="main-banner">
        <img src="<?php echo htmlspecialchars($main_banner['image']); ?>" alt="Banner Kingfood">
    </div>
<?php endif; ?>

<!-- Sản phẩm flash sale -->
<div class="product-section">
    <div class="flash-sale">
        <h2><i class="fas fa-bolt"></i> Flash Sale</h2>
        <div class="product-grid">
            <?php foreach ($flash_sale_products as $product): ?>
                <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="product-card">
                    <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    <div class="price"><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</div>
                    <div class="new">Mới</div>
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
                    <div class="add-to-cart" onclick="addToCart(<?php echo $product['id']; ?>)">Thêm vào giỏ</div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Banner nhỏ -->
<?php if ($small_banners): ?>
    <div class="ad-banner-between small-banner">
        <?php foreach ($small_banners as $banner): ?>
            <img src="<?php echo htmlspecialchars($banner['image']); ?>" alt="Quảng cáo sản phẩm">
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Sản phẩm mới -->
<div class="product-section">
    <h2><i class="fas fa-box-open"></i> Sản phẩm mới</h2>
    <div class="product-grid">
        <?php foreach ($new_products as $product): ?>
            <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="product-card">
                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                <div class="price"><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</div>
                <div class="new">Mới</div>
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
                <div class="add-to-cart" onclick="addToCart(<?php echo $product['id']; ?>)">Thêm vào giỏ</div>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Sản phẩm nổi bật -->
<div class="product-section">
    <h2><i class="fas fa-rice"></i> Sản phẩm nổi bật</h2>
    <div class="product-grid">
        <?php foreach ($featured_products as $product): ?>
            <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="product-card">
                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                <div class="price"><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</div>
                <div class="new">Mới</div>
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
                <div class="add-to-cart" onclick="addToCart(<?php echo $product['id']; ?>)">Thêm vào giỏ</div>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Tin tức và Công thức -->
<div class="articles-section articles-2col">
    <div class="articles-col articles-recipes">
        <h2><i class="fas fa-coffee"></i> Công thức</h2>
        <?php foreach ($recipes as $item): ?>
            <div class="article-item article-item-link" data-article-id="<?php echo $item['id']; ?>">
                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                <div class="article-info">
                    <div class="article-date"><?php echo isset($item['created_at']) ? date('D d/m/Y', strtotime($item['created_at'])) : 'N/A'; ?></div>
                    <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                    <p><?php echo htmlspecialchars($item['summary']); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
        <a href="recipes.php" class="view-all-articles">Xem tất cả →</a>
    </div>
    <div class="articles-col articles-news">
        <h2><i class="fas fa-newspaper"></i> Tin tức</h2>
        <?php foreach ($news as $item): ?>
            <div class="article-item article-item-link" data-article-id="<?php echo $item['id']; ?>">
                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                <div class="article-info">
                    <div class="article-date"><?php echo isset($item['created_at']) ? date('D d/m/Y', strtotime($item['created_at'])) : 'N/A'; ?></div>
                    <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                    <p><?php echo htmlspecialchars($item['summary']); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
        <a href="news.php" class="view-all-articles">Xem tất cả →</a>
    </div>
</div>

<!-- Chia sẻ thông tin -->
<div class="share-status-section">
    <h2><i class="fas fa-share-alt"></i> Chia sẻ thông tin, công thức & kiến thức bổ ích</h2>
    <div class="share-status-content">
        <p>Hãy chia sẻ những công thức, mẹo vặt, hoặc kiến thức hữu ích về sản phẩm, nấu ăn... để cộng đồng cùng học hỏi và phát triển!</p>
        <form action="submit_share.php" method="post">
            <textarea name="share_content" placeholder="Chia sẻ của bạn..." rows="3"></textarea>
            <button type="submit" class="share-btn">Chia sẻ ngay</button>
        </form>
    </div>
</div>
<?php include 'footer.php'; ?>

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
</body>
</html>