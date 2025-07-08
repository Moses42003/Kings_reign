document.addEventListener('DOMContentLoaded', function() {
        // Nav logic
        document.querySelectorAll('.nav-link').forEach(function(link) {
            link.addEventListener('click', function() {
                document.getElementById('main-nav').classList.remove('open');
            });
        });
        // Product view logic
        window.openProductView = function(btn) {
            var card = btn.closest('.product-card');
            document.getElementById('pv-img').src = card.getAttribute('data-img');
            document.getElementById('pv-name').textContent = card.getAttribute('data-name');
            document.getElementById('pv-desc').textContent = card.getAttribute('data-desc');
            document.getElementById('add-to-cart-btn').setAttribute('data-id', card.getAttribute('data-id'));
            document.getElementById('product-view-panel').style.display = 'flex';
        };
        window.closeProductView = function() {
            document.getElementById('product-view-panel').style.display = 'none';
        };
        document.getElementById('add-to-cart-btn').onclick = function() {
            var productId = this.getAttribute('data-id');
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'product_id=' + encodeURIComponent(productId)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Added to cart!');
                } else {
                    alert('Error: ' + data.message);
                }
                window.closeProductView();
            })
            .catch(() => {
                alert('Network error.');
                window.closeProductView();
            });
        };
        // Cart panel logic
        window.openCart = function() {
            document.getElementById('cart-panel').style.display = 'flex';
            // AJAX to load cart items
            fetch('get_cart.php')
                .then(response => response.json())
                .then(data => {
                    var cartItemsDiv = document.getElementById('cart-items');
                    cartItemsDiv.innerHTML = '';
                    if (data.success && data.cart.length > 0) {
                        var html = '<div class="cart-flex">';
                        let total = 0;
                        data.cart.forEach(function(item) {
                            let itemTotal = item.price * item.quantity;
                            total += itemTotal;
                            html += `
                            <div class="cart-item-card">
                                <img src="${item.file_path}" alt="${item.name}" />
                                <div class="cart-item-info">
                                    <h4>${item.name}</h4>
                                    <p>Price: GH ${item.price}</p>
                                    <label>Qty: <input type="number" min="1" value="${item.quantity}" data-id="${item.product_id}" class="cart-qty-input"></label>
                                    <button class="cart-remove-btn" data-id="${item.product_id}" style="margin-top:8px;background:#e53935;color:#fff;border:none;padding:6px 16px;border-radius:6px;cursor:pointer;">Remove</button>
                                    <p class="item-total">Item Total: GH <span>${itemTotal}</span></p>
                                </div>
                            </div>`;
                        });
                        html += '</div>';
                        html += `<div class="cart-total-section" style="text-align:center;margin-top:18px;">
                            <h3>Total: GH <span id="cart-total">${total}</span></h3>
                            <button class="buy-btn" style="margin-top:12px;padding:10px 32px;background:#1a237e;color:#fff;border:none;border-radius:8px;font-size:1.1rem;">Buy</button>
                        </div>`;
                        cartItemsDiv.innerHTML = html;
                        // Add event listeners for quantity inputs
                        document.querySelectorAll('.cart-qty-input').forEach(function(input) {
                            input.addEventListener('change', function() {
                                var newQty = this.value;
                                var pid = this.getAttribute('data-id');
                                fetch('update_cart.php', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                    body: 'product_id=' + encodeURIComponent(pid) + '&quantity=' + encodeURIComponent(newQty)
                                })
                                .then(() => window.openCart()); // reload cart
                            });
                        });
                        // Add event listeners for remove buttons
                        document.querySelectorAll('.cart-remove-btn').forEach(function(btn) {
                            btn.addEventListener('click', function() {
                                var pid = this.getAttribute('data-id');
                                fetch('update_cart.php', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                    body: 'product_id=' + encodeURIComponent(pid) + '&quantity=0'
                                })
                                .then(() => window.openCart()); // reload cart
                            });
                        });
                    } else {
                        cartItemsDiv.innerHTML = '<p style="text-align:center;">Your cart is empty.</p>';
                    }
                });
        };
        window.closeCart = function() {
            document.getElementById('cart-panel').style.display = 'none';
        };
        var cartBtn = document.querySelector('.cart-btn');
        if (cartBtn) cartBtn.onclick = window.openCart;
    });
    function openNav() {
        document.getElementById('main-nav').classList.add('open');
    }
    function closeNav(e) {
        e.preventDefault();
        document.getElementById('main-nav').classList.remove('open');
    }
    // Optional: close nav when clicking outside
    window.addEventListener('click', function(e) {
        var nav = document.getElementById('main-nav');
        var btn = document.querySelector('.nav-btn');
        if (nav.classList.contains('open') && !nav.contains(e.target) && e.target !== btn) {
            nav.classList.remove('open');
        }
    });


