-- Unified Kings Reign E-commerce Database Schema
-- Single products table instead of separate clothes and phones tables

-- Create database
CREATE DATABASE IF NOT EXISTS kings_reign;
USE kings_reign;

-- Users table (for customer accounts)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fname VARCHAR(50) NOT NULL,
    lname VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    passwd VARCHAR(255) NOT NULL,
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Admin table (for admin accounts)
CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    passwd VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Unified Products table (replaces phones and clothes tables)
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    original_price DECIMAL(10,2),
    description TEXT,
    file_path VARCHAR(500) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    category VARCHAR(100) NOT NULL,
    subcategory VARCHAR(100),
    brand VARCHAR(100),
    discount_percentage INT DEFAULT 0,
    is_featured BOOLEAN DEFAULT FALSE,
    is_flash_sale BOOLEAN DEFAULT FALSE,
    flash_sale_end TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Cart table (for shopping cart)
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Orders table (for completed orders)
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    shipping_address TEXT,
    phone_number VARCHAR(20),
    payment_method VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order items table (for individual items in orders)
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Contact messages table (for customer support)
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    reply TEXT,
    replied_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin account
-- Password: admin123 (hashed)
INSERT INTO admin (name, email, passwd) VALUES 
('Admin', 'admin@kingsreign.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Sample products data
INSERT INTO products (name, price, original_price, description, file_path, stock, category, subcategory, brand, discount_percentage, is_featured, is_flash_sale) VALUES
-- Smartphones
('iPhone 15 Pro', 8500.00, 9500.00, 'Latest iPhone with advanced features', 'images/phone imgs/iphone-15-pro.jpg', 15, 'Electronics', 'Smartphones', 'Apple', 11, TRUE, FALSE),
('Samsung Galaxy S24', 6500.00, 7500.00, 'Premium Android smartphone', 'images/phone imgs/samsung-galaxy-s24.jpg', 12, 'Electronics', 'Smartphones', 'Samsung', 13, TRUE, FALSE),
('Tecno Spark 10 Pro', 1200.00, 1500.00, 'Latest Tecno smartphone', 'images/phone imgs/tecno-spark 10 pro.jpg', 25, 'Electronics', 'Smartphones', 'Tecno', 20, FALSE, TRUE),
('Infinix Note 30', 1800.00, 2200.00, 'Powerful performance phone', 'images/phone imgs/infinix-note-30.jpg', 18, 'Electronics', 'Smartphones', 'Infinix', 18, FALSE, TRUE),

-- Fashion
('Nike Air Max', 450.00, 600.00, 'Comfortable running shoes', 'images/cloth imgs/nike-air-max.jpg', 30, 'Fashion', 'Shoes', 'Nike', 25, TRUE, FALSE),
('Adidas Hoodie', 180.00, 250.00, 'Warm and stylish hoodie', 'images/cloth imgs/adidas-hoodie.jpg', 40, 'Fashion', 'Clothing', 'Adidas', 28, FALSE, TRUE),
('Levi\'s Jeans', 220.00, 300.00, 'Classic blue jeans', 'images/cloth imgs/levis-jeans.jpg', 35, 'Fashion', 'Clothing', 'Levi\'s', 27, TRUE, FALSE),
('Puma T-Shirt', 80.00, 120.00, 'Comfortable cotton t-shirt', 'images/cloth imgs/puma-tshirt.jpg', 50, 'Fashion', 'Clothing', 'Puma', 33, FALSE, TRUE),

-- Electronics
('MacBook Pro M3', 25000.00, 28000.00, 'Professional laptop for work', 'images/electronics/macbook-pro.jpg', 8, 'Electronics', 'Computers', 'Apple', 11, TRUE, FALSE),
('Sony WH-1000XM4', 1200.00, 1500.00, 'Premium noise-canceling headphones', 'images/electronics/sony-headphones.jpg', 20, 'Electronics', 'Audio', 'Sony', 20, FALSE, TRUE),
('Samsung 4K TV', 3500.00, 4500.00, '55-inch 4K Smart TV', 'images/electronics/samsung-tv.jpg', 10, 'Electronics', 'TVs', 'Samsung', 22, TRUE, FALSE),
('JBL Flip 6', 350.00, 450.00, 'Portable Bluetooth speaker', 'images/electronics/jbl-speaker.jpg', 25, 'Electronics', 'Audio', 'JBL', 22, FALSE, TRUE),

-- Home & Office
('IKEA Desk Chair', 450.00, 600.00, 'Ergonomic office chair', 'images/home/ikea-chair.jpg', 15, 'Home & Office', 'Furniture', 'IKEA', 25, TRUE, FALSE),
('Staples Notebook', 25.00, 35.00, 'Premium A4 notebook', 'images/home/staples-notebook.jpg', 100, 'Home & Office', 'Stationery', 'Staples', 29, FALSE, TRUE),
('Philips Desk Lamp', 120.00, 180.00, 'LED desk lamp with USB port', 'images/home/philips-lamp.jpg', 30, 'Home & Office', 'Lighting', 'Philips', 33, FALSE, TRUE),
('Canon Printer', 800.00, 1000.00, 'All-in-one printer scanner', 'images/home/canon-printer.jpg', 12, 'Home & Office', 'Electronics', 'Canon', 20, TRUE, FALSE),

-- Appliances
('LG Refrigerator', 2800.00, 3500.00, 'Side-by-side refrigerator', 'images/appliances/lg-fridge.jpg', 8, 'Appliances', 'Kitchen', 'LG', 20, TRUE, FALSE),
('Samsung Washing Machine', 1800.00, 2200.00, 'Front load washing machine', 'images/appliances/samsung-washer.jpg', 10, 'Appliances', 'Laundry', 'Samsung', 18, FALSE, TRUE),
('Bosch Microwave', 450.00, 600.00, 'Convection microwave oven', 'images/appliances/bosch-microwave.jpg', 20, 'Appliances', 'Kitchen', 'Bosch', 25, FALSE, TRUE),
('Dyson Vacuum', 1200.00, 1500.00, 'Cordless vacuum cleaner', 'images/appliances/dyson-vacuum.jpg', 15, 'Appliances', 'Cleaning', 'Dyson', 20, TRUE, FALSE);

-- Create indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_admin_email ON admin(email);
CREATE INDEX idx_products_category ON products(category);
CREATE INDEX idx_products_subcategory ON products(subcategory);
CREATE INDEX idx_products_brand ON products(brand);
CREATE INDEX idx_products_featured ON products(is_featured);
CREATE INDEX idx_products_flash_sale ON products(is_flash_sale);
CREATE INDEX idx_cart_user_id ON cart(user_id);
CREATE INDEX idx_cart_product_id ON cart(product_id);
CREATE INDEX idx_orders_user_id ON orders(user_id);
CREATE INDEX idx_order_items_order_id ON order_items(order_id);
CREATE INDEX idx_order_items_product_id ON order_items(product_id);
CREATE INDEX idx_contact_messages_email ON contact_messages(email); 