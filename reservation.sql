-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 27, 2025 at 05:32 AM
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
-- Database: `reservation`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `booking_number` varchar(20) DEFAULT NULL,
  `customer_id` int(11) NOT NULL,
  `check_in_date` date NOT NULL,
  `check_out_date` date NOT NULL,
  `total_guests` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `down_payment` decimal(10,2) DEFAULT NULL,
  `payment_source_id` varchar(255) DEFAULT NULL,
  `payment_status` varchar(50) DEFAULT 'pending',
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `venue_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `booking_number`, `customer_id`, `check_in_date`, `check_out_date`, `total_guests`, `total_amount`, `down_payment`, `payment_source_id`, `payment_status`, `status`, `created_at`, `venue_id`) VALUES
(18, 'BK2025724838', 1, '2025-02-21', '2025-02-22', 2, 846.00, 423.00, 'src_LevRsQiD25TJCYCF7rQz9dE4', 'paid', 'cancelled', '2025-02-21 03:13:44', NULL),
(19, 'BK2025424068', 1, '2025-02-21', '2025-02-22', 2, 4000.00, 2000.00, 'src_T1WgkwjkSDuTKgjjsHWx5MR3', 'paid', 'cancelled', '2025-02-21 03:15:00', NULL),
(20, 'BK2025808930', 1, '2025-02-26', '2025-02-27', 2, 846.00, 423.00, 'src_v4KkztmDXSH7rEERKD4izQ3n', 'paid', 'cancelled', '2025-02-21 03:18:07', NULL),
(21, 'BK2025754761', 1, '2025-02-21', '2025-02-22', 2, 6000.00, 3000.00, 'src_i6Vi586AgSLzUq3aiG55DHML', 'paid', 'cancelled', '2025-02-21 03:19:40', NULL),
(22, 'BK2025835161', 1, '2025-03-04', '2025-03-05', 2, 4230.00, 2115.00, 'src_5SwXLgncwyQCv4bCbDHQuWQD', 'paid', 'cancelled', '2025-02-21 03:21:08', NULL),
(26, 'BK2025469056', 1, '2025-02-21', '2025-02-24', 2, 6000.00, 3000.00, 'src_eekDuqyhdAhcCNthHZFCaWyW', 'paid', 'completed', '2025-02-21 03:36:57', NULL),
(27, 'BK2025788360', 1, '2025-02-23', '2025-02-25', 2, 4846.00, 2423.00, 'src_obFFqDyXRvVzkX54nz4miVah', 'paid', 'completed', '2025-02-23 14:23:05', NULL),
(28, 'BK2025842657', 2, '2025-02-26', '2025-02-27', 2, 2423.00, 1211.50, 'src_5pTDWyykbT4vGXY7xLvdx1F2', 'paid', 'pending', '2025-02-26 06:47:32', NULL),
(29, 'BK2025786564', 6, '2025-02-27', '2025-03-01', 13, 20000.00, 10000.00, 'src_kqf6PYRZGTZGbEJjN3M8o6TK', 'paid', 'pending', '2025-02-26 06:47:33', NULL),
(30, 'BK2025349407', 6, '2025-02-27', '2025-03-01', 10, 34000.00, 17000.00, 'src_HrBqmbEkm8DDxapCW6rXjR9g', 'paid', 'confirmed', '2025-02-26 06:51:31', NULL),
(31, 'BK2025843757', 2, '2025-02-01', '2025-02-28', 2, 34155.00, 17077.50, 'src_pRtAzjQWZg3vo5NP3abrn9jj', 'paid', 'completed', '2025-02-27 03:16:03', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `booking_rooms`
--

CREATE TABLE `booking_rooms` (
  `booking_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `time_slot` enum('day','night') NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_per_night` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking_rooms`
--

INSERT INTO `booking_rooms` (`booking_id`, `room_id`, `time_slot`, `quantity`, `price_per_night`) VALUES
(12, 9, 'day', 1, 846.00),
(18, 9, 'day', 1, 846.00),
(20, 9, 'day', 1, 846.00),
(22, 9, 'day', 1, 4230.00),
(27, 9, 'day', 1, 846.00),
(28, 9, 'day', 1, 423.00),
(31, 12, 'night', 1, 405.00);

-- --------------------------------------------------------

--
-- Table structure for table `booking_venues`
--

CREATE TABLE `booking_venues` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `venue_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking_venues`
--

