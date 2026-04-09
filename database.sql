CREATE DATABASE IF NOT EXISTS food_delivery;

USE food_delivery;

DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart;
DROP TABLE IF EXISTS food_items;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE food_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    description VARCHAR(255) NOT NULL,
    image VARCHAR(30) NOT NULL,
    price DECIMAL(10,2) NOT NULL
);

CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    food_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    UNIQUE KEY unique_cart_item (user_id, food_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (food_id) REFERENCES food_items(id) ON DELETE CASCADE
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    delivery_fee DECIMAL(10,2) NOT NULL,
    tax_amount DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'Placed',
    delivery_address TEXT NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    delivery_zone VARCHAR(50) NOT NULL,
    distance_km DECIMAL(6,2) NOT NULL,
    estimated_delivery_minutes INT NOT NULL,
    actual_delivery_minutes INT NULL,
    prep_time_minutes INT NOT NULL,
    traffic_level VARCHAR(20) NOT NULL,
    weather_condition VARCHAR(20) NOT NULL,
    customer_rating DECIMAL(3,1) NULL,
    special_instructions VARCHAR(255) DEFAULT '',
    delivered_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    food_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (food_id) REFERENCES food_items(id) ON DELETE CASCADE
);

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_id INT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(140) NOT NULL,
    message VARCHAR(255) NOT NULL,
    severity VARCHAR(20) NOT NULL DEFAULT 'info',
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

INSERT INTO users (name, email, password, role, created_at) VALUES
('QuickBite Admin', 'admin@quickbite.test', 'ADMIN123_SEEDED', 'admin', '2025-01-01 09:00:00'),
('Aarav Sharma', 'aarav@quickbite.test', 'SEEDED_USER', 'customer', '2025-10-12 10:15:00'),
('Isha Verma', 'isha@quickbite.test', 'SEEDED_USER', 'customer', '2025-11-05 11:20:00'),
('Meera Nair', 'meera@quickbite.test', 'SEEDED_USER', 'customer', '2025-09-21 18:05:00'),
('Rahul Mehta', 'rahul@quickbite.test', 'SEEDED_USER', 'customer', '2025-12-01 14:30:00'),
('Zoya Khan', 'zoya@quickbite.test', 'SEEDED_USER', 'customer', '2025-08-18 16:45:00');

INSERT INTO food_items (name, category, description, image, price) VALUES
('Margherita Pizza', 'Pizza', 'Stone-baked pizza with tomato, basil, and mozzarella.', 'PIZZA', 249.00),
('Farmhouse Pizza', 'Pizza', 'Loaded with onion, capsicum, tomato, and sweet corn.', 'PIZZA', 319.00),
('Smoky Burger', 'Burger', 'Grilled veg patty, house sauce, and caramelized onions.', 'BURGER', 189.00),
('Crispy Chicken Burger', 'Burger', 'Crunchy chicken fillet with lettuce and chili mayo.', 'BURGER', 229.00),
('White Sauce Pasta', 'Pasta', 'Creamy penne pasta finished with herbs and garlic.', 'PASTA', 209.00),
('Arrabbiata Pasta', 'Pasta', 'Spicy tomato pasta with olives and parmesan.', 'PASTA', 219.00),
('Club Sandwich', 'Sandwich', 'Triple-layer sandwich with fresh salad and sauce.', 'SANDWICH', 159.00),
('Paneer Wrap', 'Wraps', 'Soft tortilla packed with paneer tikka and crunchy veggies.', 'WRAP', 179.00),
('Peri Peri Fries', 'Sides', 'Crispy fries dusted with smoky peri peri seasoning.', 'SIDES', 119.00),
('Chocolate Shake', 'Drinks', 'Thick shake blended with cocoa and vanilla ice cream.', 'DRINK', 149.00),
('Lemon Iced Tea', 'Drinks', 'Fresh brewed tea with lemon and mint.', 'DRINK', 99.00),
('Brownie Sundae', 'Dessert', 'Warm brownie with ice cream and chocolate sauce.', 'DESSERT', 169.00);

