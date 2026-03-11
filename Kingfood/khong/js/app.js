// Khi trang web đã tải xong
document.addEventListener('DOMContentLoaded', function() {
    // Khởi tạo giỏ hàng
    updateCartUI();
    
    // Hiển thị danh mục
    renderCategories();
    
    // Hiển thị các sản phẩm
    renderQuickCategories();
    renderFlashSaleProducts();
    renderNewProducts();
    renderBestSellerProducts();
    renderRiceProducts();
    
    // Khởi tạo countdown cho flash sale
    initCountdown();
    
    // Thiết lập các sự kiện cho giỏ hàng
    setupCartEvents();
    
    // Thiết lập sự kiện tìm kiếm
    setupSearchEvent();
});

// Hiển thị danh mục trên thanh điều hướng
function renderCategories() {
    const categoryList = document.getElementById('category-list');
    if (!categoryList) return;
    
    let html = '';
    categories.forEach(category => {
        html += `
            <li>
                <a href="#${category.slug}">
                    <i class="${category.icon}"></i>
                    <span>${category.name}</span>
                </a>
            </li>
        `;
    });
    
    categoryList.innerHTML = html;
}

// Hiển thị danh mục quick categories
function renderQuickCategories() {
    const quickCategoriesContainer = document.getElementById('quick-categories');
    if (!quickCategoriesContainer) return;
    
    let html = '';
    
    // Lấy 7 danh mục đầu tiên
    const displayCategories = categories.slice(0, 7);
    
    displayCategories.forEach(category => {
        html += `
            <div class="category-card">
                <div class="category-icon">
                    <i class="${category.icon}"></i>
                </div>
                <span>${category.name}</span>
            </div>
        `;
    });
    
    // Thêm card "Xem tất cả"
    html += `
        <div class="category-card">
            <div class="category-icon">
                <i class="fas fa-th-large"></i>
            </div>
            <span>Xem tất cả</span>
        </div>
    `;
    
    quickCategoriesContainer.innerHTML = html;
}

// Hàm tạo HTML cho product card
function createProductCardHTML(product, compact = false) {
    // Xác định loại badge
    let badgeHTML = '';
    if (product.isFlashSale) {
        badgeHTML = `<span class="product-badge badge-sale">-${product.discountPercent}%</span>`;
    } else if (product.isNew) {
        badgeHTML = `<span class="product-badge badge-new">Mới</span>`;
    } else if (product.isBestSeller) {
        badgeHTML = `<span class="product-badge badge-best">Bán chạy</span>`;
    }
    
    // Tạo HTML cho rating
    let ratingHTML = '';
    if (!compact) {
        ratingHTML = '<div class="rating">';
        for (let i = 1; i <= 5; i++) {
            ratingHTML += `<i class="fas fa-star${i <= product.rating ? '' : ' text-gray-300'}"></i>`;
        }
        ratingHTML += '</div>';
    }
    
    // HTML cho giá gốc (nếu có)
    const originalPriceHTML = product.originalPrice 
        ? `<span class="original-price">${formatPrice(product.originalPrice)}</span>` 
        : '';
    
    // HTML cho số lượng đã bán
    const soldCountHTML = `<span>Đã bán ${product.soldCount}</span>`;
    
    return `
        <div class="product-card" data-id="${product.id}">
            <div class="product-image">
                <img src="${product.image}" alt="${product.name}">
            </div>
            <div class="product-info">
                <h3 class="product-name">${product.name}</h3>
                <div class="price-wrapper">
                    <span class="current-price">${formatPrice(product.price)}</span>
                    ${originalPriceHTML}
                </div>
                <div class="product-meta">
                    ${badgeHTML}
                    ${compact ? soldCountHTML : ratingHTML}
                </div>
                ${!compact ? `<div class="sold-count">${soldCountHTML}</div>` : ''}
                <button class="add-to-cart">Thêm vào giỏ</button>
            </div>
        </div>
    `;
}

// Hiển thị sản phẩm Flash Sale
function renderFlashSaleProducts() {
    const container = document.getElementById('flash-sale-products');
    if (!container) return;
    
    // Lọc sản phẩm flash sale
    const flashSaleProducts = products.filter(product => product.isFlashSale);
    
    let html = '';
    flashSaleProducts.forEach(product => {
        html += createProductCardHTML(product, true);
    });
    
    container.innerHTML = html;
    
    // Thêm sự kiện cho các nút "Thêm vào giỏ"
    addToCartEvents(container);
}

