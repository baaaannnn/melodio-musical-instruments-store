-- Portfolio copy: user and order records removed; product seed retained.
-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 16, 2026 at 04:16 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `melodiop3`
--

DELIMITER $$
--
-- Procedures
--
CREATE PROCEDURE `sp_AddOrderItem` (IN `p_order_id` INT, IN `p_product_id` INT, IN `p_quantity` INT, IN `p_price` DECIMAL(10,2))   BEGIN
    INSERT INTO order_items (order_id, product_id, quantity, price)
    VALUES (p_order_id, p_product_id, p_quantity, p_price);
    SELECT LAST_INSERT_ID() AS new_id;
END$$

CREATE PROCEDURE `sp_AddProduct` (IN `p_name` VARCHAR(100), IN `p_description` TEXT, IN `p_price` DECIMAL(10,2), IN `p_stock` INT, IN `p_category` VARCHAR(50), IN `p_image` VARCHAR(255))   BEGIN
    INSERT INTO products (name, description, price, stock, category, image)
    VALUES (p_name, p_description, p_price, p_stock, p_category, p_image);
    SELECT LAST_INSERT_ID() AS new_id;
END$$

CREATE PROCEDURE `sp_CheckStock` (IN `p_id` INT)   BEGIN
    SELECT stock FROM products WHERE id = p_id;
END$$

CREATE PROCEDURE `sp_CreateOrder` (IN `p_user_id` INT, IN `p_phone` VARCHAR(20))   BEGIN
    INSERT INTO orders (user_id, phone, status)
    VALUES (p_user_id, p_phone, 'pending');
    SELECT LAST_INSERT_ID() AS order_id;
END$$

CREATE PROCEDURE `sp_DecreaseStock` (IN `p_id` INT, IN `p_quantity` INT)   BEGIN
    UPDATE products
    SET stock = stock - p_quantity
    WHERE id = p_id AND stock >= p_quantity;
    SELECT ROW_COUNT() AS affected;
END$$

CREATE PROCEDURE `sp_DeleteProduct` (IN `p_id` INT)   BEGIN
    DELETE FROM products WHERE id = p_id;
    SELECT ROW_COUNT() AS affected;
END$$

CREATE PROCEDURE `sp_EmailExists` (IN `p_email` VARCHAR(100))   BEGIN
    SELECT COUNT(*) AS count
    FROM users
    WHERE email = p_email;
END$$

CREATE PROCEDURE `sp_GetAllOrders` ()   BEGIN
    SELECT o.*, u.full_name
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC;
END$$

CREATE PROCEDURE `sp_GetAllProducts` ()   BEGIN
    SELECT * FROM products
    ORDER BY created_at DESC;
END$$

CREATE PROCEDURE `sp_GetAllUsers` ()   BEGIN
    SELECT id, full_name, email, role, created_at
    FROM users
    ORDER BY created_at DESC;
END$$

CREATE PROCEDURE `sp_GetOrderById` (IN `p_id` INT)   BEGIN
    SELECT o.*, u.full_name
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.id = p_id;
END$$

CREATE PROCEDURE `sp_GetOrderItems` (IN `p_order_id` INT)   BEGIN
    SELECT oi.*, p.name, p.image
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = p_order_id;
END$$

CREATE PROCEDURE `sp_GetOrdersByUser` (IN `p_user_id` INT)   BEGIN
    SELECT * FROM orders
    WHERE user_id = p_user_id
    ORDER BY created_at DESC;
END$$

CREATE PROCEDURE `sp_GetOrderTotal` (IN `p_order_id` INT)   BEGIN
    SELECT COALESCE(SUM(quantity * price), 0) AS total
    FROM order_items
    WHERE order_id = p_order_id;
END$$

CREATE PROCEDURE `sp_GetProductById` (IN `p_id` INT)   BEGIN
    SELECT * FROM products
    WHERE id = p_id;
END$$

CREATE PROCEDURE `sp_GetProductsByCategory` (IN `p_category` VARCHAR(50))   BEGIN
    SELECT * FROM products
    WHERE category = p_category
    ORDER BY created_at DESC;
END$$

CREATE PROCEDURE `sp_GetUserById` (IN `p_id` INT)   BEGIN
    SELECT id, full_name, email, role, created_at
    FROM users
    WHERE id = p_id;
END$$

CREATE PROCEDURE `sp_LoginUser` (IN `p_email` VARCHAR(100))   BEGIN
    SELECT id, full_name, email, password, role
    FROM users
    WHERE email = p_email
    LIMIT 1;
END$$

CREATE PROCEDURE `sp_RegisterUser` (IN `p_full_name` VARCHAR(100), IN `p_email` VARCHAR(100), IN `p_password` VARCHAR(255))   BEGIN
    INSERT INTO users (full_name, email, password, role)
    VALUES (p_full_name, p_email, p_password, 'customer');
    SELECT LAST_INSERT_ID() AS new_id;
END$$

CREATE PROCEDURE `sp_UpdateOrderStatus` (IN `p_order_id` INT, IN `p_status` VARCHAR(20))   BEGIN
    UPDATE orders SET status = p_status WHERE id = p_order_id;
    SELECT ROW_COUNT() AS affected;
END$$

CREATE PROCEDURE `sp_UpdateProduct` (IN `p_id` INT, IN `p_name` VARCHAR(100), IN `p_description` TEXT, IN `p_price` DECIMAL(10,2), IN `p_stock` INT, IN `p_category` VARCHAR(50), IN `p_image` VARCHAR(255))   BEGIN
    UPDATE products
    SET name=p_name, description=p_description, price=p_price,
        stock=p_stock, category=p_category, image=p_image
    WHERE id = p_id;
    SELECT ROW_COUNT() AS affected;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `status` enum('pending','picked_up','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `category` varchar(50) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `stock`, `category`, `image`, `created_at`) VALUES
(1, 'Acoustic Guitar', 'Classic acoustic guitar with a warm, natural tone. Ideal for everyday practice and live performances.', 199.99, 4, 'Guitars', 'acoustic_guitar.jpg', '2026-05-02 08:39:07'),
(2, 'Fender Player Guitar', 'Professional Fender electric guitar, perfect for rock and blues.', 369.99, 6, 'Guitars', 'fender_guitar.jpg', '2026-05-02 08:39:07'),
(3, 'Digital Guitar', 'Compact digital guitar with built-in effects and amp simulation.', 259.99, 0, 'Guitars', 'digital_guitar.jpg', '2026-05-02 08:39:07'),
(4, 'Conga Drum', 'Traditional conga drum with rich, deep sound.', 199.99, 7, 'Drums', 'conga_drum.jpg', '2026-05-02 08:39:07'),
(5, 'Digital Piano', 'Full-size digital piano with weighted keys and realistic sound.', 499.99, 0, 'Keyboards', 'digital_piano.jpg', '2026-05-02 08:39:07');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','admin','delivery') NOT NULL DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

--
-- Indexes for dumped tables
--

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- Constraints for dumped tables
--

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
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