INSERT INTO `booking_venues` (`id`, `booking_id`, `venue_id`, `created_at`) VALUES
(1, 19, 2, '2025-02-21 03:15:00'),
(2, 21, 2, '2025-02-21 03:19:40'),
(4, 26, 2, '2025-02-21 03:36:57'),
(5, 27, 2, '2025-02-23 14:23:05'),
(6, 28, 2, '2025-02-26 06:47:32'),
(7, 29, 4, '2025-02-26 06:47:33'),
(8, 30, 2, '2025-02-26 06:51:31'),
(9, 30, 3, '2025-02-26 06:51:31'),
(10, 31, 7, '2025-02-27 03:16:03');

-- --------------------------------------------------------

--
-- Table structure for table `cottages`
--

CREATE TABLE `cottages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `capacity` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('cottage','hall') NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cottages`
--

INSERT INTO `cottages` (`id`, `name`, `price`, `capacity`, `description`, `type`, `image`, `created_at`) VALUES
(5, 'testing', 1250.00, 12, 'dasdas', 'cottage', '1739631477.png', '2025-02-15 14:57:57');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `contact_number` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `user_id`, `full_name`, `contact_number`) VALUES
(1, 3, 'juan dela cruz', '09759209976'),
(2, 19, 'JAYAR ALCANA COPE', '09759209976'),
(3, 21, 'john supot', '09759209976'),
(4, 22, 'Mary Jane Martinez', '09759209976'),
(6, 24, 'JOhny meow meow', '09323255212'),
(7, 25, 'Michael John Dacillo', '09959433804'),
(8, 26, 'ioioi', '09292905818');

-- --------------------------------------------------------

--
-- Table structure for table `features`
--

CREATE TABLE `features` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `features`
--

INSERT INTO `features` (`id`, `title`, `description`, `image`, `created_at`, `updated_at`) VALUES
(12, 'SSLG 2025', 'fafasf', '1739798616.png', '2025-02-17 13:23:36', '2025-02-17 13:23:36');

-- --------------------------------------------------------

--
-- Table structure for table `feedbacks`
--

