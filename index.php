<?php
session_start();
include('db.php');

// Get categories from database
$categories_query = "SELECT name, slug FROM categories WHERE is_active = 1 ORDER BY sort_order ASC, name ASC";
$categories_result = $conn->query($categories_query);
$categories = [];
if ($categories_result) {
    while ($row = $categories_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Get all products for display
$featured_products = mysqli_query($conn, "SELECT p.*, pi.image_path as main_image, c.name as category_name
                                         FROM products p 
                                         LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1 
                                         LEFT JOIN categories c ON p.category_id = c.id
                                         WHERE p.is_featured = 1 
                                         ORDER BY p.created_at DESC LIMIT 8");
$flash_sale_products = mysqli_query($conn, "SELECT p.*, pi.image_path as main_image, c.name as category_name
                                           FROM products p 
                                           LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1 
                                           LEFT JOIN categories c ON p.category_id = c.id
                                           WHERE p.is_flash_sale = 1 
                                           ORDER BY p.created_at DESC LIMIT 6");
$all_products = mysqli_query($conn, "SELECT p.*, pi.image_path as main_image, c.name as category_name
                                    FROM products p 
                                    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1 
                                    LEFT JOIN categories c ON p.category_id = c.id
                                    ORDER BY p.created_at DESC LIMIT 20");

function getCategoryIcon($category) {
    $icons = [
        'Electronics' => 'mobile-alt',
        'Clothing' => 'tshirt',
        'Shoes' => 'shoe-prints',
        'Accessories' => 'gem',
        'Home & Garden' => 'home',
        'Sports & Outdoors' => 'futbol',
        'Books & Media' => 'book',
        'Health & Beauty' => 'spa',
        'Automotive' => 'car',
        'Toys & Games' => 'gamepad'
    ];
    
    return isset($icons[$category]) ? $icons[$category] : 'box';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kings Reign - Your Premium Shopping Destination</title>
    <link rel="stylesheet" href="styles/modern_style.css">
    <link rel="shortcut icon" href="images/logos/logo-black.jpg" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Professional Header Styles */
        .main-header {
            background: white;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid #e5e7eb;
        }

        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .logo-section {
            flex-shrink: 0;
        }

        .logo {
            height: 50px;
            width: auto;
        }

        .search-section {
            flex: 1;
            max-width: 600px;
        }

        .search-container {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-input {
            width: 100%;
            padding: 16px 24px;
            border: 2px solid #e2e8f0;
            border-radius: 30px;
            font-size: 16px;
            outline: none;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .search-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .search-btn {
            position: absolute;
            right: 6px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            border: none;
            padding: 14px 24px;
            border-radius: 24px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3);
        }

        .search-btn:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }

        .user-actions {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .action-link {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #374151;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .action-link:hover {
            color: #2563eb;
            transform: translateY(-1px);
        }

        .action-link i {
            font-size: 20px;
            margin-bottom: 4px;
        }

        .cart-link {
            position: relative;
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
        }

        /* User Menu Styles */
        .user-menu {
            position: relative;
        }

        .user-menu-btn {
            background: none;
            border: none;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #374151;
            font-weight: 500;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .user-menu-btn:hover {
            background: #f3f4f6;
            color: #2563eb;
        }

        .user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            min-width: 280px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 1000;
            border: 1px solid #f1f5f9;
        }

        .user-dropdown.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .user-info {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .user-info h3 {
            margin: 0;
            font-size: 16px;
            color: #111827;
        }

        .user-info p {
            margin: 4px 0 0 0;
            font-size: 14px;
            color: #6b7280;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 1rem;
            color: #374151;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .dropdown-item:hover {
            background: #f9fafb;
            color: #2563eb;
        }

        .dropdown-item.text-danger:hover {
            color: #dc2626;
        }

        /* Main Container */
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            gap: 2rem;
            padding: 2rem;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            flex-shrink: 0;
        }

        .category-nav {
            /* position: fixed; */
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #f1f5f9;
        }

        .sidebar-title {
            font-size: 20px;
            font-weight: 800;
            color: #111827;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 3px solid #e5e7eb;
        }

        .category-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .category-item {
            margin-bottom: 0.5rem;
        }

        .category-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #374151;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .category-link:hover {
            background: #f3f4f6;
            color: #2563eb;
        }

        .category-link i {
            width: 20px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            flex: 1;
        }

        /* Hero Section */
        .hero-section {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .hero-banner {
            flex: 1;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 20px;
            padding: 2.5rem;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(16, 185, 129, 0.3);
        }

        .banner-content {
            position: relative;
            z-index: 2;
        }

        .banner-title {
            font-size: 3rem;
            font-weight: 900;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .banner-subtitle {
            font-size: 1.1rem;
            margin-bottom: 1rem;
            opacity: 0.9;
        }

        .banner-discount {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .banner-terms {
            font-size: 0.9rem;
            opacity: 0.8;
            margin-bottom: 1.5rem;
        }

        .banner-btn {
            background: white;
            color: #059669;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .banner-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .banner-image {
            position: absolute;
            right: 0;
            top: 0;
            height: 100%;
            width: 40%;
            background: url('images/banner-appliances.jpg') center/cover;
            border-radius: 0 16px 16px 0;
        }

        /* Promo Cards */
        .promo-cards {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            width: 200px;
        }

        .promo-card {
            background: white;
            border-radius: 12px;
            padding: 1rem;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .promo-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .promo-card.whatsapp {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .promo-card.sell {
            background: white;
            color: #374151;
        }

        .promo-card.track {
            background: white;
            color: #374151;
        }

        .promo-card.gear-up {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .promo-card i {
            font-size: 24px;
            margin-bottom: 0.5rem;
        }

        .promo-card h4 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .promo-card p {
            font-size: 12px;
            margin: 0;
            opacity: 0.9;
        }

        /* Section Headers */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-title {
            font-size: 1.75rem;
            font-weight: 800;
            color: #111827;
            margin: 0;
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 40px;
            height: 3px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            border-radius: 2px;
        }

        .section-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .countdown {
            font-size: 14px;
            color: #6b7280;
        }

        .see-all {
            color: #2563eb;
            text-decoration: none;
            font-weight: 500;
        }

        .see-all:hover {
            text-decoration: underline;
        }

        /* Product Cards */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .product-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            position: relative;
            border: 1px solid #f1f5f9;
        }

        .product-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.12);
            border-color: #e2e8f0;
        }

        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            position: relative;
        }

        .discount-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
        }

        .product-info {
            padding: 1rem;
        }

        .product-name {
            font-size: 16px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }

        .product-price {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .current-price {
            font-size: 20px;
            font-weight: 800;
            color: #2563eb;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .original-price {
            font-size: 14px;
            color: #6b7280;
            text-decoration: line-through;
        }

        .stock-info {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 1rem;
        }

        .product-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn {
            flex: 1;
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .main-container {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
            }
            
            .hero-section {
                flex-direction: column;
            }
            
            .promo-cards {
                width: 100%;
                flex-direction: row;
            }
        }

        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                gap: 1rem;
            }
            
            .search-section {
                width: 100%;
            }
            
            .user-actions {
                width: 100%;
                justify-content: center;
            }
            
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }

        /* Search Results Styles */
        .search-results {
            margin-bottom: 2rem;
        }

        .search-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: #6b7280;
            font-size: 14px;
        }

        .search-info span {
            font-weight: 500;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }

        .pagination-btn {
            padding: 0.5rem 1rem;
            border: 1px solid #e5e7eb;
            background: white;
            color: #374151;
            text-decoration: none;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .pagination-btn:hover {
            background: #f3f4f6;
        }

        .pagination-btn.active {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            border-color: #2563eb;
        }

        .category-link.active {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
        }

        /* Professional Footer Styles */
        .main-footer {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: white;
            margin-top: 4rem;
            padding: 3rem 0 1rem;
        }

        .footer-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .footer-section h3 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #f8fafc;
            position: relative;
        }

        .footer-section h3::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 40px;
            height: 3px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 2px;
        }

        .footer-section p {
            color: #cbd5e1;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .footer-section ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-section ul li {
            margin-bottom: 0.75rem;
        }

        .footer-section ul li a {
            color: #cbd5e1;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .footer-section ul li a:hover {
            color: #10b981;
            transform: translateX(4px);
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #374151 0%, #4b5563 100%);
            color: white;
            border-radius: 50%;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 18px;
        }

        .social-links a:hover {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
        }

        .footer-bottom {
            border-top: 1px solid #475569;
            margin-top: 2rem;
            padding-top: 1.5rem;
            text-align: center;
        }

        .footer-bottom p {
            color: #94a3b8;
            font-size: 14px;
            margin: 0;
        }

        @media (max-width: 768px) {
            .footer-content {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .social-links {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <div class="header-container">
            <!-- Logo -->
            <div class="logo-section">
                <img src="images/logos/logo-black.jpg" alt="Kings Reign" class="logo">
            </div>

            <!-- Search Bar -->
            <div class="search-section">
                <div class="search-container">
                    <input type="search" class="search-input" id="searchInput" placeholder="Search products, brands and categories">
                    <button class="search-btn" onclick="performSearch()">Search</button>
                </div>
            </div>

            <!-- User Actions -->
            <div class="user-actions">
                <?php if(isset($_SESSION['user_id'])) { ?>
                    <div class="user-menu">
                        <button class="user-menu-btn" onclick="toggleUserMenu()">
                            <i class="fas fa-user"></i>
                            <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        </button>
                        <div class="user-dropdown" id="userDropdown">
                            <div class="user-info">
                                <h3><?php echo htmlspecialchars($_SESSION['user_name']); ?></h3>
                                <p><?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
                            </div>
                            <a href="update_account.php" class="dropdown-item">
                                <i class="fas fa-user-edit"></i> My Account
                            </a>
                            <a href="user_orders.php" class="dropdown-item">
                                <i class="fas fa-shopping-bag"></i> My Orders
                            </a>
                            <a href="user_messages.php" class="dropdown-item">
                                <i class="fas fa-envelope"></i> Messages
                            </a>
                            <a href="logout.php" class="dropdown-item text-danger">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>
                    <a href="cart.php" class="action-link cart-link">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Cart</span>
                        <span class="cart-count">0</span>
                    </a>
                <?php } else { ?>
                    <a href="login.php" class="action-link">
                        <i class="fas fa-user"></i>
                        <span>Account</span>
                    </a>
                    <a href="#help" class="action-link">
                        <i class="fas fa-question-circle"></i>
                        <span>Help</span>
                    </a>
                    <a href="cart.php" class="action-link cart-link">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Cart</span>
                        <span class="cart-count">0</span>
                    </a>
                <?php } ?>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="main-container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <nav class="category-nav">
                <h3 class="sidebar-title">Categories</h3>
                <ul class="category-list">
                    <li class="category-item">
                        <a href="#" class="category-link active" onclick="filterByCategory('')">
                            <i class="fas fa-th-large"></i>
                            <span>All Categories</span>
                        </a>
                    </li>
                    <?php foreach($categories as $cat) { ?>
                        <li class="category-item">
                            <a href="#" class="category-link" onclick="filterByCategory('<?php echo htmlspecialchars($cat['name']); ?>')">
                                <i class="fas fa-<?php echo getCategoryIcon($cat['name']); ?>"></i>
                                <span><?php echo $cat['name']; ?></span>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <!-- Search Results Section (Hidden by default) -->
            <section id="searchResults" class="search-results" style="display: none;">
                <div class="section-header">
                    <h2 class="section-title">Search Results</h2>
                    <div class="search-info">
                        <span id="searchInfo"></span>
                        <button class="btn btn-secondary" onclick="clearSearch()">
                            <i class="fas fa-times"></i> Clear Search
                        </button>
                    </div>
                </div>
                <div id="searchProductsGrid" class="products-grid">
                    <!-- Search results will be loaded here -->
                </div>
                <div id="searchPagination" class="pagination" style="display: none;">
                    <!-- Pagination will be loaded here -->
                </div>
            </section>

            <!-- Hero Banner Section -->
            <section class="hero-section">
                <div class="hero-banner">
                    <div class="banner-content">
                        <h1 class="banner-title">GEAR UP</h1>
                        <p class="banner-subtitle">Our Kitchen, Upgraded</p>
                        <p class="banner-discount">UP TO 30%</p>
                        <p class="banner-terms">T & C's Apply</p>
                        <button class="banner-btn">SHOP NOW</button>
                    </div>
                    <div class="banner-image"></div>
                </div>

                <!-- Right Side Promotions -->
                <div class="promo-cards">
                    <div class="promo-card whatsapp">
                        <i class="fab fa-whatsapp"></i>
                        <h4>CALL/WHATSAPP</h4>
                        <p>030 274 0642</p>
                    </div>
                    <div class="promo-card sell">
                        <i class="fas fa-store"></i>
                        <h4>SELL ON KINGS REIGN</h4>
                        <p>Make more money</p>
                    </div>
                    <div class="promo-card track">
                        <i class="fas fa-envelope"></i>
                        <h4>TRACK YOUR ORDER</h4>
                        <p>Stay up to date</p>
                    </div>
                    <div class="promo-card gear-up">
                        <h4>GEAR UP</h4>
                        <p>UP TO 40%</p>
                    </div>
                </div>
            </section>

            <!-- Flash Sales Section -->
            <section class="flash-sales">
                <div class="section-header">
                    <h2 class="section-title">Promotions Ending Soon</h2>
                    <div class="section-actions">
                        <div class="countdown">
                            <span>Time Left: </span>
                            <span id="countdown">19h : 32m : 56s</span>
                        </div>
                        <a href="#flash-sales" class="see-all">See All ></a>
                    </div>
                </div>

                <div class="products-grid">
                    <?php while($product = mysqli_fetch_assoc($flash_sale_products)) { ?>
                        <div class="product-card">
                            <div class="product-image">
                                <img src="<?php echo $product['main_image']; ?>" alt="<?php echo $product['name']; ?>" width="90%" height="100%">
                                <span class="discount-badge">-<?php echo $product['discount_percentage']; ?>%</span>
                            </div>
                            <div class="product-info">
                                <h3 class="product-name"><?php echo $product['name']; ?></h3>
                                <div class="product-price">
                                    <span class="current-price">GH ₵<?php echo number_format($product['price'], 2); ?></span>
                                    <span class="original-price">GH ₵<?php echo number_format($product['original_price'], 2); ?></span>
                                </div>
                                <p class="stock-info"><?php echo $product['stock']; ?> items left</p>
                                <div class="product-actions">
                                    <button class="btn btn-primary" onclick="addToCart(<?php echo $product['id']; ?>)">
                                        <i class="fas fa-cart-plus"></i> Add to Cart
                                    </button>
                                    <button class="btn btn-secondary" onclick="viewProduct(<?php echo $product['id']; ?>)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </section>

            <!-- Featured Products -->
            <section class="featured-products">
                <div class="section-header">
                    <h2 class="section-title">Featured Products</h2>
                </div>

                <div class="products-grid">
                    <?php while($product = mysqli_fetch_assoc($featured_products)) { ?>
                        <div class="product-card">
                            <div class="product-image">
                                <img src="<?php echo $product['main_image']; ?>" alt="<?php echo $product['name']; ?>">
                                <?php if($product['discount_percentage'] > 0) { ?>
                                    <span class="discount-badge">-<?php echo $product['discount_percentage']; ?>%</span>
                                <?php } ?>
                            </div>
                            <div class="product-info">
                                <h3 class="product-name"><?php echo $product['name']; ?></h3>
                                <div class="product-price">
                                    <span class="current-price">GH ₵<?php echo number_format($product['price'], 2); ?></span>
                                    <?php if($product['original_price'] > $product['price']) { ?>
                                        <span class="original-price">GH ₵<?php echo number_format($product['original_price'], 2); ?></span>
                                    <?php } ?>
                                </div>
                                <div class="product-actions">
                                    <button class="btn btn-primary" onclick="addToCart(<?php echo $product['id']; ?>)">
                                        <i class="fas fa-cart-plus"></i> Add to Cart
                                    </button>
                                    <button class="btn btn-secondary" onclick="viewProduct(<?php echo $product['id']; ?>)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </section>

            <!-- All Products -->
            <section class="all-products">
                <div class="section-header">
                    <h2 class="section-title">All Products</h2>
                </div>

                <div class="products-grid">
                    <?php while($product = mysqli_fetch_assoc($all_products)) { ?>
                        <div class="product-card">
                            <div class="product-image">
                                <img src="<?php echo $product['main_image']; ?>" alt="<?php echo $product['name']; ?>" width="100%" height="100%">
                                <?php if($product['discount_percentage'] > 0) { ?>
                                    <span class="discount-badge">-<?php echo $product['discount_percentage']; ?>%</span>
                                <?php } ?>
                            </div>
                            <div class="product-info">
                                <h3 class="product-name"><?php echo $product['name']; ?></h3>
                                <div class="product-price">
                                    <span class="current-price">GH ₵<?php echo number_format($product['price'], 2); ?></span>
                                    <?php if($product['original_price'] > $product['price']) { ?>
                                        <span class="original-price">GH ₵<?php echo number_format($product['original_price'], 2); ?></span>
                                    <?php } ?>
                                </div>
                                <div class="product-actions">
                                    <button class="btn btn-primary" onclick="addToCart(<?php echo $product['id']; ?>)">
                                        <i class="fas fa-cart-plus"></i> Add to Cart
                                    </button>
                                    <button class="btn btn-secondary" onclick="viewProduct(<?php echo $product['id']; ?>)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </section>
        </main>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3><i class="fas fa-crown"></i> About Kings Reign</h3>
                <p>Your trusted online shopping destination for quality products at unbeatable prices. We bring you the best deals on electronics, fashion, and more.</p>
            </div>
            <div class="footer-section">
                <h3><i class="fas fa-link"></i> Quick Links</h3>
                <ul>
                    <li><a href="#about"><i class="fas fa-info-circle"></i> About Us</a></li>
                    <li><a href="#contact"><i class="fas fa-envelope"></i> Contact</a></li>
                    <li><a href="#help"><i class="fas fa-question-circle"></i> Help Center</a></li>
                    <li><a href="#terms"><i class="fas fa-file-contract"></i> Terms & Conditions</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3><i class="fas fa-headset"></i> Customer Service</h3>
                <ul>
                    <li><a href="#shipping"><i class="fas fa-shipping-fast"></i> Shipping Info</a></li>
                    <li><a href="#returns"><i class="fas fa-undo"></i> Returns</a></li>
                    <li><a href="#faq"><i class="fas fa-question"></i> FAQ</a></li>
                    <li><a href="#support"><i class="fas fa-life-ring"></i> Support</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3><i class="fas fa-share-alt"></i> Connect With Us</h3>
                <div class="social-links">
                    <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 Kings Reign. All rights reserved. | Designed with <i class="fas fa-heart" style="color: #ef4444;"></i> for you</p>
        </div>
    </footer>

    <script>
        // Countdown timer for flash sales
        function updateCountdown() {
            const now = new Date();
            const endTime = new Date(now.getTime() + (19 * 60 * 60 * 1000) + (32 * 60 * 1000) + (56 * 1000));
            
            const timer = setInterval(() => {
                const currentTime = new Date();
                const timeLeft = endTime - currentTime;
                
                if (timeLeft <= 0) {
                    clearInterval(timer);
                    document.getElementById('countdown').textContent = '00h : 00m : 00s';
                    return;
                }
                
                const hours = Math.floor(timeLeft / (1000 * 60 * 60));
                const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);
                
                document.getElementById('countdown').textContent = 
                    `${hours.toString().padStart(2, '0')}h : ${minutes.toString().padStart(2, '0')}m : ${seconds.toString().padStart(2, '0')}s`;
            }, 1000);
        }

        // Add to cart functionality
        function addToCart(productId) {
            // Check if user is logged in
            <?php if(isset($_SESSION['user_id'])) { ?>
                // Show loading state
                Swal.fire({
                    title: 'Adding to Cart...',
                    text: 'Please wait',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                fetch('add_to_cart_unified.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'product_id=' + productId
                })
                .then(response => response.json())
                .then(data => {
                    Swal.close();
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Added to Cart!',
                            text: 'Product has been added to your cart successfully.',
                            showConfirmButton: false,
                            timer: 1500
                        });
                        updateCartCount();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Failed to add product to cart.'
                        });
                    }
                })
                .catch(() => {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Network Error',
                        text: 'Please check your connection and try again.'
                    });
                });
            <?php } else { ?>
                // Redirect to login if not logged in
                window.location.href = 'login.php';
            <?php } ?>
        }

        // View product details
        function viewProduct(productId) {
            window.location.href = 'product.php?id=' + productId;
        }

        // Update cart count
        function updateCartCount() {
            <?php if(isset($_SESSION['user_id'])) { ?>
                fetch('get_cart_unified.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const cartCount = data.cart.length || 0;
                            document.querySelectorAll('.cart-count').forEach(element => {
                                element.textContent = cartCount;
                            });
                        }
                    })
                    .catch(() => {
                        // If error, set count to 0
                        document.querySelectorAll('.cart-count').forEach(element => {
                            element.textContent = '0';
                        });
                    });
            <?php } else { ?>
                // For non-logged in users, set count to 0
                document.querySelectorAll('.cart-count').forEach(element => {
                    element.textContent = '0';
                });
            <?php } ?>
        }

        // Show alert messages
        function showAlert(message, type) {
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.innerHTML = `<i class="fas fa-${type === 'success' ? 'check' : 'exclamation-triangle'}"></i> ${message}`;
            
            document.body.appendChild(alert);
            
            setTimeout(() => {
                alert.remove();
            }, 3000);
        }

        // User menu functionality
        function toggleUserMenu() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('show');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.user-menu')) {
                const dropdown = document.getElementById('userDropdown');
                if (dropdown) {
                    dropdown.classList.remove('show');
                }
            }
        });

        // Search and filter functionality
        let currentSearch = '';
        let currentCategory = '';
        let currentPage = 1;

        function performSearch() {
            const searchInput = document.getElementById('searchInput');
            const searchTerm = searchInput.value.trim();
            
            if (searchTerm === '' && currentCategory === '') {
                clearSearch();
                return;
            }
            
            currentSearch = searchTerm;
            currentPage = 1;
            loadSearchResults();
        }

        function filterByCategory(category) {
            currentCategory = category;
            currentSearch = '';
            currentPage = 1;
            
            // Update active category link
            document.querySelectorAll('.category-link').forEach(link => {
                link.classList.remove('active');
            });
            event.target.classList.add('active');
            
            // Clear search input
            document.getElementById('searchInput').value = '';
            
            loadSearchResults();
        }

        function loadSearchResults() {
            const searchResults = document.getElementById('searchResults');
            const searchProductsGrid = document.getElementById('searchProductsGrid');
            const searchInfo = document.getElementById('searchInfo');
            const searchPagination = document.getElementById('searchPagination');
            
            // Show loading
            searchProductsGrid.innerHTML = '<div style="text-align: center; padding: 2rem; color: #6b7280;">Loading...</div>';
            searchResults.style.display = 'block';
            
            // Hide other sections
            document.querySelectorAll('.hero-section, .flash-sales, .featured-products, .all-products').forEach(section => {
                section.style.display = 'none';
            });
            
            // Build query parameters
            const params = new URLSearchParams();
            if (currentSearch) params.append('search', currentSearch);
            if (currentCategory) params.append('category', currentCategory);
            if (currentPage > 1) params.append('page', currentPage);
            
            fetch('search_products.php?' + params.toString())
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displaySearchResults(data);
                    } else {
                        searchProductsGrid.innerHTML = '<div style="text-align: center; padding: 2rem; color: #6b7280;">Error loading results</div>';
                    }
                })
                .catch(() => {
                    searchProductsGrid.innerHTML = '<div style="text-align: center; padding: 2rem; color: #6b7280;">Network error</div>';
                });
        }

        function displaySearchResults(data) {
            const searchProductsGrid = document.getElementById('searchProductsGrid');
            const searchInfo = document.getElementById('searchInfo');
            const searchPagination = document.getElementById('searchPagination');
            
            // Update search info
            let infoText = `Found ${data.total_products} product${data.total_products !== 1 ? 's' : ''}`;
            if (data.search) infoText += ` for "${data.search}"`;
            if (data.category) infoText += ` in ${data.category}`;
            searchInfo.textContent = infoText;
            
            if (data.products.length === 0) {
                searchProductsGrid.innerHTML = `
                    <div style="text-align: center; padding: 3rem; color: #6b7280;">
                        <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 1rem; color: #d1d5db;"></i>
                        <h3>No products found</h3>
                        <p>Try adjusting your search terms or browse all categories</p>
                    </div>
                `;
                searchPagination.style.display = 'none';
                return;
            }
            
            // Display products
            let productsHtml = '';
            data.products.forEach(product => {
                const discountBadge = product.discount_percentage > 0 ? 
                    `<span class="discount-badge">-${product.discount_percentage}%</span>` : '';
                
                const originalPrice = product.original_price > product.price ? 
                    `<span class="original-price">GH ₵${parseFloat(product.original_price).toFixed(2)}</span>` : '';
                
                productsHtml += `
                    <div class="product-card">
                        <div class="product-image">
                            <img src="${product.main_image || 'images/placeholder.jpg'}" alt="${product.name}">
                            ${discountBadge}
                        </div>
                        <div class="product-info">
                            <h3 class="product-name">${product.name}</h3>
                            <div class="product-price">
                                <span class="current-price">GH ₵${parseFloat(product.price).toFixed(2)}</span>
                                ${originalPrice}
                            </div>
                            <p class="stock-info">${product.stock} items left</p>
                            <div class="product-actions">
                                <button class="btn btn-primary" onclick="addToCart(${product.id})">
                                    <i class="fas fa-cart-plus"></i> Add to Cart
                                </button>
                                <button class="btn btn-secondary" onclick="viewProduct(${product.id})">
                                    <i class="fas fa-eye"></i> View
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            searchProductsGrid.innerHTML = productsHtml;
            
            // Display pagination
            if (data.total_pages > 1) {
                let paginationHtml = '';
                
                if (data.current_page > 1) {
                    paginationHtml += `<a href="#" class="pagination-btn" onclick="changePage(${data.current_page - 1})">Previous</a>`;
                }
                
                for (let i = Math.max(1, data.current_page - 2); i <= Math.min(data.total_pages, data.current_page + 2); i++) {
                    paginationHtml += `<a href="#" class="pagination-btn ${i === data.current_page ? 'active' : ''}" onclick="changePage(${i})">${i}</a>`;
                }
                
                if (data.current_page < data.total_pages) {
                    paginationHtml += `<a href="#" class="pagination-btn" onclick="changePage(${data.current_page + 1})">Next</a>`;
                }
                
                searchPagination.innerHTML = paginationHtml;
                searchPagination.style.display = 'flex';
            } else {
                searchPagination.style.display = 'none';
            }
        }

        function changePage(page) {
            currentPage = page;
            loadSearchResults();
        }

        function clearSearch() {
            currentSearch = '';
            currentCategory = '';
            currentPage = 1;
            
            // Clear search input
            document.getElementById('searchInput').value = '';
            
            // Remove active class from category links
            document.querySelectorAll('.category-link').forEach(link => {
                link.classList.remove('active');
            });
            document.querySelector('.category-link').classList.add('active'); // Set "All Categories" as active
            
            // Hide search results
            document.getElementById('searchResults').style.display = 'none';
            
            // Show all sections
            document.querySelectorAll('.hero-section, .flash-sales, .featured-products, .all-products').forEach(section => {
                section.style.display = 'block';
            });
        }

        // Handle Enter key in search input
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    performSearch();
                }
            });
        });

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            updateCountdown();
            updateCartCount();
        });
    </script>
</body>
</html> 