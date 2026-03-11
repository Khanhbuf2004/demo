var gk_isXlsx = false;
var gk_xlsxFileLookup = {};
var gk_fileData = {};

function filledCell(cell) {
    return cell !== '' && cell != null;
}

function loadFileData(filename) {
    if (gk_isXlsx && gk_xlsxFileLookup[filename]) {
        try {
            var workbook = XLSX.read(gk_fileData[filename], { type: 'base64' });
            var firstSheetName = workbook.SheetNames[0];
            var worksheet = workbook.Sheets[firstSheetName];

            // Convert sheet to JSON to filter blank rows
            var jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1, blankrows: false, defval: '' });
            // Filter out blank rows (rows where all cells are empty, null, or undefined)
            var filteredData = jsonData.filter(row => row.some(filledCell));

            // Heuristic to find the header row by ignoring rows with fewer filled cells than the next row
            var headerRowIndex = filteredData.findIndex((row, index) =>
                row.filter(filledCell).length >= filteredData[index + 1]?.filter(filledCell).length
            );
            // Fallback
            if (headerRowIndex === -1 || headerRowIndex > 25) {
                headerRowIndex = 0;
            }

            // Convert filtered JSON back to CSV
            var csv = XLSX.utils.aoa_to_sheet(filteredData.slice(headerRowIndex)); // Create a new sheet from filtered array of arrays
            csv = XLSX.utils.sheet_to_csv(csv, { header: 1 });
            return csv;
        } catch (e) {
            console.error(e);
            return "";
        }
    }
    return gk_fileData[filename] || "";
}

document.querySelectorAll('.category').forEach(function(cat) {
    var dropdown = cat.querySelector('.dropdown');
    if (!dropdown) return;
    cat.addEventListener('mouseenter', function() {
        dropdown.style.display = 'flex';
    });
    cat.addEventListener('mouseleave', function() {
        dropdown.style.display = 'none';
    });
});

// Hiển thị ảnh động khi hover vào từng sản phẩm con trong tất cả dropdown danh mục

document.querySelectorAll('.category .dropdown').forEach(function(dropdown) {
    const previewImg = dropdown.querySelector('.dropdown-img-preview img');
    const links = dropdown.querySelectorAll('.sub-items a');
    if (!previewImg) return;
    links.forEach(link => {
        link.addEventListener('mouseenter', function() {
            const img = this.getAttribute('data-img');
            if (img && previewImg.src !== img) {
                previewImg.classList.add('fade-out');
                setTimeout(() => {
                    previewImg.src = img;
                    previewImg.classList.remove('fade-out');
                }, 200);
            }
        });
    });
});

