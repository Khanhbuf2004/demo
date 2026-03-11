// Quản lý giỏ hàng
const CART_STORAGE_KEY = 'vietmart-cart';

// Lấy giỏ hàng từ localStorage
function getCartFromStorage() {
    try {
        const stored = localStorage.getItem(CART_STORAGE_KEY);
        return stored ? JSON.parse(stored) : [];
    } catch (error) {
        console.error('Lỗi khi đọc giỏ hàng từ localStorage:', error);
        return [];
    }
}

// Lưu giỏ hàng vào localStorage
function saveCartToStorage(cartItems) {
    try {
        localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cartItems));
    } catch (error) {
        console.error('Lỗi khi lưu giỏ hàng vào localStorage:', error);
    }
}

// Thêm sản phẩm vào giỏ hàng
function addToCart(product, quantity = 1) {
    const cartItems = getCartFromStorage();
    
    // Kiểm tra xem sản phẩm đã có trong giỏ hàng chưa
    const existingItemIndex = cartItems.findIndex(item => item.id === product.id);
    
    if (existingItemIndex !== -1) {
        // Nếu sản phẩm đã có trong giỏ hàng, tăng số lượng
        cartItems[existingItemIndex].quantity += quantity;
    } else {
        // Nếu sản phẩm chưa có trong giỏ hàng, thêm mới
        cartItems.push({
            id: product.id,
            name: product.name,
            price: product.price,
            image: product.image,
            quantity: quantity
        });
    }
    
    // Lưu giỏ hàng đã cập nhật vào localStorage
    saveCartToStorage(cartItems);
    
    // Cập nhật UI
    updateCartUI();
    
    // Hiển thị thông báo
    showToast(`Đã thêm ${product.name} vào giỏ hàng`);
}

// Cập nhật số lượng sản phẩm trong giỏ hàng
function updateQuantity(productId, newQuantity) {
    const cartItems = getCartFromStorage();
    
    if (newQuantity <= 0) {
        // Nếu số lượng <= 0, xóa sản phẩm khỏi giỏ hàng
        removeFromCart(productId);
        return;
    }
    
    // Tìm và cập nhật số lượng sản phẩm
    const itemIndex = cartItems.findIndex(item => item.id === productId);
    if (itemIndex !== -1) {
        cartItems[itemIndex].quantity = newQuantity;
        saveCartToStorage(cartItems);
        updateCartUI();
    }
}

// Xóa sản phẩm khỏi giỏ hàng
function removeFromCart(productId) {
    const cartItems = getCartFromStorage();
    const updatedCart = cartItems.filter(item => item.id !== productId);
    
    saveCartToStorage(updatedCart);
    updateCartUI();
}

// Xóa toàn bộ giỏ hàng
function clearCart() {
    saveCartToStorage([]);
    updateCartUI();
}

// Tính tổng tiền trong giỏ hàng
function calculateTotal() {
    const cartItems = getCartFromStorage();
    return cartItems.reduce((total, item) => total + (item.price * item.quantity), 0);
}

// Format giá tiền
function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN').format(price) + 'đ';
}

// Hiển thị thông báo (toast message)
function showToast(message) {
    // Kiểm tra xem đã có toast trên trang chưa
    let toast = document.querySelector('.toast');
    
    // Nếu chưa có, tạo mới
    if (!toast) {
        toast = document.createElement('div');
        toast.className = 'toast';
        document.body.appendChild(toast);
        
        // Thêm CSS cho toast
        toast.style.position = 'fixed';
        toast.style.bottom = '20px';
        toast.style.right = '20px';
        toast.style.backgroundColor = 'rgba(34, 197, 94, 0.9)';
        toast.style.color = 'white';
        toast.style.padding = '12px 20px';
        toast.style.borderRadius = '8px';
        toast.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.15)';
        toast.style.zIndex = '9999';
        toast.style.transition = 'opacity 0.3s ease-in-out';
        toast.style.opacity = '0';
        toast.style.maxWidth = '300px';
    }
    
    // Cập nhật nội dung và hiển thị toast
    toast.textContent = message;
    toast.style.opacity = '1';
    
    // Tự động ẩn toast sau 3 giây
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}

// Cập nhật giao diện giỏ hàng
function updateCartUI() {
    const cartItems = getCartFromStorage();
    const cartCount = document.getElementById('cart-count');
    const cartItemsContainer = document.getElementById('cart-items');
    const cartFooter = document.getElementById('cart-footer');
    const cartSidebarCount = document.getElementById('cart-sidebar-count');
    const cartTotal = document.getElementById('cart-total');
    
    // Cập nhật số lượng sản phẩm trên icon giỏ hàng
    if (cartCount) {
        const totalItems = cartItems.reduce((sum, item) => sum + item.quantity, 0);
        cartCount.textContent = totalItems;
        
        // Ẩn badge nếu không có sản phẩm
        if (totalItems === 0) {
            cartCount.style.display = 'none';
        } else {
            cartCount.style.display = 'flex';
        }
    }
    
    // Cập nhật số lượng sản phẩm trên sidebar giỏ hàng
    if (cartSidebarCount) {
        cartSidebarCount.textContent = `${cartItems.length} sản phẩm`;
    }
    
    // Cập nhật danh sách sản phẩm trong giỏ hàng
    if (cartItemsContainer) {
        if (cartItems.length === 0) {
            // Hiển thị thông báo giỏ hàng trống
            cartItemsContainer.innerHTML = `
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <p>Giỏ hàng của bạn đang trống</p>
                </div>
            `;
            
            // Ẩn footer giỏ hàng
            if (cartFooter) {
                cartFooter.style.display = 'none';
            }
        } else {
            // Hiển thị danh sách sản phẩm
            cartItemsContainer.innerHTML = '';
            
            cartItems.forEach(item => {
                const cartItemElement = document.createElement('div');
                cartItemElement.className = 'cart-item';
                cartItemElement.innerHTML = `
                    <div class="cart-item-image">
                        <img src="${item.image}" alt="${item.name}">
                    </div>
                    <div class="cart-item-details">
                        <div class="cart-item-name">${item.name}</div>
                        <div class="cart-item-price">${formatPrice(item.price)}</div>
                        <div class="cart-item-quantity">
                            <button class="quantity-btn minus-btn" data-id="${item.id}">-</button>
                            <input type="text" class="quantity-input" value="${item.quantity}" readonly>
                            <button class="quantity-btn plus-btn" data-id="${item.id}">+</button>
                            <button class="remove-item" data-id="${item.id}">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                `;
                
                cartItemsContainer.appendChild(cartItemElement);
                
                // Thêm event listener cho các nút
                const minusBtn = cartItemElement.querySelector('.minus-btn');
                const plusBtn = cartItemElement.querySelector('.plus-btn');
                const removeBtn = cartItemElement.querySelector('.remove-item');
                
                minusBtn.addEventListener('click', () => {
                    updateQuantity(item.id, item.quantity - 1);
                });
                
                plusBtn.addEventListener('click', () => {
                    updateQuantity(item.id, item.quantity + 1);
                });
                
                removeBtn.addEventListener('click', () => {
                    removeFromCart(item.id);
                });
            });
            
            // Hiển thị footer giỏ hàng
            if (cartFooter) {
                cartFooter.style.display = 'block';
            }
        }
    }
    
    // Cập nhật tổng tiền
    if (cartTotal) {
        cartTotal.textContent = formatPrice(calculateTotal());
    }
}