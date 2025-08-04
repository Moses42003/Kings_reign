-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 04, 2025 at 04:59 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_kings_reign`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `passwd` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `name`, `email`, `passwd`, `created_at`) VALUES
(1, 'Admin', 'admin@kingsreign.com', '$2y$10$nQ4J5.pKKwkEAWdFqTgkLOhbAMNKQc01WqpaemvtWjEx.hUz/SJTa', '2025-08-02 16:32:40');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `slug` varchar(100) DEFAULT NULL,
  `image_path` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `slug`, `image_path`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Electronics', 'Electronic devices and gadgets', 'electronics', NULL, 1, 1, '2025-08-04 11:51:17', '2025-08-04 11:51:17'),
(2, 'Clothing', 'Fashion and apparel', 'clothing', NULL, 1, 2, '2025-08-04 11:51:17', '2025-08-04 11:51:17'),
(3, 'Shoes', 'Footwear for all occasions', 'shoes', NULL, 1, 3, '2025-08-04 11:51:17', '2025-08-04 11:51:17'),
(4, 'Accessories', 'Fashion and electronic accessories', 'accessories', NULL, 1, 4, '2025-08-04 11:51:17', '2025-08-04 11:51:17'),
(11, 'Fashion', 'Fashion and clothing items', 'fashion', NULL, 1, 2, '2025-08-04 12:04:21', '2025-08-04 12:04:21'),
(12, 'Home & Office', 'Home and office supplies', 'home-office', NULL, 1, 6, '2025-08-04 12:04:21', '2025-08-04 12:04:21'),
(13, 'Appliances', 'Home appliances and electronics', 'appliances', NULL, 1, 7, '2025-08-04 12:04:21', '2025-08-04 12:04:21');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read','replied') DEFAULT 'unread',
  `reply` text DEFAULT NULL,
  `replied_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `shipping_address` text DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total`, `status`, `shipping_address`, `phone_number`, `payment_method`, `created_at`) VALUES
(1, 1, 7499.99, 'pending', 'Address not provided', NULL, 'Cash on Delivery', '2025-08-04 14:11:26');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `price`, `quantity`) VALUES
(1, 1, 1, 'iPhone 15 Pro', 999.99, 1),
(2, 1, 2, 'Samsung Galaxy S24', 6500.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `original_price` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `category` varchar(100) NOT NULL,
  `subcategory` varchar(100) DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `discount_percentage` int(11) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_flash_sale` tinyint(1) DEFAULT 0,
  `flash_sale_end` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `price`, `original_price`, `description`, `stock`, `category`, `subcategory`, `brand`, `discount_percentage`, `is_featured`, `is_flash_sale`, `flash_sale_end`, `created_at`, `updated_at`) VALUES
