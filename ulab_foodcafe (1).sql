-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 19, 2025 at 08:37 AM
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
-- Database: `ulab_foodcafe`
--

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `menu_item_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `user_id`, `order_id`, `menu_item_id`, `rating`, `comment`, `created_at`) VALUES
(1, 4, 2, NULL, 4, 'It was average', '2025-12-19 06:57:40'),
(2, 4, 2, 3, 4, '', '2025-12-19 06:57:40');

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `category` enum('breakfast','lunch','snacks','drinks') NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `availability` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `name`, `description`, `category`, `price`, `image`, `stock`, `availability`, `created_at`, `updated_at`) VALUES
(1, 'Fried Rice', 'Delicious fried rice with vegetables and chicken', 'lunch', 120.00, NULL, 50, 1, '2025-12-19 06:28:17', '2025-12-19 06:28:17'),
(2, 'Chicken Biryani', 'Spicy chicken biryani with raita', 'lunch', 150.00, 'https://www.shutterstock.com/shutterstock/photos/1454620694/display_1500/stock-photo-chicken-curry-lacha-paratha-gulab-jamun-1454620694.jpg', 30, 1, '2025-12-19 06:28:17', '2025-12-19 07:17:45'),
(3, 'Beef Curry', 'Tender beef curry with rice', 'lunch', 180.00, 'https://i0.wp.com/www.gastrosenses.com/wp-content/uploads/2020/12/Beef-Masala-Curry-4.jpg?resize=1200%2C1798&quality=100&strip=all&ssl=1', 25, 1, '2025-12-19 06:28:17', '2025-12-19 07:19:59'),
(5, 'Chicken Sandwich', 'Grilled chicken sandwich', 'snacks', 100.00, NULL, 35, 1, '2025-12-19 06:28:17', '2025-12-19 06:28:17'),
(6, 'French Fries', 'Crispy french fries', 'snacks', 60.00, NULL, 50, 1, '2025-12-19 06:28:17', '2025-12-19 06:28:17'),
(7, 'Coffee', 'Hot coffee', 'drinks', 40.00, NULL, 100, 1, '2025-12-19 06:28:17', '2025-12-19 06:28:17'),
(8, 'Tea', 'Hot tea', 'drinks', 20.00, NULL, 100, 1, '2025-12-19 06:28:17', '2025-12-19 06:28:17'),
(9, 'Soft Drink', 'Cold soft drink', 'drinks', 30.00, NULL, 80, 1, '2025-12-19 06:28:17', '2025-12-19 06:28:17'),
(10, 'Water Bottle', 'Mineral water', 'drinks', 15.00, NULL, 100, 1, '2025-12-19 06:28:17', '2025-12-19 06:28:17');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `type` enum('order','payment','menu','system') DEFAULT 'system',
  `read_status` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `type`, `read_status`, `created_at`) VALUES
(1, 1, 'Your order #ULAB-20251219-4E918D has been placed successfully!', 'order', 1, '2025-12-19 06:30:12'),
(2, 1, 'Your order #ULAB-20251219-4E918D is being prepared.', 'order', 0, '2025-12-19 06:42:57'),
(3, 4, 'Your order #ULAB-20251219-575997 has been placed successfully!', 'order', 1, '2025-12-19 06:55:49'),
(4, 4, 'Your order #ULAB-20251219-575997 has been completed.', 'order', 1, '2025-12-19 06:56:23'),
(5, 4, 'Your order #ULAB-20251219-EAFD84 has been placed successfully!', 'order', 0, '2025-12-19 07:27:58'),
(6, 4, 'Payment successful for order #ULAB-20251219-EAFD84', 'payment', 0, '2025-12-19 07:28:07'),
(7, 4, 'Your order #ULAB-20251219-744553 has been placed successfully!', 'order', 0, '2025-12-19 07:29:59');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_number` varchar(20) NOT NULL,
  `order_date` datetime NOT NULL,
  `pickup_time` datetime DEFAULT NULL,
  `status` enum('pending','preparing','ready','completed','cancelled') DEFAULT 'pending',
  `payment_method` enum('cash','card','bkash','nagad','ssl') DEFAULT 'cash',
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `transaction_id` varchar(255) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `order_date`, `pickup_time`, `status`, `payment_method`, `payment_status`, `transaction_id`, `total_amount`, `created_at`, `updated_at`) VALUES
(1, 1, 'ULAB-20251219-4E918D', '2025-12-19 07:30:12', '2025-12-26 12:30:00', 'preparing', 'cash', 'pending', NULL, 80.00, '2025-12-19 06:30:12', '2025-12-19 06:42:57'),
(2, 4, 'ULAB-20251219-575997', '2025-12-19 07:55:49', '2025-12-19 15:55:00', 'completed', 'cash', 'pending', NULL, 180.00, '2025-12-19 06:55:49', '2025-12-19 06:56:23'),
(3, 4, 'ULAB-20251219-EAFD84', '2025-12-19 08:27:58', '2025-12-20 13:27:00', 'pending', 'bkash', 'paid', 'TXN-6944FE7EB6AE6', 120.00, '2025-12-19 07:27:58', '2025-12-19 07:28:07'),
(4, 4, 'ULAB-20251219-744553', '2025-12-19 08:29:59', '2025-12-20 13:29:00', 'pending', 'nagad', 'pending', NULL, 150.00, '2025-12-19 07:29:59', '2025-12-19 07:29:59');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `menu_item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `menu_item_id`, `quantity`, `price`, `subtotal`) VALUES
(2, 2, 3, 1, 180.00, 180.00),
(3, 3, 1, 1, 120.00, 120.00),
(4, 4, 2, 1, 150.00, 150.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','faculty','staff','cafeteria_staff','admin') NOT NULL DEFAULT 'student',
  `phone` varchar(20) DEFAULT NULL,
  `student_id` varchar(50) DEFAULT NULL,
  `verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `phone`, `student_id`, `verified`, `verification_token`, `created_at`, `updated_at`) VALUES