CREATE TABLE `feedbacks` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedbacks`
--

INSERT INTO `feedbacks` (`id`, `customer_id`, `rating`, `message`, `created_at`, `status`) VALUES
(1, 1, 4, 'daj afkw  fajfdawe ', '2025-02-15 16:03:32', 'rejected'),
(2, 1, 4, 'fasfaf', '2025-02-15 16:06:16', 'approved'),
(3, 1, 4, 'fasfafs fafasfas', '2025-02-15 16:07:33', 'rejected'),
(4, 1, 3, 'fasasf fafaff', '2025-02-15 16:10:11', 'approved'),
(5, 1, 4, 'fsfsfs', '2025-02-15 16:18:46', 'approved'),
(6, 1, 3, 'fsfsdf', '2025-02-15 16:18:51', 'approved'),
(7, 1, 4, 'ggsgsgdsg', '2025-02-15 16:18:56', 'approved'),
(8, 1, 3, 'gsgsdgsd', '2025-02-15 16:19:01', 'approved'),
(9, 1, 4, 'gsdgsdgsd', '2025-02-15 16:19:05', 'approved'),
(10, 1, 4, 'fhdfgfg', '2025-02-15 16:23:07', 'rejected'),
(11, 1, 4, 'gsdggsg', '2025-02-15 16:23:32', 'rejected'),
(12, 1, 4, 'gsgsg', '2025-02-15 16:25:02', 'rejected'),
(13, 1, 4, 'fsdfsdf', '2025-02-15 16:27:31', 'rejected'),
(14, 1, 4, 'afafsfaf', '2025-02-15 16:28:18', 'rejected'),
(15, 1, 3, 'fafaf', '2025-02-17 14:16:22', 'pending'),
(16, 1, 5, 'fafasf', '2025-02-17 14:28:13', 'pending'),
(17, 6, 4, 'ilove boto\r\n', '2025-02-26 06:48:59', 'approved');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `created_at`) VALUES
(1, 1, 'New Booking Requires Confirmation', 'New booking #2 from juan dela cruz requires your confirmation. Check-in: Feb 20, 2025, Check-out: Feb 22, 2025', 'new_booking', 0, '2025-02-20 13:43:22'),
(2, 3, 'Booking Payment Received', 'Your payment for booking #2 has been received and is pending confirmation. We\'ll notify you once it\'s confirmed.', 'booking_pending', 1, '2025-02-20 13:43:22'),
(3, 1, 'Booking Cancelled', 'Booking #00000002 has been cancelled by the customer.', 'booking_cancelled', 0, '2025-02-20 13:45:22'),
(4, 1, 'New Booking Requires Confirmation', 'New booking #3 from juan dela cruz requires your confirmation. Check-in: Feb 20, 2025, Check-out: Feb 22, 2025', 'new_booking', 0, '2025-02-20 13:49:27'),
(5, 3, 'Booking Payment Received', 'Your payment for booking #3 has been received and is pending confirmation. We\'ll notify you once it\'s confirmed.', 'booking_pending', 1, '2025-02-20 13:49:27'),
(6, 1, 'New Booking Requires Confirmation', 'New booking #4 from JAYAR ALCANA COPE requires your confirmation. Check-in: Feb 20, 2025, Check-out: Feb 22, 2025', 'new_booking', 0, '2025-02-20 15:06:12'),
(7, 19, 'Booking Payment Received', 'Your payment for booking #4 has been received and is pending confirmation. We\'ll notify you once it\'s confirmed.', 'booking_pending', 1, '2025-02-20 15:06:12'),
(8, 1, 'New Booking Requires Confirmation', 'New booking #5 from JAYAR ALCANA COPE requires your confirmation. Check-in: Feb 20, 2025, Check-out: Feb 24, 2025', 'new_booking', 0, '2025-02-20 15:07:57'),
(9, 19, 'Booking Payment Received', 'Your payment for booking #5 has been received and is pending confirmation. We\'ll notify you once it\'s confirmed.', 'booking_pending', 1, '2025-02-20 15:07:57'),
(10, 19, 'Booking Confirmed', 'Your booking #00000005 has been confirmed.', 'booking_confirmed', 1, '2025-02-20 15:10:06'),
(11, 3, 'Booking Rejected', 'Your booking #00000003 has been rejected.', 'booking_rejected', 1, '2025-02-20 15:15:06'),
(12, 19, 'Booking Rejected', 'Your booking #00000004 has been rejected.', 'booking_rejected', 1, '2025-02-20 15:15:09'),
(13, 19, 'Booking Completed', 'Your booking #00000005 has been marked as completed.', 'booking_completed', 1, '2025-02-20 15:18:08'),
(14, 1, 'New Booking Requires Confirmation', 'New booking #6 from JAYAR ALCANA COPE requires your confirmation. Check-in: Feb 20, 2025, Check-out: Feb 24, 2025', 'new_booking', 0, '2025-02-20 15:22:12'),
(15, 19, 'Booking Payment Received', 'Your payment for booking #6 has been received and is pending confirmation. We\'ll notify you once it\'s confirmed.', 'booking_pending', 1, '2025-02-20 15:22:12'),
(16, 1, 'Booking Cancelled', 'Booking #00000006 has been cancelled by the customer.', 'booking_cancelled', 0, '2025-02-20 15:32:05'),
(17, 1, 'New Booking Requires Confirmation', 'New booking #7 from juan dela cruz requires your confirmation. Check-in: Mar 03, 2025, Check-out: Mar 06, 2025', 'new_booking', 0, '2025-02-21 02:31:30'),
(18, 3, 'Booking Payment Received', 'Your payment for booking #7 has been received and is pending confirmation. We\'ll notify you once it\'s confirmed.', 'booking_pending', 1, '2025-02-21 02:31:30'),
(19, 1, 'New Booking Requires Confirmation', 'New booking #8 from juan dela cruz requires your confirmation. Check-in: Feb 27, 2025, Check-out: Mar 01, 2025', 'new_booking', 0, '2025-02-21 02:39:33'),
(20, 3, 'Booking Payment Received', 'Your payment for booking #8 has been received and is pending confirmation. We\'ll notify you once it\'s confirmed.', 'booking_pending', 1, '2025-02-21 02:39:33'),
(21, 1, 'New Booking Requires Confirmation', 'New booking #9 from juan dela cruz requires your confirmation. Check-in: Feb 25, 2025, Check-out: Feb 26, 2025', 'new_booking', 0, '2025-02-21 02:51:20'),
(22, 3, 'Booking Payment Received', 'Your payment for booking #9 has been received and is pending confirmation. We\'ll notify you once it\'s confirmed.', 'booking_pending', 1, '2025-02-21 02:51:20'),
(23, 3, 'Booking Rejected', 'Your booking #00000009 has been rejected.', 'booking_rejected', 1, '2025-02-21 02:53:55'),
(24, 3, 'Booking Rejected', 'Your booking #00000008 has been rejected.', 'booking_rejected', 1, '2025-02-21 02:53:59'),
(25, 1, 'New Booking Requires Confirmation', 'New booking #10 from juan dela cruz requires your confirmation. Check-in: Feb 21, 2025, Check-out: Feb 23, 2025', 'new_booking', 0, '2025-02-21 02:54:44'),
(26, 3, 'Booking Payment Received', 'Your payment for booking #10 has been received and is pending confirmation. We\'ll notify you once it\'s confirmed.', 'booking_pending', 1, '2025-02-21 02:54:44'),
(27, 1, 'New Booking Requires Confirmation', 'New booking #11 from juan dela cruz requires your confirmation. Check-in: Mar 04, 2025, Check-out: Mar 19, 2025', 'new_booking', 0, '2025-02-21 02:56:26'),
(28, 3, 'Booking Payment Received', 'Your payment for booking #11 has been received and is pending confirmation. We\'ll notify you once it\'s confirmed.', 'booking_pending', 1, '2025-02-21 02:56:26'),
(29, 1, 'Booking Cancelled', 'Booking #00000010 has been cancelled by the customer.', 'booking_cancelled', 0, '2025-02-21 02:57:49'),
(30, 1, 'Booking Cancelled', 'Booking #00000011 has been cancelled by the customer.', 'booking_cancelled', 0, '2025-02-21 02:57:51'),
(31, 1, 'New Booking Requires Confirmation', 'New booking #12 from juan dela cruz requires your confirmation. Check-in: Feb 21, 2025, Check-out: Feb 23, 2025', 'new_booking', 0, '2025-02-21 02:58:14'),
(32, 3, 'Booking Payment Received', 'Your payment for booking #12 has been received and is pending confirmation. We\'ll notify you once it\'s confirmed.', 'booking_pending', 1, '2025-02-21 02:58:14'),
(33, 1, 'New Booking Requires Confirmation', 'New booking #13 from juan dela cruz requires your confirmation. Check-in: Feb 21, 2025, Check-out: Feb 25, 2025', 'new_booking', 0, '2025-02-21 03:00:03'),
(34, 3, 'Booking Payment Received', 'Your payment for booking #13 has been received and is pending confirmation. We\'ll notify you once it\'s confirmed.', 'booking_pending', 1, '2025-02-21 03:00:03'),
(35, 1, 'New Booking Requires Confirmation', 'New booking #14 from juan dela cruz requires your confirmation. Check-in: Feb 21, 2025, Check-out: Feb 24, 2025', 'new_booking', 0, '2025-02-21 03:01:22'),
(36, 3, 'Booking Payment Received', 'Your payment for booking #14 has been received and is pending confirmation. We\'ll notify you once it\'s confirmed.', 'booking_pending', 1, '2025-02-21 03:01:22'),
(37, 1, 'Booking Cancelled', 'Booking #00000014 has been cancelled by the customer.', 'booking_cancelled', 0, '2025-02-21 03:03:59'),
(38, 1, 'Booking Cancelled', 'Booking #00000013 has been cancelled by the customer.', 'booking_cancelled', 0, '2025-02-21 03:04:03'),
(39, 1, 'New Booking Requires Confirmation', 'New booking #15 from juan dela cruz requires your confirmation. Check-in: Feb 21, 2025, Check-out: Feb 23, 2025', 'new_booking', 0, '2025-02-21 03:05:09'),
(40, 3, 'Booking Payment Received', 'Your payment for booking #15 has been received and is pending confirmation. We\'ll notify you once it\'s confirmed.', 'booking_pending', 1, '2025-02-21 03:05:09'),
(41, 1, 'Booking Cancelled', 'Booking #00000015 has been cancelled by the customer.', 'booking_cancelled', 0, '2025-02-21 03:07:57'),
(42, 1, 'New Booking Requires Confirmation', 'New booking #16 from juan dela cruz requires your confirmation. Check-in: Feb 21, 2025, Check-out: Feb 23, 2025', 'new_booking', 0, '2025-02-21 03:10:07'),
(43, 3, 'Booking Payment Received', 'Your payment for booking #16 has been received and is pending confirmation. We\'ll notify you once it\'s confirmed.', 'booking_pending', 1, '2025-02-21 03:10:07'),
(44, 1, 'New Booking Requires Confirmation', 'New booking #18 from juan dela cruz requires your confirmation. Check-in: Feb 21, 2025, Check-out: Feb 23, 2025', 'new_booking', 0, '2025-02-21 03:13:44'),
(45, 3, 'Booking Payment Received', 'Your payment for booking #18 has been received and is pending confirmation. We\'ll notify you once it\'s confirmed.', 'booking_pending', 1, '2025-02-21 03:13:44'),
(46, 1, 'New Booking Requires Confirmation', 'New booking #19 from juan dela cruz requires your confirmation. Check-in: Feb 21, 2025, Check-out: Feb 23, 2025', 'new_booking', 0, '2025-02-21 03:15:00'),
(47, 3, 'Booking Payment Received', 'Your payment for booking #19 has been received and is pending confirmation. We\'ll notify you once it\'s confirmed.', 'booking_pending', 1, '2025-02-21 03:15:00'),
(48, 1, 'New Booking Requires Confirmation', 'New booking #20 from juan dela cruz requires your confirmation. Check-in: Feb 26, 2025, Check-out: Feb 28, 2025', 'new_booking', 0, '2025-02-21 03:18:07'),
(49, 3, 'Booking Payment Received', 'Your payment for booking #20 has been received and is pending confirmation. We\'ll notify you once it\'s confirmed.', 'booking_pending', 1, '2025-02-21 03:18:07'),
(50, 1, 'New Booking Requires Confirmation', 'New booking #21 from juan dela cruz requires your confirmation. Check-in: Feb 21, 2025, Check-out: Feb 24, 2025', 'new_booking', 0, '2025-02-21 03:19:40'),
(51, 3, 'Booking Payment Received', 'Your payment for booking #21 has been received and is pending confirmation. We\'ll notify you once it\'s confirmed.', 'booking_pending', 1, '2025-02-21 03:19:40'),
(52, 1, 'New Booking Requires Confirmation', 'New booking #22 from juan dela cruz requires your confirmation. Check-in: Mar 04, 2025, Check-out: Mar 14, 2025', 'new_booking', 0, '2025-02-21 03:21:08'),
(53, 3, 'Booking Payment Received', 'Your payment for booking #22 has been received and is pending confirmation. We\'ll notify you once it\'s confirmed.', 'booking_pending', 1, '2025-02-21 03:21:08'),
(54, 1, 'Booking Cancelled', 'Booking #00000018 has been cancelled by the customer.', 'booking_cancelled', 0, '2025-02-21 03:25:33'),
(55, 1, 'Booking Cancelled', 'Booking #00000019 has been cancelled by the customer.', 'booking_cancelled', 0, '2025-02-21 03:25:37'),
(56, 1, 'Booking Cancelled', 'Booking #00000022 has been cancelled by the customer.', 'booking_cancelled', 0, '2025-02-21 03:25:43'),
(57, 1, 'Booking Cancelled', 'Booking #00000021 has been cancelled by the customer.', 'booking_cancelled', 0, '2025-02-21 03:25:46'),
(58, 1, 'Booking Cancelled', 'Booking #00000020 has been cancelled by the customer.', 'booking_cancelled', 0, '2025-02-21 03:25:49'),
(59, 1, 'New Booking Requires Confirmation', 'New booking #26 from juan dela cruz requires your confirmation. Check-in: Feb 21, 2025, Check-out: Feb 24, 2025', 'new_booking', 0, '2025-02-21 03:36:57'),
(60, 3, 'Booking Payment Received', 'Your payment for booking #26 has been received and is pending confirmation. We\'ll notify you once it\'s confirmed.', 'booking_pending', 1, '2025-02-21 03:36:57'),
(61, 3, 'Booking Confirmed', 'Your booking #00000026 has been confirmed.', 'booking_confirmed', 1, '2025-02-21 03:38:40'),
(62, 3, 'Booking Completed', 'Your booking #00000026 has been marked as completed.', 'booking_completed', 1, '2025-02-23 12:53:31'),
(63, 1, 'New Booking Requires Confirmation', 'New booking #27 from juan dela cruz requires your confirmation. Check-in: Feb 23, 2025, Check-out: Feb 25, 2025', 'new_booking', 0, '2025-02-23 14:23:05'),
(64, 3, 'Booking Payment Received', 'Your payment for booking #27 has been received and is pending confirmation. We\'ll notify you once it\'s confirmed.', 'booking_pending', 0, '2025-02-23 14:23:05'),
(65, 3, 'Booking Confirmed', 'Your booking #00000027 has been confirmed.', 'booking_confirmed', 0, '2025-02-23 14:24:44'),
(66, 3, 'Booking Completed', 'Your booking #00000027 has been marked as completed.', 'booking_completed', 0, '2025-02-23 15:27:09'),
(67, 1, 'New Booking Requires Confirmation', 'New booking #28 from JAYAR ALCANA COPE requires your confirmation. Check-in: Feb 26, 2025, Check-out: Feb 27, 2025', 'new_booking', 0, '2025-02-26 06:47:32'),
(68, 19, 'Booking Payment Received', 'Your payment for booking #28 has been received and is pending confirmation. We\'ll notify you once it\'s confirmed.', 'booking_pending', 1, '2025-02-26 06:47:32'),
(69, 1, 'New Booking Requires Confirmation', 'New booking #29 from JOhny meow meow requires your confirmation. Check-in: Feb 27, 2025, Check-out: Mar 01, 2025', 'new_booking', 0, '2025-02-26 06:47:33'),
(70, 24, 'Booking Payment Received', 'Your payment for booking #29 has been received and is pending confirmation. We\'ll notify you once it\'s confirmed.', 'booking_pending', 1, '2025-02-26 06:47:33'),
(71, 1, 'New Booking Requires Confirmation', 'New booking #30 from JOhny meow meow requires your confirmation. Check-in: Feb 27, 2025, Check-out: Mar 01, 2025', 'new_booking', 0, '2025-02-26 06:51:31'),
(72, 24, 'Booking Payment Received', 'Your payment for booking #30 has been received and is pending confirmation. We\'ll notify you once it\'s confirmed.', 'booking_pending', 1, '2025-02-26 06:51:31'),
(73, 24, 'Booking Confirmed', 'Your booking #00000030 has been confirmed.', 'booking_confirmed', 1, '2025-02-26 06:52:03'),
(74, 1, 'New Booking Requires Confirmation', 'New booking #31 from JAYAR ALCANA COPE requires your confirmation. Check-in: Feb 01, 2025, Check-out: Feb 28, 2025', 'new_booking', 0, '2025-02-27 03:16:03'),
(75, 19, 'Booking Payment Received', 'Your payment for booking #31 has been received and is pending confirmation. We\'ll notify you once it\'s confirmed.', 'booking_pending', 0, '2025-02-27 03:16:03'),
(76, 19, 'Booking Confirmed', 'Your booking #00000031 has been confirmed.', 'booking_confirmed', 0, '2025-02-27 03:19:49'),
(77, 19, 'Booking Completed', 'Your booking #00000031 has been marked as completed.', 'booking_completed', 0, '2025-02-27 03:20:01');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `room_name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `capacity` int(11) NOT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `day_price` decimal(10,2) NOT NULL,
  `night_price` decimal(10,2) NOT NULL,
  `picture` varchar(255) NOT NULL,
  `status` enum('available','occupied','maintenance') DEFAULT 'available',
  `inclusions` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `room_name`, `description`, `price`, `capacity`, `base_price`, `day_price`, `night_price`, `picture`, `status`, `inclusions`, `image`, `created_at`) VALUES
