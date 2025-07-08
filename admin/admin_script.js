// admin_script.js
// Handles admin panel AJAX actions (e.g., product delete)
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.admin-delete-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            if (!confirm('Are you sure you want to delete this product?')) return;
            var card = btn.closest('.admin-table-card');
            var id = btn.getAttribute('data-id');
            var type = btn.getAttribute('data-type');
            fetch('delete_product.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + encodeURIComponent(id) + '&type=' + encodeURIComponent(type)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    card.remove();
                } else {
                    alert('Delete failed: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(() => alert('Network error.'));
        });
    });
});
