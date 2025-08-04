-- Product Images Pivot Table
-- This table allows one product to have multiple images

CREATE TABLE IF NOT EXISTS product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_path VARCHAR(500) NOT NULL,
    image_name VARCHAR(255),
    is_main BOOLEAN DEFAULT FALSE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Add indexes for better performance
CREATE INDEX idx_product_images_product_id ON product_images(product_id);
CREATE INDEX idx_product_images_is_main ON product_images(is_main);
CREATE INDEX idx_product_images_sort_order ON product_images(sort_order);

-- Insert sample data for existing products (assuming they have images)
-- This will create entries for existing products with their current file_path
INSERT INTO product_images (product_id, image_path, image_name, is_main, sort_order)
SELECT 
    id as product_id,
    file_path as image_path,
    CONCAT(name, ' - Main Image') as image_name,
    TRUE as is_main,
    0 as sort_order
FROM products 
WHERE file_path IS NOT NULL AND file_path != '';

-- Update the products table to keep file_path for backward compatibility
-- But now it will reference the main image from product_images table
ALTER TABLE products ADD COLUMN main_image_id INT NULL;
ALTER TABLE products ADD FOREIGN KEY (main_image_id) REFERENCES product_images(id) ON DELETE SET NULL;

-- Update main_image_id for existing products
UPDATE products p 
JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = TRUE
SET p.main_image_id = pi.id; 