// Hiển thị sản phẩm mới
function renderNewProducts() {
    const container = document.getElementById('new-products');
    if (!container) return;
    
    // Lọc sản phẩm mới
    const newProducts = products.filter(product => product.isNew);
    
    let html = '';
    newProducts.forEach(product => {
        html += createProductCardHTML(product);
    });
    
    container.innerHTML = html;
    
    // Thêm sự kiện cho các nút "Thêm vào giỏ"
    addToCartEvents(container);
}

// Hiển thị sản phẩm bán chạy
function renderBestSellerProducts() {
    const container = document.getElementById('best-seller-products');
    if (!container) return;
    
    // Lọc sản phẩm bán chạy
    const bestSellers = products.filter(product => product.isBestSeller);
    
    let html = '';
    bestSellers.forEach(product => {
        html += createProductCardHTML(product);
    });
    
    container.innerHTML = html;
    
    // Thêm sự kiện cho các nút "Thêm vào giỏ"
    addToCartEvents(container);
}

// Hiển thị sản phẩm gạo
function renderRiceProducts() {
    const container = document.getElementById('rice-products');
    if (!container) return;
    
    // Lọc sản phẩm thuộc danh mục gạo (categoryId = 1)
    const riceProducts = products.filter(product => product.categoryId === 1);
    
    let html = '';
    riceProducts.forEach(product => {
        html += createProductCardHTML(product);
    });
    
    container.innerHTML = html;
    
    // Thêm sự kiện cho các nút "Thêm vào giỏ"
    addToCartEvents(container);
}

// Thêm sự kiện cho các nút "Thêm vào giỏ"
function addToCartEvents(container) {
    const addToCartButtons = container.querySelectorAll('.add-to-cart');
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            
            // Lấy ID sản phẩm từ thẻ cha
            const productCard = this.closest('.product-card');
            const productId = parseInt(productCard.dataset.id);
            
            // Tìm sản phẩm trong danh sách
            const product = products.find(p => p.id === productId);
            
            if (product) {
                // Thêm sản phẩm vào giỏ hàng
                addToCart(product);
            }
        });
    });
}

// Khởi tạo countdown cho flash sale
function initCountdown() {
    const countdownEl = document.getElementById('countdown-timer');
    if (!countdownEl) return;
    
    // Giả lập thời gian còn lại (2 giờ 15 phút 30 giây)
    let hours = 2;
    let minutes = 15;
    let seconds = 30;
    
    // Cập nhật countdown mỗi giây
    const countdownInterval = setInterval(() => {
        seconds--;
        
        if (seconds < 0) {
            seconds = 59;
            minutes--;
            
            if (minutes < 0) {
                minutes = 59;
                hours--;
                
                if (hours < 0) {
                    // Kết thúc flash sale
                    clearInterval(countdownInterval);
                    countdownEl.parentElement.textContent = 'Flash Sale đã kết thúc';
                    return;
                }
            }
        }
        
        // Cập nhật hiển thị
        countdownEl.textContent = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    }, 1000);
}

// Thiết lập các sự kiện cho giỏ hàng
function setupCartEvents() {
    const cartBtn = document.getElementById('cart-btn');
    const cartSidebar = document.getElementById('cart-sidebar');
    const closeCartBtn = document.getElementById('close-cart');
    const overlay = document.getElementById('overlay');
    
    if (cartBtn && cartSidebar && closeCartBtn && overlay) {
        // Mở giỏ hàng khi nhấp vào nút giỏ hàng
        cartBtn.addEventListener('click', function(event) {
            event.preventDefault();
            cartSidebar.classList.add('active');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden'; // Ngăn cuộn trang
        });
        
        // Đóng giỏ hàng khi nhấp vào nút đóng
        closeCartBtn.addEventListener('click', function() {
            cartSidebar.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = ''; // Cho phép cuộn trang
        });
        
        // Đóng giỏ hàng khi nhấp vào overlay
        overlay.addEventListener('click', function() {
            cartSidebar.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = ''; // Cho phép cuộn trang
        });
    }
}

// Thiết lập sự kiện tìm kiếm
function setupSearchEvent() {
    const searchForm = document.getElementById('search-form');
    const searchInput = document.getElementById('search-input');
    
    if (searchForm && searchInput) {
        searchForm.addEventListener('submit', function(event) {
            event.preventDefault();
            
            const searchTerm = searchInput.value.trim().toLowerCase();
            if (searchTerm === '') return;
            
            // Tìm kiếm sản phẩm
            const searchResults = products.filter(product => 
                product.name.toLowerCase().includes(searchTerm) || 
                (product.description && product.description.toLowerCase().includes(searchTerm))
            );
            
            // Hiển thị kết quả tìm kiếm
            alert(`Tìm thấy ${searchResults.length} sản phẩm phù hợp với "${searchTerm}"`);
            
            // Xóa giá trị tìm kiếm
            searchInput.value = '';
        });
    }
}