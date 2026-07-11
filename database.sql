-- EatSmart Restaurant Management System
-- Database: eatsmart

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('customer','admin','kitchen') DEFAULT 'customer',
    loyalty_points INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Menu categories
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    icon VARCHAR(10) DEFAULT NULL,
    sort_order INT DEFAULT 0
);

-- Menu items
CREATE TABLE menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(8,2) NOT NULL,
    image_url VARCHAR(255) DEFAULT '',
    is_available TINYINT(1) DEFAULT 1,
    is_popular TINYINT(1) DEFAULT 0,
    prep_time INT DEFAULT 15,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Tables
CREATE TABLE restaurant_tables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_number VARCHAR(10) NOT NULL,
    capacity INT DEFAULT 4,
    location VARCHAR(50) DEFAULT 'Main Hall',
    is_active TINYINT(1) DEFAULT 1
);

-- Reservations
CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    table_id INT,
    reservation_date DATE NOT NULL,
    time_slot VARCHAR(20) NOT NULL,
    guests INT DEFAULT 2,
    special_requests TEXT,
    status ENUM('pending','confirmed','cancelled','completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (table_id) REFERENCES restaurant_tables(id) ON DELETE SET NULL
);

-- Orders
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    reservation_id INT DEFAULT NULL,
    table_number VARCHAR(10) DEFAULT NULL,
    order_type ENUM('dine-in','pre-order','takeaway') DEFAULT 'dine-in',
    status ENUM('pending','accepted','cooking','ready','delivered','cancelled') DEFAULT 'pending',
    total_amount DECIMAL(10,2) DEFAULT 0,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    special_instructions TEXT,
    estimated_time INT DEFAULT 20,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE SET NULL
);

-- Order items
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    quantity INT DEFAULT 1,
    unit_price DECIMAL(8,2) NOT NULL,
    notes TEXT,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE
);

-- Waste deals
CREATE TABLE waste_deals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    menu_item_id INT NOT NULL,
    original_price DECIMAL(8,2) NOT NULL,
    discounted_price DECIMAL(8,2) NOT NULL,
    quantity_available INT DEFAULT 1,
    expires_at TIMESTAMP NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE
);

-- Reviews
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    menu_item_id INT,
    order_id INT,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE
);

-- Chatbot logs
CREATE TABLE chatbot_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    message TEXT,
    response TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================
-- SEED DATA
-- =====================

INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@eatsmart.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Kitchen Staff', 'kitchen@eatsmart.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'kitchen'),
('Mahesh Yadav', 'mahesh@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer'),
('Priya Singh', 'priya@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer');
-- Default password for all: "password"

INSERT INTO categories (name, icon, sort_order) VALUES
('Starters', '🥗', 1),
('Main Course', '🍛', 2),
('Breads', '🫓', 3),
('Desserts', '🍮', 4),
('Beverages', '🥤', 5),
('Rice & Biryani', '🍚', 6);