INSERT INTO orders (
    id, user_id, subtotal, delivery_fee, tax_amount, total, status, delivery_address,
    payment_method, delivery_zone, distance_km, estimated_delivery_minutes, actual_delivery_minutes,
    prep_time_minutes, traffic_level, weather_condition, customer_rating, special_instructions,
    delivered_at, created_at
) VALUES
(1, 2, 666.00, 49.00, 33.30, 748.30, 'Delivered', '12 Green Residency, Indiranagar, Bengaluru', 'Cash on Delivery', 'Central', 3.20, 24, 26, 12, 'Medium', 'Clear', 4.8, 'Leave at security desk', '2026-02-20 13:31:00', '2026-02-20 13:05:00'),
(2, 3, 547.00, 39.00, 27.35, 613.35, 'Delivered', '44 Lake View Apartments, Koramangala, Bengaluru', 'UPI', 'South', 6.80, 32, 36, 16, 'High', 'Clear', 4.4, 'Call on arrival', '2026-02-22 20:51:00', '2026-02-22 20:15:00'),
(3, 4, 656.00, 59.00, 32.80, 747.80, 'Delivered', '9 Metro Park, Rajajinagar, Bengaluru', 'Card on Delivery', 'West', 7.50, 38, 44, 18, 'High', 'Clear', 4.1, 'Extra napkins please', '2026-02-24 19:54:00', '2026-02-24 19:10:00'),
(4, 5, 397.00, 39.00, 19.85, 455.85, 'Delivered', '82 Orchid Enclave, MG Road, Bengaluru', 'Wallet', 'Central', 3.80, 24, 23, 12, 'Medium', 'Clear', 4.7, 'Do not ring bell', '2026-02-27 12:48:00', '2026-02-27 12:25:00'),
(5, 6, 538.00, 49.00, 26.90, 613.90, 'Delivered', '21 Horizon Flats, Whitefield, Bengaluru', 'UPI', 'East', 5.00, 35, 39, 15, 'High', 'Clear', 4.3, 'Deliver to lobby', '2026-03-01 21:39:00', '2026-03-01 21:00:00'),
(6, 2, 636.00, 59.00, 31.80, 726.80, 'Delivered', '12 Green Residency, Indiranagar, Bengaluru', 'Cash on Delivery', 'North', 6.10, 40, 42, 17, 'High', 'Cloudy', 4.2, 'Add extra ketchup', '2026-03-03 19:17:00', '2026-03-03 18:35:00'),
(7, 3, 577.00, 49.00, 28.85, 654.85, 'Delivered', '44 Lake View Apartments, Koramangala, Bengaluru', 'Wallet', 'South', 5.60, 28, 32, 14, 'Medium', 'Clear', 4.5, 'Guard has visitor pass', '2026-03-05 14:17:00', '2026-03-05 13:45:00'),
(8, 4, 636.00, 69.00, 31.80, 736.80, 'Delivered', '9 Metro Park, Rajajinagar, Bengaluru', 'Card on Delivery', 'West', 7.80, 41, 44, 18, 'High', 'Rain', 4.0, 'Use side entrance', '2026-03-08 21:04:00', '2026-03-08 20:20:00'),
(9, 5, 607.00, 59.00, 30.35, 696.35, 'Out for Delivery', '82 Orchid Enclave, MG Road, Bengaluru', 'UPI', 'East', 5.70, 39, NULL, 16, 'High', 'Clear', NULL, 'Please call first', NULL, '2026-03-15 19:15:00'),
(10, 6, 527.00, 49.00, 26.35, 602.35, 'Preparing', '21 Horizon Flats, Whitefield, Bengaluru', 'Cash on Delivery', 'Central', 3.60, 27, NULL, 13, 'Medium', 'Clear', NULL, 'Less ice in drinks', NULL, '2026-03-16 12:40:00');

INSERT INTO order_items (order_id, food_id, quantity, price) VALUES
(1, 1, 1, 249.00),
(1, 9, 1, 119.00),
(1, 10, 2, 149.00),
(2, 3, 1, 189.00),
(2, 5, 1, 209.00),
(2, 10, 1, 149.00),
(3, 4, 1, 229.00),
(3, 5, 1, 209.00),
(3, 9, 1, 119.00),
(3, 11, 1, 99.00),
(4, 8, 1, 179.00),
(4, 9, 1, 119.00),
(4, 11, 1, 99.00),
(5, 2, 1, 319.00),
(5, 6, 1, 219.00),
(6, 7, 2, 159.00),
(6, 10, 1, 149.00),
(6, 12, 1, 169.00),
(7, 1, 1, 249.00),
(7, 4, 1, 229.00),
(7, 11, 1, 99.00),
(8, 3, 1, 189.00),
(8, 8, 1, 179.00),
(8, 9, 1, 119.00),
(8, 10, 1, 149.00),
(9, 2, 1, 319.00),
(9, 9, 1, 119.00),
(9, 12, 1, 169.00),
(10, 5, 1, 209.00),
(10, 6, 1, 219.00),
(10, 11, 1, 99.00);