(1, 'Muskan', 'muskan.gouri.cse@ulab.edu.bd', '$2y$10$RBHib34rCMRTBiIHfV2S9u.hV1UaQjbx8QgLMwJfBZMABvMiqGwMW', 'cafeteria_staff', '01970000000', '232014027', 1, '27376c4dc63cab170224666508ee08655a1898dbb89eb94cb2698b0ff2b63ae0', '2025-12-19 06:29:32', '2025-12-19 06:41:48'),
(2, 'Sima', 'sima.akter.cse@ulab.edu.bd', '$2y$10$n9kspWOg1Dcpl.lzPLS3wOTH4nitk/fFnEdQS4d2D.k91IXvuL2CS', 'admin', '01300000000', '232014040', 1, 'ac702d29ef11b63a943cefd2783f7c69aa22dad4b2862fb6f9f75bddf5e4a93a', '2025-12-19 06:31:21', '2025-12-19 06:42:29'),
(3, 'Fairoz', 'tasmia.zaman.cse@ulab.edu.bd', '$2y$10$tjn0ReduAkOLW1VRrSp0GOb8VzVfiEFU9HkdWvVxrosuqD.5EH/KG', 'student', '01700000000', '232014094', 1, '878dd2be2e816187375593b7f40e2b4abaa0d8a5d9a0d22924ed27ddfdd767fa', '2025-12-19 06:53:01', '2025-12-19 06:53:01'),
(4, 'Fairoz', 'fairoz.cse@ulab.edu.bd', '$2y$10$Hqa04xEdaWVPiY0BH0W4Z.isTUzHbURSEmKv5tEFyf5h0cD6azsRe', 'student', '01900000000', '232014091', 1, '5170c01942f92e6da0bc6925f5afdaa8e3e4fc6132fffaece98834886b8e42e4', '2025-12-19 06:55:14', '2025-12-19 06:55:14');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `menu_item_id` (`menu_item_id`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `menu_item_id` (`menu_item_id`);

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
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `feedback_ibfk_3` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
