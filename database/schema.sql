-- Hero Digital E-commerce Database Schema
-- Created for digital product selling platform

-- Create database
CREATE DATABASE IF NOT EXISTS hero_digital;
USE hero_digital;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    phone VARCHAR(20),
    role ENUM('customer', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);

-- Categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    slug VARCHAR(100) UNIQUE NOT NULL,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);

-- Products table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    short_description VARCHAR(500),
    sku VARCHAR(100) UNIQUE NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    sale_price DECIMAL(10,2),
    category_id INT,
    image_url VARCHAR(255),
    gallery_images JSON,
    file_url VARCHAR(255), -- For digital products
    file_type VARCHAR(50), -- pdf, zip, video, etc.
    file_size BIGINT,
    download_limit INT DEFAULT 5,
    stock_quantity INT DEFAULT 0,
    is_digital BOOLEAN DEFAULT TRUE,
    is_featured BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'inactive', 'draft') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Orders table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'cancelled', 'refunded') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    transaction_id VARCHAR(100),
    billing_address JSON,
    shipping_address JSON,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Order items table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    product_sku VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Cart table
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    session_id VARCHAR(255),
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_item (user_id, product_id),
    UNIQUE KEY unique_session_item (session_id, product_id)
);

-- Downloads table (for digital products)
CREATE TABLE downloads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    download_link VARCHAR(255) NOT NULL,
    downloads_remaining INT NOT NULL,
    expires_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Settings table
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default settings
INSERT INTO settings (setting_key, setting_value, description) VALUES
('site_name', 'Hero Digital', 'Website name'),
('site_email', 'info@herodigital.com', 'Admin email'),
('currency', 'USD', 'Default currency'),
('tax_rate', '0.00', 'Tax rate as decimal'),
('shipping_cost', '0.00', 'Flat shipping cost'),
('downloads_expire_days', '30', 'Days until download links expire');

-- Insert sample categories
INSERT INTO categories (name, description, slug) VALUES
('Software', 'Digital software and applications', 'software'),
('E-books', 'Digital books and guides', 'ebooks'),
('Templates', 'Website templates and designs', 'templates'),
('Courses', 'Online courses and tutorials', 'courses'),
('Graphics', 'Digital graphics and assets', 'graphics');

-- Insert sample products
INSERT INTO products (name, description, short_description, sku, price, category_id, image_url, is_digital, is_featured) VALUES
('Hero CRM Pro', 'Complete customer relationship management system for small businesses', 'Professional CRM software with advanced features', 'HERO-CRM-001', 299.99, 1, '/images/crm-pro.jpg', TRUE, TRUE),
('E-commerce Website Template', 'Modern responsive e-commerce website template', 'Beautiful and functional online store template', 'HERO-TEMP-001', 149.99, 3, '/images/ecom-template.jpg', TRUE, TRUE),
('Digital Marketing Guide', 'Complete guide to digital marketing strategies', 'Comprehensive marketing e-book', 'HERO-EBOOK-001', 49.99, 2, '/images/marketing-guide.jpg', TRUE, FALSE),
('Web Development Course', 'Full stack web development online course', 'Learn modern web development', 'HERO-COURSE-001', 199.99, 4, '/images/web-dev-course.jpg', TRUE, FALSE),
('Logo Design Pack', 'Professional logo design templates pack', '100+ premium logo templates', 'HERO-GRAPH-001', 79.99, 5, '/images/logo-pack.jpg', TRUE, FALSE);

-- Create admin user (password: admin123)
INSERT INTO users (username, email, password_hash, first_name, last_name, role) VALUES
('admin', 'admin@herodigital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'admin');

-- Create indexes for better performance
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_status ON products(status);
CREATE INDEX idx_orders_user ON orders(user_id);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_cart_user ON cart(user_id);
CREATE INDEX idx_cart_session ON cart(session_id);