(9, 'VISTa', 'afasf', 41244.00, 3, 34.00, 423.00, 1850.00, '67b3383c80875.png', 'available', 'f', '1739542980.png', '2025-02-14 14:23:00'),
(12, 'Casita', 'fasfasf', 0.00, 3, 3423.00, 12.00, 15.00, '67b71d89bb4cc.png', 'available', NULL, NULL, '2025-02-20 12:18:17');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `staff_name` varchar(255) NOT NULL,
  `contact_number` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `user_id`, `staff_name`, `contact_number`) VALUES
(5, 16, 'jayar cope', '09759209976'),
(7, 18, 'luka', '09123456231');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('admin','staff','customer') NOT NULL,
  `reset_code` varchar(6) DEFAULT NULL,
  `reset_code_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `user_type`, `reset_code`, `reset_code_expiry`) VALUES
(1, 'admin@casitadegrands.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL, NULL),
(3, 'admin123@gmail.com', '$2y$10$9fT1kyj4LvP1xSifMUp4KecpoER6Xted22Upb2UI0eCBTFYNyKUmi', 'customer', NULL, NULL),
(16, 'copejayar33@gmail.com', '$2y$10$K3Zw.de7xUwbtIGw/ySqz.koldah9F7FVVg9RaMFFgjdTAwWM7I7S', 'staff', NULL, NULL),
(18, 'testing@gmail.com', '$2y$10$AbachApaSKZbzVhiKVNFo.bg35z/4UnBfM/z.idiv3pe9y8OONWlm', 'staff', '694926', '2025-02-26 08:40:21'),
(19, 'copejayar090903@gmail.com', '$2y$10$SgfE/Bj9hIVJbIPAXA7c2OkjYmyg/m7BYCJYn6lXGcmWiVqvSqOq.', 'customer', '500190', '2025-02-26 09:20:32'),
(21, 'copejayar424@gmail.com', '$2y$10$KmryV35K5Vc8ohY/Q7bWD.DNe1kgOBWpoGPd7YwlSC6cH7R4Xyjr.', 'customer', NULL, NULL),
(22, 'copemaryjane@gmail.com', '$2y$10$vNFzxvtYsPEH/IMlsIrGUe0rV9UI4/1Gjm/LEiXhfA6ZvFnHcgV4i', 'customer', '187791', '2025-02-21 14:46:53'),
(24, 'wanene4339@bitflirt.com', '$2y$10$JFaj2BsZkWGxSADpRWxJF..3raG9GJtOgqSFY5p66ZskeTtVmixq.', 'customer', NULL, NULL),
(25, 'mjod2022-6459-28153@bicol-u.edu.ph', '$2y$10$MoWIruPKEvoYf2aD9Bgs3.aNfkEVTC8.LCP5WVIIXP1yGP0VnWRsi', 'customer', '375212', '2025-02-26 09:22:26'),
(26, 'gytffyoioi@gmail.com', '$2y$10$5yRQADE2Jk2Gdlj3h7I5yu3uiKZ8rRHQ4QdVOgR6BvPdmFRmythLO', 'customer', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `venues`
--

CREATE TABLE `venues` (
  `id` int(11) NOT NULL,
  `type` enum('cottage','hall') NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `capacity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `picture` varchar(255) NOT NULL,
  `status` enum('available','occupied','maintenance') DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `venues`
