-- Kings Reign E-commerce Database Schema
-- MySQL compatible syntax for XAMPP/LAMPP environment
-- Run this in phpMyAdmin or MySQL command line

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

-- Phones table (for phone products)
CREATE TABLE IF NOT EXISTS phones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    file_path VARCHAR(500) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    category VARCHAR(100) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Clothes table (for clothing products)
CREATE TABLE IF NOT EXISTS clothes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    file_path VARCHAR(500) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    category VARCHAR(100) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cart table (for shopping cart)
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id VARCHAR(255) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Orders table (for completed orders)
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order items table (for individual items in orders)
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
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
('Moses Otu ADMIN', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Sample data for phones (optional)
INSERT INTO phones (name, price, description, file_path, stock, category) VALUES
('Tecno Spark 10 Pro', 1200.00, 'Latest Tecno smartphone with advanced features', 'images/phone imgs/tecno-spark 10 pro.jpg', 15, 'Tecno'),
('iPhone 15 Pro', 8500.00, 'Apple\'s flagship smartphone', 'images/phone imgs/iphone-15-pro.jpg', 8, 'iPhone'),
('Samsung Galaxy S24', 6500.00, 'Premium Android smartphone', 'images/phone imgs/samsung-galaxy-s24.jpg', 12, 'Samsung');

-- Sample data for clothes (optional)
INSERT INTO clothes (name, price, description, file_path, stock, category) VALUES
('Nike Air Max', 450.00, 'Comfortable running shoes', 'images/cloth imgs/nike-air-max.jpg', 25, 'Shoes'),
('Adidas Hoodie', 180.00, 'Warm and stylish hoodie', 'images/cloth imgs/adidas-hoodie.jpg', 30, 'Hoodies'),
('Levi\'s Jeans', 220.00, 'Classic blue jeans', 'images/cloth imgs/levis-jeans.jpg', 20, 'Jeans');

-- Create indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_admin_email ON admin(email);
CREATE INDEX idx_phones_category ON phones(category);
CREATE INDEX idx_clothes_category ON clothes(category);
CREATE INDEX idx_cart_user_id ON cart(user_id);
CREATE INDEX idx_orders_user_id ON orders(user_id);
CREATE INDEX idx_order_items_order_id ON order_items(order_id);
CREATE INDEX idx_contact_messages_email ON contact_messages(email); 