INSERT INTO menu_items (category_id, name, description, price, image_url, is_available, is_popular, prep_time) VALUES
(1, 'Paneer Tikka', 'Marinated cottage cheese grilled in tandoor with spices', 220.00,'images/menu/paneer_tikka.jpg', 1, 1, 15),
(1, 'Chicken 65', 'Spicy deep-fried chicken with curry leaves', 280.00, 'images/menu/chicken_65.jpg', 1, 1, 20),
(1, 'Veg Spring Rolls', 'Crispy rolls stuffed with seasoned vegetables', 160.00, 'images/menu/veg_spring_rolls.jpg', 1, 0, 10),
(1, 'Seekh Kebab', 'Minced lamb on skewers, smoky and tender', 320.00, 'images/menu/seekh_kebab.jpg', 1, 0, 25),
(2, 'Butter Chicken', 'Tender chicken in rich tomato-butter gravy', 380.00, 'images/menu/butter_chicken.jpg', 1, 1, 25),
(2, 'Dal Makhani', 'Slow-cooked black lentils in creamy butter sauce', 280.00, 'images/menu/dal_makhani.jpg', 1, 1, 20),
(2, 'Paneer Butter Masala', 'Cottage cheese cubes in smooth creamy gravy', 320.00, 'images/menu/paneer_butter_masala.jpg', 1, 1, 20),
(2, 'Mutton Rogan Josh', 'Slow-cooked lamb in aromatic Kashmiri spices', 480.00, 'images/menu/mutton_rogan_josh.jpg', 1, 0, 40),
(2, 'Palak Paneer', 'Cottage cheese in smooth spinach gravy', 300.00, 'images/menu/palak_paneer.jpg', 1, 0, 20),
(3, 'Butter Naan', 'Soft leavened bread with butter', 60.00, 'images/menu/butter_naan.jpg', 1, 1, 8),
(3, 'Garlic Naan', 'Naan topped with garlic and herbs', 70.00, 'images/menu/garlic_naan.jpg', 1, 1, 8),
(3, 'Tandoori Roti', 'Whole wheat bread from tandoor', 40.00, 'images/menu/tandoori_roti.jpg', 1, 0, 8),
(4, 'Gulab Jamun', 'Soft milk dumplings in rose sugar syrup', 120.00, 'images/menu/gulab_jamun.jpg', 1, 1, 5),
(4, 'Rasgulla', 'Light cottage cheese balls in light syrup', 110.00, 'images/menu/rasgulla.jpg', 1, 0, 5),
(4, 'Kulfi', 'Traditional Indian ice cream with pistachios', 130.00, 'images/menu/kulfi.jpg', 1, 1, 2),
(5, 'Mango Lassi', 'Creamy yogurt drink blended with alphonso mango', 120.00, 'images/menu/mango_lassi.jpg', 1, 1, 3),
(5, 'Masala Chai', 'Spiced Indian tea with ginger and cardamom', 60.00, 'images/menu/masala_chai.jpg', 1, 1, 5),
(5, 'Fresh Lime Soda', 'Chilled lime juice with soda', 80.00, 'images/menu/fresh_lime_soda.jpg', 1, 0, 3),
(6, 'Chicken Biryani', 'Fragrant basmati rice with spiced chicken layers', 420.00, 'images/menu/chicken_biryani.jpg', 1, 1, 35),
(6, 'Veg Biryani', 'Aromatic basmati with mixed vegetables', 320.00, 'images/menu/veg_biryani.jpg', 1, 0, 30),
(6, 'Egg Fried Rice', 'Wok-tossed rice with eggs and vegetables', 220.00, 'images/menu/egg_fried_rice.jpg', 1, 0, 15);


INSERT INTO restaurant_tables (table_number, capacity, location) VALUES
('T01', 2, 'Window Side'),
('T02', 2, 'Window Side'),
('T03', 4, 'Main Hall'),
('T04', 4, 'Main Hall'),
('T05', 4, 'Main Hall'),
('T06', 6, 'Main Hall'),
('T07', 6, 'Garden'),
('T08', 8, 'Private Room'),
('T09', 4, 'Garden'),
('T10', 2, 'Bar Side');

INSERT INTO orders (user_id, order_type, status, total_amount, created_at) VALUES
(3, 'dine-in', 'delivered', 860.00, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(4, 'pre-order', 'delivered', 540.00, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(3, 'dine-in', 'delivered', 1200.00, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(4, 'takeaway', 'delivered', 380.00, NOW()),
(3, 'dine-in', 'cooking', 760.00, NOW());

INSERT INTO order_items (order_id, menu_item_id, quantity, unit_price) VALUES
(1, 5, 1, 380.00),(1, 10, 2, 60.00),(1, 16, 2, 120.00),(1, 13, 1, 120.00),
(2, 7, 1, 320.00),(2, 10, 1, 60.00),(2, 16, 1, 120.00),(2, 4, 1, 40.00),
(3, 19, 2, 420.00),(3, 1, 2, 220.00),(3, 17, 2, 60.00),(3, 15, 2, 130.00),
(4, 5, 1, 380.00),
(5, 19, 1, 420.00),(5, 5, 1, 380.00);

INSERT INTO waste_deals (menu_item_id, original_price, discounted_price, quantity_available, expires_at, is_active) VALUES
(8, 480.00, 280.00, 3, DATE_ADD(NOW(), INTERVAL 3 HOUR), 1),
(19, 420.00, 250.00, 2, DATE_ADD(NOW(), INTERVAL 2 HOUR), 1),
(4, 320.00, 180.00, 4, DATE_ADD(NOW(), INTERVAL 4 HOUR), 1);

INSERT INTO reservations (user_id, table_id, reservation_date, time_slot, guests, status) VALUES
(3, 3, CURDATE(), '19:00', 3, 'confirmed'),
(4, 6, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '20:00', 5, 'pending');
