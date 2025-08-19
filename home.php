<?php
include('db.php');
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$pquery = "SELECT name, price, description, file_path, stock FROM phones";
$result = mysqli_query($conn, $pquery);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kings Reign</title>
    <!-- link to css file -->
    <link rel="stylesheet" href="styles/style.css">
    <!-- icon -->
    <link rel="shortcut icon" href="images/logos/logo-black.jpg" type="image/x-icon">
</head>
<body>

    <!-- header -->
    <header>
        <img src="images/logos/logo-black.jpg" alt="Kings Reign Logo" class="header-logo">
        <!-- navigation icon on mobile -->
        <span class="nav-btn" onclick="openNav()">≡</span>
        <!-- navigation -->
        <nav id="main-nav">
            <button class="close-btn" onclick="closeNav(event)">&times;</button>
            <div class="mobile-nav-header">
                <img src="images/logos/logo-black.jpg" alt="Kings Reign Logo" class="mobile-nav-logo">
                <div class="mobile-user-info">
                    <span class="mobile-user-name">
                        <?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'User'; ?>
                    </span>
                    <span class="mobile-user-email">
                        <?php echo isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : ''; ?>
                    </span>
                </div>
            </div>
            <li><a href="#home-section" class="nav-link active" onclick="showSection(event, 'home-section')">Home</a></li>
            <li><a href="#categories-section" class="nav-link" onclick="showSection(event, 'categories-section')">Categories</a></li>
            <li><a href="#contact-section" class="nav-link" onclick="showSection(event, 'contact-section')">Contact Us</a></li>
            <li><a href="#account-section" class="nav-link" onclick="showSection(event, 'account-section')">Account</a></li>
            <li><a href="user_orders.php" class="nav-link">My Orders</a></li>
        </nav>
        <div id="account-box" class="account-box">
            <button class="close-account-btn" onclick="closeAccountBox(event)">&times;</button>
            <div class="account-info-flex">
                <img src="images/logos/logo-black.jpg" alt="Profile" class="account-profile-pic">
                <div class="account-details">
                    <div class="account-user-name">
                        <?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'User'; ?>
                    </div>
                    <div class="account-user-email">
                        <?php echo isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : ''; ?>
                    </div>
                </div>
            </div>
            <div class="account-actions">
                <a href="update_account.php" class="account-action-btn">Update Account</a>
                <a href="user_orders.php" class="account-action-btn">My Orders</a>
                <a href="user_messages.php" class="account-action-btn">My Messages</a>
                <a href="logout.php" class="account-action-btn logout">Logout</a>
            </div>
        </div>
        <!-- search -->
        <input type="search" class="search" placeholder="Search Product...">
        <!-- cart -->
        <span class="cart-btn">🛒</span>
    </header>


    <!-- main section -->
    <section id="home-section" class="slide-section active-section">
        <!-- tag to indicate new added products -->
        <div class="new new-added-header">
            <h3>New Added</h3>
        </div>

        <div class="product-slider">
            <?php
            // Fetch the 10 most recently added products from both phones and clothes
            $newPhones = mysqli_query($conn, "SELECT name, price, description, file_path, stock, 'phones' as type FROM phones ORDER BY id DESC LIMIT 10");
            $newClothes = mysqli_query($conn, "SELECT name, price, description, file_path, stock, 'clothes' as type FROM clothes ORDER BY id DESC LIMIT 10");
            $allNew = [];
            while ($row = mysqli_fetch_assoc($newPhones)) $allNew[$row['name'].'|phones'] = $row;
            while ($row = mysqli_fetch_assoc($newClothes)) $allNew[$row['name'].'|clothes'] = $row;
            // Only keep the 10 most recent, no duplicates
            $allNew = array_slice($allNew, 0, 10);
            foreach ($allNew as $row) {
                $outOfStock = ($row['stock'] <= 0);
                echo '<div class="product-card" data-name="'.htmlspecialchars($row['name']).'" data-img="'.htmlspecialchars($row['file_path']).'" data-desc="'.htmlspecialchars($row['description']).'" data-id="'.htmlspecialchars($row['name']).'">';
                echo '<span class="new-tag">New</span>';
                if ($outOfStock) {
                    echo '<span class="out-of-stock">Out of Stock</span>';
                }
                echo '<img src="'. $row['file_path'] .'" alt="'. $row['name'] .'">';
                echo '<div class="product-info">';
                echo '<p class="product-name">'. $row['name'] .'</p>';
                echo '<p class="product-price">GH '. $row['price'] .'</p>';
                if ($outOfStock) {
                    echo '<button class="view-btn" disabled>View</button>';
                } else {
                    echo '<button class="view-btn" onclick="openProductView(this)">View</button>';
                }
                echo '</div></div>';
            }
            ?>
        </div>

        <!-- products by category -->
        <div class="new category-header">
            <h3>Product Categories</h3>
        </div>
        <div class="category-slider">
            <div class="category-card">
                <img src="images/logos/logo-blue.jpg" alt="Phones">
                <span>Phones</span>
            </div>
            <div class="category-card">
                <img src="images/logos/logo-white.jpg" alt="Clothings">
                <span>Clothings</span>
            </div>
            <!-- Add more category-card divs as needed -->
        </div>
        <div class="new best-deals-header">
            <h3>Best Phone Deals</h3>
        </div>
        <div class="deals-slider">
            <?php
            // Re-query for best phone deals (or reuse $result if appropriate)
            $deals_query = "SELECT name, price, description, file_path, stock FROM phones LIMIT 10";
            $deals_result = mysqli_query($conn, $deals_query);
            while ($row = mysqli_fetch_array($deals_result)) {
                $outOfStock = ($row['stock'] <= 0);
                echo '
                <div class="product-card" data-name="'.htmlspecialchars($row['name']).'" data-img="'.htmlspecialchars($row['file_path']).'" data-desc="'.htmlspecialchars($row['description']).'" data-id="'.htmlspecialchars($row['name']).'">';
                if ($outOfStock) {
                    echo '<span class="out-of-stock">Out of Stock</span>';
                }
                echo '<img src="'. $row['file_path'] .'" alt="'. $row['name'] .'">
                <div class="product-info">
                <p class="product-name">'. $row['name'] .'</p>
                <p class="product-price">GH '. $row['price'] .'</p>';
                if ($outOfStock) {
                    echo '<button class="view-btn" disabled>View</button>';
                } else {
                    echo '<button class="view-btn" onclick="openProductView(this)">View</button>';
                }
                echo '</div></div>';
            }
            ?>
        </div>
        <div class="new best-cloth-deals-header">
            <h3>Best Clothing Deals</h3>
        </div>
        <div class="deals-slider">
            <?php
            $cloth_deals_query = "SELECT name, price, description, file_path, stock FROM clothes LIMIT 10";
            $cloth_deals_result = mysqli_query($conn, $cloth_deals_query);
            while ($row = mysqli_fetch_array($cloth_deals_result)) {
                $outOfStock = ($row['stock'] <= 0);
                echo '
                <div class="product-card" data-name="'.htmlspecialchars($row['name']).'" data-img="'.htmlspecialchars($row['file_path']).'" data-desc="'.htmlspecialchars($row['description']).'" data-id="'.htmlspecialchars($row['name']).'">';
                if ($outOfStock) {
                    echo '<span class="out-of-stock">Out of Stock</span>';
                }
                echo '<img src="'. $row['file_path'] .'" alt="'. $row['name'] .'" width="100%" height="100%">
                <div class="product-info">
                <p class="product-name">'. $row['name'] .'</p>
                <p class="product-price">GH '. $row['price'] .'</p>';
                if ($outOfStock) {
                    echo '<button class="view-btn" disabled>View</button>';
                } else {
                    echo '<button class="view-btn" onclick="openProductView(this)">View</button>';
                }
                echo '</div></div>';
            }
            ?>
        </div>

    </section>
    <section id="categories-section" class="slide-section">
        <div class="section-content">
            <h2>Categories</h2>
            <div class="categories-grid horizontal-categories">
                <div class="category-card active-category" data-type="phones" onclick="selectCategory(this, 'phones')">
                    <img src="images/logos/logo-blue.jpg" alt="Phones">
                    <span>Phones</span>
                </div>
                <div class="category-card" data-type="clothes" onclick="selectCategory(this, 'clothes')">
                    <img src="images/logos/logo-white.jpg" alt="Clothings">
                    <span>Clothings</span>
                </div>
            </div>
            <div id="category-products-list" style="margin-top:32px;"></div>
        </div>
    </section>
    <section id="contact-section" class="slide-section">
        <div class="section-content contact-section-content">
            <h2>Contact Us</h2>
            <div class="contact-flex">
                <div class="contact-info-box">
                    <h3>Get in Touch</h3>
                    <p><strong>Email:</strong> <span id="contact-email">[your@email.com]</span></p>
                    <p><strong>Phone:</strong> <span id="contact-phone">[+233 000 000 000]</span></p>
                    <p><strong>Address:</strong> <span id="contact-address">[Your Address Here]</span></p>
                </div>
                <form class="contact-form" id="contactForm" method="post" action="#">
                    <h3>Send Us a Message</h3>
                    <input type="text" name="name" placeholder="Your Name" required>
                    <input type="email" name="email" placeholder="Your Email" required>
                    <textarea name="message" placeholder="Your Message" rows="5" required></textarea>
                    <button type="submit">Send Message</button>
                    <div id="contact-form-status" style="margin-top:10px;text-align:center;"></div>
                </form>
            </div>
        </div>
    </section>
    <section id="account-section" class="slide-section">
        <div class="section-content">
            <h2>Account</h2>
            <div class="account-info-flex">
                <img src="images/logos/logo-black.jpg" alt="Profile" class="account-profile-pic">
                <div class="account-details">
                    <div class="account-user-name">
                        <?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'User'; ?>
                    </div>
                    <div class="account-user-email">
                        <?php echo isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : ''; ?>
                    </div>
                </div>
            </div>
            <div class="account-actions">
                <a href="update_account.php" class="account-action-btn">Update Account</a>
                <a href="user_orders.php" class="account-action-btn">My Orders</a>
                <a href="user_messages.php" class="account-action-btn">My Messages</a>
                <a href="logout.php" class="account-action-btn logout">Logout</a>
            </div>
        </div>
    </section>
    <!-- Product Quick View Slide-in Panel -->
    <div id="product-view-panel" class="product-view-panel" style="display:none;">
        <div class="product-view-content">
            <button class="close-product-view" onclick="closeProductView()">&times;</button>
            <img id="pv-img" src="" alt="Product Image">
            <h3 id="pv-name"></h3>
            <p id="pv-desc"></p>
            <button id="add-to-cart-btn" class="add-to-cart-btn">Add to Cart</button>
        </div>
    </div>
    <!-- Cart Slide-in Panel -->
    <div id="cart-panel" class="cart-panel" style="display:none;">
        <div class="cart-content">
            <button class="close-cart" onclick="closeCart()">&times;</button>
            <h3>Your Cart</h3>
            <div id="cart-items"></div>
        </div>
    </div>

    <script src="script.js"></script>
    <script>
    // Home page product search with result count and display only results (name only)
    document.addEventListener('DOMContentLoaded', function() {
        var searchInput = document.querySelector('input.search');
        var resultCount = document.querySelector('.search-result-count');
        if (!resultCount) {
            resultCount = document.createElement('div');
            resultCount.className = 'search-result-count';
            resultCount.style.textAlign = 'center';
            resultCount.style.margin = '12px 0';
            searchInput.parentNode.insertBefore(resultCount, searchInput.nextSibling);
        }
        function updateSearch() {
            var query = searchInput.value.toLowerCase();
            var allCards = Array.from(document.querySelectorAll('.product-card'));
            // Find all new product names (from the New section)
            var newSection = document.querySelector('.product-slider');
            var newCards = newSection ? Array.from(newSection.querySelectorAll('.product-card')) : [];
            var newNames = newCards.map(card => card.getAttribute('data-name'));
            var matches = 0;
            allCards.forEach(function(card) {
                var name = card.getAttribute('data-name') ? card.getAttribute('data-name').toLowerCase() : '';
                var isNew = newNames.includes(card.getAttribute('data-name'));
                if (query && name.includes(query)) {
                    // Only show new card if it's in the new section, otherwise only show if not new
                    if (isNew && card.parentElement.classList.contains('product-slider')) {
                        card.style.display = '';
                        matches++;
                    } else if (!isNew && !card.parentElement.classList.contains('product-slider')) {
                        card.style.display = '';
                        matches++;
                    } else {
                        card.style.display = 'none';
                    }
                } else if (query) {
                    card.style.display = 'none';
                } else {
                    card.style.display = '';
                }
            });
            // Hide all home sections if searching
            var homeSections = document.querySelectorAll('.new, .category-header, .category-slider, .best-deals-header, .best-cloth-deals-header, .deals-slider');
            if (query) {
                homeSections.forEach(function(sec) { sec.style.display = 'none'; });
                resultCount.textContent = matches + ' result' + (matches === 1 ? '' : 's') + ' found.';
                // Only show product cards that match
                var productSliders = document.querySelectorAll('.product-slider, .deals-slider');
                productSliders.forEach(function(slider) {
                    var hasVisible = false;
                    slider.querySelectorAll('.product-card').forEach(function(card) {
                        if (card.style.display !== 'none') hasVisible = true;
                    });
                    slider.style.display = hasVisible ? '' : 'none';
                });
            } else {
                homeSections.forEach(function(sec) { sec.style.display = ''; });
                var productSliders = document.querySelectorAll('.product-slider, .deals-slider');
                productSliders.forEach(function(slider) { slider.style.display = ''; });
                resultCount.textContent = '';
            }
        }
        if (searchInput) {
            searchInput.addEventListener('input', updateSearch);
        }
    });
    </script>
    <script>