(1, 4, 'iPhone 15 Pro', 999.99, 1099.99, 'Latest iPhone model', 49, 'Electronics', 'Phones', 'Apple', 10, 1, 0, NULL, '2025-08-02 16:32:40', '2025-08-04 14:11:26'),
(2, 4, 'Samsung Galaxy S24', 6500.00, 7500.00, 'Premium Android smartphone', 11, 'Electronics', 'Smartphones', 'Samsung', 13, 1, 0, NULL, '2025-08-02 16:32:40', '2025-08-04 14:11:26'),
(3, 4, 'Tecno Spark 10 Pro', 1200.00, 1500.00, 'Latest Tecno smartphone', 25, 'Accessories', 'Smartphones', 'Tecno', 20, 0, 1, NULL, '2025-08-02 16:32:40', '2025-08-04 12:04:03'),
(4, 1, 'Infinix Note 30', 1800.00, 2200.00, 'Powerful performance phone', 18, 'Electronics', 'Smartphones', 'Infinix', 18, 0, 1, NULL, '2025-08-02 16:32:40', '2025-08-04 12:04:03'),
(5, 11, 'Nike Air Max', 450.00, 600.00, 'Comfortable running shoes', 30, 'Fashion', 'Shoes', 'Nike', 25, 1, 0, NULL, '2025-08-02 16:32:40', '2025-08-04 12:05:07'),
(6, 11, 'Adidas Hoodie', 180.00, 250.00, 'Warm and stylish hoodie', 40, 'Fashion', 'Clothing', 'Adidas', 28, 0, 1, NULL, '2025-08-02 16:32:40', '2025-08-04 12:05:07'),
(7, 11, 'Levi\'s Jeans', 220.00, 300.00, 'Classic blue jeans', 35, 'Fashion', 'Clothing', 'Levi\'s', 27, 1, 0, NULL, '2025-08-02 16:32:40', '2025-08-04 12:05:07'),
(8, 11, 'Puma T-Shirt', 80.00, 120.00, 'Comfortable cotton t-shirt', 50, 'Fashion', 'Clothing', 'Puma', 33, 0, 1, NULL, '2025-08-02 16:32:40', '2025-08-04 12:05:07'),
(9, 1, 'MacBook Pro M3', 25000.00, 28000.00, 'Professional laptop for work', 8, 'Electronics', 'Computers', 'Apple', 11, 1, 0, NULL, '2025-08-02 16:32:40', '2025-08-04 12:04:03'),
(10, 1, 'Sony WH-1000XM4', 1200.00, 1500.00, 'Premium noise-canceling headphones', 20, 'Electronics', 'Audio', 'Sony', 20, 0, 1, NULL, '2025-08-02 16:32:40', '2025-08-04 12:04:03'),
(11, 1, 'Samsung 4K TV', 3500.00, 4500.00, '55-inch 4K Smart TV', 10, 'Electronics', 'TVs', 'Samsung', 22, 1, 0, NULL, '2025-08-02 16:32:40', '2025-08-04 12:04:03'),
(12, 1, 'JBL Flip 6', 350.00, 450.00, 'Portable Bluetooth speaker', 25, 'Electronics', 'Audio', 'JBL', 22, 0, 1, NULL, '2025-08-02 16:32:40', '2025-08-04 12:04:03'),
(13, 12, 'IKEA Desk Chair', 450.00, 600.00, 'Ergonomic office chair', 15, 'Home & Office', 'Furniture', 'IKEA', 25, 1, 0, NULL, '2025-08-02 16:32:40', '2025-08-04 12:05:07'),
(14, 12, 'Staples Notebook', 25.00, 35.00, 'Premium A4 notebook', 100, 'Home & Office', 'Stationery', 'Staples', 29, 0, 1, NULL, '2025-08-02 16:32:40', '2025-08-04 12:05:07'),
(15, 12, 'Philips Desk Lamp', 120.00, 180.00, 'LED desk lamp with USB port', 30, 'Home & Office', 'Lighting', 'Philips', 33, 0, 1, NULL, '2025-08-02 16:32:40', '2025-08-04 12:05:07'),
(16, 12, 'Canon Printer', 800.00, 1000.00, 'All-in-one printer scanner', 12, 'Home & Office', 'Electronics', 'Canon', 20, 1, 0, NULL, '2025-08-02 16:32:40', '2025-08-04 12:05:07'),
(17, 13, 'LG Refrigerator', 2800.00, 3500.00, 'Side-by-side refrigerator', 8, 'Appliances', 'Kitchen', 'LG', 20, 1, 0, NULL, '2025-08-02 16:32:40', '2025-08-04 12:05:07'),
(18, 13, 'Samsung Washing Machine', 1800.00, 2200.00, 'Front load washing machine', 10, 'Appliances', 'Laundry', 'Samsung', 18, 0, 1, NULL, '2025-08-02 16:32:40', '2025-08-04 12:05:07'),
(19, 13, 'Bosch Microwave', 450.00, 600.00, 'Convection microwave oven', 20, 'Appliances', 'Kitchen', 'Bosch', 25, 0, 1, NULL, '2025-08-02 16:32:40', '2025-08-04 12:05:07'),
(20, 13, 'Dyson Vacuum', 1200.00, 1500.00, 'Cordless vacuum cleaner', 15, 'Appliances', 'Cleaning', 'Dyson', 20, 1, 0, NULL, '2025-08-02 16:32:40', '2025-08-04 12:05:07');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(500) NOT NULL,
  `image_name` varchar(255) DEFAULT NULL,
  `is_main` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `image_name`, `is_main`, `sort_order`, `created_at`, `updated_at`) VALUES
