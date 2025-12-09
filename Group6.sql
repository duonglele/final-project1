USE final_project;
CREATE TABLE users (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    created_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Bảng categories
CREATE TABLE categories (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT
);

-- DỮ LIỆU MẪU CHO THIẾT BỊ ĐIỆN TỬ
INSERT INTO categories (name) VALUES 
('Điện thoại & Máy tính bảng'), 
('Laptop & Máy tính bàn'), 
('Linh kiện Điện tử'), 
('Phụ kiện & Thiết bị ngoại vi');

-- 3. Bảng products
CREATE TABLE products (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(50) UNIQUE,
    price DECIMAL(10,2) NOT NULL,
    quantity INT(11) NOT NULL,
    category_id INT(11),
    image VARCHAR(255),
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- 4. Bảng orders
CREATE TABLE orders (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    user_id INT(11),
    total DECIMAL(10,2),
    status VARCHAR(50) DEFAULT 'Pending',
    created_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- 5. Bảng order_items
CREATE TABLE order_items (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    order_id INT(11),
    product_id INT(11),
    price DECIMAL(10,2),
    quantity INT(11),
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- 6. Bảng carts
CREATE TABLE carts (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    user_id INT(11),
    created_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- 7. Bảng cart_items
CREATE TABLE cart_items (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    cart_id INT(11),
    product_id INT(11),
    quantity INT(11),
    FOREIGN KEY (cart_id) REFERENCES carts(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);