function openAccountBox(e) {
    e.preventDefault();
    document.getElementById('account-box').classList.add('show');
}
function closeAccountBox(e) {
    e.preventDefault();
    document.getElementById('account-box').classList.remove('show');
}
// Optional: close account box when clicking outside
window.addEventListener('click', function(e) {
    var box = document.getElementById('account-box');
    var accBtn = document.querySelector('a[href="#"][onclick*="openAccountBox"]');
    if (box && box.classList.contains('show') && !box.contains(e.target) && e.target !== accBtn) {
        box.classList.remove('show');
    }
});
function showSection(e, sectionId) {
    if (e) e.preventDefault();
    // Save last section to localStorage
    localStorage.setItem('lastSection', sectionId);
    // Remove active from all nav links
    document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
    // Add active to clicked nav link
    if (e && e.target) e.target.classList.add('active');
    // Slide out all sections
    document.querySelectorAll('.slide-section').forEach(sec => sec.classList.remove('active-section', 'slide-in', 'slide-out'));
    // Find the currently active section
    const current = document.querySelector('.slide-section.active-section');
    if (current && current.id !== sectionId) {
        current.classList.add('slide-out');
        setTimeout(() => {
            current.classList.remove('slide-out', 'active-section');
            const next = document.getElementById(sectionId);
            next.classList.add('slide-in', 'active-section');
            setTimeout(() => next.classList.remove('slide-in'), 350);
        }, 350);
    } else {
        const next = document.getElementById(sectionId);
        next.classList.add('slide-in', 'active-section');
        setTimeout(() => next.classList.remove('slide-in'), 350);
    }
}
// On page load, show last section if available
window.addEventListener('DOMContentLoaded', function() {
    var lastSection = localStorage.getItem('lastSection');
    if (lastSection && document.getElementById(lastSection)) {
        // Find the nav link for this section
        var navLink = Array.from(document.querySelectorAll('.nav-link')).find(link => link.getAttribute('onclick') && link.getAttribute('onclick').includes(lastSection));
        showSection({preventDefault:()=>{},target:navLink}, lastSection);
    }
});
    </script>
    <script>