--

INSERT INTO `venues` (`id`, `type`, `name`, `description`, `capacity`, `price`, `picture`, `status`) VALUES
(2, 'cottage', 'Pool Cottage', 'Located near the infinity pool', 10, 2000.00, 'pool-cottage.jpg', 'available'),
(3, 'hall', 'Grand Hall', 'Perfect for events and celebrations', 100, 15000.00, 'grand-hall.jpg', 'available'),
(4, 'hall', 'Conference Hall', 'Ideal for business meetings', 50, 10000.00, '67b338b00dec4.png', 'available'),
(7, 'cottage', 'testing', 'fasfa', 3, 1250.00, '67b3330d190a6.png', 'available'),
(8, 'hall', 'faff', 'safsf', 2, 44.00, '67b3459c44ee2.png', 'available');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_number` (`booking_number`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `venue_id` (`venue_id`);

--
-- Indexes for table `booking_rooms`
--
ALTER TABLE `booking_rooms`
  ADD PRIMARY KEY (`booking_id`,`room_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `booking_venues`
--
ALTER TABLE `booking_venues`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `venue_id` (`venue_id`);

--
-- Indexes for table `cottages`
--
ALTER TABLE `cottages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `features`
--
ALTER TABLE `features`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `feedbacks`
--
ALTER TABLE `feedbacks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `venues`
--
ALTER TABLE `venues`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `booking_venues`
--
ALTER TABLE `booking_venues`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `cottages`
--
ALTER TABLE `cottages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `features`
--
ALTER TABLE `features`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `feedbacks`
--
ALTER TABLE `feedbacks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `venues`
--
ALTER TABLE `venues`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`);

--
-- Constraints for table `booking_rooms`
--
ALTER TABLE `booking_rooms`
  ADD CONSTRAINT `booking_rooms_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `booking_rooms_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `booking_venues`
--
ALTER TABLE `booking_venues`
  ADD CONSTRAINT `booking_venues_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`),
  ADD CONSTRAINT `booking_venues_ibfk_2` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`);

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `feedbacks`
--
ALTER TABLE `feedbacks`
  ADD CONSTRAINT `feedbacks_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
