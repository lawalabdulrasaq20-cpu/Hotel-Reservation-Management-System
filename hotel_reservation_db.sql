-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 28, 2026 at 02:57 PM
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
-- Database: `hotel_reservation_db`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `CheckRoomAvailability` (IN `p_check_in` DATE, IN `p_check_out` DATE, IN `p_room_type` VARCHAR(50), IN `p_guests` INT)   BEGIN
    SELECT 
        r.id,
        r.room_number,
        r.type,
        r.price,
        r.description,
        r.image,
        r.max_guests,
        r.status,
        -- Calculate number of nights
        DATEDIFF(p_check_out, p_check_in) as nights,
        -- Calculate total price
        (r.price * DATEDIFF(p_check_out, p_check_in)) as total_price
    FROM rooms r
    WHERE 
        r.status = 'available'
        AND r.max_guests >= p_guests
        AND (p_room_type = '' OR r.type = p_room_type)
        AND NOT EXISTS (
            SELECT 1 FROM reservations res 
            WHERE res.room_id = r.id 
            AND res.status IN ('confirmed', 'pending')
            AND (
                -- Check for date overlap logic
                (res.check_in < p_check_out AND res.check_out > p_check_in)
            )
        );
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `email`, `full_name`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$9pizI8LKi98tIozsogMqBuYj4qPm13T0vG2spBgAuRQuxAGoSwn0G', 'admin@hotel.com', 'Hotel Administrator', '2026-01-28 13:56:35', '2026-01-20 13:25:04', '2026-01-28 12:56:35');

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `guest_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `room_id` int(11) NOT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `special_requests` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `guest_name`, `email`, `phone`, `room_id`, `check_in`, `check_out`, `total_price`, `status`, `special_requests`, `created_at`, `updated_at`) VALUES
(1, 'dfocuz', 'lawalabdulrasaq2005@gmail.com', '+2347049569694', 11, '2026-01-25', '2026-01-26', 50.00, 'cancelled', '', '2026-01-25 17:47:17', '2026-01-26 16:12:23'),
(2, 'dfocuz', 'lawalabdulrasaq2005@gmail.com', '+2347049569694', 11, '2026-01-25', '2026-01-26', 50.00, 'cancelled', '', '2026-01-25 17:47:55', '2026-01-28 07:07:45'),
(3, 'dfocuz', 'lawalabdulrasaq2005@gmail.com', '+2347049569694', 3, '2026-01-25', '2026-01-26', 119.99, 'cancelled', '', '2026-01-25 17:58:29', '2026-01-28 07:08:26'),
(4, 'dfocuz', 'lawalabdulrasaq2005@gmail.com', '+2347049569694', 5, '2026-01-26', '2026-01-28', 359.98, 'cancelled', '', '2026-01-26 10:42:40', '2026-01-28 07:06:47'),
(5, 'dfocuz', 'lawalabdulrasaq2005@gmail.com', '+2347049569694', 11, '2026-01-26', '2026-01-28', 100.00, 'cancelled', '', '2026-01-26 11:21:51', '2026-01-28 07:06:34'),
(6, 'dfocuz', 'lawalabdulrasaq2005@gmail.com', '+2347049569694', 3, '2026-01-26', '2026-01-27', 119.99, 'cancelled', '', '2026-01-26 11:22:30', '2026-01-28 07:04:29'),
(7, 'dfocuz', 'lawalabdulrasaq2005@gmail.com', '+2347049569694', 11, '2026-01-26', '2026-01-27', 50.00, 'cancelled', '', '2026-01-26 11:23:23', '2026-01-28 07:04:18'),
(8, 'dfocuz', 'lawalabdulrasaq2005@gmail.com', '+2347049569694', 11, '2026-01-26', '2026-01-27', 50.00, 'cancelled', '', '2026-01-26 14:04:10', '2026-01-28 07:02:56'),
(9, 'dfocuz', 'lawalabdulrasaq2005@gmail.com', '+2347049569694', 11, '2026-01-26', '2026-01-27', 50.00, 'cancelled', '', '2026-01-26 14:19:51', '2026-01-26 16:12:46'),
(10, 'dfocuz', 'lawalabdulrasaq2005@gmail.com', '+2347049569694', 3, '2026-01-26', '2026-01-27', 119.99, 'cancelled', '', '2026-01-26 14:31:16', '2026-01-28 07:06:10'),
(11, 'dfocuz', 'lawalabdulrasaq2005@gmail.com', '+2347049569694', 12, '2026-01-27', '2026-01-28', 500.00, 'cancelled', '', '2026-01-27 17:38:05', '2026-01-28 07:04:42'),
(12, 'dfocuz', 'lawalabdulrasaq2005@gmail.com', '+2347049569694', 11, '2026-01-27', '2026-01-28', 50.00, 'cancelled', '', '2026-01-27 18:00:44', '2026-01-28 07:03:54'),
(13, 'dfocuz', 'lawalabdulrasaq2005@gmail.com', '+2347049569694', 11, '2026-01-27', '2026-01-28', 50.00, 'cancelled', '', '2026-01-27 18:07:36', '2026-01-28 07:04:08'),
(14, 'dfocuz', 'lawalabdulrasaq2005@gmail.com', '+2347049569694', 11, '2026-01-27', '2026-01-28', 50.00, 'cancelled', '', '2026-01-27 18:29:16', '2026-01-28 07:08:14'),
(15, 'dfocuz', 'lawalabdulrasaq2005@gmail.com', '+2347049569694', 12, '2026-01-27', '2026-01-28', 500.00, 'cancelled', '', '2026-01-27 18:32:27', '2026-01-28 07:07:54'),
(16, 'dfocuz', 'lawalabdulrasaq2005@gmail.com', '+2347049569694', 11, '2026-01-27', '2026-01-28', 50.00, 'cancelled', '', '2026-01-27 18:42:52', '2026-01-28 07:08:03'),
(17, 'dfocuz', 'lawalabdulrasaq2005@gmail.com', '+2347049569694', 11, '2026-01-27', '2026-01-28', 50.00, 'cancelled', '', '2026-01-27 18:48:32', '2026-01-28 07:07:35'),
(18, 'dfocuz', 'lawalabdulrasaq2005@gmail.com', '+2347049569694', 3, '2026-01-28', '2026-01-29', 119.99, 'pending', '', '2026-01-28 07:34:51', '2026-01-28 07:34:51'),
(19, 'dfocuz', 'lawalabdulrasaq2005@gmail.com', '+2347049569694', 3, '2026-01-28', '2026-01-29', 119.99, 'confirmed', '', '2026-01-28 07:39:27', '2026-01-28 12:59:36'),
(20, 'dfocuz', 'lawalabdulrasaq2005@gmail.com', '+2347049569694', 12, '2026-01-28', '2026-01-29', 500.00, 'confirmed', '', '2026-01-28 13:00:21', '2026-01-28 13:01:16');

--
-- Triggers `reservations`
--
DELIMITER $$
CREATE TRIGGER `update_room_status_on_confirm` AFTER UPDATE ON `reservations` FOR EACH ROW BEGIN
    IF NEW.status = 'confirmed' AND OLD.status != 'confirmed' THEN
        UPDATE rooms SET status = 'occupied' WHERE id = NEW.room_id;
    END IF;
    
    IF OLD.status = 'confirmed' AND NEW.status != 'confirmed' THEN
        UPDATE rooms SET status = 'available' WHERE id = NEW.room_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `room_number` varchar(10) NOT NULL,
  `type` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('available','occupied','maintenance') DEFAULT 'available',
  `max_guests` int(11) DEFAULT 2,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `room_number`, `type`, `price`, `description`, `image`, `status`, `max_guests`, `created_at`, `updated_at`) VALUES
(3, '201', 'Standard Double', 119.99, 'Spacious double room with two comfortable beds, perfect for friends or small families. Modern bathroom with rainfall shower.', 'room-double.jpg', 'occupied', 2, '2026-01-20 13:25:04', '2026-01-28 12:59:36'),
(5, '301', 'Deluxe King', 179.99, 'Luxurious king-size bed room with panoramic views. Features premium linens, marble bathroom, and sitting area.', 'room-deluxe.jpg', 'available', 2, '2026-01-20 13:25:04', '2026-01-28 07:06:47'),
(11, '503', 'Suite', 50.00, 'face id, wifi, free food', 'room_1769250206_69749d9ea6898.jpg', 'available', 2, '2026-01-24 10:23:26', '2026-01-28 07:06:34'),
(12, '101', 'Family Room', 500.00, 'wretyuiopiuytertyuiopoiuytrewrtyuioouytrtty', 'room_1769529897_6978e229cfbd2.jpg', 'occupied', 5, '2026-01-27 16:04:57', '2026-01-28 13:01:16');

-- --------------------------------------------------------

--
-- Stand-in structure for view `room_availability`
-- (See below for the actual view)
--
CREATE TABLE `room_availability` (
`id` int(11)
,`room_number` varchar(10)
,`type` varchar(50)
,`price` decimal(10,2)
,`description` text
,`image` varchar(255)
,`max_guests` int(11)
,`status` enum('available','occupied','maintenance')
,`current_status` varchar(11)
);

-- --------------------------------------------------------

--
-- Structure for view `room_availability`
--
DROP TABLE IF EXISTS `room_availability`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `room_availability`  AS SELECT `r`.`id` AS `id`, `r`.`room_number` AS `room_number`, `r`.`type` AS `type`, `r`.`price` AS `price`, `r`.`description` AS `description`, `r`.`image` AS `image`, `r`.`max_guests` AS `max_guests`, `r`.`status` AS `status`, CASE WHEN exists(select 1 from `reservations` `res` where `res`.`room_id` = `r`.`id` AND `res`.`status` in ('confirmed','pending') AND `res`.`check_in` <= curdate() AND `res`.`check_out` > curdate() limit 1) THEN 'occupied' ELSE `r`.`status` END AS `current_status` FROM `rooms` AS `r` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `idx_check_in` (`check_in`),
  ADD KEY `idx_check_out` (`check_out`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_reservation_dates` (`check_in`,`check_out`,`status`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `room_number` (`room_number`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_room_availability` (`status`,`type`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `fk_reservations_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