// Restore simple category section logic
function selectCategory(el, type) {
    document.querySelectorAll('.category-card').forEach(function(card) {
        card.classList.remove('active-category');
    });
    el.classList.add('active-category');
    showCategoryProducts(type);
}
function showCategoryProducts(type) {
    var container = document.getElementById('category-products-list');
    container.innerHTML = '<div style="text-align:center;">Loading...</div>';
    fetch('get_category_products.php?type=' + encodeURIComponent(type))
        .then(response => response.text())
        .then(html => {
            container.innerHTML = html;
        })
        .catch(() => {
            container.innerHTML = '<div style="color:red;text-align:center;">Failed to load products.</div>';
        });
}
// Load default (phones) on page load
window.addEventListener('DOMContentLoaded', function() {
    showCategoryProducts('phones');
});
</script>
<script>
document.getElementById('contactForm').onsubmit = function(e) {
    e.preventDefault();
    var form = this;
    var status = document.getElementById('contact-form-status');
    status.textContent = 'Sending...';
    fetch('contact_message.php', {
        method: 'POST',
        body: new FormData(form)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            status.textContent = 'Message sent!';
            form.reset();z
            // If user_messages list is present, reload it
            if (typeof reloadUserMessages === 'function') reloadUserMessages();
            else if (window.parent && window.parent.reloadUserMessages) window.parent.reloadUserMessages();
        } else {
            status.textContent = data.message || 'Failed to send.';
        }
    })
    .catch(() => { status.textContent = 'Network error.'; });
};
</script>
</body>
</html>