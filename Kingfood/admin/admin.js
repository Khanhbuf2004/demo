document.addEventListener('DOMContentLoaded', function() {
    // Xử lý xóa sản phẩm
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Bạn có chắc chắn muốn xóa?')) {
                e.preventDefault();
            }
        });
    });
}); 