(3, 3, 'images/phone imgs/tecno-spark 10 pro.jpg', 'Tecno Spark 10 Pro - Main Image', 1, 0, '2025-08-04 10:46:07', '2025-08-04 10:46:07'),
(4, 4, 'images/phone imgs/infinix-note-30.jpg', 'Infinix Note 30 - Main Image', 1, 0, '2025-08-04 10:46:07', '2025-08-04 10:46:07'),
(5, 5, 'images/cloth imgs/nike-air-max.jpg', 'Nike Air Max - Main Image', 1, 0, '2025-08-04 10:46:07', '2025-08-04 10:46:07'),
(6, 6, 'images/cloth imgs/adidas-hoodie.jpg', 'Adidas Hoodie - Main Image', 1, 0, '2025-08-04 10:46:07', '2025-08-04 10:46:07'),
(7, 7, 'images/cloth imgs/levis-jeans.jpg', 'Levi\'s Jeans - Main Image', 1, 0, '2025-08-04 10:46:07', '2025-08-04 10:46:07'),
(8, 8, 'images/cloth imgs/puma-tshirt.jpg', 'Puma T-Shirt - Main Image', 1, 0, '2025-08-04 10:46:07', '2025-08-04 10:46:07'),
(9, 9, 'images/electronics/macbook-pro.jpg', 'MacBook Pro M3 - Main Image', 1, 0, '2025-08-04 10:46:07', '2025-08-04 10:46:07'),
(10, 10, 'images/electronics/sony-headphones.jpg', 'Sony WH-1000XM4 - Main Image', 1, 0, '2025-08-04 10:46:07', '2025-08-04 10:46:07'),
(11, 11, 'images/electronics/samsung-tv.jpg', 'Samsung 4K TV - Main Image', 1, 0, '2025-08-04 10:46:07', '2025-08-04 10:46:07'),
(12, 12, 'images/electronics/jbl-speaker.jpg', 'JBL Flip 6 - Main Image', 1, 0, '2025-08-04 10:46:07', '2025-08-04 10:46:07'),
(13, 13, 'images/home/ikea-chair.jpg', 'IKEA Desk Chair - Main Image', 1, 0, '2025-08-04 10:46:07', '2025-08-04 10:46:07'),
(14, 14, 'images/home/staples-notebook.jpg', 'Staples Notebook - Main Image', 1, 0, '2025-08-04 10:46:07', '2025-08-04 10:46:07'),
(15, 15, 'images/home/philips-lamp.jpg', 'Philips Desk Lamp - Main Image', 1, 0, '2025-08-04 10:46:07', '2025-08-04 10:46:07'),
(16, 16, 'images/home/canon-printer.jpg', 'Canon Printer - Main Image', 1, 0, '2025-08-04 10:46:07', '2025-08-04 10:46:07'),
(17, 17, 'images/appliances/lg-fridge.jpg', 'LG Refrigerator - Main Image', 1, 0, '2025-08-04 10:46:07', '2025-08-04 10:46:07'),
(18, 18, 'images/appliances/samsung-washer.jpg', 'Samsung Washing Machine - Main Image', 1, 0, '2025-08-04 10:46:07', '2025-08-04 10:46:07'),
(19, 19, 'images/appliances/bosch-microwave.jpg', 'Bosch Microwave - Main Image', 1, 0, '2025-08-04 10:46:07', '2025-08-04 10:46:07'),
(20, 20, 'images/appliances/dyson-vacuum.jpg', 'Dyson Vacuum - Main Image', 1, 0, '2025-08-04 10:46:07', '2025-08-04 10:46:07'),
(21, 1, 'images/products/prod_6890993c24bfb3.71137320_0.jpeg', 'prod_6890993c24bfb3.71137320_0.jpeg', 1, 1, '2025-08-04 11:27:56', '2025-08-04 11:46:59'),
(22, 1, 'images/products/prod_6890993c24ca23.61029625_1.jpeg', 'prod_6890993c24ca23.61029625_1.jpeg', 0, 2, '2025-08-04 11:27:56', '2025-08-04 11:27:56'),
(23, 1, 'images/products/prod_6890993c24cd66.58265484_2.jpeg', 'prod_6890993c24cd66.58265484_2.jpeg', 0, 3, '2025-08-04 11:27:56', '2025-08-04 11:27:56'),
(25, 2, 'images/products/prod_68909ca95052a1.97844575_0.jpeg', 'prod_68909ca95052a1.97844575_0.jpeg', 1, 1, '2025-08-04 11:42:33', '2025-08-04 11:46:59');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fname` varchar(50) NOT NULL,
  `lname` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `passwd` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fname`, `lname`, `email`, `phone`, `passwd`, `address`, `created_at`) VALUES
(1, 'BISMARK', 'OTU', 'bismarkotu1006@gmail.com', NULL, '$2y$10$VUNHgSEZ9JuSynjqlKU2E.YGresxaQdRLsy4bfze.GnBSnuy55Gi2', NULL, '2025-08-04 12:26:28');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_admin_email` (`email`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cart_user_id` (`user_id`),
  ADD KEY `idx_cart_product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_categories_active` (`is_active`),
  ADD KEY `idx_categories_sort_order` (`sort_order`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_contact_messages_email` (`email`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_orders_user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_items_order_id` (`order_id`),
  ADD KEY `idx_order_items_product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_products_category` (`category`),
  ADD KEY `idx_products_subcategory` (`subcategory`),
  ADD KEY `idx_products_brand` (`brand`),
  ADD KEY `idx_products_featured` (`is_featured`),
  ADD KEY `idx_products_flash_sale` (`is_flash_sale`),
  ADD KEY `idx_products_category_id` (`category_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_images_product_id` (`product_id`),
  ADD KEY `idx_product_images_is_main` (`is_main`),
  ADD KEY `idx_product_images_sort_order` (`sort_order`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