// Product Modal Functionality
document.addEventListener('DOMContentLoaded', function() {
    const productLinks = document.querySelectorAll('.product-card');
    const modal = document.getElementById('productModal');
    const modalProductDetails = document.getElementById('modalProductDetails');
    const closeButton = document.querySelector('.close-button');

    // Add checks to ensure modal and closeButton exist
    if (modal && modalProductDetails && closeButton) {
        productLinks.forEach(link => {
            link.addEventListener('click', function(event) {
                event.preventDefault(); // Prevent default link behavior
                const productId = this.href.split('id=')[1]; // Extract product ID from href
                fetchDetails('product', productId);
            });
        });

        closeButton.addEventListener('click', function() {
            modal.style.display = 'none'; // Hide the modal
        });

        // Close modal when clicking outside of it
        window.addEventListener('click', function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        });

        // Function to format currency
        function formatCurrency(amount) {
            return new Intl.NumberFormat('vi-VN').format(amount);
        }

        // Handle product card clicks to open modal
        document.querySelectorAll('.product-card').forEach(card => {
            card.addEventListener('click', function(e) {
                // Find the closest ancestor with a valid href containing product_detail.php?id='
                const productLink = this.closest('a[href*="product_detail.php?id="]');

                if (productLink) {
                     e.preventDefault(); // Prevent default link behavior
                    const productId = productLink.href.split('id=')[1];
                    if (productId) {
                        fetchDetails('product', productId); // Sử dụng hàm fetchDetails chung
                    } else {
                        console.error('Product ID not found in link');
                    }
                }
            });
        });

        // Handle article item clicks to open modal
        document.querySelectorAll('.article-item-link').forEach(articleItem => {
            articleItem.addEventListener('click', function(e) {
                e.preventDefault(); // Prevent default link behavior
                const articleId = this.dataset.articleId; // Lấy ID từ data-article-id
                if (articleId) {
                    fetchDetails('article', articleId); // Sử dụng hàm fetchDetails chung
                } else {
                    console.error('Article ID not found in data attribute');
                }
            });
        });

        // Close modal when clicking on the close button
        closeButton.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        // Close modal when clicking outside of it
        window.addEventListener('click', (event) => {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        });

        // Chỉnh sửa hàm fetchDetails để lấy cả thông tin sản phẩm và bài viết
        function fetchDetails(type, id) {
            const modal = document.getElementById('productModal'); // Tái sử dụng modal product
            const modalContentArea = document.getElementById('modalProductDetails');

            // Clear previous details and show loading indicator
            modalContentArea.innerHTML = '<p>Đang tải...</p>';
            modal.style.display = 'block'; // Show modal while loading

            // Gửi yêu cầu AJAX đến product_detail.php với type và id
            fetch(`product_detail.php?id=${id}&type=${type}&ajax=1`)
                .then(response => response.json())
                .then(data => {
                    if (data) {
                        if (type === 'product') {
                            // Xây dựng nội dung cho sản phẩm (code hiện tại)
                             let priceHtml = '';
                            if (data.sale_price) {
                                priceHtml = `<span class="sale-price">${formatCurrency(data.sale_price)}đ</span><span class="original-price">${formatCurrency(data.price)}đ</span>`;
                            } else {
                                priceHtml = `<span class="price">${formatCurrency(data.price)}đ</span>`;
                            }

                            modalContentArea.innerHTML = `
                                <div class="modal-product-content">
                                    <div class="modal-product-images">
                                        <img src="${data.image}" alt="${data.name}">
                                    </div>
                                    <div class="modal-product-info">
                                        <h2>${data.name}</h2>
                                        <div class="modal-product-meta">
                                            <span class="category">Danh mục: ${data.category_name || 'Không phân loại'}</span>
                                            <span class="sku">Mã sản phẩm: ${data.sku || 'Đang cập nhật'}</span>
                                        </div>
                                        <div class="modal-product-price">${priceHtml}</div>
                                        <div class="modal-product-description">${data.description ? data.description.replace(/\n/g, '<br>') : 'Chưa có mô tả sản phẩm'}</div>

                                        <!-- Quantity Selector -->
                                        <div class="quantity-selector modal-quantity">
                                            <button class="quantity-btn" data-change="-1">-</button>
                                            <input type="number" class="quantity-input" value="1" min="1" readonly>
                                            <button class="quantity-btn" data-change="1">+</button>
                                        </div>

                                        <div class="modal-product-actions">
                                            <button class="add-to-cart-btn" data-product-id="${data.id}">
                                                <i class="fas fa-shopping-cart"></i> Thêm vào giỏ
                                            </button>
                                            <button class="wishlist-btn" data-product-id="${data.id}">
                                                <i class="far fa-heart"></i> Yêu thích
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            `;

                            // Add event listeners for quantity buttons
                            modalContentArea.querySelectorAll('.quantity-btn').forEach(button => {
                                button.addEventListener('click', function() {
                                    const change = parseInt(this.dataset.change);
                                    const quantityInput = modalContentArea.querySelector('.quantity-input');
                                    let newValue = parseInt(quantityInput.value) + change;
                                    if (newValue < 1) newValue = 1;
                                    quantityInput.value = newValue;
                                });
                            });

                            // Add event listener for Add to Cart button inside modal
                            modalContentArea.querySelector('.add-to-cart-btn').addEventListener('click', function() {
                                const productId = this.dataset.productId;
                                const quantityInput = modalContentArea.querySelector('.quantity-input');
                                const quantity = quantityInput.value;
                                addToCart(productId, quantity); // Call the existing addToCart function
                                // Optional: close modal after adding to cart
                                // modal.style.display = 'none';
                            });

                             // Add event listener for Add to Wishlist button inside modal
                             modalContentArea.querySelector('.wishlist-btn').addEventListener('click', function() {
                                const productId = this.dataset.productId;
                                addToWishlist(productId); // Call the existing addToWishlist function
                                // Optional: close modal after adding to wishlist or show a confirmation
                            });

                        } else if (type === 'article') {
                            // Xây dựng nội dung cho bài viết
                            modalContentArea.innerHTML = `
                                <div class="modal-article-content">
                                    <div class="modal-article-image">
                                        <img src="${data.image}" alt="${data.title}">
                                    </div>
                                    <div class="modal-article-info">
                                        <h2>${data.title}</h2>
                                        <div class="modal-article-meta">
                                            <span class="type">Loại: ${data.type || 'Không phân loại'}</span>
                                            <span class="date">Ngày đăng: ${data.created_at ? new Date(data.created_at).toLocaleDateString('vi-VN') : 'N/A'}</span>
                                        </div>
                                        <div class="modal-article-summary">${data.summary ? data.summary.replace(/\n/g, '<br>') : 'Chưa có tóm tắt'}</div>
                                        <div class="modal-article-content-full">${data.content ? data.content.replace(/\n/g, '<br>') : 'Chưa có nội dung chi tiết'}</div>
                                    </div>
                                </div>
                            `;
                             // Có thể thêm các nút hành động cho bài viết tại đây nếu cần (ví dụ: Chia sẻ)
                        }

                        modal.style.display = 'block'; // Show the modal after content is ready

                    } else {
                        // Có thể hiển thị thông báo lỗi
                        modalContentArea.innerHTML = '<p>Không tìm thấy thông tin.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching details:', error);
                    modalContentArea.innerHTML = '<p>Có lỗi xảy ra khi tải thông tin.</p>';
                });
        }

        // Keep the existing addToCart function if it's here or ensure it's loaded
        // This function should handle the AJAX request to add to cart
        // Ensure it accepts productId and quantity as arguments
    } else {
        // Optional: Log a message if modal elements are not found
        // console.log("Modal elements not found on this page.");
    }
}); 