-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 15, 2025 at 06:21 AM
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
-- Database: `foodapp`
--

-- --------------------------------------------------------

--
-- Table structure for table `delivery`
--

CREATE TABLE `delivery` (
  `delivery_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `delivery_boy_id` int(11) NOT NULL,
  `status` enum('assigned','picked','on_the_way','delivered') DEFAULT 'assigned',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `delivery`
--

INSERT INTO `delivery` (`delivery_id`, `order_id`, `delivery_boy_id`, `status`, `created_at`) VALUES
(244, 329, 10, 'delivered', '2025-11-17 08:10:01'),
(245, 330, 10, 'delivered', '2025-11-18 07:19:16'),
(246, 331, 10, 'delivered', '2025-11-18 07:24:17'),
(247, 333, 10, 'delivered', '2025-11-18 07:42:58'),
(248, 332, 10, 'delivered', '2025-11-18 07:44:42'),
(249, 334, 10, 'delivered', '2025-11-19 06:59:34'),
(251, 335, 10, 'delivered', '2025-11-20 08:33:11'),
(252, 336, 10, 'delivered', '2025-11-20 08:34:57'),
(253, 340, 10, 'delivered', '2025-11-22 09:54:19'),
(255, 339, 10, 'delivered', '2025-11-22 14:49:10'),
(256, 341, 10, 'delivered', '2025-11-23 05:42:07'),
(257, 342, 9, 'delivered', '2025-11-23 06:04:17'),
(258, 346, 10, 'delivered', '2025-11-23 06:37:19'),
(259, 345, 10, 'delivered', '2025-11-23 06:37:10'),
(260, 343, 10, 'delivered', '2025-11-23 06:37:14'),
(261, 352, 10, 'delivered', '2025-11-23 14:15:04'),
(262, 351, 10, 'delivered', '2025-11-23 14:15:12'),
(263, 350, 10, 'delivered', '2025-11-23 14:15:22'),
(264, 349, 10, 'delivered', '2025-11-23 14:16:39'),
(265, 348, 10, 'delivered', '2025-11-23 14:16:51'),
(266, 347, 10, 'delivered', '2025-11-23 14:16:15'),
(268, 353, 10, 'delivered', '2025-11-24 08:35:22'),
(269, 354, 10, 'delivered', '2025-11-25 08:28:18'),
(271, 359, 10, 'delivered', '2025-11-26 08:33:56'),
(272, 360, 10, 'delivered', '2025-11-26 08:45:29'),
(273, 358, 10, 'delivered', '2025-11-26 08:46:44'),
(274, 357, 10, 'delivered', '2025-11-26 09:13:37'),
(275, 356, 10, 'delivered', '2025-11-26 09:13:51'),
(276, 363, 10, 'delivered', '2025-11-27 09:23:07'),
(277, 362, 10, 'delivered', '2025-11-27 09:27:14'),
(279, 367, 1, 'assigned', '2025-11-28 16:00:24'),
(282, 369, 10, 'delivered', '2025-11-29 04:17:29'),
(283, 368, 10, 'delivered', '2025-11-30 11:57:44'),
(284, 373, 10, 'delivered', '2025-11-30 13:31:59'),
(285, 370, 10, 'delivered', '2025-11-30 13:33:08'),
(286, 377, 10, 'delivered', '2025-12-03 11:53:27'),
(287, 378, 10, 'delivered', '2025-12-04 14:01:47'),
(288, 388, 10, 'delivered', '2025-12-12 17:05:18'),
(289, 390, 10, 'delivered', '2025-12-14 08:42:37'),
(290, 391, 10, 'delivered', '2025-12-14 08:52:03');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_tracking`
--

CREATE TABLE `delivery_tracking` (
  `order_id` int(11) NOT NULL,
  `delivery_boy_id` int(11) DEFAULT NULL,
  `lat` double DEFAULT NULL,
  `lng` double DEFAULT NULL,
  `status` enum('pending','on_the_way','delivered') DEFAULT 'pending',
  `last_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `delivery_tracking`
--

INSERT INTO `delivery_tracking` (`order_id`, `delivery_boy_id`, `lat`, `lng`, `status`, `last_update`) VALUES
(274, 10, 16.799926333333, 96.197162666667, 'delivered', '2025-10-26 15:20:51'),
(278, 10, 16.799926333333, 96.197162666667, 'delivered', '2025-10-26 15:26:11'),
(280, 10, 16.805608374142, 96.19945246349, 'delivered', '2025-10-29 08:28:10'),
(281, 10, 16.805614024091, 96.199459517613, 'delivered', '2025-10-31 06:40:59'),
(282, 10, 16.805606034599, 96.199458644941, 'delivered', '2025-10-31 06:53:41'),
(283, 10, 16.805588716284, 96.199454958505, 'delivered', '2025-10-31 07:37:46'),
(284, 10, 16.805612389413, 96.199459990061, 'delivered', '2025-10-31 08:36:19'),
(285, 10, 16.800168416667, 96.196766166667, 'delivered', '2025-11-01 16:23:33'),
(286, 10, 16.8003007, 96.1965946, 'delivered', '2025-11-01 16:54:04'),
(289, 10, 16.805631533856, 96.199452177525, 'delivered', '2025-11-07 06:37:54'),
(290, 10, NULL, NULL, 'delivered', '2025-11-07 06:25:23'),
(293, 10, 16.805679, 96.199467, 'delivered', '2025-11-07 08:55:47'),
(294, 10, 16.8003408, 96.1966364, 'delivered', '2025-11-07 13:15:47'),
(295, 10, 16.805645561073, 96.199461157378, 'delivered', '2025-11-08 08:48:25'),
(297, 10, 16.80021725, 96.196709, 'delivered', '2025-11-09 11:45:41'),
(298, 10, 16.805602502703, 96.199463619974, 'delivered', '2025-11-10 07:31:37'),
(299, 10, 16.805645063267, 96.199457967147, 'delivered', '2025-11-10 09:05:10'),
(300, 10, 16.805623226826, 96.199394707881, 'delivered', '2025-11-11 08:32:46'),
(301, 10, 16.805637940507, 96.199393341739, 'delivered', '2025-11-11 08:52:20'),
(302, 10, 16.805643633899, 96.199433025563, 'delivered', '2025-11-11 09:03:29'),
(303, 10, 16.805579126755, 96.199390412308, 'delivered', '2025-11-12 06:18:00'),
(304, 10, 16.805625965966, 96.199426821402, 'delivered', '2025-11-12 06:42:06'),
(305, 10, 16.805594268789, 96.199389361961, 'delivered', '2025-11-12 07:34:08'),
(306, 10, 16.805640526446, 96.199451045733, 'delivered', '2025-11-12 08:19:16'),
(307, 10, 16.805630642408, 96.199439192897, 'delivered', '2025-11-12 08:29:02'),
(308, 10, 16.805641837876, 96.199453936601, 'delivered', '2025-11-12 08:45:41'),
(309, 10, 16.805606078305, 96.199428286538, 'delivered', '2025-11-12 09:26:23'),
(310, 10, NULL, NULL, 'delivered', '2025-11-12 09:25:41'),
(311, 10, NULL, NULL, 'delivered', '2025-11-12 09:32:00'),
(312, 10, NULL, NULL, 'delivered', '2025-11-14 10:07:52'),
(313, 10, NULL, NULL, 'delivered', '2025-11-14 10:10:26'),
(314, 10, NULL, NULL, 'delivered', '2025-11-14 10:14:51'),
(315, 10, 16.8003408, 96.1966364, 'delivered', '2025-11-14 15:10:26'),
(316, 10, 16.800138333333, 96.196810666667, 'delivered', '2025-11-14 14:51:40'),
(317, 10, 16.8003408, 96.1966364, 'delivered', '2025-11-14 15:22:29'),
(318, 10, 16.8003125, 96.1966945, 'delivered', '2025-11-14 15:12:27'),
(319, 10, NULL, NULL, 'delivered', '2025-11-15 05:53:20'),
(320, 10, NULL, NULL, 'delivered', '2025-11-14 16:08:56'),
(321, 10, 16.80054925, 96.1963895, 'delivered', '2025-11-16 07:36:22'),
(322, 10, NULL, NULL, 'delivered', '2025-11-16 07:40:10'),
(323, 10, NULL, NULL, 'delivered', '2025-11-16 08:03:29'),
(324, 10, 16.7995083, 96.1976836, 'delivered', '2025-11-16 09:12:35'),
(325, 10, 16.7997274, 96.1970098, 'delivered', '2025-11-16 09:13:44'),
(326, 10, 16.7995236, 96.1976882, 'delivered', '2025-11-16 12:35:20'),
(327, 10, 16.7997286, 96.1970241, 'delivered', '2025-11-16 12:48:43'),
(328, 10, NULL, NULL, 'delivered', '2025-11-17 06:08:16'),
(329, 10, NULL, NULL, 'delivered', '2025-11-17 08:10:01'),
(330, 10, 16.805581691203, 96.199365149666, 'delivered', '2025-11-18 07:22:16'),
(331, 10, 16.805567172027, 96.199354316296, 'delivered', '2025-11-18 07:33:06'),
(332, 10, NULL, NULL, 'delivered', '2025-11-18 07:44:42'),
(333, 10, NULL, NULL, 'delivered', '2025-11-18 07:42:58'),
(334, 10, 16.826368, 96.1544192, 'delivered', '2025-11-19 07:08:05'),
(335, 10, 16.805577696367, 96.199364181434, 'delivered', '2025-11-20 08:34:15'),
(336, 10, 16.805623579028, 96.199388279441, 'delivered', '2025-11-20 08:45:00'),
(339, 10, 16.7999069, 96.1972024, 'delivered', '2025-11-23 05:29:37'),
(340, 10, 16.8000963, 96.1969584, 'delivered', '2025-11-22 09:54:26'),
(341, 10, 16.7999069, 96.1972024, 'delivered', '2025-11-23 06:02:51'),
(342, 9, 16.7995018, 96.1976824, 'delivered', '2025-11-23 06:04:44'),
(343, 10, 16.8460288, 96.1314816, 'delivered', '2025-11-23 06:37:14'),
(345, 10, 16.7995032, 96.1976809, 'delivered', '2025-11-23 10:52:42'),
(346, 10, NULL, NULL, 'delivered', '2025-11-23 06:37:19'),
(347, 10, NULL, NULL, 'delivered', '2025-11-23 14:16:15'),
(348, 10, NULL, NULL, 'delivered', '2025-11-23 14:16:51'),
(349, 10, NULL, NULL, 'delivered', '2025-11-23 14:16:39'),
(350, 10, NULL, NULL, 'delivered', '2025-11-23 14:15:22'),
(351, 10, NULL, NULL, 'delivered', '2025-11-23 14:15:12'),
(352, 10, NULL, NULL, 'delivered', '2025-11-23 14:15:04'),
(353, 10, 16.805562438257, 96.199457341214, 'delivered', '2025-11-24 08:35:22'),
(354, 10, 16.805612618639, 96.199460167861, 'delivered', '2025-11-25 08:28:18'),
(356, 10, 16.8493056, 96.1314816, 'delivered', '2025-11-26 09:16:15'),
(357, 10, 16.8493056, 96.1314816, 'delivered', '2025-11-26 09:13:37'),
(358, 10, 16.8493056, 96.1314816, 'delivered', '2025-11-26 08:46:44'),
(359, 10, 16.7995112, 96.1976627, 'delivered', '2025-11-26 08:34:46'),
(360, 10, 16.8493056, 96.1314816, 'delivered', '2025-11-26 08:45:29'),
(362, 10, 16.805488832829, 96.199456434961, 'delivered', '2025-11-27 09:30:22'),
(363, 10, 16.805557802897, 96.199459225078, 'delivered', '2025-11-27 09:24:26'),
(368, 10, 16.799571, 96.19752975, 'delivered', '2025-11-30 12:18:53'),
(369, 10, 16.799571, 96.19752975, 'delivered', '2025-11-30 07:19:44'),
(370, 10, 16.799559333333, 96.197653333333, 'delivered', '2025-11-30 13:33:46'),
(373, 10, 16.799571, 96.19752975, 'delivered', '2025-11-30 13:31:59'),
(377, 10, 16.7995227, 96.1976802, 'delivered', '2025-12-03 11:53:48'),
(378, 10, 16.799559333333, 96.197653333333, 'delivered', '2025-12-04 14:47:51'),
(388, 10, 16.7995179, 96.197662, 'delivered', '2025-12-12 17:05:37'),
(390, 10, 16.7995159, 96.1976669, 'delivered', '2025-12-14 08:51:28'),
(391, 10, 16.799523, 96.1976713, 'delivered', '2025-12-14 08:52:05');

-- --------------------------------------------------------

--
-- Table structure for table `driver_locations`
--

CREATE TABLE `driver_locations` (
  `order_id` int(11) NOT NULL,
  `lat` double NOT NULL,
  `lng` double NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `driver_locations`
--

INSERT INTO `driver_locations` (`order_id`, `lat`, `lng`, `updated_at`) VALUES
(3, 16.7995343048492, 96.1975298440272, '2025-08-26 17:34:56'),
(6, 16, 96.19727882721499, '2025-08-26 18:18:17'),
(9, 16, 96.19734328335988, '2025-08-26 17:55:12'),
(14, 16, 96.1975298440272, '2025-08-27 03:53:13'),
(15, 16, 96.19734328335988, '2025-08-26 17:47:28'),
(17, 16, 96.1975298440272, '2025-08-27 03:42:23'),
(24, 16, 96.19737577239236, '2025-08-26 18:13:31'),
(25, 16, 96.19737577239236, '2025-08-26 18:11:36'),
(27, 16, 96.19752709351293, '2025-08-26 18:11:15'),
(79, 16.799533, 96.1976873, '2025-09-04 13:26:34'),
(81, 16.8558592, 96.1314816, '2025-09-04 13:37:52'),
(83, 16.799533652868, 96.197529615834, '2025-09-04 13:56:03'),
(84, 16.799534818547, 96.197525951777, '2025-09-04 14:33:15'),
(85, 16.799533950697, 96.197527771336, '2025-09-04 14:37:01'),
(87, 16.805511925935, 96.199460810266, '2025-09-05 07:54:49'),
(104, 16.8198144, 96.1511424, '2025-09-24 09:17:59'),
(265, 16.7998225, 96.19727225, '2025-10-26 14:02:38'),
(270, 16.7998225, 96.19727225, '2025-10-26 14:03:16'),
(271, 16.799926333333, 96.197162666667, '2025-10-26 13:55:51'),
(274, 16.799926333333, 96.197162666667, '2025-10-26 15:20:51'),
(278, 16.799926333333, 96.197162666667, '2025-10-26 15:26:11'),
(280, 16.805608374142, 96.19945246349, '2025-10-29 08:28:10'),
(281, 16.805614024091, 96.199459517613, '2025-10-31 06:40:59'),
(282, 16.805606034599, 96.199458644941, '2025-10-31 06:53:41'),
(283, 16.805588716284, 96.199454958505, '2025-10-31 07:37:44'),
(284, 16.805612389413, 96.199459990061, '2025-10-31 08:36:19'),
(285, 16.800168416667, 96.196766166667, '2025-11-01 16:23:33'),
(286, 16.8003007, 96.1965946, '2025-11-01 16:54:04'),
(289, 16.805631533856, 96.199452177525, '2025-11-07 06:37:54'),
(293, 16.805679, 96.199467, '2025-11-07 08:55:47'),
(294, 16.8003408, 96.1966364, '2025-11-07 13:15:47'),
(295, 16.805645561073, 96.199461157378, '2025-11-08 08:47:43'),
(297, 16.80021725, 96.196709, '2025-11-09 11:45:41'),
(298, 16.805602502703, 96.199463619974, '2025-11-10 07:31:37'),
(299, 16.805645063267, 96.199457967147, '2025-11-10 09:05:10'),
(300, 16.805623226826, 96.199394707881, '2025-11-11 08:32:46'),
(301, 16.805637940507, 96.199393341739, '2025-11-11 08:52:20'),
(302, 16.805643633899, 96.199433025563, '2025-11-11 09:03:29'),
(303, 16.805579126755, 96.199390412308, '2025-11-12 06:18:00'),
(304, 16.805625965966, 96.199426821402, '2025-11-12 06:42:06'),
(305, 16.805594268789, 96.199389361961, '2025-11-12 07:34:08'),
(306, 16.805640526446, 96.199451045733, '2025-11-12 08:19:16'),
(307, 16.805630642408, 96.199439192897, '2025-11-12 08:29:02'),
(308, 16.805641837876, 96.199453936601, '2025-11-12 08:45:41'),
(309, 16.805606078305, 96.199428286538, '2025-11-12 09:26:23'),
(315, 16.8003408, 96.1966364, '2025-11-14 15:10:26'),
(316, 16.800138333333, 96.196810666667, '2025-11-14 14:51:27'),
(317, 16.8003408, 96.1966364, '2025-11-14 15:22:29'),
(318, 16.8003125, 96.1966945, '2025-11-14 15:12:27'),
(321, 16.80054925, 96.1963895, '2025-11-16 07:36:22'),
(324, 16.7995083, 96.1976836, '2025-11-16 09:12:35'),
(325, 16.7997274, 96.1970098, '2025-11-16 09:13:44'),
(326, 16.7995236, 96.1976882, '2025-11-16 12:35:20'),
(327, 16.7997286, 96.1970241, '2025-11-16 12:48:43'),
(330, 16.805581691203, 96.199365149666, '2025-11-18 07:22:16'),
(331, 16.805567172027, 96.199354316296, '2025-11-18 07:33:06'),
(334, 16.826368, 96.1544192, '2025-11-19 07:08:05'),
(335, 16.805577696367, 96.199364181434, '2025-11-20 08:34:15'),
(336, 16.805623579028, 96.199388279441, '2025-11-20 08:45:00'),
(339, 16.7999069, 96.1972024, '2025-11-23 05:29:37'),
(340, 16.8000963, 96.1969584, '2025-11-22 09:54:26'),
(341, 16.7999069, 96.1972024, '2025-11-23 06:02:51'),
(342, 16.7995018, 96.1976824, '2025-11-23 06:04:44'),
(343, 16.8460288, 96.1314816, '2025-11-23 06:36:26'),
(345, 16.7995032, 96.1976809, '2025-11-23 10:52:42'),
(353, 16.805562438257, 96.199457341214, '2025-11-24 08:22:17'),
(354, 16.805612618639, 96.199460167861, '2025-11-25 08:27:56'),
(356, 16.8493056, 96.1314816, '2025-11-26 09:16:15'),
(357, 16.8493056, 96.1314816, '2025-11-26 09:13:32'),
(358, 16.8493056, 96.1314816, '2025-11-26 08:46:44'),
(359, 16.7995112, 96.1976627, '2025-11-26 08:34:46'),
(360, 16.8493056, 96.1314816, '2025-11-26 08:45:29'),
(362, 16.805488832829, 96.199456434961, '2025-11-27 09:30:22'),
(363, 16.805557802897, 96.199459225078, '2025-11-27 09:24:26'),
(368, 16.799571, 96.19752975, '2025-11-30 12:18:53'),
(369, 16.799571, 96.19752975, '2025-11-30 07:19:44'),
(370, 16.799559333333, 96.197653333333, '2025-11-30 13:33:46'),
(373, 16.799571, 96.19752975, '2025-11-30 13:31:45'),
(377, 16.7995227, 96.1976802, '2025-12-03 11:53:48'),
(378, 16.799559333333, 96.197653333333, '2025-12-04 14:47:51'),
(388, 16.7995179, 96.197662, '2025-12-12 17:05:37'),
(390, 16.7995159, 96.1976669, '2025-12-14 08:51:28'),
(391, 16.799523, 96.1976713, '2025-12-14 08:52:05');

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `expense_id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `note` text DEFAULT NULL,
  `rider_id` int(11) DEFAULT NULL,
  `restaurant_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_slip` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`expense_id`, `category`, `amount`, `note`, `rider_id`, `restaurant_id`, `created_at`, `payment_slip`) VALUES
(1, 'Restaurant Payout', 4875.00, 'Settlement for Week 202540', NULL, 11, '2025-10-04 23:04:59', ''),
(2, 'Restaurant Payout', 32175.00, 'Settlement for Week 202538 - CONFIRMED by restaurant on 2025-11-24 15:16:23', NULL, 1, '2025-10-05 12:53:39', ''),
(3, 'Restaurant Payout', 95175.00, 'Settlement for Week 202541 - CONFIRMED by restaurant on 2025-11-24 14:15:32', NULL, 4, '2025-10-08 08:26:29', ''),
(4, 'Restaurant Payout', 16725.00, 'Settlement for Week 202540', NULL, 26, '2025-10-08 08:26:29', ''),
(5, 'Restaurant Payout', 19051.50, 'Settlement for Week 202539', NULL, 2, '2025-10-08 08:26:29', ''),
(6, 'Restaurant Payout', 34425.00, 'Settlement for Week 202541 - CONFIRMED by restaurant on 2025-11-24 15:16:19', NULL, 1, '2025-10-12 15:51:36', ''),
(7, 'Restaurant Payout', 6000.00, 'Settlement for Week 202541', NULL, 2, '2025-10-13 06:45:42', ''),
(8, 'Restaurant Payout', 252075.00, 'Settlement for Week 202542 - CONFIRMED by restaurant on 2025-11-24 15:16:13', NULL, 1, '2025-10-29 08:29:47', ''),
(9, 'Restaurant Payout', 23400.00, 'Settlement for Week 202542 - CONFIRMED by restaurant on 2025-11-24 14:15:29', NULL, 4, '2025-10-29 08:31:48', ''),
(10, 'Restaurant Payout', 140400.00, 'Settlement for Week 202543 - CONFIRMED by restaurant on 2025-11-24 14:15:25', NULL, 4, '2025-10-29 08:31:57', ''),
(11, 'Restaurant Payout', 11025.00, 'Settlement for Week 202544 - CONFIRMED by restaurant on 2025-11-24 15:16:10', NULL, 1, '2025-10-29 08:34:24', ''),
(12, 'Restaurant Payout - JO JO Hot Pot & Mala Xiang Guo (Thaketa)', 35100.00, 'I can\'t  - Settlement for Week 202544 (ID: 22) - CONFIRMED by restaurant on 2025-11-30 18:52:55', NULL, 4, '2025-11-30 12:18:44', 'reprocessed_20251130_132019_692c36839173b.png'),
(13, 'Restaurant Payout - JO JO Hot Pot & Mala Xiang Guo (Thaketa)', 11700.00, 'Settlement for Week 202546 (ID: 23) - CONFIRMED by restaurant on 2025-12-13 19:28:40', NULL, 4, '2025-12-01 08:39:26', ''),
(14, 'Restaurant Payout - KFC (Capital Hypermarket)', 11025.00, 'Settlement for Week 202545 (ID: 24)', NULL, 1, '2025-12-01 08:40:14', ''),
(15, 'Restaurant Payout - JO JO Hot Pot & Mala Xiang Guo (Thaketa)', 11700.00, 'Settlement for Week 202549 (ID: 25) - CONFIRMED by restaurant on 2025-12-13 19:28:38', NULL, 4, '2025-12-08 06:50:20', ''),
(16, 'Restaurant Payout - KFC (Capital Hypermarket)', 3300.00, 'Settlement for Week 202549 (ID: 26)', NULL, 1, '2025-12-09 09:20:58', ''),
(17, 'Restaurant Payout - JO JO Hot Pot & Mala Xiang Guo (Thaketa)', 58500.00, 'Settlement for Week 202545 (ID: 27) - CONFIRMED by restaurant on 2025-12-13 19:28:43', NULL, 4, '2025-12-10 07:15:29', ''),
(18, 'Rider Payout - me', 3579.00, 'Payout ID: 14, Period: 2025-12-12 to 2025-12-12', 10, NULL, '2025-12-13 03:59:14', 'payout_20251213_102914_693ce49282aba.png'),
(19, 'Rider Payout - me', 700.00, 'Payout ID: 15, Period: 2025-12-04 to 2025-12-04', 10, NULL, '2025-12-13 04:41:09', 'payout_20251213_111109_693cee653a0ab.png'),
(20, 'Rider Payout - me', 700.00, 'Payout ID: 16, Period: 2025-12-03 to 2025-12-03', 10, NULL, '2025-12-13 04:41:09', 'payout_20251213_111109_693cee653a0ab.png'),
(21, 'Rider Payout - me', 4099.00, 'Payout ID: 17, Period: 2025-11-30 to 2025-11-30', 10, NULL, '2025-12-13 04:41:09', 'payout_20251213_111109_693cee653a0ab.png'),
(22, 'Rider Payout - me', 2800.00, 'Payout ID: 10, Period: 2025-11-29 to 2025-11-29', 10, NULL, '2025-12-13 04:41:09', 'payout_20251213_111109_693cee653a0ab.png'),
(23, 'Rider Payout - me', 3764.00, 'Payout ID: 18, Period: 2025-11-27 to 2025-11-27', 10, NULL, '2025-12-13 04:41:09', 'payout_20251213_111109_693cee653a0ab.png'),
(24, 'Rider Payout - me', 8812.00, 'Payout ID: 19, Period: 2025-11-26 to 2025-11-26', 10, NULL, '2025-12-13 04:41:09', 'payout_20251213_111109_693cee653a0ab.png'),
(25, 'Rider Payout - me', 6917.00, 'Payout ID: 20, Period: 2025-11-24 to 2025-11-24', 10, NULL, '2025-12-13 04:41:09', 'payout_20251213_111109_693cee653a0ab.png'),
(26, 'Rider Payout - hla myo ', 700.00, 'Payout ID: 21, Period: 2025-11-23 to 2025-11-23', 9, NULL, '2025-12-13 04:41:09', 'payout_20251213_111109_693cee653a0ab.png'),
(27, 'Rider Payout - me', 7000.00, 'Payout ID: 12, Period: 2025-11-23 to 2025-11-23', 10, NULL, '2025-12-13 04:41:09', 'payout_20251213_111109_693cee653a0ab.png'),
(28, 'Rider Payout - me', 3965.00, 'Payout ID: 22, Period: 2025-11-22 to 2025-11-22', 10, NULL, '2025-12-13 04:41:09', 'payout_20251213_111109_693cee653a0ab.png'),
(29, 'Rider Payout - me', 1400.00, 'Payout ID: 23, Period: 2025-11-20 to 2025-11-20', 10, NULL, '2025-12-13 04:41:09', 'payout_20251213_111109_693cee653a0ab.png'),
(30, 'Rider Payout - me', 700.00, 'Payout ID: 24, Period: 2025-11-19 to 2025-11-19', 10, NULL, '2025-12-13 04:41:09', 'payout_20251213_111109_693cee653a0ab.png'),
(31, 'Rider Payout - me', 4200.00, 'Payout ID: 25, Period: 2025-11-18 to 2025-11-18', 10, NULL, '2025-12-13 04:41:09', 'payout_20251213_111109_693cee653a0ab.png'),
(32, 'Rider Payout - me', 700.00, 'Payout ID: 11, Period: 2025-11-17 to 2025-11-17', 10, NULL, '2025-12-13 04:41:09', 'payout_20251213_111109_693cee653a0ab.png'),
(33, 'Restaurant Payout - JO JO Hot Pot & Mala Xiang Guo (Thaketa)', 11700.00, 'REJECTED: invalid - invalid - invalid - Settlement for Week 202550 (ID: 28)', NULL, 4, '2025-12-13 12:59:50', 'reprocessed_20251213_194218_693d663220455.png'),
(34, 'Restaurant Payout - OMUK', 13875.00, 'Settlement for Week 202546 (ID: 29)', NULL, 25, '2025-12-13 12:59:59', ''),
(36, 'Rider Payout - Kyaw Gyi', 7858.00, 'Payout ID: 29, Period: 2025-12-08 to 2025-12-14', 10, NULL, '2025-12-14 12:29:23', 'payout_20251214_185923_693eada33c751.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `item_id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `status` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`item_id`, `restaurant_id`, `name`, `description`, `price`, `image`, `category`, `is_available`, `status`) VALUES
(1, 1, 'Trio Snack Box Combo', 'Chicken\r\n', 14700.00, 'triosnackbox.png\r\n', 'Sides and Snacks', 1, 0),
(3, 1, 'Popcorn Chicken', '1 popcorn chicken', 5500.00, 'popcornchicken.png\r\n', 'Sides and Snacks', 1, 1),
(4, 1, 'Chicken Nuggets ', '5 pcs chicken nuggets', 8200.00, 'chickennuggets.png', 'Sides and Snacks', 1, 1),
(14, 1, 'Large Fries', '1 large fries', 4400.00, 'lgfries.png', 'Sides and Snacks', 1, 1),
(15, 2, 'coffee', 'COffee', 8000.00, 'coffee.jfif', 'other', 1, 1),
(16, 10, 'Prawn', NULL, 1500.00, 'prawn.png', 'meat', 1, 1),
(17, 10, 'squid fish', NULL, 1500.00, 'squid2.png', 'fish', 1, 1),
(18, 10, 'Chicken Noodle', NULL, 4500.00, 'chickenNoodle.png', 'Noodle', 1, 1),
(21, 14, 'Pounded Papaya salad', 'fresh green papaya salad pounded with chili,carrot,lime,garlic,and fish sauce for a spicy,tangy,and refreshing flavor', 12500.00, 'ThaiPapayaSalad.jpg', 'salad', 1, 1),
(22, 14, 'Chicken Rice', 'Tender chicken served with fragrant rice,paired with light soup and flavorful dipping sauce', 13500.00, 'chicken-rice.jpg', 'Main', 1, 1),
(23, 14, 'Grilled Pork Neck', 'Tender grilled pork with savory sause', 21500.00, 'grillporkneck.png', 'meat', 1, 1),
(24, 14, 'Thai Fried Rice', 'Thai style fried rice with vegetables and your choice of protein', 12500.00, 'thai-fried-rice.png', 'main', 1, 1),
(25, 14, 'Thai Traditional Tom-Yam Soup', 'Spicy and tangy with fresh herbs and zesty lime flavours', 16500.00, 'thai-ty.png', 'soup', 1, 1),
(26, 28, 'Grilled Pork Knuckle', 'Crispy skin, juicy meat, fire-grilled flavor.', 8000.00, 'grillpork.jpg', 'meat', 1, 1),
(27, 28, 'Pounded Papaya', 'green papaya salad with chili, lime, and garlic.', 3000.00, 'papayapounded.jpg', 'salad', 1, 1),
(28, 28, 'Mala Xiang Guo(small)', 'Spicy, smoky, and customizable.', 8000.00, 'malaxg.jpg', 'Chinese', 1, 1),
(29, 28, 'Mala Chuan', 'Spicy, numbing Sichuan hotpot.', 12000.00, 'malamc.jpg', 'Chinese', 1, 1),
(30, 28, 'Mala-spiced grilled meat salad', 'Grilled meat with spicy Sichuan kick.', 0.00, 'malakin.jpg', 'Chinese', 1, 1),
(31, 18, 'Sausage and seaweed Crepe', 'Mala+sausage+seaweed', 0.00, 'crepe1.png', 'crepe', 1, 1),
(32, 18, 'Crab stick crepe', 'crab stick+cheddar cheese', 0.00, 'crepe2.png', 'crepe', 1, 1),
(33, 18, 'sausage and cheese crepe', 'Pizza sause,sausage,cheese', 0.00, 'crepe3.png', 'crepe', 1, 1),
(34, 18, 'Chicken Floss Crepe', 'BBQ ,Chicken Flooos and Chese Slice', 6100.00, 'crepe4.png', 'crepe', 1, 1),
(35, 18, 'Chicken Floss and Egg Crepe', 'Pizza Sauce,Chicken Floss,Mozzarella Chese and Egg', 7200.00, 'crepe5.png', 'crepe', 1, 1),
(37, 18, 'Strawberry Crepe', 'Strawberry,Cashcew Nut', 0.00, 'crepe7.png', 'crepe', 1, 1),
(38, 17, 'Cookie Ice Cream', 'oreo taste', 100.00, 'ice-coo.png', 'Ice Cream', 1, 1),
(39, 17, 'Hokkaido Milk Matcha Lce Cream', 'Creamy Hokkaido milk with bold matcha.', 100.00, 'ice-hmm.png', 'Ice Cream', 1, 1),
(40, 17, 'Lime Ice cream', 'Tangy, creamy, and refreshingly citrusy.', 3000.00, 'ice-lime.png', 'Ice-Cream', 1, 1),
(41, 17, 'Raspberry Ice Cream', 'Sweet, tangy, and bursting with berry flavor.', 3000.00, 'ice-ras.png', 'Ice Cream', 1, 1),
(44, 17, 'Taro Ice Cream', 'Taste The Magic Of Taro', 3000.00, 'ice-taro.png', 'Ice Cream', 1, 1),
(45, 15, 'Classic Corndog', 'Crispy, golden, and savory.', 2500.00, 'ele-classic.jpg', 'corndog', 1, 1),
(46, 15, 'Potato Corndog', 'Potato-coated sausage, golden-fried and savory.', 3000.00, 'ele-patota.jpg', 'corndog', 1, 1),
(47, 15, 'Chese Corndog', 'Crispy batter outside, gooey mozzarella inside.', 2500.00, 'ele-cheese.jpg', 'corndog', 1, 1),
(48, 15, 'half Cheese classic Corndog', 'Mozzarella and sausage in crispy batter.', 3000.00, 'ele-classic.jpg', 'corndog', 1, 1),
(49, 23, 'Bubble Milk Tea', 'Creamy milk tea with chewy tapioca pearls.', 3000.00, 'bubblemilk-tea.jpg', 'Bubble Tea', 1, 1),
(50, 23, 'Mango Smoothie', 'Sweet, creamy, and tropically refreshing.', 6800.00, 'mango-smoothie.jpg', 'drink', 1, 1),
(51, 23, 'Double Chocolate', 'Rich, fudgy, and packed with chocolatey goodness.', 6800.00, 'ocha-double-chocolate.jpg', 'drink', 1, 1),
(52, 23, 'Matcha Green Bubble', 'Earthy matcha tea with chewy tapioca pearls.', 7000.00, 'ocha-matcha-green-bubble.jpg', 'Tea', 1, 1),
(53, 23, 'Strawberry Smotthie', 'Sweet, creamy, and refreshingly fruity.', 6800.00, 'ocha-strawberrysmottie.jpg', 'drink', 1, 1),
(54, 23, 'Chocolate Milk Tea', 'Creamy, chocolatey, with chewy boba.', 7000.00, 'ocha-chocolatemilktea.jpg', 'drink', 1, 1),
(55, 23, 'Thai Green Milk Tea', 'Creamy green tea with a sweet, floral twist.', 7000.00, 'thaigreenmilktea.jpg', 'Tea', 1, 1),
(56, 14, 'ToFu Soup with Meatball', 'Light broth with tender meatballs and silky tofu.', 12000.00, 'tofumeatball.png', 'soup', 1, 1),
(57, 25, 'Kimbap', 'Korean seaweed rice roll with assorted fillings.', 8500.00, 'omukkimbap.png', 'Korea', 1, 1),
(58, 25, 'Tteokbottki', 'Chewy rice cakes in spicy-sweet Korean chili sauce.', 6500.00, 'omuk-tbk.png', 'korea', 1, 1),
(59, 25, 'Kimchi Jjigae', 'Spicy stew with kimchi and tofu.', 10000.00, 'omuk-kj.png', NULL, 1, 1),
(60, 25, 'Fishcake', NULL, 6500.00, 'omuk-fc.png', 'korea', 1, 1),
(61, 25, 'Bundae Jjigae', NULL, 10500.00, 'omuk-bj.png', 'korea', 1, 1),
(62, 25, 'Bibimbap', NULL, 11000.00, 'omuk-bibimbap.jpg', 'korea', 1, 1),
(63, 26, 'Thai black Tea', NULL, 9500.00, 'thaiblacktea.png', 'Tea', 1, 1),
(64, 26, 'Thai Coffee', NULL, 10000.00, 'thaicoffee.png', 'coffee', 1, 1),
(65, 26, 'Vegan Green Tea', NULL, 10000.00, 'vegangreentea.png', 'Tea', 1, 1),
(66, 26, 'Vegan Tea Oak', NULL, 10000.00, 'veganoak.png', 'Tea', 1, 1),
(67, 26, 'Vegan Green Tea', NULL, 9500.00, 'vegangreentea.png', 'Tea', 1, 1),
(68, 26, 'Lemon Tea', NULL, 9000.00, 'lemontea.png', 'Tea', 1, 1),
(69, 26, 'Vegan Strawberry', NULL, 10000.00, 'veganstraw.png', 'Tea', 1, 1),
(70, 12, 'butterfly Puff', NULL, 2100.00, 'kudo1.png', 'bakery', 1, 1),
(71, 12, 'Cheese Puff', NULL, 3100.00, 'kudos2.png', 'bakery', 1, 1),
(72, 12, 'Chocolate Chip Cookies', NULL, 3500.00, 'kudos3.png', 'bakery', 1, 1),
(73, 12, 'Chicken Curry puff', NULL, 3100.00, 'kudos4.jpg', 'bakery', 1, 1),
(74, 12, 'Chocolate Cube', NULL, 3400.00, 'chocube.jpg', 'bakery', 1, 1),
(75, 10, 'Rice', NULL, 1000.00, 'rice.png', 'rice', 1, 1),
(77, 1, 'Zinger Burger Combo', 'Contains 1 zinger burger,1 fries and 1 of 16oz beverages', 16200.00, 'zingercombo.png', 'Burger', 1, 1),
(78, 1, 'Zinger Stacker Burger', '1 zinger stacker burger ', 14500.00, 'zingerstacker.png', 'Burger', 1, 1),
(79, 1, 'Zinger Stacker Burger Combo', 'Contains 1 zinger stacker burger,1 fries and 1 of 16oz beverages', 20300.00, 'zingerstackercombo.png', 'Burger', 1, 1),
(80, 1, 'Double Down Combo', 'Contains 1 double down,1 fries and 1 of 16oz beverages', 16500.00, 'doubledowncombo.png', 'Burger', 1, 1),
(81, 1, 'Double Down', '1 double down', 10500.00, 'doubledown.png', 'Burger', 1, 1),
(82, 3, 'Kyay Oh Sichet Regular', 'chicken kyay oh', 8000.00, 'sichetregular.webp', 'Kyay Oh', 1, 1),
(83, 20, 'Fried dumpling', 'crispy outside, juicy inside. A golden bite of savory delight.', 7000.00, 'kob-6.jpg', 'Chinese', 1, 1),
(84, 20, 'Tom Yum Soup', 'Spicy, sour Thai broth with herbs and shrimp.', 9000.00, 'kob7.jpg', 'Thai', 1, 1),
(85, 19, 'Pe Kyar Zan Thoke (Glass Noodle Salad)', 'Tangy glass noodles with veggies and crunchy toppings.', 7000.00, 'swell-1.jpg', 'Myanmar', 1, 1),
(86, 19, 'mala Xiang Guo', 'Chicken Couple Set', 17000.00, 'swell-2.jpg', 'Chinese', 1, 1),
(87, 19, 'Cola Chicken Wing', 'Sticky, sweet wings glazed with cola and soy.', 9800.00, 'swell-31.png', 'Thai', 1, 1),
(88, 19, 'Fried Rice with Fish Paste-khao Kluk Kapi', 'Thai shrimp paste fried rice with sides.', 7500.00, 'swell-4.png', 'Thai', 1, 0),
(89, 19, 'Fried Chicken Thai Style', 'Crispy chicken with Thai herbs,fried shallots.', 11500.00, 'swell-5.png', 'Thai', 1, 1),
(90, 19, 'Kung Pao Chicken', 'Spicy stir-fried chicken with peanuts and chilies.', 11000.00, 'swell-6.jpg', 'Chinese', 1, 0),
(91, 19, 'Delicious Sweet and Sour Fish', 'Crispy fish in tangy pineapple-tomato sauce.', 17000.00, 'swell-7.jpg', 'Chinese', 1, 0),
(92, 21, 'Spaghetti', 'Savor the flavor in every single bite', 10000.00, 'kbl-1.jpg', 'dish', 1, 1),
(93, 21, 'Salmon Sashimi', 'A delicate Slice of rich Flavor', 12000.00, 'kbl-2.jpg', 'meat', 1, 1),
(94, 21, 'Grilled Beat Mixed Rice', 'Grilled to perfection Mixed with flavor', 20000.00, 'kbl-3.jpg', 'main', 1, 1),
(95, 21, 'Dumpling Soup ', 'Soup for the soul Dumplings for the heart', 15000.00, 'kbl-4.jpg', 'soup', 1, 1),
(96, 21, 'Braised Cola Chicken Wings', 'Cola Magic , Wings Perfection', 15000.00, 'kbl-6.jpg', 'dish', 1, 1),
(97, 21, 'Vegetable Tempura', 'A delicate crunch, A fresh taste', 14000.00, 'kbl-5.jpg', 'dish', 1, 1),
(98, 21, 'Mala Tofu', 'soft Tofu , Fierce Flavor', 18000.00, 'kbl-7.jpg', 'soup', 1, 1),
(99, 11, 'Rakhine Traditional Fish sop(Mont Ti)', 'Spicy fish noodle soup.', 3000.00, 'ml-1.png', 'Myanmar', 1, 1),
(100, 11, 'Rakhine papaya Salad', 'Fresh papaya with chili , lime ', 4000.00, 'ml-2.jpg', 'Myanmar', 1, 1),
(101, 11, 'Lobster soup', 'Rich, velvety broth with tender lobster and herbs.', 5500.00, 'ml-3.jpg', 'Soup', 1, 1),
(102, 11, 'Roasted Soba Fish', 'Grilled fish served over nutty soba noodles.', 8000.00, 'ml-4.jpg', 'Curry', 1, 1),
(103, 11, 'Fish head Curry', 'Spicy, tangy curry with tender fish head', 5000.00, 'ml-5.png', 'Curry', 1, 1),
(104, 11, 'Fish Soup', 'delicious and fresh fish with chili', 6000.00, 'ml-6.png', 'soup', 1, 1),
(105, 11, 'Rakhine Seafood Soup', 'Delicious and Fresh Seafood with chili , lime', 14000.00, 'ml-7.png', 'soup', 1, 1),
(112, 13, 'Combination 2 pork Belly', 'Juicy pork belly with tasty sides', 15000.00, 'hk-1.jpg', 'Chinese', 1, 1),
(113, 13, 'Clay port rice with honey', 'Honey-glazed clay pot rice, warm and flavorful', 20000.00, 'hk-2.jpg', 'Chinese', 1, 1),
(114, 13, 'Sichuan Style Dry Pot with assorted Meat and Vegetable', 'Spicy Sichuan dry pot with mixed meats and veggies.', 16000.00, 'hk-3.jpg', 'Chinese', 1, 1),
(115, 13, 'Hong Kong Style Fried Rice', 'Classic Hong Kong-style fried rice and flavorful', 12000.00, 'hk-4.jpg', 'Chinese', 1, 1),
(116, 13, 'Crispy Broneless Chicken with prawn & Fishcake', 'Crispy Broneless Chicken with prawn & Fishcake', 14000.00, 'hk-5.jpg', 'Chinese', 1, 1),
(117, 13, 'Poached Spicy Seabass', 'Tender seabass poached in spicy broth.', 12000.00, 'hk-6.jpg', 'Chinese', 1, 1),
(118, 13, 'Mapo Tofu', 'Spicy Sichuan tofu with minced meat in rich chili sauce.', 12000.00, 'hk-7.jpg', 'Chinese', 1, 1),
(119, 31, 'sichuan mala', 'Bold, spicy Sichuan stir-fry with numbing heat.', 12000.00, 'lat-1.jpg', 'dish', 1, 1),
(120, 31, 'mala xiang guo', 'Fiery dry pot with mixed meats and vegetables.', 15000.00, 'lat-2.jpg', 'Chinese', 1, 1),
(121, 31, 'Breef Salad', 'Fresh salad with tender slices of beef.', 15000.00, 'lat-4.jpg', 'dish', 1, 1),
(122, 31, 'Fried Enoki Mashroom', 'Crispy golden-fried enoki mushrooms', 8000.00, 'lat-5.jpg', 'dish', 1, 1),
(123, 31, 'Chicken Cola', 'Sweet and savory chicken cooked with cola sauce.', 12000.00, 'lat-3.jpg', 'dish', 1, 1),
(124, 31, 'Spicy Potato Salad', 'Zesty potato salad with a spicy kick.', 10000.00, 'lat-6.jpg', 'dish', 1, 1),
(125, 31, 'Fried Lotus Salad', 'Zesty potato salad with a spicy kick.', 9000.00, 'lat-7.jpg', 'salad', 1, 1),
(129, 1, 'Nestea Lemon', 'fresh and energy drink', 3400.00, 'nestea.webp', 'Beverages', 1, 1),
(133, 10, 'Rice', NULL, 1000.00, 'Rice.png', 'rice', 1, 1),
(134, 10, 'Prawn', NULL, 1500.00, 'prawn.png', 'meat', 1, 1),
(135, 10, 'squid fish', NULL, 1500.00, 'squid2.png', 'fish', 1, 1),
(136, 10, 'Chicken Noodle', NULL, 4500.00, 'chickenNoodle.jpg', 'Noodle', 1, 1),
(137, 14, 'Pounded Papaya salad', 'fresh green papaya salad pounded with chili,carrot,lime,garlic,and fish sauce for a spicy,tangy,and refreshing flavor', 12500.00, 'ThaiPapayaSalad.jpg', 'salad', 1, 1),
(138, 14, 'Chicken Rice', 'Tender chicken served with fragrant rice,paired with light soup and flavorful dipping sauce', 13500.00, 'chicken-rice.jpeg', 'Main', 1, 1),
(139, 14, 'Grilled Pork Neck', 'Tender grilled pork with savory sause', 21500.00, 'grillporkneck.png', 'meat', 1, 1),
(140, 14, 'Thai Fried Rice', 'Thai style fried rice with vegetables and your choice of protein', 12500.00, 'thai-fried-rice.png', 'main', 1, 1),
(141, 14, 'Thai Traditional Tom-Yam Soup', 'Spicy and tangy with fresh herbs and zesty lime flavours', 16500.00, 'thai-ty.jpeg', 'soup', 1, 1),
(142, 28, 'Grilled Pork Knuckle', 'Crispy skin, juicy meat, fire-grilled flavor.', 8000.00, 'grillpork.jpg', 'meat', 1, 1),
(143, 28, 'Pounded Papaya', 'green papaya salad with chili, lime, and garlic.', 3000.00, 'papayapounded.jpg', 'salad', 1, 1),
(144, 28, 'Mala Xiang Guo(small)', 'Spicy, smoky, and customizable.', 8000.00, 'malaxg.jpg', 'Chinese', 1, 1),
(145, 28, 'Mala Chuan', 'Spicy, numbing Sichuan hotpot.', 12000.00, 'malamc.jpg', 'Chinese', 1, 1),
(146, 28, 'Mala-spiced grilled meat salad', 'Grilled meat with spicy Sichuan kick.', 0.00, 'malakin.jpg', 'Chinese', 1, 1),
(147, 18, 'Sausage and seaweed Crepe', 'Mala+sausage+seaweed', 0.00, 'crepe1.png', 'crepe', 1, 1),
(148, 18, 'Crab stick crepe', 'crab stick+cheddar cheese', 0.00, 'crepe2.png', 'crepe', 1, 1),
(149, 18, 'sausage and cheese crepe', 'Pizza sause,sausage,cheese', 0.00, 'crepe3.png', 'crepe', 1, 1),
(150, 18, 'Chicken Floss Crepe', 'BBQ ,Chicken Flooos and Chese Slice', 6100.00, 'crepe4.png', 'crepe', 1, 1),
(151, 18, 'Chicken Floss and Egg Crepe', 'Pizza Sauce,Chicken Floss,Mozzarella Chese and Egg', 7200.00, 'crepe5.png', 'crepe', 1, 1),
(152, 18, 'Chocolate orea Crepe', 'Chocolate , orea', 0.00, 'crepe6.png', 'crepe', 1, 1),
(153, 18, 'Strawberry Crepe', 'Strawberry,Cashcew Nut', 0.00, 'crepe7.png', 'crepe', 1, 1),
(154, 17, 'Cookie Ice Cream', 'oreo taste', 100.00, 'ice-coo.png', 'Ice Cream', 1, 1),
(155, 17, 'Hokkaido Milk Matcha Lce Cream', 'Creamy Hokkaido milk with bold matcha.', 100.00, 'ice-hmm.png', 'Ice Cream', 1, 1),
(156, 17, 'Lime Ice cream', 'Tangy, creamy, and refreshingly citrusy.', 3000.00, 'ice-lime.png', 'Ice-Cream', 1, 1),
(157, 17, 'Raspberry Ice Cream', 'Sweet, tangy, and bursting with berry flavor.', 3000.00, 'ice-ras.png', 'Ice Cream', 1, 1),
(158, 17, 'Taro Ice Cream', 'Taste The Magic Of Taro', 3000.00, 'ice-taro.png', 'Ice Cream', 1, 1),
(159, 15, 'Classic Corndog', 'Crispy, golden, and savory.', 2500.00, 'ele-classic.jpg', 'corndog', 1, 1),
(160, 15, 'Potato Corndog', 'Potato-coated sausage, golden-fried and savory.', 3000.00, 'ele-patota.jpg', 'corndog', 1, 1),
(161, 15, 'Chese Corndog', 'Crispy batter outside, gooey mozzarella inside.', 2500.00, 'ele-cheese.jpg', 'corndog', 1, 1),
(162, 15, 'half Cheese classic Corndog', 'Mozzarella and sausage in crispy batter.', 3000.00, 'ele-classic.jpg', 'corndog', 1, 1),
(163, 23, 'Bubble Milk Tea', 'Creamy milk tea with chewy tapioca pearls.', 3000.00, 'bubblemilk-tea.jpg', 'Bubble Tea', 1, 1),
(164, 23, 'Mango Smoothie', 'Sweet, creamy, and tropically refreshing.', 6800.00, 'mango-smoothie.jpg', 'drink', 1, 1),
(165, 23, 'Double Chocolate', 'Rich, fudgy, and packed with chocolatey goodness.', 6800.00, 'ocha-double-chocolate.jpg', 'drink', 1, 1),
(166, 23, 'Matcha Green Bubble', 'Earthy matcha tea with chewy tapioca pearls.', 7000.00, 'ocha-matcha-green-bubble.jpg', 'Tea', 1, 1),
(167, 23, 'Strawberry Smotthie', 'Sweet, creamy, and refreshingly fruity.', 6800.00, 'ocha-strawberrysmottie.jpg', 'drink', 1, 1),
(168, 23, 'Chocolate Milk Tea', 'Creamy, chocolatey, with chewy boba.', 7000.00, 'ocha-chocolatemilktea.jpg', 'drink', 1, 1),
(169, 23, 'Thai Green Milk Tea', 'Creamy green tea with a sweet, floral twist.', 7000.00, 'thaigreenmilktea.jpg', 'Tea', 1, 1),
(170, 14, 'ToFu Soup with Meatball', 'Light broth with tender meatballs and silky tofu.', 12000.00, 'tofumeatball.png', 'soup', 1, 1),
(171, 25, 'Kimbap', 'Korean seaweed rice roll with assorted fillings.', 8500.00, 'omukkimbap.png', 'Korea', 1, 1),
(172, 25, 'Tteokbottki', 'Chewy rice cakes in spicy-sweet Korean chili sauce.', 6500.00, 'omuk-tbk.png', 'korea', 1, 1),
(173, 25, 'Kimchi Jjigae', 'Spicy stew with kimchi and tofu.', 10000.00, 'omuk-kj.png', 'korea', 1, 1),
(174, 25, 'Fishcake', 'fresh and delicious fishcake', 6500.00, 'omuk-fc.png', 'korea', 1, 1),
(175, 25, 'Bundae Jjigae', 'Spicy stew with ramen, kimchi, Spam, and sausage.', 10500.00, 'omuk-bj.png', 'korea', 1, 1),
(176, 25, 'Bibimbap', 'Mixed rice with veggies, beef, and spicy sauce.', 11000.00, 'omuk-bibimbap.jpg', 'korea', 1, 1),
(177, 26, 'Thai black Tea', 'Bold, spiced iced tea—refreshing and aromatic.', 9500.00, 'thaiblacktea.png', 'Tea', 1, 1),
(178, 26, 'Thai Coffee', 'Strong, sweet, and spiced—classic Thai-style brew.', 10000.00, 'thaicoffee.png', 'coffee', 1, 1),
(179, 26, 'Vegan Green Tea', 'Light, clean, and plant-based.', 10000.00, 'vegangreentea.png', 'Tea', 1, 1),
(180, 26, 'Vegan Tea Oak', 'Earthy, plant-based tea blend.', 10000.00, 'veganoak.png', 'Tea', 1, 1),
(181, 26, 'Vegan Green Tea', 'Pure, plant-based green tea—light and refreshing', 9500.00, 'vegangreentea.png', 'Tea', 1, 1),
(182, 26, 'Lemon Tea', 'Zesty, soothing tea with a splash of lemon.', 9000.00, 'lemontea.png', 'Tea', 1, 1),
(183, 26, 'Vegan Strawberry', 'Fresh, fruity, and dairy-free strawberry delight.', 10000.00, 'veganstraw.png', 'Tea', 1, 1),
(184, 12, 'butterfly Puff', 'Sweet, flaky pastry shaped like wings.', 2100.00, 'kudo1.png', 'bakery', 1, 1),
(185, 12, 'Cheese Puff', 'Light, crispy pastry filled with rich, cheese.', 3100.00, 'kudos2.png', 'bakery', 1, 1),
(186, 12, 'Chocolate Chip Cookies', 'Classic, chewy cookies loaded with rich chocolate chips.', 3500.00, 'kudos3.png', 'bakery', 1, 1),
(187, 12, 'Chicken Curry puff', 'Spiced chicken in flaky pastry.', 3100.00, 'kudos4.jpg', 'bakery', 1, 1),
(188, 12, 'Chocolate Cube', 'Rich, bite-sized chocolate with a smooth melt.', 3400.00, 'chocube.jpg', 'bakery', 1, 1),
(189, 20, 'Kyay Oh(Chicken)', 'Chicken noodle soup with meatballs and vegetable.', 10800.00, 'kob1.jpg', 'main', 1, 1),
(190, 20, 'Dry Kyay Oh Salad', 'Sesame-oil noodles with meatballs and crispy toppings.', 10800.00, 'kob2.png', 'Kyay Oh', 1, 1),
(191, 20, 'Htamin Pound', 'Rice with a wider variety of vegetables and meats', 9500.00, 'kob3.jpg', 'Myanmar', 1, 1),
(192, 20, 'Myanmar Fried Stir Noodles (Kyar Zan Kyaw)', 'Stir-fried glass noodle with meat, veggies,flavor.', 9000.00, 'kob-4.png', 'Myanmar', 1, 1),
(193, 20, 'Dumping Soup', 'tender dumplings in hot broth. Cozy and tasty.', 7000.00, 'kob5.jpg', 'Chinese', 1, 1),
(194, 4, 'Mala Xiang Guo(Set Menu)', 'Fromm 15600 MMK', 15600.00, 'xiangguo.jpg', 'Mala Xiang Guo', 1, 1),
(195, 4, 'Mala Xiang Guo(Customize)', 'From 0 MMK', 0.00, 'xiangguo.jpg', 'Mala Xiang Guo', 1, 1),
(196, 1, 'Butterfly Peach', 'Beat the heat with butterfly peach drink', 4400.00, '691c0dab1a1ae.webp', 'Beverages', 1, 1),
(197, 1, 'Raspberry Fizz', 'Beat the heart with raspberry fizz drink', 4400.00, 'rasp.webp', 'Beverages', 1, 1),
(198, 1, 'Iced Coffee', '1 iced coffee', 4100.00, 'ice.webp', 'Beverages', 1, 1),
(199, 1, 'Iced Milo', '1 iced milo', 4200.00, 'milo.webp', 'Beverages', 1, 1),
(200, 1, 'Coke', '1 16oz coke', 2800.00, 'coke.webp', 'Beverages', 1, 1),
(201, 1, 'Sprite', '1 16oz sprite', 2800.00, 'sprite.webp', 'Beverages', 1, 1),
(202, 1, 'Popcorn Tokyo Curry Rice Box', '1 popcorn tokyo curry rice box', 13800.00, 'tokyo.webp', 'Rice Box', 1, 1),
(203, 1, 'Hta Min See San', 'Contains 1pc chicken with hta min', 7100.00, 'htamin.webp', 'Rice Box', 1, 1),
(204, 1, 'Hta Min See San 16oz Drink Combo', 'Contains 1pc chicken with hta min see san and 1 of 16oz beverages', 9400.00, 'htamindrink.webp', 'Rice Box', 1, 1),
(205, 1, 'Hta Min See San Iced Lemon Tea Combo', 'Contains 1pc chicken with hta min see san and 1Nesta Lemon', 10200.00, 'htaminlemon.webp', 'Rice Box', 1, 1),
(206, 1, 'Zinger Tokyo Curry Rice Box Combo', 'Contains 1 zinger tokyo curry rice box,1 fries and 1 of 16oz beverages', 19700.00, 'zingertokyo.webp', 'Rice Box', 1, 1),
(207, 1, 'Zinger Tokyo Curry Rice Set', 'Contains 1 zinger tokyo curry rice box and 1 of 16oz beverages', 16200.00, 'zingercurry.webp', 'Rice Box', 1, 1),
(208, 1, '4 Pcs Chicken', 'Contains 2pc chicken and 1 of 16oz beverages', 12000.00, '4pcs.webp', 'Meals To Share', 1, 1),
(209, 1, '6 Pcs Chicken', '6 Pcs Chicken', 29200.00, '6pcs.webp', 'Meals To Share', 1, 1),
(210, 1, '8 Pcs Bucket', 'Contains 8 Pcs Chicken', 38900.00, '8pc.webp', 'Meals To Share', 1, 1),
(211, 1, '10pc Bucket', 'Contains 10 Pcs Chicken', 48600.00, '10pc.webp', 'Meals To Share', 1, 1),
(212, 1, 'Trio Chicken Box Combo', 'Contains 3pcs chicken,1boneless chicken and 1popcorn chicken', 25000.00, 'trio.webp', 'Meals To Share', 1, 1),
(213, 1, 'Family Feast ', 'Contains 5pc chicken,2 fries,1 popcorn chicken and 2 of 16oz beverages', 41700.00, 'family.webp', 'Meals To Share', 1, 1),
(215, 4, 'Beef Slice Set', 'Set Menu', 13000.00, '69158111663bc.webp', 'Hot Pot', 1, 1),
(216, 4, 'Chicken Slice Set', 'Set Menu', 13000.00, '691581b340843.webp', 'Hot Pot', 1, 1),
(217, 4, 'Pork Slice Set', 'Set Menu', 13000.00, '6915821375bd8.webp', 'Hot Pot', 1, 1),
(218, 4, 'Sea Food Set', 'Set Menu', 16000.00, '691582613a9b2.webp', 'Hot Pot', 1, 1),
(219, 4, 'HtooAungHlaing', 'Tasty delicious ,beautiful pretty boy', 99999999.99, '691592d6c402f.jpg', 'Snacks', 1, 0),
(220, 1, 'Trio Snack Box', 'Chicken', 14700.00, '69218b9396b1d.png', 'Sides and Snacks', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `menu_item_options`
--

CREATE TABLE `menu_item_options` (
  `item_id` int(11) NOT NULL,
  `option_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `menu_item_options`
--

INSERT INTO `menu_item_options` (`item_id`, `option_id`) VALUES
(49, 6),
(82, 1),
(82, 2),
(195, 7),
(195, 8),
(195, 9),
(195, 10),
(195, 11),
(196, 20),
(215, 14),
(216, 15),
(218, 16),
(218, 17),
(218, 18),
(218, 19);

-- --------------------------------------------------------

--
-- Table structure for table `menu_options`
--

CREATE TABLE `menu_options` (
  `option_id` int(11) NOT NULL,
  `option_name` varchar(100) NOT NULL,
  `option_type` enum('single_select','multi_select') NOT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `menu_options`
--

INSERT INTO `menu_options` (`option_id`, `option_name`, `option_type`, `is_required`) VALUES
(1, 'Meat Type', 'single_select', 1),
(2, 'Addons', 'multi_select', 0),
(6, 'Sugar Level', 'single_select', 1),
(7, 'Choice of Type for Mala Xiang Guo', 'single_select', 1),
(8, 'Choice of Taste', 'single_select', 1),
(9, 'Choice of Add Ons Meat Slice for Mala Xiang Guo', 'multi_select', 1),
(10, 'Choice of Meat Ball for Mala Xiang Guo', 'multi_select', 1),
(11, 'Choice of Vegetables for Mala Xiang Guo', 'multi_select', 1),
(14, 'Choice of Soup For Set', 'single_select', 1),
(15, 'Choice of Soup For Set', 'single_select', 1),
(16, 'Choice of Rice and Noodles for Set', 'single_select', 1),
(17, 'Choice of Soup For Set', 'single_select', 1),
(18, 'Choice of Vegetables for Set', 'multi_select', 0),
(19, 'Choice of Meats for Set', 'multi_select', 0),
(20, 'SUGAR', 'single_select', 1);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `recipient_type` enum('customer','restaurant','rider','admin') NOT NULL DEFAULT 'customer',
  `title` varchar(255) NOT NULL,
  `message` varchar(255) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `restaurant_id`, `order_id`, `recipient_type`, `title`, `message`, `is_read`, `created_at`) VALUES
(7, 16, 4, 123, 'restaurant', 'Order #123 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-04 16:12:37'),
(10, 16, 4, 137, 'restaurant', 'Order #137 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-05 15:56:11'),
(11, 16, 4, 140, 'restaurant', 'Order #140 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-06 12:13:39'),
(12, 16, 4, 141, 'restaurant', 'Order #141 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-06 12:26:50'),
(13, 16, 4, 148, 'restaurant', 'Order #148 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-07 06:19:44'),
(14, 16, 4, 145, 'restaurant', 'Order #145 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-07 06:19:45'),
(15, 16, 4, 146, 'restaurant', 'Order #146 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-07 06:19:47'),
(16, 2, 1, 155, 'restaurant', 'Order #155 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-07 06:23:21'),
(17, 2, 1, 175, 'restaurant', 'Order #175 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-08 06:38:13'),
(18, 2, 1, 176, 'restaurant', 'Order #176 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-08 06:44:38'),
(19, 16, 4, 170, 'restaurant', 'Order #170 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-08 06:50:47'),
(20, 16, 4, 167, 'restaurant', 'Order #167 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-08 07:03:01'),
(21, 11, 2, 178, 'restaurant', 'Order #178 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-08 07:23:40'),
(22, 16, 4, 184, 'restaurant', 'Order #184 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-09 15:56:40'),
(23, 2, 1, 185, 'restaurant', 'Order #185 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-10 07:04:05'),
(24, 2, 1, 187, 'restaurant', 'Order #187 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-10 07:32:14'),
(25, 11, 2, 186, 'restaurant', 'Order #186 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-10 08:12:49'),
(26, 2, 1, 189, 'restaurant', 'Order #189 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-10 08:35:08'),
(27, 2, 1, 190, 'restaurant', 'Order #190 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-10 16:21:45'),
(28, 2, 1, 183, 'restaurant', 'Order #183 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-10 16:24:29'),
(29, 16, 4, 191, 'restaurant', 'Order #191 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-10 17:00:47'),
(30, 16, 4, 192, 'restaurant', 'Order #192 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-10 17:43:37'),
(31, 16, 4, 193, 'restaurant', 'Order #193 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-10 17:47:27'),
(32, 16, 4, 194, 'restaurant', 'Order #194 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-10 18:34:50'),
(33, 16, 4, 195, 'restaurant', 'Order #195 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-10 19:23:44'),
(34, 16, 4, 196, 'restaurant', 'Order #196 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-10 19:35:24'),
(35, 16, 4, 197, 'restaurant', 'Order #197 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-10 19:43:40'),
(36, 16, 4, 198, 'restaurant', 'Order #198 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-10 19:48:14'),
(37, 16, 4, 199, 'restaurant', 'Order #199 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-10 19:51:29'),
(38, 16, 4, 200, 'restaurant', 'Order #200 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-10 19:52:31'),
(39, 16, 4, 201, 'restaurant', 'Order #201 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-10 19:55:15'),
(40, 16, 4, 202, 'restaurant', 'Order #202 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-11 09:58:42'),
(41, 16, 4, 203, 'restaurant', 'Order #203 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-11 10:18:50'),
(42, 16, 4, 206, 'restaurant', 'Order #206 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-11 16:04:17'),
(43, 16, 4, 207, 'restaurant', 'Order #207 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-11 16:48:45'),
(44, 16, 4, 208, 'restaurant', 'Order #208 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-11 17:19:13'),
(45, 16, 4, 210, 'restaurant', 'Order #210 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-12 07:46:32'),
(46, 16, 4, 211, 'restaurant', 'Order #211 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-12 08:04:46'),
(47, 16, 4, 212, 'restaurant', 'Order #212 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-12 08:21:31'),
(48, 16, 4, 213, 'restaurant', 'Order #213 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-12 08:36:33'),
(49, 16, 4, 214, 'restaurant', 'Order #214 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-12 08:41:53'),
(50, 16, 4, 215, 'restaurant', 'Order #215 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-12 08:49:37'),
(51, 16, 4, 216, 'restaurant', 'Order #216 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-12 08:59:27'),
(52, 16, 4, 217, 'restaurant', 'Order #217 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-12 09:11:37'),
(53, 16, 4, 218, 'restaurant', 'Order #218 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-12 11:45:16'),
(54, 16, 4, 220, 'restaurant', 'Order #220 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-12 12:07:59'),
(55, 16, 4, 221, 'restaurant', 'Order #221 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-12 12:14:10'),
(56, 16, 4, 222, 'restaurant', 'Order #222 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-12 12:22:16'),
(57, 16, 4, 223, 'restaurant', 'Order #223 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-12 12:30:14'),
(58, 16, 4, 224, 'restaurant', 'Order #224 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-12 12:46:54'),
(59, 16, 4, 225, 'restaurant', 'Order #225 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-12 13:08:12'),
(60, 16, 4, 226, 'restaurant', 'Order #226 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-12 13:14:53'),
(61, 16, 4, 227, 'restaurant', 'Order #227 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-12 13:45:02'),
(62, 16, 4, 228, 'restaurant', 'Order #228 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-12 15:02:05'),
(63, 16, 4, 229, 'restaurant', 'Order #229 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-12 15:03:12'),
(64, 16, 4, 230, 'restaurant', 'Order #230 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-12 15:15:33'),
(65, 16, 4, 231, 'restaurant', 'Order #231 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-12 15:55:57'),
(66, 16, 4, 233, 'restaurant', 'Order #233 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-15 08:24:01'),
(67, 16, 4, 234, 'restaurant', 'Order #234 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-15 08:44:25'),
(68, 2, 1, 232, 'restaurant', 'Order #232 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-15 15:56:33'),
(69, 2, 1, 235, 'restaurant', 'Order #235 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-15 15:56:37'),
(70, 2, 1, 236, 'restaurant', 'Order #236 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-16 03:41:27'),
(71, 2, 1, 237, 'restaurant', 'Order #237 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-16 03:41:54'),
(72, 2, 1, 238, 'restaurant', 'Order #238 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-16 03:58:13'),
(73, 2, 1, 239, 'restaurant', 'Order #239 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-16 06:45:11'),
(74, 2, 1, 240, 'restaurant', 'Order #240 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-16 06:47:58'),
(75, 2, 1, 241, 'restaurant', 'Order #241 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-16 13:51:59'),
(76, 2, 1, 243, 'restaurant', 'Order #243 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-17 03:33:42'),
(77, 2, 1, 244, 'restaurant', 'Order #244 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-17 07:22:27'),
(78, 2, 1, 245, 'restaurant', 'Order #245 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-17 07:22:42'),
(79, 2, 1, 246, 'restaurant', 'Order #246 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-17 07:22:55'),
(80, 2, 1, 247, 'restaurant', 'Order #247 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-17 14:02:37'),
(81, 2, 1, 248, 'restaurant', 'Order #248 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-17 14:03:35'),
(82, 2, 1, 249, 'restaurant', 'Order #249 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-17 14:03:47'),
(83, 2, 1, 250, 'restaurant', 'Order #250 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-17 14:03:57'),
(84, 2, 1, 251, 'restaurant', 'Order #251 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-17 14:50:29'),
(85, 2, 1, 252, 'restaurant', 'Order #252 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-17 14:51:23'),
(86, 2, 1, 253, 'restaurant', 'Order #253 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-17 14:58:34'),
(87, 2, 1, 254, 'restaurant', 'Order #254 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-17 15:03:30'),
(88, 2, 1, 255, 'restaurant', 'Order #255 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-17 15:07:56'),
(89, 2, 1, 256, 'restaurant', 'Order #256 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-17 15:10:36'),
(90, 2, 1, 257, 'restaurant', 'Order #257 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-17 15:20:08'),
(91, 2, 1, 258, 'restaurant', 'Order #258 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-17 15:21:40'),
(92, 2, 1, 259, 'restaurant', 'Order #259 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-17 15:22:36'),
(93, 2, 1, 261, 'restaurant', 'Order #261 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-17 15:29:45'),
(94, 2, 1, 260, 'restaurant', 'Order #260 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-18 07:28:36'),
(95, 16, 4, 262, 'restaurant', 'Order #262 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-21 08:35:13'),
(96, 16, 4, 263, 'restaurant', 'Order #263 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-21 08:37:43'),
(97, 16, 4, 264, 'restaurant', 'Order #264 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-21 09:06:31'),
(98, 16, 4, 267, 'restaurant', 'Order #267 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-21 15:11:08'),
(99, 16, 4, 268, 'restaurant', 'Order #268 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-21 15:20:17'),
(100, 16, 4, 265, 'restaurant', 'Order #265 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-21 15:54:38'),
(101, 16, 4, 270, 'restaurant', 'Order #270 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-22 06:09:14'),
(102, 4, 4, 265, 'restaurant', 'Order #265 Rider Timeout', 'Rider me did not pick up order #265 within 15 minutes. Order is now available for other riders.', 0, '2025-10-22 06:10:38'),
(103, 16, 4, 271, 'restaurant', 'Order #271 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-22 06:34:06'),
(104, 16, 4, 265, 'restaurant', 'Order #265 Accepted', 'Rider: me has accepted your order and is on the way.', 0, '2025-10-22 06:40:21'),
(105, 16, 4, 272, 'restaurant', 'Order #272 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-10-22 06:46:26'),
(106, 16, 4, 273, 'restaurant', 'Order #273 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-10-22 06:49:43'),
(107, 16, 4, 277, 'restaurant', 'Order #277 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-10-26 13:16:12'),
(108, 4, 4, 272, 'restaurant', 'Order #272 Rider Timeout', 'Rider me did not pick up order #272 within 15 minutes. Order is now available for other riders.', 0, '2025-10-26 13:18:00'),
(109, 16, 4, 275, 'restaurant', 'Order #275 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-10-26 14:20:52'),
(110, 16, 4, 274, 'restaurant', 'Order #274 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-10-26 14:42:48'),
(111, 16, 4, 278, 'restaurant', 'Order #278 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-10-26 15:22:42'),
(112, 2, 1, 280, 'restaurant', 'Order #280 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-10-29 07:10:17'),
(113, 16, 4, 281, 'restaurant', 'Order #281 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-10-31 06:30:46'),
(114, 16, 4, 282, 'restaurant', 'Order #282 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-10-31 06:48:29'),
(115, 16, 4, 283, 'restaurant', 'Order #283 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-10-31 07:36:46'),
(116, 2, 1, 284, 'restaurant', 'Order #284 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-10-31 08:26:10'),
(117, 2, 1, 285, 'restaurant', 'Order #285 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-01 16:16:17'),
(118, 2, 1, 286, 'restaurant', 'Order #286 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-01 16:52:59'),
(119, 16, 4, 289, 'restaurant', 'Order #289 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-06 09:22:56'),
(120, 16, 4, 290, 'restaurant', 'Order #290 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-06 09:25:06'),
(121, 16, 4, 289, 'restaurant', 'Order #289 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-07 06:23:46'),
(122, 16, 4, 290, 'restaurant', 'Order #290 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-07 06:23:50'),
(123, 16, 4, 293, 'restaurant', 'Order #293 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-07 08:52:42'),
(124, 16, 4, 294, 'restaurant', 'Order #294 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-07 08:57:22'),
(125, 2, 1, 295, 'restaurant', 'Order #295 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-08 08:35:51'),
(126, 16, 4, 297, 'restaurant', 'Order #297 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-08 08:47:08'),
(127, 16, 4, 298, 'restaurant', 'Order #298 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-10 07:09:57'),
(128, 2, 1, 299, 'restaurant', 'Order #299 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-10 07:36:29'),
(129, 2, 1, 300, 'restaurant', 'Order #300 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-11 08:27:52'),
(130, 2, 1, 301, 'restaurant', 'Order #301 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-11 08:48:34'),
(131, 2, 1, 302, 'restaurant', 'Order #302 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-11 08:53:02'),
(132, 2, 1, 303, 'restaurant', 'Order #303 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-11 09:14:55'),
(133, 2, 1, 304, 'restaurant', 'Order #304 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-12 06:19:04'),
(134, 2, 1, 305, 'restaurant', 'Order #305 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-12 07:21:18'),
(135, 2, 1, 306, 'restaurant', 'Order #306 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-12 07:39:44'),
(136, 2, 1, 307, 'restaurant', 'Order #307 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-12 08:19:22'),
(137, 2, 1, 308, 'restaurant', 'Order #308 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-12 08:38:08'),
(138, 2, 1, 309, 'restaurant', 'Order #309 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-12 08:54:03'),
(139, 2, 1, 310, 'restaurant', 'Order #310 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-12 09:24:46'),
(140, 2, 1, 311, 'restaurant', 'Order #311 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-12 09:31:31'),
(141, 2, 1, 312, 'restaurant', 'Order #312 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-14 10:05:14'),
(142, 2, 1, 313, 'restaurant', 'Order #313 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-14 10:09:59'),
(143, 2, 1, 314, 'restaurant', 'Order #314 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-14 10:14:15'),
(144, 2, 1, 316, 'restaurant', 'Order #316 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-14 14:51:11'),
(145, 2, 1, 315, 'restaurant', 'Order #315 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-14 14:51:14'),
(146, 2, 1, 318, 'restaurant', 'Order #318 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-14 15:10:41'),
(147, 2, 1, 317, 'restaurant', 'Order #317 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-14 15:11:52'),
(148, 72, 25, 320, 'restaurant', 'Order #320 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-14 16:07:18'),
(149, 72, 25, 319, 'restaurant', 'Order #319 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-14 16:09:04'),
(150, 72, 25, 319, 'restaurant', 'Order #319 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-15 05:48:58'),
(151, 2, 1, 321, 'restaurant', 'Order #321 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-16 07:25:01'),
(152, 2, 1, 322, 'restaurant', 'Order #322 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-16 07:39:29'),
(153, 2, 1, 323, 'restaurant', 'Order #323 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-16 08:02:59'),
(154, 73, 11, 324, 'restaurant', 'Order #324 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-16 08:55:21'),
(155, 73, 11, 325, 'restaurant', 'Order #325 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-16 09:12:33'),
(156, 2, 1, 326, 'restaurant', 'Order #326 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-16 12:22:04'),
(157, 2, 1, 327, 'restaurant', 'Order #327 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-16 12:41:11'),
(158, 2, 1, 328, 'restaurant', 'Order #328 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-17 06:07:44'),
(159, 2, 1, 329, 'restaurant', 'Order #329 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-17 08:09:12'),
(160, 16, 4, 330, 'restaurant', 'Order #330 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-18 07:18:29'),
(161, 16, 4, 331, 'restaurant', 'Order #331 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-18 07:22:19'),
(162, 16, 4, 333, 'restaurant', 'Order #333 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-18 07:42:51'),
(163, 16, 4, 332, 'restaurant', 'Order #332 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-18 07:42:52'),
(164, 72, 25, 334, 'restaurant', 'Order #334 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-19 06:59:19'),
(165, 72, 25, 335, 'restaurant', 'Order #335 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-20 08:16:03'),
(166, 72, 25, 335, 'restaurant', 'Order #335 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-20 08:32:06'),
(167, 2, 1, 336, 'restaurant', 'Order #336 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-20 08:34:24'),
(168, 76, 19, 340, 'restaurant', 'Order #340 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-22 09:53:37'),
(169, 2, 1, 339, 'restaurant', 'Order #339 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-22 09:54:28'),
(170, 2, 1, 339, 'restaurant', 'Order #339 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-22 14:49:03'),
(171, 2, 1, 341, 'restaurant', 'Order #341 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-23 05:25:51'),
(172, 2, 1, 342, 'restaurant', 'Order #342 Accepted', 'Rider: hla myo  has accepted your order. Please prepare the food.', 0, '2025-11-23 06:02:21'),
(173, 16, 4, 346, 'restaurant', 'Order #346 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-23 06:35:48'),
(174, 16, 4, 345, 'restaurant', 'Order #345 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-23 06:35:56'),
(175, 16, 4, 343, 'restaurant', 'Order #343 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-23 06:36:17'),
(176, 16, 4, 352, 'restaurant', 'Order #352 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-23 14:14:29'),
(177, 16, 4, 351, 'restaurant', 'Order #351 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-23 14:14:33'),
(178, 16, 4, 350, 'restaurant', 'Order #350 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-23 14:14:37'),
(179, 16, 4, 349, 'restaurant', 'Order #349 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-23 14:14:43'),
(180, 16, 4, 348, 'restaurant', 'Order #348 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-23 14:14:54'),
(181, 16, 4, 347, 'restaurant', 'Order #347 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-23 14:14:57'),
(182, 16, 4, 353, 'restaurant', 'Order #353 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-24 06:37:23'),
(183, 16, 4, 353, 'restaurant', 'Order #353 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-24 08:16:57'),
(184, 2, 1, 354, 'restaurant', 'Order #354 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-25 08:26:34'),
(185, 16, 4, 356, 'restaurant', 'Order #356 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-26 07:46:28'),
(186, 16, 4, 359, 'restaurant', 'Order #359 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-26 08:20:46'),
(187, 16, 4, 360, 'restaurant', 'Order #360 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-26 08:44:39'),
(188, 16, 4, 358, 'restaurant', 'Order #358 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-26 08:44:42'),
(189, 16, 4, 357, 'restaurant', 'Order #357 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-26 08:45:18'),
(190, 16, 4, 356, 'restaurant', 'Order #356 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-26 08:55:59'),
(191, 16, 4, 363, 'restaurant', 'Order #363 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-27 09:21:42'),
(192, 16, 4, 362, 'restaurant', 'Order #362 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-27 09:27:07'),
(193, 16, 4, 368, 'restaurant', 'Order #368 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-29 04:16:13'),
(194, 82, 23, 369, 'restaurant', 'Order #369 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-29 04:16:22'),
(195, 4, 4, 368, 'restaurant', 'Order #368 Rider Timeout', 'Rider me did not pick up order #368 within 15 minutes. Order is now available for other riders.', 0, '2025-11-29 10:43:24'),
(196, 16, 4, 368, 'restaurant', 'Order #368 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-30 11:57:33'),
(197, 16, 4, 373, 'restaurant', 'Order #373 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-30 13:31:29'),
(198, 16, 4, 370, 'restaurant', 'Order #370 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-11-30 13:32:16'),
(199, 2, 1, 377, 'restaurant', 'Order #377 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-12-03 11:52:24'),
(200, 16, 4, 378, 'restaurant', 'Order #378 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-12-04 14:00:09'),
(201, 86, 4, 388, 'restaurant', 'Order #388 Accepted', 'Rider: me has accepted your order. Please prepare the food.', 0, '2025-12-12 17:02:41'),
(202, 86, 4, 390, 'restaurant', 'Order #390 Accepted', 'Rider: Kyaw Gyi has accepted your order. Please prepare the food.', 0, '2025-12-14 08:41:46'),
(203, 86, 4, 391, 'restaurant', 'Order #391 Accepted', 'Rider: Kyaw Gyi has accepted your order. Please prepare the food.', 0, '2025-12-14 08:51:10'),
(204, 86, 4, 393, 'restaurant', 'New Order Received', 'New order #393 from MIn Min for 16300 MMK - Phone: 0925102041444', 0, '2025-12-14 13:59:00'),
(205, 86, 4, 394, 'restaurant', 'New Order Received', 'New order #394 from MIn Min for 16300 MMK - Phone: 09251020414', 0, '2025-12-14 14:01:59');

-- --------------------------------------------------------

--
-- Table structure for table `option_values`
--

CREATE TABLE `option_values` (
  `value_id` int(11) NOT NULL,
  `option_id` int(11) NOT NULL,
  `value_name` varchar(100) NOT NULL,
  `price_modifier` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `option_values`
--

INSERT INTO `option_values` (`value_id`, `option_id`, `value_name`, `price_modifier`) VALUES
(1, 1, 'Chicken', 0.00),
(2, 1, 'Pork', 0.00),
(3, 2, 'Extra Meat Ball (Chicken)', 2500.00),
(4, 2, 'Extra Meat Ball (Pork)', 2500.00),
(5, 6, 'High', 0.00),
(6, 6, 'Medium', 0.00),
(7, 7, 'Soup', 0.00),
(8, 7, 'Salad', 0.00),
(9, 8, 'No Spicy and No Numbing Tingle', 0.00),
(10, 8, 'Regular Spicy and Numbing Tingle ', 0.00),
(11, 8, 'More Spicy and Numbing Tingle', 0.00),
(12, 9, 'Chicken', 5700.00),
(13, 9, 'Beef', 5700.00),
(14, 9, 'Pork', 5700.00),
(15, 9, 'Seafood', 6300.00),
(16, 10, 'Chicken Mushroom 3 Balls', 3900.00),
(17, 10, 'Pork Mushroom 3 Balls', 3900.00),
(20, 11, 'Mon Nhyinn Phyu', 1450.00),
(21, 11, 'Taiwan Mon Nhyinn', 1700.00),
(22, 11, 'Water Grass', 1600.00),
(23, 11, 'Lotus Root', 1800.00),
(24, 11, 'Wood Ear Mushroom', 2050.00),
(25, 11, 'Parasol Mushroom', 1450.00),
(26, 11, 'Taro', 1450.00),
(27, 11, 'Carrot', 1450.00),
(28, 11, 'Corn', 1450.00),
(31, 14, 'Tom Yum', 0.00),
(32, 14, 'Mala', 0.00),
(33, 14, 'Boiled Chicken Soup', 0.00),
(34, 15, 'Tom Yum', 0.00),
(35, 15, 'Mala', 0.00),
(36, 15, 'Boiled Chicken Soup', 0.00),
(37, 16, 'Homemade Egg Noodles', 0.00),
(38, 16, 'Egg Noodles', 0.00),
(39, 16, 'Glass Noodles', 0.00),
(40, 17, 'Tom Yum', 0.00),
(41, 17, 'Mala', 0.00),
(42, 17, 'Boiled Chicken Soup', 0.00),
(43, 16, 'Instant Noodles', 0.00),
(44, 16, 'Mala White Noodles', 0.00),
(45, 16, 'Mala Black Noodles', 0.00),
(46, 16, 'Steamed Rice', 0.00),
(47, 18, 'Chinese Cabbage ', 1500.00),
(48, 18, 'Water Spinach', 1800.00),
(49, 18, 'Cauliflower', 1500.00),
(50, 18, 'Lotus Toosts', 2100.00),
(51, 18, 'Aspargus Tettuce', 1500.00),
(52, 19, 'Chicken Liver and Gizzard', 1800.00),
(53, 19, 'Clam', 2900.00),
(54, 19, 'Squid Hand', 3200.00),
(55, 19, 'Cuttlefish', 3500.00),
(56, 20, '80', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `delivery_address` text NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_status` enum('paid','unpaid') DEFAULT 'unpaid',
  `order_status` enum('pending','preparing','on_the_way','ready','delivered','canceled','accepted') DEFAULT 'pending',
  `cancellation_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `lat` double DEFAULT NULL,
  `lng` double DEFAULT NULL,
  `is_deleted` tinyint(4) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_reviewed` tinyint(1) DEFAULT 0,
  `payment_method` enum('cod','gateway') DEFAULT 'cod'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `restaurant_id`, `delivery_address`, `total_amount`, `delivery_fee`, `payment_status`, `order_status`, `cancellation_reason`, `created_at`, `lat`, `lng`, `is_deleted`, `subtotal`, `is_reviewed`, `payment_method`) VALUES
(1, 1, 1, 'Sule Pagoda Rd, Yangon', 5000.00, 0.00, 'paid', 'delivered', NULL, '2025-08-25 12:40:50', 16.8051, 96.1802, 0, 0.00, 0, 'cod'),
(2, 1, 1, 'Yangon, Myanmar', 2500.00, 0.00, 'paid', 'delivered', NULL, '2025-08-25 13:05:00', 16.8048, 96.1798, 0, 0.00, 0, 'cod'),
(3, 1, 1, 'Yangon, Myanmar', 2500.00, 0.00, 'paid', 'delivered', NULL, '2025-08-25 13:06:04', 16.8055, 96.1805, 0, 0.00, 0, 'cod'),
(4, 1, 1, 'Yangon, Myanmar', 2500.00, 0.00, 'paid', 'delivered', NULL, '2025-08-25 13:22:28', 16.8049, 96.1799, 0, 0.00, 0, 'cod'),
(5, 1, 1, 'Yangon, Myanmar', 1800.00, 0.00, 'paid', 'delivered', NULL, '2025-08-25 13:23:14', 16.8053, 96.1803, 0, 0.00, 0, 'cod'),
(6, 5, 1, 'nlkjlijlajflajflkajdflkajslkfjdlksjf', 68100.00, 0.00, 'paid', 'delivered', NULL, '2025-08-25 16:43:13', 16.805, 96.18, 0, 0.00, 0, 'cod'),
(7, 5, 1, 'oihkhkhilhy', 5000.00, 0.00, 'paid', 'delivered', NULL, '2025-08-25 16:47:02', 16.8052, 96.1801, 0, 0.00, 0, 'cod'),
(8, 5, 1, 'opjlkjlkjlkj', 2500.00, 0.00, 'paid', 'delivered', NULL, '2025-08-25 16:49:46', 16.8047, 96.1797, 0, 0.00, 0, 'cod'),
(9, 5, 1, 'ghgdj', 2500.00, 0.00, 'paid', 'delivered', NULL, '2025-08-25 16:52:30', 16.8054, 96.1804, 0, 0.00, 0, 'cod'),
(10, 5, 1, 'afdfa', 2500.00, 0.00, 'paid', 'delivered', NULL, '2025-08-25 16:54:48', 16.8049, 96.1799, 0, 0.00, 0, 'cod'),
(11, 5, 1, 'oiijk', 2500.00, 0.00, 'paid', 'delivered', NULL, '2025-08-25 16:57:56', 16.8051, 96.1802, 0, 0.00, 0, 'cod'),
(12, 5, 1, 'fafda', 2500.00, 0.00, 'paid', 'delivered', NULL, '2025-08-25 16:59:46', 16.805, 96.18, 0, 0.00, 0, 'cod'),
(13, 5, 1, 'twrtw', 2500.00, 0.00, 'paid', 'delivered', NULL, '2025-08-25 17:00:20', 16.8048, 96.1798, 0, 0.00, 0, 'cod'),
(14, 5, 1, 'qert', 2500.00, 0.00, 'paid', 'delivered', NULL, '2025-08-25 17:01:02', 16.8053, 96.1803, 0, 0.00, 0, 'cod'),
(15, 5, 1, 'afda', 2500.00, 0.00, 'paid', 'delivered', NULL, '2025-08-25 17:01:37', 16.8052, 96.1801, 0, 0.00, 0, 'cod'),
(16, 5, 1, 'trqet', 2500.00, 0.00, 'paid', 'delivered', NULL, '2025-08-25 17:03:57', 16.8049, 96.1799, 0, 0.00, 0, 'cod'),
(17, 5, 1, 'afda', 2500.00, 0.00, 'paid', 'delivered', NULL, '2025-08-25 17:04:39', 16.8055, 96.1805, 0, 0.00, 0, 'cod'),
(18, 5, 1, 'nohaiohfdlkahkghdakgh;ioaeshgrh', 15000.00, 0.00, 'paid', 'delivered', NULL, '2025-08-26 08:05:21', 16.805, 96.18, 0, 0.00, 0, 'cod'),
(19, 5, 1, 'nohaiohfdlkahkghdakgh;ioaeshgrh', 2500.00, 0.00, 'paid', 'delivered', NULL, '2025-08-26 08:07:03', 16.8051, 96.1802, 0, 0.00, 0, 'cod'),
(20, 5, 1, 'nohaiohfdlkahkghdakgh;ioaeshgrh', 2500.00, 0.00, 'paid', 'delivered', NULL, '2025-08-26 08:41:19', 16.8048, 96.1798, 0, 0.00, 0, 'cod'),
(21, 9, 1, 'afasdfgasdgafdgadsfgareg', 2500.00, 0.00, 'paid', 'delivered', NULL, '2025-08-26 08:52:23', 16.799536, 96.197517, 0, 0.00, 0, 'cod'),
(22, 5, 1, 'nohaiohfdlkahkghdakgh;ioaeshgrh', 16000.00, 0.00, 'paid', 'delivered', NULL, '2025-08-26 09:33:22', 16.8053, 96.1803, 0, 0.00, 0, 'cod'),
(23, 5, 1, 'nohaiohfdlkahkghdakgh;ioaeshgrh', 2500.00, 0.00, 'paid', 'delivered', NULL, '2025-08-26 12:30:11', 16.8054, 96.1804, 0, 0.00, 0, 'cod'),
(24, 5, 1, 'nohaiohfdlkahkghdakgh;ioaeshgrh', 2500.00, 0.00, 'paid', 'delivered', NULL, '2025-08-26 17:16:00', NULL, NULL, 0, 0.00, 0, 'cod'),
(25, 5, 1, 'nohaiohfdlkahkghdakgh;ioaeshgrh', 2500.00, 0.00, 'paid', 'delivered', NULL, '2025-08-26 17:17:00', NULL, NULL, 0, 0.00, 0, 'cod'),
(26, 5, 1, 'nohaiohfdlkahkghdakgh;ioaeshgrh', 2000.00, 0.00, 'paid', 'delivered', NULL, '2025-08-26 17:38:09', NULL, NULL, 0, 0.00, 0, 'cod'),
(27, 9, 1, 'afasdfgasdgafdgadsfgareg', 2500.00, 0.00, 'paid', 'delivered', NULL, '2025-08-26 17:55:36', NULL, NULL, 0, 0.00, 0, 'cod'),
(28, 5, 1, 'nohaiohfdlkahkghdakgh;ioaeshgrh', 2500.00, 0.00, 'paid', 'delivered', NULL, '2025-08-26 17:57:08', NULL, NULL, 0, 0.00, 0, 'cod'),
(29, 5, 1, 'nohaiohfdlkahkghdakgh;ioaeshgrh', 2500.00, 0.00, 'paid', 'delivered', NULL, '2025-08-26 18:07:03', NULL, NULL, 0, 0.00, 0, 'cod'),
(30, 5, 1, 'nohaiohfdlkahkghdakgh;ioaeshgrh', 1800.00, 0.00, 'paid', 'delivered', NULL, '2025-08-26 18:07:29', NULL, NULL, 0, 0.00, 0, 'cod'),
(31, 8, 1, 'adfadaf', 7500.00, 0.00, 'paid', 'delivered', NULL, '2025-08-27 03:40:02', NULL, NULL, 0, 0.00, 0, 'cod'),
(32, 8, 1, 'adfadaf', 2000.00, 0.00, 'paid', 'delivered', NULL, '2025-08-27 03:42:20', NULL, NULL, 0, 0.00, 0, 'cod'),
(33, 5, 1, 'nohaiohfdlkahkghdakgh;ioaeshgrh', 2500.00, 0.00, 'paid', 'delivered', NULL, '2025-08-27 04:04:51', NULL, NULL, 0, 0.00, 0, 'cod'),
(34, 5, 1, 'nohaiohfdlkahkghdakgh;ioaeshgrh', 2500.00, 0.00, 'paid', 'delivered', NULL, '2025-08-27 04:19:16', NULL, NULL, 0, 0.00, 0, 'cod'),
(35, 5, 1, 'nohaiohfdlkahkghdakgh;ioaeshgrh', 2000.00, 0.00, 'paid', 'delivered', NULL, '2025-08-27 04:23:30', NULL, NULL, 0, 0.00, 0, 'cod'),
(36, 5, 1, 'nohaiohfdlkahkghdakgh;ioaeshgrh', 2500.00, 0.00, 'paid', 'delivered', NULL, '2025-08-27 04:41:07', NULL, NULL, 0, 0.00, 0, 'cod'),
(37, 5, 1, 'nohaiohfdlkahkghdakgh;ioaeshgrh', 2500.00, 0.00, 'paid', 'delivered', NULL, '2025-08-27 04:42:17', NULL, NULL, 0, 0.00, 0, 'cod'),
(38, 5, 1, 'nohaiohfdlkahkghdakgh;ioaeshgrh', 2500.00, 0.00, 'paid', 'delivered', NULL, '2025-08-27 04:45:40', NULL, NULL, 0, 0.00, 0, 'cod'),
(39, 5, 1, 'j;klfah;dohas;oeg', 4000.00, 0.00, 'paid', 'delivered', NULL, '2025-08-27 07:31:02', 0, 0, 0, 0.00, 0, 'cod'),
(40, 5, 1, 'kljhlkjlajdflkajflkj', 4000.00, 0.00, 'paid', 'delivered', NULL, '2025-08-27 07:40:34', 0, 0, 0, 0.00, 0, 'cod'),
(41, 5, 1, 'kljhlkjlajdflkajflkj', 4000.00, 0.00, 'paid', 'delivered', NULL, '2025-08-27 07:44:23', 0, 0, 0, 0.00, 0, 'cod'),
(42, 5, 1, 'kljhlkjlajdflkajflkj', 4000.00, 0.00, 'paid', 'delivered', NULL, '2025-08-27 07:44:34', 0, 0, 0, 0.00, 0, 'cod'),
(43, 5, 1, 'Sule Pagoda Rd, Yangon', 1800.00, 0.00, 'paid', 'delivered', NULL, '2025-08-27 07:46:55', 0, 0, 0, 0.00, 0, 'cod'),
(44, 5, 1, 'Sule Pagoda Rd, Yangon', 6000.00, 0.00, 'paid', 'delivered', NULL, '2025-08-27 08:02:06', 0, 0, 0, 0.00, 0, 'cod'),
(45, 5, 1, 'Sule Pagoda Rd, Yangon', 6000.00, 0.00, 'paid', 'delivered', NULL, '2025-08-27 08:21:14', NULL, NULL, 0, 0.00, 0, 'cod'),
(46, 5, 1, 'thaketa', 2000.00, 0.00, 'paid', 'delivered', NULL, '2025-08-27 08:58:02', 16.805406132025, 96.199412871265, 0, 0.00, 0, 'cod'),
(47, 11, 1, 'thakea', 2000.00, 0.00, 'paid', 'delivered', NULL, '2025-08-27 09:00:33', NULL, NULL, 0, 0.00, 0, 'cod'),
(48, 11, 1, 'Metro IT and Japanese Language Center Yangon', 5000.00, 0.00, 'paid', 'delivered', NULL, '2025-08-27 09:02:21', NULL, NULL, 0, 0.00, 0, 'cod'),
(49, 11, 1, 'Sule Lay Pagoda', 5000.00, 0.00, 'paid', 'delivered', NULL, '2025-08-27 09:04:16', NULL, NULL, 0, 0.00, 0, 'cod'),
(50, 11, 1, 'Shwedagon pagoda', 2500.00, 0.00, 'paid', 'delivered', NULL, '2025-08-27 09:05:35', NULL, NULL, 0, 0.00, 0, 'cod'),
(51, 11, 1, 'Myanmagoyi Street', 5000.00, 0.00, 'paid', 'delivered', NULL, '2025-08-27 09:06:42', NULL, NULL, 0, 0.00, 0, 'cod'),
(52, 5, 1, 'thaketa', 4000.00, 0.00, 'paid', 'delivered', NULL, '2025-08-27 09:19:03', 16.805415046277, 96.199414173042, 0, 0.00, 0, 'cod'),
(53, 5, 1, 'thaketa', 2000.00, 0.00, 'paid', 'delivered', NULL, '2025-08-27 09:22:09', 16.805418072853, 96.199421852641, 0, 0.00, 0, 'cod'),
(54, 5, 1, 'thaketa', 2000.00, 0.00, 'paid', 'delivered', NULL, '2025-08-27 09:34:03', 16.805409030153, 96.199431013832, 0, 0.00, 0, 'cod'),
(55, 5, 1, 'thaketa', 2000.00, 0.00, 'paid', 'delivered', NULL, '2025-08-27 09:35:04', 16.805433148708, 96.199432830279, 0, 0.00, 0, 'cod'),
(56, 12, 1, 'Hlaing, Maubin, Ayeyarwady, Myanmar', 20500.00, 0.00, 'paid', 'delivered', NULL, '2025-08-30 15:02:44', NULL, NULL, 0, 0.00, 0, 'cod'),
(58, 8, 1, 'Hlaing, Mayangon District, Yangon City, Yangon, 11061, Myanmar', 13000.00, 0.00, 'paid', 'delivered', NULL, '2025-08-31 05:56:14', 16.859136, 96.1314816, 0, 0.00, 0, 'cod'),
(59, 8, 1, 'Hlaing, Mayangon District, Yangon City, Yangon, 11061, Myanmar', 7103.00, 0.00, 'paid', 'delivered', NULL, '2025-08-31 07:15:34', 16.859136, 96.1314816, 0, 0.00, 0, 'cod'),
(60, 8, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 4355.00, 0.00, 'paid', 'delivered', NULL, '2025-08-31 07:30:13', 16.7995338, 96.1977029, 0, 0.00, 0, 'cod'),
(61, 8, 1, 'Hlaing, Mayangon District, Yangon City, Yangon, 11061, Myanmar', 5303.00, 0.00, 'paid', 'delivered', NULL, '2025-08-31 07:30:28', 16.859136, 96.1314816, 0, 0.00, 0, 'cod'),
(62, 8, 1, 'Hlaing, Mayangon District, Yangon City, Yangon, 11061, Myanmar', 5303.00, 0.00, 'paid', 'delivered', NULL, '2025-08-31 07:32:20', 16.859136, 96.1314816, 0, 0.00, 0, 'cod'),
(63, 13, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 6500.00, 0.00, 'paid', 'delivered', NULL, '2025-08-31 07:44:38', 16.7986, 96.1497, 0, 0.00, 0, 'cod'),
(64, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 4355.00, 0.00, 'paid', 'delivered', NULL, '2025-08-31 10:26:41', 16.7995345, 96.1976872, 0, 0.00, 0, 'cod'),
(65, 5, 1, 'Hlaing, Mayangon District, Yangon City, Yangon, 11061, Myanmar', 5503.00, 0.00, 'paid', 'delivered', NULL, '2025-08-31 10:28:41', 16.859136, 96.1314816, 0, 0.00, 0, 'cod'),
(66, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 5546.00, 0.00, 'paid', 'delivered', NULL, '2025-08-31 12:30:04', 16.799534565642, 96.197526757983, 0, 0.00, 0, 'cod'),
(67, 10, 1, 'Dawbon Capital, 14-E, Min Nandar Road, Dawbon, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17451.00, 0.00, 'paid', 'delivered', NULL, '2025-08-31 12:55:39', 16.8023249, 96.1955816, 0, 0.00, 0, 'cod'),
(68, 10, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 32555.00, 0.00, 'paid', 'delivered', NULL, '2025-08-31 13:24:19', 16.7995326, 96.1976854, 0, 0.00, 0, 'cod'),
(69, 5, 1, 'Myama Gon Yi Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 32668.00, 0.00, 'paid', 'delivered', NULL, '2025-09-01 06:25:48', 16.805664, 96.1992901, 0, 0.00, 0, 'cod'),
(70, 5, 1, 'Hlaing, Mayangon District, Yangon City, Yangon, 11052, Myanmar', 6212.00, 0.00, 'paid', 'delivered', NULL, '2025-09-01 15:35:58', 16.8525824, 96.1282048, 0, 0.00, 0, 'cod'),
(71, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 5332836.00, 0.00, 'paid', 'delivered', NULL, '2025-09-01 15:57:29', 0, 0, 0, 0.00, 0, 'cod'),
(72, 5, 1, 'Hlaing, Mayangon District, Yangon City, Yangon, 11052, Myanmar', 5212.00, 0.00, 'paid', 'delivered', NULL, '2025-09-01 15:58:46', 16.8525824, 96.1282048, 0, 0.00, 0, 'cod'),
(73, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 5425036.00, 0.00, 'paid', 'delivered', NULL, '2025-09-02 10:26:33', 0, 0, 0, 0.00, 0, 'cod'),
(74, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 32667.00, 0.00, 'paid', 'delivered', NULL, '2025-09-04 07:30:15', 16.8056431, 96.1992674, 0, 0.00, 0, 'cod'),
(75, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 4546.00, 0.00, 'paid', 'delivered', NULL, '2025-09-04 12:55:05', 16.799533950697, 96.197527771336, 0, 0.00, 0, 'cod'),
(76, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 4536.00, 0.00, 'paid', 'delivered', NULL, '2025-09-04 13:05:19', 16.799727304574, 96.19732385132, 0, 0.00, 0, 'cod'),
(77, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 4346.00, 0.00, 'paid', 'delivered', NULL, '2025-09-04 13:11:45', 16.799533950697, 96.197527771336, 0, 0.00, 0, 'cod'),
(78, 5, 2, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 9500.00, 0.00, 'paid', 'delivered', NULL, '2025-09-04 13:12:12', 16.799533950697, 96.197527771336, 0, 0.00, 0, 'cod'),
(79, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 4346.00, 0.00, 'paid', 'delivered', NULL, '2025-09-04 13:25:55', 16.799533950697, 96.197527771336, 0, 0.00, 0, 'cod'),
(80, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 4346.00, 0.00, 'paid', 'delivered', NULL, '2025-09-04 13:31:03', 16.799533950697, 96.197527771336, 0, 0.00, 0, 'cod'),
(81, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 4346.00, 700.00, 'paid', 'delivered', NULL, '2025-09-04 13:34:05', 16.799534130988, 96.197526910112, 0, 0.00, 0, 'cod'),
(82, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 4346.00, 0.00, 'paid', 'delivered', NULL, '2025-09-04 13:38:20', 16.79953379653, 96.19752884365, 0, 0.00, 0, 'cod'),
(83, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17546.00, 0.00, 'paid', 'delivered', NULL, '2025-09-04 13:45:51', 16.79953504575, 96.197524952087, 0, 0.00, 0, 'cod'),
(84, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17554.00, 0.00, 'paid', 'delivered', NULL, '2025-09-04 13:48:36', 16.7995313, 96.1976806, 0, 0.00, 0, 'cod'),
(85, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 5333036.00, 0.00, 'paid', 'delivered', NULL, '2025-09-04 14:36:27', 0, 0, 0, 0.00, 0, 'cod'),
(86, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17668.00, 0.00, 'paid', 'delivered', NULL, '2025-09-05 06:33:21', 16.8056724, 96.1992763, 0, 0.00, 0, 'cod'),
(87, 5, 1, 'Avenue Wine Bar & Bistro, New University Avenue Road, Bahan, Kamayut District, Yangon City, Yangon, 11201, Myanmar', 4500.00, 0.00, 'paid', 'delivered', NULL, '2025-09-05 07:54:31', 16.8230912, 96.1544192, 0, 0.00, 0, 'cod'),
(88, 5, 26, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 12116.00, 0.00, 'paid', 'delivered', NULL, '2025-09-05 14:44:04', 16.8029936, 96.1959103, 0, 0.00, 0, 'cod'),
(89, 5, 2, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 9500.00, 0.00, 'paid', 'delivered', NULL, '2025-09-08 06:45:34', 16.8056377, 96.1992676, 0, 0.00, 0, 'cod'),
(90, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 5674.00, 0.00, 'paid', 'delivered', NULL, '2025-09-08 06:51:12', 16.805592857119, 96.199405800351, 0, 0.00, 0, 'cod'),
(91, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 32672.00, 0.00, 'paid', 'delivered', NULL, '2025-09-08 06:54:06', 16.805461888275, 96.19938864738, 0, 0.00, 0, 'cod'),
(93, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 13489.00, 0.00, 'paid', 'delivered', NULL, '2025-09-09 07:46:36', 16.842752, 96.157696, 0, 0.00, 0, 'cod'),
(94, 5, 1, 'Xã Quốc Oai, Hà Nội, 01234, Vietnam', 12707.00, 0.00, 'paid', 'delivered', NULL, '2025-09-09 08:36:08', 16.7827043, 96.1600484, 0, 0.00, 0, 'cod'),
(95, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 12000.00, 0.00, 'paid', 'delivered', NULL, '2025-09-10 08:56:12', 16.805643653439, 96.199428277262, 0, 0.00, 0, 'cod'),
(96, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 12000.00, 0.00, 'paid', 'delivered', NULL, '2025-09-10 08:57:21', 16.805722586286, 96.199426938492, 0, 0.00, 0, 'cod'),
(97, 10, 1, 'Bahan, Kamayut District, Yangon City, Yangon, 11201, Myanmar', 12934.00, 0.00, 'paid', 'delivered', NULL, '2025-09-11 08:56:10', 16.8132608, 96.1511424, 0, 0.00, 0, 'cod'),
(98, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 5398271.00, 0.00, 'paid', 'delivered', NULL, '2025-09-13 11:41:41', 0, 0, 0, 0.00, 0, 'cod'),
(99, 5, 10, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 5331656.00, 0.00, 'paid', 'delivered', NULL, '2025-09-13 11:44:58', 0, 0, 0, 0.00, 0, 'cod'),
(100, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 5349971.00, 0.00, 'paid', 'delivered', NULL, '2025-09-13 11:45:17', 0, 0, 0, 0.00, 0, 'cod'),
(101, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 5343971.00, 0.00, 'paid', 'delivered', NULL, '2025-09-13 11:48:36', 0, 0, 0, 0.00, 0, 'cod'),
(102, 5, 31, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 14481.00, 0.00, 'paid', 'delivered', NULL, '2025-09-15 09:02:11', 16.8165376, 96.1511424, 0, 0.00, 0, 'cod'),
(103, 5, 3, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 62464.00, 0.00, 'paid', 'delivered', NULL, '2025-09-18 07:54:24', 16.8230912, 96.1544192, 0, 0.00, 0, 'cod'),
(104, 5, 20, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 12331.00, 0.00, 'paid', 'delivered', NULL, '2025-09-18 08:06:06', 16.8230912, 96.1544192, 0, 0.00, 0, 'cod'),
(105, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16200.00, 0.00, 'paid', 'delivered', NULL, '2025-09-18 12:35:04', 16.799527, 96.1976793, 0, 0.00, 0, 'cod'),
(106, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16200.00, 0.00, 'paid', 'delivered', NULL, '2025-09-18 12:35:59', 16.799527, 96.1976793, 0, 0.00, 0, 'cod'),
(107, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 10500.00, 0.00, 'paid', 'delivered', NULL, '2025-09-21 15:22:13', 16.803085974636, 96.195619184659, 0, 0.00, 0, 'cod'),
(108, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon, 11231, Myanmar', 15600.00, 0.00, 'paid', 'delivered', NULL, '2025-09-24 15:04:33', 16.7995255, 96.1976753, 0, 0.00, 0, 'cod'),
(109, 5, 2, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 8001.00, 0.00, 'paid', 'delivered', NULL, '2025-09-25 06:16:20', 16.8056356, 96.1992843, 0, 0.00, 0, 'cod'),
(110, 5, 26, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 10000.00, 0.00, 'paid', 'delivered', NULL, '2025-09-26 07:34:40', 16.8279, 96.1542, 0, 0.00, 0, 'cod'),
(111, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 15600.00, 0.00, 'paid', 'delivered', NULL, '2025-09-26 07:37:30', 16.805403808018, 96.199301726372, 0, 0.00, 0, 'cod'),
(112, 5, 2, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 8001.00, 0.00, 'paid', 'delivered', NULL, '2025-09-26 07:38:56', 16.805450099179, 96.199320792809, 0, 0.00, 0, 'cod'),
(113, 5, 26, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 11400.00, 0.00, 'paid', 'delivered', NULL, '2025-09-26 07:49:35', 16.8230912, 96.1314816, 0, 0.00, 0, 'cod'),
(114, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 18305.00, 0.00, 'paid', 'delivered', NULL, '2025-09-26 07:53:34', 16.8230912, 96.1314816, 0, 0.00, 0, 'cod'),
(115, 5, 26, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 12100.00, 0.00, 'paid', 'delivered', NULL, '2025-09-28 14:43:50', 16.7995238, 96.19769, 0, 0.00, 0, 'cod'),
(116, 5, 2, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 9400.00, 0.00, 'paid', 'delivered', NULL, '2025-09-28 14:44:26', 16.7995238, 96.19769, 0, 0.00, 0, 'cod'),
(117, 5, 26, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 10700.00, 0.00, 'paid', 'delivered', NULL, '2025-09-29 09:12:27', 16.8198144, 96.1511424, 0, 0.00, 0, 'cod'),
(118, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16800.00, 0.00, 'paid', 'delivered', NULL, '2025-10-03 06:38:59', 16.8230912, 96.1544192, 0, 0.00, 0, 'cod'),
(119, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 0.00, 'paid', 'delivered', NULL, '2025-10-04 15:49:38', 0, 0, 0, 0.00, 0, 'cod'),
(120, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 15400.00, 0.00, 'paid', 'delivered', NULL, '2025-10-04 15:52:31', 16.800089013097, 96.196747063846, 0, 0.00, 0, 'cod'),
(121, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 0.00, 'paid', 'delivered', NULL, '2025-10-04 15:52:54', 16.800343108436, 96.196393297923, 0, 0.00, 0, 'cod'),
(122, 5, 4, 'Min Nandar Road, Bamar Aye Ward, Dawbon, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 0.00, 'paid', 'delivered', NULL, '2025-10-04 15:59:47', 16.80004850063, 96.196793823952, 0, 0.00, 0, 'cod'),
(123, 5, 4, 'Min Nandar Road, Bamar Aye Ward, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 0.00, 'paid', 'delivered', NULL, '2025-10-04 16:11:31', 16.800089013097, 96.196747063846, 0, 0.00, 0, 'cod'),
(124, 5, 1, 'Min Nandar Road, Bamar Aye Ward, Dawbon, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 15400.00, 0.00, 'paid', 'delivered', NULL, '2025-10-04 16:25:44', 16.79994, 96.196956, 0, 0.00, 0, 'cod'),
(125, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 15400.00, 0.00, 'paid', 'delivered', NULL, '2025-10-04 16:26:42', 0, 0, 0, 0.00, 0, 'cod'),
(126, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 0.00, 'paid', 'delivered', NULL, '2025-10-05 05:43:27', 16.800343108436, 96.196393297923, 0, 0.00, 0, 'cod'),
(127, 5, 4, 'Min Nandar Road, Bamar Aye Ward, Dawbon, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 0.00, 'paid', 'delivered', NULL, '2025-10-05 05:51:51', 16.799971853152, 96.196908828157, 0, 0.00, 0, 'cod'),
(128, 5, 4, 'Min Nandar Road, Bamar Aye Ward, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 0.00, 'paid', 'delivered', NULL, '2025-10-05 05:55:11', 16.800089013097, 96.196747063846, 0, 0.00, 0, 'cod'),
(129, 5, 4, 'Aye Zedi Street, Bamar Aye Ward, Dawbon, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 11750.00, 0.00, 'paid', 'delivered', NULL, '2025-10-05 06:15:17', 16.800344, 96.196395, 0, 0.00, 0, 'cod'),
(130, 5, 4, 'Min Nandar Road, Bamar Aye Ward, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 0.00, 'paid', 'delivered', NULL, '2025-10-05 06:19:03', 16.800089013097, 96.196747063846, 0, 0.00, 0, 'cod'),
(131, 5, 4, 'Min Nandar Road, Bamar Aye Ward, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 0.00, 'paid', 'delivered', NULL, '2025-10-05 06:19:19', 16.800089013097, 96.196747063846, 0, 0.00, 0, 'cod'),
(132, 5, 4, 'Min Nandar Road, Bamar Aye Ward, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 2000.00, 'paid', 'delivered', NULL, '2025-10-05 06:19:31', 16.800089013097, 96.196747063846, 0, 0.00, 0, 'cod'),
(133, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 0.00, 'paid', 'delivered', NULL, '2025-10-05 09:53:54', 16.800283981529, 96.196461543099, 0, 0.00, 0, 'cod'),
(134, 5, 26, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 11600.00, 0.00, 'paid', 'delivered', NULL, '2025-10-05 13:07:12', 16.800063468921, 96.196771735863, 0, 0.00, 0, 'cod'),
(135, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 0.00, 'paid', 'delivered', NULL, '2025-10-05 15:14:52', 16.800074666667, 96.196769, 0, 0.00, 0, 'cod'),
(136, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 0.00, 'paid', 'delivered', NULL, '2025-10-05 15:45:25', 16.800063468921, 96.196771735863, 0, 0.00, 0, 'cod'),
(137, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 0.00, 'paid', 'delivered', NULL, '2025-10-05 15:55:26', 16.800089013097, 96.196747063846, 0, 0.00, 0, 'cod'),
(138, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 0.00, 'paid', 'delivered', NULL, '2025-10-05 16:07:26', 16.800089013097, 96.196747063846, 0, 0.00, 0, 'cod'),
(139, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 15400.00, 0.00, 'paid', 'delivered', NULL, '2025-10-06 12:04:34', 16.800063468921, 96.196771735863, 0, 0.00, 0, 'cod'),
(140, 5, 4, 'Min Nandar Road, Bamar Aye Ward, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 0.00, 'paid', 'delivered', NULL, '2025-10-06 12:12:53', 16.800089013097, 96.196747063846, 0, 0.00, 0, 'cod'),
(141, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 0.00, 'paid', 'delivered', NULL, '2025-10-06 12:26:06', 16.80004850063, 96.196793823952, 0, 0.00, 0, 'cod'),
(142, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 15400.00, 0.00, 'paid', 'delivered', NULL, '2025-10-06 12:27:51', 16.80004850063, 96.196793823952, 0, 0.00, 0, 'cod'),
(143, 5, 2, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 9400.00, 0.00, 'paid', 'delivered', NULL, '2025-10-06 12:28:26', 16.80004850063, 96.196793823952, 0, 0.00, 0, 'cod'),
(144, 5, 2, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 9400.00, 0.00, 'paid', 'delivered', NULL, '2025-10-06 12:43:20', 16.800074666667, 96.196769, 0, 0.00, 0, 'cod'),
(145, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 0.00, 'paid', 'delivered', NULL, '2025-10-06 12:45:34', 16.800074666667, 96.196769, 0, 0.00, 0, 'cod'),
(146, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 0.00, 'paid', 'delivered', NULL, '2025-10-06 13:45:57', 16.799912726245, 96.196977073332, 0, 0.00, 1, 'cod'),
(147, 5, 3, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 8700.00, 0.00, 'paid', 'delivered', NULL, '2025-10-06 13:53:04', 16.799912726245, 96.196977073332, 0, 0.00, 0, 'cod'),
(148, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 0.00, 'paid', 'delivered', NULL, '2025-10-06 13:53:32', 16.799912726245, 96.196977073332, 0, 0.00, 1, 'cod'),
(149, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 0.00, 'paid', 'delivered', NULL, '2025-10-06 13:54:27', 16.799944236059, 96.196931858211, 0, 0.00, 0, 'cod'),
(150, 5, 4, 'Min Nandar Road, Bamar Aye Ward, Dawbon, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 0.00, 'paid', 'delivered', NULL, '2025-10-07 05:45:51', 16.79994, 96.196956, 0, 0.00, 0, 'cod'),
(151, 5, 4, 'Min Nandar Road, Bamar Aye Ward, Dawbon, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 0.00, 'paid', 'delivered', NULL, '2025-10-07 05:48:10', 16.799971853152, 96.196908828157, 0, 0.00, 0, 'cod'),
(152, 5, 4, 'Min Nandar Road, Bamar Aye Ward, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 0.00, 'paid', 'delivered', NULL, '2025-10-07 05:49:25', 16.800089013097, 96.196747063846, 0, 0.00, 0, 'cod'),
(153, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 0.00, 'paid', 'delivered', NULL, '2025-10-07 06:08:48', 16.800063468921, 96.196771735863, 0, 0.00, 0, 'cod'),
(154, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 0.00, 'paid', 'delivered', NULL, '2025-10-07 06:15:58', 16.800063468921, 96.196771735863, 0, 0.00, 0, 'cod'),
(155, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 15400.00, 0.00, 'paid', 'delivered', NULL, '2025-10-07 06:20:41', 16.79994, 96.196956, 0, 0.00, 1, 'cod'),
(156, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 0.00, 'paid', 'delivered', NULL, '2025-10-07 06:33:47', 16.800089013097, 96.196747063846, 0, 0.00, 0, 'cod'),
(157, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 0.00, 'paid', 'delivered', NULL, '2025-10-07 06:48:46', 16.800089013097, 96.196747063846, 0, 0.00, 0, 'cod'),
(158, 5, 4, 'Min Nandar Road, Bamar Aye Ward, Dawbon, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 0.00, 'paid', 'delivered', NULL, '2025-10-07 06:52:55', 16.800063468921, 96.196771735863, 0, 0.00, 0, 'cod'),
(159, 5, 4, 'Min Nandar Road, Bamar Aye Ward, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 0.00, 'paid', 'delivered', NULL, '2025-10-07 06:54:38', 16.800089013097, 96.196747063846, 0, 0.00, 0, 'cod'),
(160, 5, 4, 'Min Nandar Road, Bamar Aye Ward, Dawbon, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 0.00, 'paid', 'delivered', NULL, '2025-10-07 07:59:26', 16.800063468921, 96.196771735863, 0, 0.00, 0, 'cod'),
(161, 5, 4, 'Min Nandar Road, Bamar Aye Ward, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 0.00, 'paid', 'delivered', NULL, '2025-10-07 08:00:47', 16.800089013097, 96.196747063846, 0, 0.00, 0, 'cod'),
(162, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 15400.00, 0.00, 'paid', 'delivered', NULL, '2025-10-07 08:14:49', 16.80004850063, 96.196793823952, 0, 0.00, 0, 'cod'),
(163, 5, 2, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 9400.00, 0.00, 'paid', 'delivered', NULL, '2025-10-07 08:44:38', 16.799971853152, 96.196908828157, 0, 0.00, 0, 'cod'),
(164, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 15400.00, 0.00, 'paid', 'delivered', NULL, '2025-10-07 08:45:03', 16.799971853152, 96.196908828157, 0, 0.00, 0, 'cod'),
(165, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 15400.00, 0.00, 'paid', 'delivered', NULL, '2025-10-07 08:56:24', 16.800089013097, 96.196747063846, 0, 0.00, 0, 'cod'),
(166, 5, 15, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 4997.00, 0.00, 'paid', 'delivered', NULL, '2025-10-07 09:31:16', 16.800063468921, 96.196771735863, 0, 0.00, 0, 'cod'),
(167, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 0.00, 'paid', 'delivered', NULL, '2025-10-07 09:32:02', 16.800287656664, 96.196454979599, 0, 0.00, 1, 'cod'),
(168, 5, 26, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 11600.00, 0.00, 'paid', 'delivered', NULL, '2025-10-07 09:47:20', 16.80004850063, 96.196793823952, 0, 0.00, 0, 'cod'),
(169, 5, 2, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 9400.00, 0.00, 'paid', 'delivered', NULL, '2025-10-07 09:55:39', 16.79994, 96.196956, 0, 0.00, 0, 'cod'),
(170, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 29100.00, 0.00, 'paid', 'delivered', NULL, '2025-10-07 09:57:27', 16.800089013097, 96.196747063846, 0, 0.00, 1, 'cod'),
(171, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 34050.00, 0.00, 'paid', 'delivered', NULL, '2025-10-07 09:58:28', 16.79994, 96.196956, 0, 0.00, 0, 'cod'),
(172, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 46000.00, 0.00, 'paid', 'delivered', NULL, '2025-10-07 09:59:10', 16.800074666667, 96.196769, 0, 0.00, 0, 'cod'),
(173, 5, 17, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 2200.00, 0.00, 'paid', 'delivered', NULL, '2025-10-07 10:00:34', 16.800074666667, 96.196769, 0, 0.00, 0, 'cod'),
(174, 16, 2, 'Avenue Wine Bar & Bistro, New University Avenue Road, Bahan, Kamayut District, Yangon City, Yangon, 11201, Myanmar', 9500.00, 0.00, 'paid', 'delivered', NULL, '2025-10-08 06:15:03', 0, 0, 0, 0.00, 0, 'cod'),
(175, 16, 1, 'Avenue Wine Bar & Bistro, New University Avenue Road, Bahan, Kamayut District, Yangon City, Yangon, 11201, Myanmar', 16800.00, 0.00, 'paid', 'delivered', NULL, '2025-10-08 06:34:30', 16.8198144, 96.1511424, 0, 0.00, 0, 'cod'),
(176, 2, 1, 'Yangon', 16800.00, 0.00, 'paid', 'delivered', NULL, '2025-10-08 06:42:44', 16.8198144, 96.1511424, 0, 0.00, 0, 'cod'),
(177, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 15400.00, 0.00, 'paid', 'delivered', NULL, '2025-10-08 07:03:53', 16.8056497, 96.199292, 0, 0.00, 0, 'cod'),
(178, 2, 2, 'Yangon', 9400.00, 0.00, 'paid', 'delivered', NULL, '2025-10-08 07:22:02', 16.8056308, 96.1992874, 0, 0.00, 0, 'cod'),
(179, 10, 4, 'asddf', 16300.00, 0.00, 'paid', 'delivered', NULL, '2025-10-08 08:50:18', 16.8056593, 96.1993096, 0, 0.00, 0, 'cod'),
(180, 10, 1, 'retre', 18675.00, 0.00, 'paid', 'delivered', NULL, '2025-10-08 13:27:16', 16.8656896, 96.1314816, 0, 0.00, 0, 'cod'),
(181, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 15400.00, 0.00, 'paid', 'delivered', NULL, '2025-10-08 16:09:01', 16.7995192, 96.1976853, 0, 0.00, 0, 'cod'),
(182, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 30100.00, 0.00, 'paid', 'delivered', NULL, '2025-10-09 14:09:47', 16.8056348, 96.1992848, 0, 0.00, 0, 'cod'),
(183, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 15400.00, 0.00, 'paid', 'delivered', NULL, '2025-10-09 14:59:43', 16.800065780656, 96.196806416451, 0, 0.00, 0, 'cod'),
(184, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 700.00, 'paid', 'delivered', NULL, '2025-10-09 15:01:24', 16.7995201, 96.1976797, 0, 0.00, 0, 'cod'),
(185, 5, 1, 'Myama Gon Yi Street, Set Hmu Let Hmu Ward, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 15400.00, 700.00, 'paid', 'delivered', NULL, '2025-10-10 07:02:56', 16.8056258, 96.1992856, 0, 14700.00, 0, 'cod'),
(186, 2, 2, 'Myama Gon Yi Street, Set Hmu Let Hmu Ward, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 9400.00, 1400.00, 'paid', 'delivered', NULL, '2025-10-10 07:05:29', 16.8056258, 96.1992856, 0, 8000.00, 0, 'cod'),
(187, 5, 1, 'B.E.P.S 24 Thaketa, Anawmar Road, No (1) Ah Naw Mar Ward, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 15400.00, 700.00, 'paid', 'delivered', NULL, '2025-10-10 07:30:57', 16.79895374521, 96.200752258301, 0, 14700.00, 0, 'cod'),
(188, 5, 2, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17400.00, 1400.00, 'paid', 'delivered', NULL, '2025-10-10 08:09:14', 16.8056291, 96.1992919, 0, 16000.00, 0, 'cod'),
(189, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 2500.00, 700.00, 'paid', 'delivered', NULL, '2025-10-10 08:34:20', 16.816865489733, 96.194765461735, 0, 1800.00, 0, 'cod'),
(190, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 18675.00, 3975.00, 'paid', 'delivered', NULL, '2025-10-10 16:21:06', 16.802404763303, 96.199893951416, 0, 14700.00, 0, 'cod'),
(191, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17100.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-10 16:59:40', 16.797419942591, 96.203141201, 0, 15600.00, 0, 'cod'),
(192, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17100.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-10 17:42:45', 16.797419942591, 96.203141201, 0, 15600.00, 0, 'cod'),
(193, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17100.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-10 17:46:43', 16.797419942591, 96.203141201, 0, 15600.00, 0, 'cod'),
(194, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17100.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-10 18:34:20', 16.797419942591, 96.203141201, 0, 15600.00, 0, 'cod'),
(195, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17100.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-10 19:23:15', 16.797419942591, 96.203141201, 0, 15600.00, 0, 'cod'),
(196, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17100.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-10 19:34:52', 16.797419942591, 96.203141201, 0, 15600.00, 0, 'cod'),
(197, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17100.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-10 19:43:12', 16.797419942591, 96.203141201, 0, 15600.00, 0, 'cod'),
(198, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17100.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-10 19:47:39', 16.797419942591, 96.203141201, 0, 15600.00, 0, 'cod'),
(199, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17100.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-10 19:51:09', 16.797419942591, 96.203141201, 0, 15600.00, 0, 'cod'),
(200, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17100.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-10 19:52:08', 16.797419942591, 96.203141201, 0, 15600.00, 0, 'cod'),
(201, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17100.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-10 19:54:26', 16.797419942591, 96.203141201, 0, 15600.00, 0, 'cod'),
(202, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17100.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-11 09:57:50', 16.800603945591, 96.199060098414, 0, 15600.00, 0, 'cod'),
(203, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17100.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-11 10:18:33', 16.800603945591, 96.199060098414, 0, 15600.00, 0, 'cod'),
(204, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17100.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-11 15:47:46', 16.786302905973, 96.178421955509, 0, 15600.00, 0, 'cod'),
(205, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17100.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-11 16:02:13', 16.786302905973, 96.178421955509, 0, 15600.00, 0, 'cod'),
(206, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17100.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-11 16:03:52', 16.786302905973, 96.178421955509, 0, 15600.00, 0, 'cod'),
(207, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17100.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-11 16:48:22', 16.786302905973, 96.178421955509, 0, 15600.00, 0, 'cod'),
(208, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17100.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-11 17:17:54', 16.786302905973, 96.178421955509, 0, 15600.00, 0, 'cod'),
(210, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17100.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-12 07:45:54', 16.799251817809, 96.205558739122, 0, 15600.00, 0, 'cod'),
(211, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17100.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-12 08:04:05', 16.794191380828, 96.207286120849, 0, 15600.00, 0, 'cod'),
(212, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17100.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-12 08:20:53', 16.800352308588, 96.199058308842, 0, 15600.00, 0, 'cod'),
(213, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17100.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-12 08:36:04', 16.787617674538, 96.185294375828, 0, 15600.00, 0, 'cod'),
(214, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17100.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-12 08:39:22', 16.821057322761, 96.209347552287, 0, 15600.00, 0, 'cod'),
(215, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17100.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-12 08:47:30', 16.80239192481, 96.199955397199, 0, 15600.00, 0, 'cod'),
(216, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 32700.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-12 08:58:48', 16.801557430954, 96.200876044654, 0, 31200.00, 0, 'cod'),
(217, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 700.00, 'paid', 'delivered', NULL, '2025-10-12 09:10:11', 16.803226425027, 96.198348999023, 0, 15600.00, 0, 'cod'),
(218, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17100.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-12 11:43:41', 16.7946809692, 96.206505231022, 0, 15600.00, 0, 'cod'),
(219, 5, 19, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 15500.00, 1500.00, 'unpaid', 'delivered', NULL, '2025-10-12 11:43:59', 16.7946809692, 96.206505231022, 0, 14000.00, 0, 'cod'),
(220, 16, 4, 'Avenue Wine Bar & Bistro, New University Avenue Road, Bahan, Kamayut District, Yangon City, Yangon, 11201, Myanmar', 17100.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-12 12:07:21', 16.781701144327, 96.199039216466, 0, 15600.00, 0, 'cod'),
(221, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17100.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-12 12:13:36', 16.799872143178, 96.203442911413, 0, 15600.00, 0, 'cod'),
(222, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17100.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-12 12:21:48', 16.7987928398, 96.201100942562, 0, 15600.00, 0, 'cod'),
(223, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17100.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-12 12:25:31', 16.797407098746, 96.204992185984, 0, 15600.00, 0, 'cod'),
(224, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17100.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-12 12:25:43', 16.797407098746, 96.204992185984, 0, 15600.00, 0, 'cod'),
(225, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17100.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-12 12:25:54', 16.797407098746, 96.204992185984, 0, 15600.00, 0, 'cod'),
(226, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17100.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-12 12:26:09', 16.797407098746, 96.204992185984, 0, 15600.00, 0, 'cod'),
(227, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17100.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-12 12:26:22', 16.797407098746, 96.204992185984, 0, 15600.00, 0, 'cod'),
(228, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17100.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-12 12:26:35', 16.797407098746, 96.204992185984, 0, 15600.00, 0, 'cod'),
(229, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17100.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-12 12:26:47', 16.797407098746, 96.204992185984, 0, 15600.00, 0, 'cod'),
(230, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17100.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-12 12:26:58', 16.797407098746, 96.204992185984, 0, 15600.00, 0, 'cod'),
(231, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17100.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-12 12:27:12', 16.797407098746, 96.204992185984, 0, 15600.00, 0, 'cod'),
(232, 5, 1, 'ShweDaGon Pagoda Road', 16800.00, 2100.00, 'paid', 'delivered', NULL, '2025-10-15 06:28:31', 16.807499008627, 96.199722290039, 0, 14700.00, 0, 'cod'),
(233, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17700.00, 2100.00, 'paid', 'delivered', NULL, '2025-10-15 08:23:13', 16.804869737799, 96.200408935547, 0, 15600.00, 0, 'cod'),
(234, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17700.00, 2100.00, 'paid', 'delivered', NULL, '2025-10-15 08:43:19', 16.802076097617, 96.199893951416, 0, 15600.00, 0, 'cod'),
(235, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 15400.00, 700.00, 'paid', 'delivered', NULL, '2025-10-15 15:54:29', 16.7995136, 96.1976847, 0, 14700.00, 0, 'cod'),
(236, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 15400.00, 700.00, 'paid', 'delivered', NULL, '2025-10-16 03:38:35', 16.7995164, 96.1976905, 0, 14700.00, 0, 'cod'),
(237, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 28552.00, 3852.00, 'paid', 'delivered', NULL, '2025-10-16 03:38:55', 16.7995164, 96.1976905, 0, 24700.00, 0, 'cod'),
(238, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 8252.00, 3852.00, 'paid', 'delivered', NULL, '2025-10-16 03:39:13', 16.7995164, 96.1976905, 0, 4400.00, 0, 'cod'),
(239, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 13252.00, 3852.00, 'paid', 'delivered', NULL, '2025-10-16 03:39:38', 16.7995164, 96.1976905, 0, 9400.00, 0, 'cod'),
(240, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 12052.00, 3852.00, 'paid', 'delivered', NULL, '2025-10-16 03:39:56', 16.7995164, 96.1976905, 0, 8200.00, 0, 'cod'),
(241, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 24452.00, 3852.00, 'paid', 'delivered', NULL, '2025-10-16 03:40:14', 16.7995164, 96.1976905, 0, 20600.00, 0, 'cod'),
(242, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 15400.00, 700.00, 'unpaid', 'delivered', NULL, '2025-10-17 02:58:22', 16.80585571863, 96.204528808594, 0, 14700.00, 0, 'cod'),
(243, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16800.00, 2100.00, 'paid', 'delivered', NULL, '2025-10-17 03:32:48', 16.799939756784, 96.200752258301, 0, 14700.00, 0, 'cod'),
(244, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 2500.00, 700.00, 'paid', 'delivered', NULL, '2025-10-17 07:17:47', 16.805644, 96.1993016, 0, 1800.00, 0, 'cod'),
(245, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 31400.00, 700.00, 'paid', 'delivered', NULL, '2025-10-17 07:18:34', 16.805644, 96.1993016, 0, 30700.00, 0, 'cod'),
(246, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 31900.00, 700.00, 'paid', 'delivered', NULL, '2025-10-17 07:18:58', 16.805644, 96.1993016, 0, 31200.00, 0, 'cod'),
(247, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 8900.00, 700.00, 'paid', 'delivered', NULL, '2025-10-17 13:57:21', 16.7995136, 96.1976852, 0, 8200.00, 0, 'cod'),
(248, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 18900.00, 700.00, 'paid', 'delivered', NULL, '2025-10-17 13:58:20', 16.7995136, 96.1976852, 0, 18200.00, 0, 'cod'),
(249, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 5100.00, 700.00, 'paid', 'delivered', NULL, '2025-10-17 13:58:39', 16.7995136, 96.1976852, 0, 4400.00, 0, 'cod'),
(250, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 5522.00, 3722.00, 'paid', 'delivered', NULL, '2025-10-17 14:00:19', 16.7995136, 96.1976852, 0, 1800.00, 0, 'cod'),
(251, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 18422.00, 3722.00, 'paid', 'delivered', NULL, '2025-10-17 14:47:52', 16.802897760764, 96.197662353516, 0, 14700.00, 0, 'cod'),
(252, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 8122.00, 3722.00, 'paid', 'delivered', NULL, '2025-10-17 14:48:04', 16.802897760764, 96.197662353516, 0, 4400.00, 0, 'cod'),
(253, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 24022.00, 3722.00, 'paid', 'delivered', NULL, '2025-10-17 14:48:19', 16.802897760764, 96.197662353516, 0, 20300.00, 0, 'cod'),
(254, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 18422.00, 3722.00, 'paid', 'delivered', NULL, '2025-10-17 15:01:59', 16.802240430531, 96.197147369385, 0, 14700.00, 0, 'cod'),
(255, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 8122.00, 3722.00, 'paid', 'delivered', NULL, '2025-10-17 15:02:13', 16.802240430531, 96.197147369385, 0, 4400.00, 0, 'cod'),
(256, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 8122.00, 3722.00, 'paid', 'delivered', NULL, '2025-10-17 15:02:29', 16.802240430531, 96.197147369385, 0, 4400.00, 0, 'cod'),
(257, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 18422.00, 3722.00, 'paid', 'delivered', NULL, '2025-10-17 15:18:29', 16.80585571863, 96.188735961914, 0, 14700.00, 0, 'cod');
INSERT INTO `orders` (`order_id`, `user_id`, `restaurant_id`, `delivery_address`, `total_amount`, `delivery_fee`, `payment_status`, `order_status`, `cancellation_reason`, `created_at`, `lat`, `lng`, `is_deleted`, `subtotal`, `is_reviewed`, `payment_method`) VALUES
(258, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 7822.00, 3722.00, 'paid', 'delivered', NULL, '2025-10-17 15:18:45', 16.80585571863, 96.188735961914, 0, 4100.00, 0, 'cod'),
(259, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 6522.00, 3722.00, 'paid', 'delivered', NULL, '2025-10-17 15:19:01', 16.80585571863, 96.188735961914, 0, 2800.00, 0, 'cod'),
(260, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 18422.00, 3722.00, 'paid', 'delivered', NULL, '2025-10-17 15:28:39', 16.803883751844, 96.18049621582, 0, 14700.00, 0, 'cod'),
(261, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 18222.00, 3722.00, 'paid', 'delivered', NULL, '2025-10-17 15:28:54', 16.803883751844, 96.18049621582, 0, 14500.00, 0, 'cod'),
(262, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 700.00, 'paid', 'delivered', NULL, '2025-10-21 07:25:34', 16.8001447, 96.1964689, 0, 15600.00, 0, 'cod'),
(263, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 700.00, 'paid', 'delivered', NULL, '2025-10-21 07:27:39', 16.8001447, 96.1964689, 0, 15600.00, 0, 'cod'),
(264, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 700.00, 'paid', 'delivered', NULL, '2025-10-21 07:37:08', 16.8001447, 96.1964689, 0, 15600.00, 0, 'cod'),
(265, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 700.00, 'paid', 'delivered', NULL, '2025-10-21 07:38:05', 16.8001447, 96.1964689, 0, 15600.00, 0, 'cod'),
(266, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 31900.00, 700.00, 'unpaid', 'delivered', 'Item out of stock', '2025-10-21 07:39:07', 16.8001447, 96.1964689, 0, 31200.00, 0, 'cod'),
(267, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17100.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-21 15:10:32', 16.806307624799, 96.192395873329, 0, 15600.00, 0, 'cod'),
(268, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17100.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-21 15:19:26', 16.806307624799, 96.192395873329, 0, 15600.00, 0, 'cod'),
(269, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 700.00, 'unpaid', 'delivered', NULL, '2025-10-22 06:05:50', 16.8056288, 96.1992874, 0, 15600.00, 0, 'cod'),
(270, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17100.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-22 06:08:09', 16.802363700184, 96.205246466145, 0, 15600.00, 0, 'cod'),
(271, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17700.00, 2100.00, 'paid', 'delivered', NULL, '2025-10-22 06:32:38', 16.802897760764, 96.199207305908, 0, 15600.00, 0, 'cod'),
(272, 16, 4, 'Avenue Wine Bar & Bistro, New University Avenue Road, Bahan, Kamayut District, Yangon City, Yangon, 11201, Myanmar', 17700.00, 2100.00, 'unpaid', 'delivered', NULL, '2025-10-22 06:46:00', 16.802897760764, 96.199207305908, 0, 15600.00, 0, 'cod'),
(273, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17700.00, 2100.00, 'paid', 'delivered', NULL, '2025-10-22 06:49:02', 16.802897760764, 96.199207305908, 0, 15600.00, 0, 'cod'),
(274, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17700.00, 2100.00, 'paid', 'delivered', NULL, '2025-10-22 06:51:34', 16.802897760764, 96.199207305908, 0, 15600.00, 0, 'cod'),
(275, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17700.00, 2100.00, 'unpaid', 'delivered', NULL, '2025-10-22 06:51:59', 16.802897760764, 96.199207305908, 0, 15600.00, 0, 'cod'),
(276, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17700.00, 2100.00, 'unpaid', 'delivered', 'Item out of stock', '2025-10-22 06:53:09', 16.801583098021, 96.200065612793, 0, 15600.00, 0, 'cod'),
(277, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17100.00, 1500.00, 'paid', 'delivered', NULL, '2025-10-26 13:14:58', 16.801480379581, 96.195602711649, 0, 15600.00, 0, 'cod'),
(278, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 700.00, 'paid', 'delivered', NULL, '2025-10-26 15:21:34', 16.7995189, 96.197705, 0, 15600.00, 0, 'cod'),
(279, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16800.00, 2100.00, 'unpaid', 'canceled', 'Other', '2025-10-29 07:02:08', 16.800597094986, 96.202320610528, 0, 14700.00, 0, 'cod'),
(280, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 15400.00, 700.00, 'paid', 'delivered', NULL, '2025-10-29 07:09:15', 16.801254430912, 96.200774872906, 0, 14700.00, 0, 'cod'),
(281, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 700.00, 'paid', 'delivered', NULL, '2025-10-31 06:29:49', 16.8056499, 96.1992887, 0, 15600.00, 0, 'cod'),
(282, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17700.00, 2100.00, 'paid', 'delivered', NULL, '2025-10-31 06:47:59', 16.799282416304, 96.20176030369, 0, 15600.00, 0, 'cod'),
(283, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17700.00, 2100.00, 'paid', 'delivered', NULL, '2025-10-31 07:35:01', 16.801747431362, 96.199529600601, 0, 15600.00, 0, 'cod'),
(284, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16800.00, 2100.00, 'paid', 'delivered', NULL, '2025-10-31 08:25:21', 16.799611086828, 96.205871079491, 0, 14700.00, 0, 'cod'),
(285, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16200.00, 1500.00, 'paid', 'delivered', NULL, '2025-11-01 16:14:39', 16.800098956089, 96.203152012901, 0, 14700.00, 0, 'cod'),
(286, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16200.00, 1500.00, 'paid', 'delivered', NULL, '2025-11-01 16:50:37', 16.801005362489, 96.198690793142, 0, 14700.00, 0, 'cod'),
(287, 5, 28, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 4500.00, 1500.00, 'unpaid', 'canceled', 'Found a better option', '2025-11-02 06:34:23', 16.853993074628, 96.211045006132, 0, 3000.00, 0, 'cod'),
(288, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 17100.00, 1500.00, 'unpaid', 'canceled', 'Ordered by mistake', '2025-11-06 07:21:36', 16.802250701334, 96.201438903809, 0, 15600.00, 0, 'cod'),
(289, 5, 4, 'Myama Gon Yi Street, Set Hmu Let Hmu Ward, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 700.00, 'paid', 'delivered', NULL, '2025-11-06 09:22:15', 16.805608114617, 96.199460278489, 0, 15600.00, 0, 'cod'),
(290, 5, 4, 'မြတ်ပန်းဝတ်ရည်လမ်း, No (25) Thu Wa Na Ward, Thingangyun, Thingangyun District, Yangon City, Yangon, 11071, Myanmar', 16300.00, 700.00, 'paid', 'delivered', NULL, '2025-11-06 09:24:31', 16.823438182623, 96.203680267447, 0, 15600.00, 0, 'cod'),
(291, 5, 1, 'Ayeyar Wun Road, Set Hmu Let Hmu Ward, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 15400.00, 700.00, 'unpaid', 'canceled', 'Ordered by mistake', '2025-11-07 06:28:04', 16.803883751844, 96.202035057708, 0, 14700.00, 0, 'cod'),
(292, 5, 1, 'Location at 16.804069, 96.199766 (address lookup failed)', 16800.00, 2100.00, 'unpaid', 'canceled', 'Ordered by mistake', '2025-11-07 06:35:51', 16.804068624601, 96.199765630785, 0, 14700.00, 0, 'cod'),
(293, 5, 4, 'Location at 16.803062, 96.201093 (address lookup failed)', 17700.00, 2100.00, 'paid', 'delivered', NULL, '2025-11-07 08:52:09', 16.803062092967, 96.201092571508, 0, 15600.00, 0, 'cod'),
(294, 5, 4, 'Location at 16.804007, 96.201870 (address lookup failed)', 16300.00, 700.00, 'paid', 'delivered', NULL, '2025-11-07 08:56:49', 16.804007000368, 96.201869785957, 0, 15600.00, 0, 'cod'),
(295, 5, 1, 'Location at 16.802138, 96.201375 (address lookup failed)', 16800.00, 2100.00, 'paid', 'delivered', NULL, '2025-11-08 06:45:18', 16.802137722477, 96.201375363099, 0, 14700.00, 0, 'cod'),
(296, 5, 4, 'Location at 16.802569, 96.201782 (address lookup failed)', 17700.00, 2100.00, 'unpaid', 'canceled', 'Changed my mind', '2025-11-08 06:46:29', 16.802569095932, 96.201782226563, 0, 15600.00, 0, 'cod'),
(297, 5, 4, 'Anawma Street 4, No (1) Ah Naw Mar Ward, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 700.00, 'paid', 'delivered', NULL, '2025-11-08 08:46:29', 16.801726889702, 96.199679726113, 0, 15600.00, 0, 'cod'),
(298, 5, 4, 'Location at 16.803062, 96.200066', 17700.00, 2100.00, 'paid', 'delivered', NULL, '2025-11-10 07:09:15', 16.803062092967, 96.200065612793, 0, 15600.00, 0, 'cod'),
(299, 5, 1, 'Myama Gon Yi Street, Set Hmu Let Hmu Ward, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 15400.00, 700.00, 'paid', 'delivered', NULL, '2025-11-10 07:35:27', 16.806060814778, 96.199421882629, 0, 14700.00, 1, 'cod'),
(300, 5, 1, 'Location at 16.799447, 96.203499', 16800.00, 2100.00, 'paid', 'delivered', NULL, '2025-11-11 08:27:18', 16.799446751637, 96.203498840332, 0, 14700.00, 0, 'cod'),
(301, 5, 1, 'Myo Pat Street, No (1) Ah Naw Mar Ward, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16800.00, 2100.00, 'paid', 'delivered', NULL, '2025-11-11 08:47:54', 16.800925763234, 96.199378967285, 0, 14700.00, 0, 'cod'),
(302, 5, 1, 'Location at 16.805625, 96.199272', 15400.00, 700.00, 'paid', 'delivered', NULL, '2025-11-11 08:52:25', 16.8056252, 96.199272, 0, 14700.00, 0, 'cod'),
(303, 5, 1, 'Location at 16.805623, 96.199273', 15400.00, 700.00, 'paid', 'delivered', NULL, '2025-11-11 09:14:15', 16.8056233, 96.199273, 0, 14700.00, 0, 'cod'),
(304, 5, 1, 'Location at 16.805634, 96.199282', 15400.00, 700.00, 'paid', 'delivered', NULL, '2025-11-12 06:18:29', 16.8056338, 96.1992817, 0, 14700.00, 0, 'cod'),
(305, 5, 1, 'Location at 16.801912, 96.200752', 16800.00, 2100.00, 'paid', 'delivered', NULL, '2025-11-12 07:20:02', 16.801911764561, 96.200752258301, 0, 14700.00, 0, 'cod'),
(306, 5, 1, 'Location at 16.801747, 96.201267', 16800.00, 2100.00, 'paid', 'delivered', NULL, '2025-11-12 07:38:37', 16.801747431362, 96.201267242432, 0, 14700.00, 0, 'cod'),
(307, 5, 1, 'Location at 16.806020, 96.199508', 15400.00, 700.00, 'paid', 'delivered', NULL, '2025-11-12 08:18:32', 16.806020048271, 96.199507713318, 0, 14700.00, 0, 'cod'),
(308, 5, 1, 'Myama Gon Yi Street, Set Hmu Let Hmu Ward, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 15400.00, 700.00, 'paid', 'delivered', NULL, '2025-11-12 08:32:00', 16.8056523, 96.1993052, 0, 14700.00, 0, 'cod'),
(309, 5, 1, 'Myama Gon Yi Street, Set Hmu Let Hmu Ward, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 15400.00, 700.00, 'paid', 'delivered', NULL, '2025-11-12 08:50:56', 16.8056458, 96.1992928, 0, 14700.00, 0, 'cod'),
(310, 5, 1, 'Getting address...', 15400.00, 700.00, 'paid', 'delivered', NULL, '2025-11-12 09:24:08', 16.799489119317, 96.208691596985, 0, 14700.00, 0, 'cod'),
(311, 5, 1, 'Location at 16.805632, 96.199279', 15400.00, 700.00, 'paid', 'delivered', NULL, '2025-11-12 09:30:42', 16.805632, 96.1992787, 0, 14700.00, 0, 'cod'),
(312, 5, 1, 'Myama Gon Yi Street, Set Hmu Let Hmu Ward, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 3900.00, 2100.00, 'paid', 'delivered', NULL, '2025-11-13 06:39:51', 16.8056335, 96.199282, 0, 1800.00, 0, 'cod'),
(313, 5, 1, 'Location at 16.799507, 96.197683', 17200.00, 700.00, 'paid', 'delivered', NULL, '2025-11-14 10:04:14', 16.799528919251, 96.197469234467, 0, 16500.00, 0, 'cod'),
(314, 5, 1, 'Getting address...', 15400.00, 700.00, 'paid', 'delivered', NULL, '2025-11-14 10:04:27', 16.799507, 96.197683, 0, 14700.00, 0, 'cod'),
(315, 5, 1, 'Getting address...', 8900.00, 700.00, 'paid', 'delivered', NULL, '2025-11-14 14:49:29', 16.7995184, 96.1976921, 0, 8200.00, 0, 'cod'),
(316, 5, 1, 'Location at 16.799518, 96.197692', 15200.00, 700.00, 'paid', 'delivered', NULL, '2025-11-14 14:50:01', 16.7995184, 96.1976921, 0, 14500.00, 0, 'cod'),
(317, 5, 1, 'Location at 16.799517, 96.197695', 15400.00, 700.00, 'paid', 'delivered', NULL, '2025-11-14 15:08:02', 16.7995174, 96.197695, 0, 14700.00, 0, 'cod'),
(318, 5, 1, 'Location at 16.799517, 96.197695', 2500.00, 700.00, 'paid', 'delivered', NULL, '2025-11-14 15:08:14', 16.7995174, 96.197695, 0, 1800.00, 0, 'cod'),
(319, 5, 25, 'Location at 16.824260, 96.162128', 10600.00, 2100.00, 'paid', 'delivered', NULL, '2025-11-14 15:48:56', 16.824259753207, 96.162128448486, 0, 8500.00, 0, 'cod'),
(320, 5, 25, 'Getting address...', 12100.00, 2100.00, 'paid', 'delivered', NULL, '2025-11-14 15:49:16', 16.824259753207, 96.162128448486, 0, 10000.00, 0, 'cod'),
(321, 5, 1, 'Getting address...', 2500.00, 700.00, 'paid', 'delivered', NULL, '2025-11-16 07:24:31', 16.7995101, 96.197681, 0, 1800.00, 0, 'cod'),
(322, 5, 1, 'Getting address...', 15400.00, 700.00, 'paid', 'delivered', NULL, '2025-11-16 07:38:41', 16.800581, 96.196384666667, 0, 14700.00, 0, 'cod'),
(323, 5, 1, 'Getting address...', 8900.00, 700.00, 'paid', 'delivered', NULL, '2025-11-16 08:02:35', 16.7995127, 96.1976808, 0, 8200.00, 0, 'cod'),
(324, 5, 11, 'Getting address...', 3700.00, 700.00, 'paid', 'delivered', NULL, '2025-11-16 08:51:40', 16.843524561869, 96.13968372345, 0, 3000.00, 0, 'cod'),
(325, 5, 11, 'Getting address...', 8700.00, 700.00, 'paid', 'delivered', NULL, '2025-11-16 09:11:27', 16.843524561869, 96.13968372345, 0, 8000.00, 0, 'cod'),
(326, 5, 1, 'Getting address...', 16900.00, 700.00, 'paid', 'delivered', NULL, '2025-11-16 12:21:09', 16.79951, 96.1976821, 0, 16200.00, 0, 'cod'),
(327, 5, 1, 'Location at 16.800217, 96.196709', 16900.00, 700.00, 'paid', 'delivered', NULL, '2025-11-16 12:37:35', 16.80021725, 96.196709, 0, 16200.00, 0, 'cod'),
(328, 5, 1, 'Location at 16.805639, 96.199302', 16900.00, 700.00, 'paid', 'delivered', NULL, '2025-11-17 06:06:52', 16.805639, 96.1993017, 0, 16200.00, 0, 'cod'),
(329, 5, 1, 'Location at 16.805607, 96.199265', 17100.00, 700.00, 'paid', 'delivered', NULL, '2025-11-17 08:08:17', 16.8056066, 96.1992649, 0, 16400.00, 0, 'cod'),
(330, 5, 4, 'Getting address...', 15100.00, 2100.00, 'paid', 'delivered', NULL, '2025-11-18 07:17:38', 16.809142284388, 96.211223602295, 0, 13000.00, 0, 'cod'),
(331, 5, 4, 'Moving to 16.804972, 96.199229...', 16300.00, 700.00, 'paid', 'delivered', NULL, '2025-11-18 07:21:22', 16.804972444375, 96.19922883216, 0, 15600.00, 0, 'cod'),
(332, 5, 4, 'Location at 16.805644, 96.199315', 16300.00, 700.00, 'paid', 'delivered', NULL, '2025-11-18 07:41:32', 16.8056439, 96.1993148, 0, 15600.00, 0, 'cod'),
(333, 5, 4, 'Location at 16.805644, 96.199315', 13700.00, 700.00, 'paid', 'delivered', NULL, '2025-11-18 07:41:57', 16.8056439, 96.1993148, 0, 13000.00, 0, 'cod'),
(334, 5, 25, 'Location at 16.826368, 96.154419', 7200.00, 700.00, 'paid', 'delivered', NULL, '2025-11-19 06:58:30', 16.826368, 96.1544192, 0, 6500.00, 0, 'cod'),
(335, 5, 25, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 11200.00, 700.00, 'paid', 'delivered', NULL, '2025-11-20 08:15:27', 16.835430192176, 96.138782501221, 0, 10500.00, 0, 'cod'),
(336, 5, 1, 'Location at 16.805650, 96.199310', 5100.00, 700.00, 'paid', 'delivered', NULL, '2025-11-20 08:33:44', 16.8056498, 96.1993098, 0, 4400.00, 0, 'cod'),
(337, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 15400.00, 700.00, 'unpaid', 'canceled', 'Item out of stock', '2025-11-22 05:22:44', 16.80125956634, 96.199207305908, 0, 14700.00, 0, 'cod'),
(338, 5, 1, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 15400.00, 700.00, 'unpaid', 'canceled', 'Item out of stock', '2025-11-22 05:40:11', 16.80125956634, 96.199207305908, 0, 14700.00, 0, 'cod'),
(339, 5, 1, 'Location at 16.801275, 96.198049', 7665.00, 3265.00, 'paid', 'delivered', NULL, '2025-11-22 09:26:58', 16.801274972623, 96.198048980024, 0, 4400.00, 0, 'cod'),
(340, 5, 19, 'Moving to 16.799714, 96.196095...', 11700.00, 700.00, 'paid', 'delivered', NULL, '2025-11-22 09:53:04', 16.799713796251, 96.196094574768, 0, 11000.00, 0, 'cod'),
(341, 5, 1, 'Anawma Street 13, No (1) Ah Naw Mar Ward, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 5100.00, 700.00, 'paid', 'delivered', NULL, '2025-11-23 05:25:14', 16.797967728514, 96.196802437504, 0, 4400.00, 0, 'cod'),
(342, 5, 1, 'Location at 16.798440, 96.197835', 17200.00, 700.00, 'paid', 'delivered', NULL, '2025-11-23 06:01:54', 16.798440195487, 96.197834551268, 0, 16500.00, 0, 'cod'),
(343, 5, 4, 'Location at 16.799907, 96.197202', 13700.00, 700.00, 'paid', 'delivered', NULL, '2025-11-23 06:07:25', 16.7999069, 96.1972024, 0, 13000.00, 0, 'cod'),
(344, 5, 1, 'Location at 16.799907, 96.197202', 5100.00, 700.00, 'unpaid', 'canceled', 'Ordered by mistake', '2025-11-23 06:10:43', 16.7999069, 96.1972024, 0, 4400.00, 0, 'cod'),
(345, 5, 4, 'Location at 16.799907, 96.197202', 13700.00, 700.00, 'paid', 'delivered', NULL, '2025-11-23 06:22:23', 16.7999069, 96.1972024, 0, 13000.00, 0, 'cod'),
(346, 5, 4, 'Location at 16.799907, 96.197202', 11750.00, 700.00, 'paid', 'delivered', NULL, '2025-11-23 06:26:09', 16.7999069, 96.1972024, 0, 11050.00, 0, 'cod'),
(347, 5, 4, 'Location at 16.799907, 96.197202', 13700.00, 700.00, 'paid', 'delivered', NULL, '2025-11-23 10:52:33', 16.7999069, 96.1972024, 0, 13000.00, 0, 'cod'),
(348, 5, 4, 'Location at 16.799907, 96.197202', 12600.00, 700.00, 'paid', 'delivered', NULL, '2025-11-23 10:53:31', 16.7999069, 96.1972024, 0, 11900.00, 0, 'cod'),
(349, 5, 4, 'Location at 16.798461, 96.196696', 11900.00, 700.00, 'paid', 'delivered', NULL, '2025-11-23 11:02:32', 16.798460737503, 96.196696314373, 0, 11200.00, 0, 'cod'),
(350, 5, 4, 'Location at 16.799907, 96.197202', 11900.00, 700.00, 'paid', 'delivered', NULL, '2025-11-23 11:20:45', 16.7999069, 96.1972024, 0, 11200.00, 0, 'cod'),
(351, 5, 4, 'Location at 16.799907, 96.197202', 12100.00, 700.00, 'paid', 'delivered', NULL, '2025-11-23 13:42:25', 16.7999069, 96.1972024, 0, 11400.00, 0, 'cod'),
(352, 5, 4, 'Getting address...', 12100.00, 700.00, 'paid', 'delivered', NULL, '2025-11-23 13:55:21', 16.7999069, 96.1972024, 0, 11400.00, 0, 'cod'),
(353, 5, 4, 'Location at 16.807355, 96.197124', 14656.00, 3456.00, 'paid', 'delivered', NULL, '2025-11-24 06:26:29', 16.807355221321, 96.197124099133, 0, 11200.00, 0, 'cod'),
(354, 5, 1, 'Location at 16.814236, 96.208134', 17961.00, 3461.00, 'paid', 'delivered', NULL, '2025-11-24 08:57:58', 16.814236348762, 96.20813369751, 0, 14500.00, 0, 'cod'),
(355, 5, 4, 'Myama Gon Yi Street, Set Hmu Let Hmu Ward, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 11900.00, 700.00, 'unpaid', 'canceled', 'Ordered by mistake', '2025-11-25 09:01:31', 16.8056549, 96.1993344, 0, 11200.00, 0, 'cod'),
(356, 5, 4, 'Myo Pat Street, No (1) Ah Naw Mar Ward, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 11750.00, 700.00, 'paid', 'delivered', NULL, '2025-11-26 07:40:51', 16.80125956634, 96.199207305908, 0, 11050.00, 0, 'cod'),
(357, 5, 4, 'Ayar Wun Yeik Thar 4rd Street, No (1) Ah Naw Mar Ward, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 14656.00, 3356.00, 'paid', 'delivered', NULL, '2025-11-26 08:01:47', 16.802445846474, 96.198134829208, 0, 11300.00, 0, 'cod'),
(358, 5, 4, 'Myit Tar Nyunt Ward, Tamwe, Thingangyun District, Yangon City, Yangon, 11212, Myanmar', 18956.00, 3356.00, 'paid', 'delivered', NULL, '2025-11-26 08:04:15', 16.809799590706, 96.185005576927, 0, 15600.00, 0, 'cod'),
(359, 5, 4, 'Tat Phwet Street, No (1) Ah Naw Mar Ward, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 700.00, 'paid', 'delivered', NULL, '2025-11-26 08:17:47', 16.799517, 96.1976576, 0, 15600.00, 0, 'cod'),
(360, 5, 4, 'Location at 16.799511, 96.197663', 16300.00, 700.00, 'paid', 'delivered', NULL, '2025-11-26 08:35:28', 16.7995112, 96.1976627, 0, 15600.00, 0, 'cod'),
(361, 5, 1, 'Location at 16.799515, 96.197659', 15400.00, 700.00, 'unpaid', 'canceled', 'Restaurant is closed', '2025-11-26 09:16:55', 16.7995145, 96.1976592, 0, 14700.00, 0, 'cod'),
(362, 5, 4, 'Location at 16.805623, 96.199282', 14114.00, 3064.00, 'paid', 'delivered', NULL, '2025-11-27 06:36:42', 16.8056232, 96.1992818, 0, 11050.00, 0, 'cod'),
(363, 5, 4, 'Location at 16.805621, 96.199279', 16300.00, 700.00, 'paid', 'delivered', NULL, '2025-11-27 08:42:25', 16.8056212, 96.1992793, 0, 15600.00, 0, 'cod'),
(364, 5, 4, 'Location at 16.800926, 96.201267', 93800.00, 700.00, 'unpaid', 'canceled', 'Preparation time too long', '2025-11-27 13:29:55', 16.800925763234, 96.201267242432, 0, 93100.00, 0, 'cod'),
(365, 5, 4, 'Anawma Street 4, No (1) Ah Naw Mar Ward, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16249.00, 3499.00, 'unpaid', 'canceled', 'Customer requested cancellation', '2025-11-27 13:52:28', 16.800925763234, 96.201267242432, 0, 12750.00, 0, 'cod'),
(366, 5, 4, 'Location at 16.800926, 96.201267', 16300.00, 700.00, 'unpaid', 'canceled', 'Restaurant is closed', '2025-11-27 14:19:08', 16.800925763234, 96.201267242432, 0, 15600.00, 0, 'cod'),
(367, 5, 1, 'Location at 16.800926, 96.201267', 12313.00, 3513.00, 'unpaid', 'canceled', 'Item out of stock', '2025-11-27 14:19:44', 16.800925763234, 96.201267242432, 0, 8800.00, 0, 'cod'),
(368, 5, 4, 'Ayar Wun Yeik Thar 2nd Street, No (1) Ah Naw Mar Ward, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 700.00, 'paid', 'delivered', NULL, '2025-11-29 03:37:24', 16.803149394391, 96.197576522827, 0, 15600.00, 1, 'cod'),
(369, 5, 23, 'No (15) Ward, South Okkalapa, Thingangyun District, Yangon City, Yangon, 11091, Myanmar', 5100.00, 2100.00, 'paid', 'delivered', NULL, '2025-11-29 04:04:46', 16.86081603565, 96.178650856018, 0, 3000.00, 0, 'cod'),
(370, 5, 4, 'Tat Phwet Street, No (1) Ah Naw Mar Ward, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 11750.00, 700.00, 'paid', 'delivered', NULL, '2025-11-30 12:52:37', 16.7995225, 96.197663, 0, 11050.00, 0, 'cod'),
(371, 5, 4, 'Tat Phwet Street, No (1) Ah Naw Mar Ward, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 14449.00, 3399.00, 'unpaid', 'canceled', 'Ordered by mistake', '2025-11-30 12:53:43', 16.7995225, 96.197663, 0, 11050.00, 0, 'cod'),
(372, 5, 4, 'Location at 16.799522, 96.197663', 11750.00, 700.00, 'unpaid', 'canceled', 'Found a better option', '2025-11-30 13:06:08', 16.7995225, 96.197663, 0, 11050.00, 0, 'cod'),
(373, 5, 4, 'Tat Phwet Street, No (1) Ah Naw Mar Ward, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 14449.00, 3399.00, 'paid', 'delivered', NULL, '2025-11-30 13:14:52', 16.7995225, 96.197663, 0, 11050.00, 0, 'cod'),
(374, 5, 3, 'Location at 16.799522, 96.197663', 13915.00, 3415.00, 'unpaid', 'canceled', 'Ordered by mistake', '2025-11-30 13:27:02', 16.7995225, 96.197663, 0, 10500.00, 0, 'cod'),
(375, 5, 4, 'Location at 16.799522, 96.197663', 16300.00, 700.00, 'unpaid', 'canceled', 'Ordered by mistake', '2025-11-30 13:34:03', 16.7995225, 96.197663, 0, 15600.00, 0, 'cod'),
(376, 5, 1, 'Location at 16.783547, 96.181183', 9500.00, 700.00, 'unpaid', 'canceled', 'Ordered by mistake', '2025-12-01 07:22:08', 16.783546649188, 96.181182861328, 0, 8800.00, 0, 'cod'),
(377, 5, 1, 'Tat Phwet Street, No (1) Ah Naw Mar Ward, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 5100.00, 700.00, 'paid', 'delivered', NULL, '2025-12-03 11:49:05', 16.7995281, 96.1976874, 0, 4400.00, 0, 'cod'),
(378, 5, 4, 'Moving to 16.813086, 96.179854...', 16300.00, 700.00, 'paid', 'delivered', NULL, '2025-12-04 13:58:12', 16.813086088123, 96.179854107995, 0, 15600.00, 0, 'cod'),
(379, 5, 4, 'Getting address...', 44050.00, 700.00, 'unpaid', 'canceled', 'Ordered by mistake', '2025-12-04 14:12:29', 16.7995111, 96.1976709, 0, 43350.00, 0, 'cod'),
(380, 5, 4, 'Location at 16.799511, 96.197671', 16300.00, 700.00, 'unpaid', 'canceled', 'Ordered by mistake', '2025-12-04 16:17:48', 16.7995111, 96.1976709, 0, 15600.00, 0, 'cod'),
(381, 5, 4, 'Location at 16.799511, 96.197671', 14655.00, 3605.00, 'unpaid', 'canceled', 'Ordered by mistake', '2025-12-04 16:24:38', 16.7995111, 96.1976709, 0, 11050.00, 0, 'cod'),
(382, 5, 4, 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 700.00, 'unpaid', 'canceled', 'Ordered by mistake', '2025-12-05 08:58:24', 16.794701511622, 96.200408935547, 0, 15600.00, 0, 'cod'),
(383, 86, 4, 'Location at 16.802240, 96.200237', 14655.00, 3605.00, 'unpaid', 'canceled', 'Restaurant is closed', '2025-12-07 16:54:54', 16.802240430531, 96.20023727417, 0, 11050.00, 0, 'cod'),
(384, 86, 1, 'SEIN888 POWER TOOLS, 01, Anawma Street 3, No (1) Ah Naw Mar Ward, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 63000.00, 700.00, 'unpaid', 'pending', NULL, '2025-12-07 17:07:13', 16.802240430531, 96.20023727417, 0, 62300.00, 0, 'cod'),
(385, 86, 4, 'SEIN888 POWER TOOLS, 01, Anawma Street 3, No (1) Ah Naw Mar Ward, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 11750.00, 700.00, 'unpaid', 'canceled', 'Restaurant is closed', '2025-12-07 17:08:10', 16.802240430531, 96.20023727417, 0, 11050.00, 0, 'cod'),
(386, 5, 23, 'Location at 16.855859, 96.170803', 8200.00, 1400.00, 'unpaid', 'canceled', 'Ordered by mistake', '2025-12-10 07:41:39', 16.8558592, 96.1708032, 0, 6800.00, 0, 'cod'),
(387, 5, 23, 'Location at 16.855859, 96.170803', 14200.00, 1400.00, 'unpaid', 'canceled', 'Delivery time too long', '2025-12-10 09:07:42', 16.8558592, 96.1708032, 0, 12800.00, 0, 'cod'),
(388, 5, 4, 'Moving to 16.799652, 96.197078...', 19179.00, 3579.00, 'paid', 'delivered', NULL, '2025-12-12 16:59:02', 16.799652170604, 96.197078139908, 0, 15600.00, 0, 'cod'),
(389, 5, 1, 'Location at 16.799526, 96.197601', 5100.00, 700.00, 'unpaid', 'pending', NULL, '2025-12-13 05:58:22', 16.799525965232, 96.197600710548, 0, 4400.00, 0, 'cod'),
(390, 5, 4, 'Location at 16.799526, 96.197601', 16300.00, 700.00, 'paid', 'delivered', NULL, '2025-12-13 06:09:04', 16.799525965232, 96.197600710548, 0, 15600.00, 0, 'cod'),
(391, 5, 4, 'Anawma Street 1, No (1) Ah Naw Mar Ward, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 30229.00, 3579.00, 'paid', 'delivered', NULL, '2025-12-14 08:48:20', 16.802733428419, 96.200408935547, 0, 26650.00, 0, 'cod'),
(392, 94, 4, 'Moving to 16.799549, 96.197491...', 16300.00, 700.00, 'unpaid', 'canceled', 'Ordered by mistake', '2025-12-14 10:00:14', 16.799549461148, 96.19749059966, 0, 15600.00, 0, 'cod'),
(393, 94, 4, 'Tat Phwet Street, No (1) Ah Naw Mar Ward, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 700.00, 'unpaid', 'ready', NULL, '2025-12-14 13:59:00', 16.799518650937, 96.197628253921, 0, 15600.00, 0, 'cod'),
(394, 94, 4, 'Tat Phwet Street, No (1) Ah Naw Mar Ward, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', 16300.00, 700.00, 'unpaid', 'canceled', 'Changed my mind', '2025-12-14 14:01:59', 16.799518650937, 96.197628253921, 0, 15600.00, 0, 'cod');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `menu_options` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `item_id`, `quantity`, `price`, `menu_options`) VALUES
(1, 1, 1, 2, 2500.00, NULL),
(2, 3, 1, 1, 2500.00, NULL),
(3, 4, 1, 1, 2500.00, NULL),
(4, 5, 3, 1, 1800.00, NULL),
(5, 6, 1, 25, 2500.00, NULL),
(6, 6, 3, 2, 1800.00, NULL),
(8, 7, 1, 2, 2500.00, NULL),
(9, 8, 1, 1, 2500.00, NULL),
(10, 9, 1, 1, 2500.00, NULL),
(11, 10, 1, 1, 2500.00, NULL),
(12, 11, 1, 1, 2500.00, NULL),
(13, 12, 1, 1, 2500.00, NULL),
(14, 13, 1, 1, 2500.00, NULL),
(15, 14, 1, 1, 2500.00, NULL),
(16, 15, 1, 1, 2500.00, NULL),
(17, 16, 1, 1, 2500.00, NULL),
(18, 17, 1, 1, 2500.00, NULL),
(19, 18, 1, 6, 2500.00, NULL),
(20, 19, 1, 1, 2500.00, NULL),
(21, 20, 1, 1, 2500.00, NULL),
(22, 21, 1, 1, 2500.00, NULL),
(24, 23, 1, 1, 2500.00, NULL),
(25, 24, 1, 1, 2500.00, NULL),
(26, 25, 1, 1, 2500.00, NULL),
(28, 27, 1, 1, 2500.00, NULL),
(29, 28, 1, 1, 2500.00, NULL),
(30, 29, 1, 1, 2500.00, NULL),
(31, 30, 3, 1, 1800.00, NULL),
(32, 31, 1, 3, 2500.00, NULL),
(34, 33, 1, 1, 2500.00, NULL),
(35, 34, 1, 1, 2500.00, NULL),
(37, 36, 1, 1, 2500.00, NULL),
(38, 37, 1, 1, 2500.00, NULL),
(39, 38, 1, 1, 2500.00, NULL),
(44, 43, 3, 1, 1800.00, NULL),
(49, 48, 1, 2, 2500.00, NULL),
(50, 49, 1, 2, 2500.00, NULL),
(51, 50, 1, 1, 2500.00, NULL),
(52, 51, 1, 2, 2500.00, NULL),
(57, 56, 1, 6, 2500.00, NULL),
(59, 58, 3, 5, 1800.00, NULL),
(60, 58, 1, 1, 2500.00, NULL),
(61, 59, 3, 2, 1800.00, NULL),
(62, 60, 3, 1, 1800.00, NULL),
(63, 61, 3, 1, 1800.00, NULL),
(64, 62, 3, 1, 1800.00, NULL),
(65, 63, 1, 2, 2500.00, NULL),
(66, 64, 3, 1, 1800.00, NULL),
(68, 66, 1, 1, 3000.00, NULL),
(69, 67, 4, 1, 15000.00, NULL),
(70, 68, 14, 1, 30000.00, NULL),
(71, 69, 14, 1, 30000.00, NULL),
(72, 70, 1, 1, 3000.00, NULL),
(73, 71, 3, 1, 1800.00, NULL),
(75, 73, 14, 3, 30000.00, NULL),
(77, 74, 14, 1, 30000.00, NULL),
(80, 77, 3, 1, 1800.00, NULL),
(81, 78, 15, 1, 8000.00, NULL),
(82, 79, 3, 1, 1800.00, NULL),
(83, 80, 3, 1, 1800.00, NULL),
(84, 81, 3, 1, 1800.00, NULL),
(85, 82, 3, 1, 1800.00, NULL),
(86, 83, 4, 1, 15000.00, NULL),
(87, 84, 4, 1, 15000.00, NULL),
(89, 86, 4, 1, 15000.00, NULL),
(90, 87, 1, 1, 3000.00, NULL),
(91, 88, 67, 1, 9500.00, NULL),
(92, 89, 15, 1, 8000.00, NULL),
(93, 90, 1, 1, 3000.00, NULL),
(94, 91, 14, 1, 30000.00, NULL),
(96, 93, 81, 1, 10500.00, NULL),
(97, 94, 81, 1, 10500.00, NULL),
(98, 95, 81, 1, 10500.00, NULL),
(99, 96, 81, 1, 10500.00, NULL),
(100, 97, 81, 1, 10500.00, NULL),
(101, 98, 77, 4, 16200.00, NULL),
(102, 99, 75, 1, 1000.00, NULL),
(103, 100, 80, 1, 16500.00, NULL),
(104, 101, 81, 1, 10500.00, NULL),
(105, 102, 123, 1, 12000.00, NULL),
(106, 103, 82, 1, 8000.00, NULL),
(107, 103, 82, 4, 13000.00, NULL),
(108, 104, 191, 1, 9500.00, NULL),
(109, 105, 77, 1, 16200.00, NULL),
(110, 106, 77, 1, 16200.00, NULL),
(111, 107, 81, 1, 10500.00, NULL),
(112, 108, 194, 1, 15600.00, NULL),
(113, 109, 15, 1, 8000.00, NULL),
(114, 110, 64, 1, 10000.00, NULL),
(115, 111, 194, 1, 15600.00, NULL),
(116, 112, 15, 1, 8000.00, NULL),
(117, 113, 178, 1, 10000.00, NULL),
(118, 114, 194, 1, 15600.00, NULL),
(119, 115, 178, 1, 10000.00, NULL),
(120, 116, 15, 1, 8000.00, NULL),
(121, 117, 64, 1, 10000.00, NULL),
(122, 118, 1, 1, 14700.00, NULL),
(123, 119, 194, 1, 15600.00, NULL),
(124, 120, 1, 1, 14700.00, NULL),
(125, 121, 194, 1, 15600.00, NULL),
(126, 122, 194, 1, 15600.00, NULL),
(127, 123, 194, 1, 15600.00, NULL),
(128, 124, 1, 1, 14700.00, NULL),
(129, 125, 1, 1, 14700.00, NULL),
(130, 126, 194, 1, 15600.00, NULL),
(131, 127, 194, 1, 15600.00, NULL),
(132, 128, 194, 1, 15600.00, NULL),
(133, 129, 195, 1, 11050.00, NULL),
(134, 130, 194, 1, 15600.00, NULL),
(135, 131, 194, 1, 15600.00, NULL),
(136, 132, 194, 1, 15600.00, NULL),
(137, 133, 194, 1, 15600.00, NULL),
(138, 134, 63, 1, 9500.00, NULL),
(139, 135, 194, 1, 15600.00, NULL),
(140, 136, 194, 1, 15600.00, NULL),
(141, 137, 194, 1, 15600.00, NULL),
(142, 138, 194, 1, 15600.00, NULL),
(143, 139, 1, 1, 14700.00, NULL),
(144, 140, 194, 1, 15600.00, NULL),
(145, 141, 194, 1, 15600.00, NULL),
(146, 142, 1, 1, 14700.00, NULL),
(147, 143, 15, 1, 8000.00, NULL),
(148, 144, 15, 1, 8000.00, NULL),
(149, 145, 194, 1, 15600.00, NULL),
(150, 146, 194, 1, 15600.00, NULL),
(151, 147, 82, 1, 8000.00, NULL),
(152, 148, 194, 1, 15600.00, NULL),
(153, 149, 194, 1, 15600.00, NULL),
(154, 150, 194, 1, 15600.00, NULL),
(155, 151, 194, 1, 15600.00, NULL),
(156, 152, 194, 1, 15600.00, NULL),
(157, 153, 194, 1, 15600.00, NULL),
(158, 154, 194, 1, 15600.00, NULL),
(159, 155, 1, 1, 14700.00, NULL),
(160, 156, 194, 1, 15600.00, NULL),
(161, 157, 194, 1, 15600.00, NULL),
(162, 158, 194, 1, 15600.00, NULL),
(163, 159, 194, 1, 15600.00, NULL),
(164, 160, 194, 1, 15600.00, NULL),
(165, 161, 194, 1, 15600.00, NULL),
(166, 162, 1, 1, 14700.00, NULL),
(167, 163, 15, 1, 8000.00, NULL),
(168, 164, 1, 1, 14700.00, NULL),
(169, 165, 1, 1, 14700.00, NULL),
(170, 166, 45, 1, 2500.00, NULL),
(171, 167, 194, 1, 15600.00, NULL),
(172, 168, 63, 1, 9500.00, NULL),
(173, 169, 15, 1, 8000.00, NULL),
(174, 170, 195, 1, 28400.00, NULL),
(175, 171, 195, 1, 33350.00, NULL),
(176, 172, 1, 1, 14700.00, NULL),
(177, 172, 3, 1, 1800.00, NULL),
(178, 172, 14, 1, 4400.00, NULL),
(179, 172, 77, 1, 16200.00, NULL),
(180, 172, 4, 1, 8200.00, NULL),
(181, 173, 38, 1, 100.00, NULL),
(182, 174, 15, 1, 8000.00, NULL),
(183, 175, 1, 1, 14700.00, NULL),
(184, 176, 1, 1, 14700.00, NULL),
(185, 177, 1, 1, 14700.00, NULL),
(186, 178, 15, 1, 8000.00, NULL),
(187, 179, 194, 1, 15600.00, NULL),
(188, 180, 1, 1, 14700.00, NULL),
(189, 181, 1, 1, 14700.00, NULL),
(190, 182, 1, 2, 14700.00, NULL),
(191, 183, 1, 1, 14700.00, NULL),
(192, 184, 194, 1, 15600.00, NULL),
(193, 185, 1, 1, 14700.00, NULL),
(194, 186, 15, 1, 8000.00, NULL),
(195, 187, 1, 1, 14700.00, NULL),
(196, 188, 15, 2, 8000.00, NULL),
(197, 189, 3, 1, 1800.00, NULL),
(198, 190, 1, 1, 14700.00, NULL),
(199, 191, 194, 1, 15600.00, NULL),
(200, 192, 194, 1, 15600.00, NULL),
(201, 193, 194, 1, 15600.00, NULL),
(202, 194, 194, 1, 15600.00, NULL),
(203, 195, 194, 1, 15600.00, NULL),
(204, 196, 194, 1, 15600.00, NULL),
(205, 197, 194, 1, 15600.00, NULL),
(206, 198, 194, 1, 15600.00, NULL),
(207, 199, 194, 1, 15600.00, NULL),
(208, 200, 194, 1, 15600.00, NULL),
(209, 201, 194, 1, 15600.00, NULL),
(210, 202, 194, 1, 15600.00, NULL),
(211, 203, 194, 1, 15600.00, NULL),
(212, 204, 194, 1, 15600.00, NULL),
(213, 205, 194, 1, 15600.00, NULL),
(214, 206, 194, 1, 15600.00, NULL),
(215, 207, 194, 1, 15600.00, NULL),
(216, 208, 194, 1, 15600.00, NULL),
(217, 210, 194, 1, 15600.00, NULL),
(218, 211, 194, 1, 15600.00, NULL),
(219, 212, 194, 1, 15600.00, NULL),
(220, 213, 194, 1, 15600.00, NULL),
(221, 214, 194, 1, 15600.00, NULL),
(222, 215, 194, 1, 15600.00, NULL),
(223, 216, 194, 2, 15600.00, NULL),
(224, 217, 194, 1, 15600.00, NULL),
(225, 218, 194, 1, 15600.00, NULL),
(226, 219, 85, 2, 7000.00, NULL),
(227, 220, 194, 1, 15600.00, NULL),
(228, 221, 194, 1, 15600.00, NULL),
(229, 222, 194, 1, 15600.00, NULL),
(230, 223, 194, 1, 15600.00, NULL),
(231, 224, 194, 1, 15600.00, NULL),
(232, 225, 194, 1, 15600.00, NULL),
(233, 226, 194, 1, 15600.00, NULL),
(234, 227, 194, 1, 15600.00, NULL),
(235, 228, 194, 1, 15600.00, NULL),
(236, 229, 194, 1, 15600.00, NULL),
(237, 230, 194, 1, 15600.00, NULL),
(238, 231, 194, 1, 15600.00, NULL),
(239, 232, 1, 1, 14700.00, NULL),
(240, 233, 194, 1, 15600.00, NULL),
(241, 234, 194, 1, 15600.00, NULL),
(242, 235, 1, 1, 14700.00, NULL),
(243, 236, 1, 1, 14700.00, NULL),
(244, 237, 14, 1, 4400.00, NULL),
(245, 237, 79, 1, 20300.00, NULL),
(246, 238, 197, 1, 4400.00, NULL),
(247, 239, 204, 1, 9400.00, NULL),
(248, 240, 4, 1, 8200.00, NULL),
(249, 241, 77, 1, 16200.00, NULL),
(250, 241, 14, 1, 4400.00, NULL),
(251, 242, 1, 1, 14700.00, NULL),
(252, 243, 1, 1, 14700.00, NULL),
(253, 244, 3, 1, 1800.00, NULL),
(254, 245, 77, 1, 16200.00, NULL),
(255, 245, 78, 1, 14500.00, NULL),
(256, 246, 1, 1, 14700.00, NULL),
(257, 246, 80, 1, 16500.00, NULL),
(258, 247, 4, 1, 8200.00, NULL),
(259, 248, 197, 1, 4400.00, NULL),
(260, 248, 202, 1, 13800.00, NULL),
(261, 249, 14, 1, 4400.00, NULL),
(262, 250, 3, 1, 1800.00, NULL),
(263, 251, 1, 1, 14700.00, NULL),
(264, 252, 14, 1, 4400.00, NULL),
(265, 253, 79, 1, 20300.00, NULL),
(266, 254, 1, 1, 14700.00, NULL),
(267, 255, 197, 1, 4400.00, NULL),
(268, 256, 14, 1, 4400.00, NULL),
(269, 257, 1, 1, 14700.00, NULL),
(270, 258, 198, 1, 4100.00, NULL),
(271, 259, 201, 1, 2800.00, NULL),
(272, 260, 1, 1, 14700.00, NULL),
(273, 261, 78, 1, 14500.00, NULL),
(274, 262, 194, 1, 15600.00, NULL),
(275, 263, 194, 1, 15600.00, NULL),
(276, 264, 194, 1, 15600.00, NULL),
(277, 265, 194, 1, 15600.00, NULL),
(278, 266, 194, 2, 15600.00, NULL),
(279, 267, 194, 1, 15600.00, NULL),
(280, 268, 194, 1, 15600.00, NULL),
(281, 269, 194, 1, 15600.00, NULL),
(282, 270, 194, 1, 15600.00, NULL),
(283, 271, 194, 1, 15600.00, NULL),
(284, 272, 194, 1, 15600.00, NULL),
(285, 273, 194, 1, 15600.00, NULL),
(286, 274, 194, 1, 15600.00, NULL),
(287, 275, 194, 1, 15600.00, NULL),
(288, 276, 194, 1, 15600.00, NULL),
(289, 277, 194, 1, 15600.00, NULL),
(290, 278, 194, 1, 15600.00, NULL),
(291, 279, 1, 1, 14700.00, NULL),
(292, 280, 1, 1, 14700.00, NULL),
(293, 281, 194, 1, 15600.00, NULL),
(294, 282, 194, 1, 15600.00, NULL),
(295, 283, 194, 1, 15600.00, NULL),
(296, 284, 1, 1, 14700.00, NULL),
(297, 285, 1, 1, 14700.00, NULL),
(298, 286, 1, 1, 14700.00, NULL),
(299, 287, 27, 1, 3000.00, NULL),
(300, 288, 194, 1, 15600.00, NULL),
(301, 289, 194, 1, 15600.00, NULL),
(302, 290, 194, 1, 15600.00, NULL),
(303, 291, 1, 1, 14700.00, NULL),
(304, 292, 1, 1, 14700.00, NULL),
(305, 293, 194, 1, 15600.00, NULL),
(306, 294, 194, 1, 15600.00, NULL),
(307, 295, 1, 1, 14700.00, NULL),
(308, 296, 194, 1, 15600.00, NULL),
(309, 297, 194, 1, 15600.00, NULL),
(310, 298, 194, 1, 15600.00, NULL),
(311, 299, 1, 1, 14700.00, NULL),
(312, 300, 1, 1, 14700.00, NULL),
(313, 301, 1, 1, 14700.00, NULL),
(314, 302, 1, 1, 14700.00, NULL),
(315, 303, 1, 1, 14700.00, NULL),
(316, 304, 1, 1, 14700.00, NULL),
(317, 305, 1, 1, 14700.00, NULL),
(318, 306, 1, 1, 14700.00, NULL),
(319, 307, 1, 1, 14700.00, NULL),
(320, 308, 1, 1, 14700.00, NULL),
(321, 309, 1, 1, 14700.00, NULL),
(322, 310, 1, 1, 14700.00, NULL),
(323, 311, 1, 1, 14700.00, NULL),
(324, 312, 3, 1, 1800.00, NULL),
(325, 313, 80, 1, 16500.00, NULL),
(326, 314, 1, 1, 14700.00, NULL),
(327, 315, 4, 1, 8200.00, NULL),
(328, 316, 78, 1, 14500.00, NULL),
(329, 317, 1, 1, 14700.00, NULL),
(330, 318, 3, 1, 1800.00, NULL),
(331, 319, 57, 1, 8500.00, NULL),
(332, 320, 173, 1, 10000.00, NULL),
(333, 321, 3, 1, 1800.00, NULL),
(334, 322, 1, 1, 14700.00, NULL),
(335, 323, 4, 1, 8200.00, NULL),
(336, 324, 99, 1, 3000.00, NULL),
(337, 325, 102, 1, 8000.00, NULL),
(338, 326, 77, 1, 16200.00, NULL),
(339, 327, 77, 1, 16200.00, NULL),
(340, 328, 77, 1, 16200.00, NULL),
(341, 329, 4, 2, 8200.00, NULL),
(342, 330, 217, 1, 13000.00, NULL),
(343, 331, 194, 1, 15600.00, NULL),
(344, 332, 194, 1, 15600.00, NULL),
(345, 333, 215, 1, 13000.00, NULL),
(346, 334, 172, 1, 6500.00, NULL),
(347, 335, 175, 1, 10500.00, NULL),
(348, 336, 14, 1, 4400.00, NULL),
(349, 337, 1, 1, 14700.00, NULL),
(350, 338, 1, 1, 14700.00, NULL),
(351, 339, 14, 1, 4400.00, NULL),
(352, 340, 90, 1, 11000.00, NULL),
(353, 341, 14, 1, 4400.00, NULL),
(354, 342, 80, 1, 16500.00, NULL),
(355, 343, 216, 1, 13000.00, NULL),
(356, 344, 14, 1, 4400.00, NULL),
(357, 345, 216, 1, 13000.00, '[{\"option_id\":15,\"option_name\":\"Choice of Soup For Set\",\"value_id\":35,\"value_name\":\"Mala\",\"price_modifier\":\"0.00\"}]'),
(358, 346, 195, 1, 11050.00, '[{\"option_id\":7,\"option_name\":\"Choice of Type for Mala Xiang Guo\",\"value_id\":8,\"value_name\":\"Salad\",\"price_modifier\":\"0.00\"},{\"option_id\":8,\"option_name\":\"Choice of Taste\",\"value_id\":11,\"value_name\":\"More Spicy and Numbing Tingle\",\"price_modifier\":\"0.00\"},{\"option_id\":9,\"option_name\":\"Choice of Add Ons Meat Slice for Mala Xiang Guo\",\"value_id\":14,\"value_name\":\"Pork\",\"price_modifier\":\"5700.00\"},{\"option_id\":10,\"option_name\":\"Choice of Meat Ball for Mala Xiang Guo\",\"value_id\":17,\"value_name\":\"Pork Mushroom 3 Balls\",\"price_modifier\":\"3900.00\"},{\"option_id\":11,\"option_name\":\"Choice of Vegetables for Mala Xiang Guo\",\"value_id\":28,\"value_name\":\"Corn\",\"price_modifier\":\"1450.00\"}]'),
(359, 347, 216, 1, 13000.00, '[{\"option_id\":15,\"option_name\":\"Choice of Soup For Set\",\"value_id\":35,\"value_name\":\"Mala\",\"price_modifier\":\"0.00\"}]'),
(360, 348, 195, 1, 11900.00, '[{\"option_id\":7,\"option_name\":\"Choice of Type for Mala Xiang Guo\",\"value_id\":8,\"value_name\":\"Salad\",\"price_modifier\":\"0.00\"},{\"option_id\":8,\"option_name\":\"Choice of Taste\",\"value_id\":10,\"value_name\":\"Regular Spicy and Numbing Tingle \",\"price_modifier\":\"0.00\"},{\"option_id\":9,\"option_name\":\"Choice of Add Ons Meat Slice for Mala Xiang Guo\",\"value_id\":15,\"value_name\":\"Seafood\",\"price_modifier\":\"6300.00\"},{\"option_id\":10,\"option_name\":\"Choice of Meat Ball for Mala Xiang Guo\",\"value_id\":16,\"value_name\":\"Chicken Mushroom 3 Balls\",\"price_modifier\":\"3900.00\"},{\"option_id\":11,\"option_name\":\"Choice of Vegetables for Mala Xiang Guo\",\"value_id\":21,\"value_name\":\"Taiwan Mon Nhyinn\",\"price_modifier\":\"1700.00\"}]'),
(361, 349, 195, 1, 11200.00, '[{\"option_id\":7,\"option_name\":\"Choice of Type for Mala Xiang Guo\",\"value_id\":8,\"value_name\":\"Salad\",\"price_modifier\":\"0.00\"},{\"option_id\":8,\"option_name\":\"Choice of Taste\",\"value_id\":10,\"value_name\":\"Regular Spicy and Numbing Tingle \",\"price_modifier\":\"0.00\"},{\"option_id\":9,\"option_name\":\"Choice of Add Ons Meat Slice for Mala Xiang Guo\",\"value_id\":13,\"value_name\":\"Beef\",\"price_modifier\":\"5700.00\"},{\"option_id\":10,\"option_name\":\"Choice of Meat Ball for Mala Xiang Guo\",\"value_id\":17,\"value_name\":\"Pork Mushroom 3 Balls\",\"price_modifier\":\"3900.00\"},{\"option_id\":11,\"option_name\":\"Choice of Vegetables for Mala Xiang Guo\",\"value_id\":22,\"value_name\":\"Water Grass\",\"price_modifier\":\"1600.00\"}]'),
(362, 350, 195, 1, 11200.00, '[{\"option_name\":\"Choice of Type for Mala Xiang Guo\",\"value_name\":\"Salad\",\"extra_price\":0},{\"option_name\":\"Choice of Taste\",\"value_name\":\"No Spicy and No Numbing Tingle\",\"extra_price\":0},{\"option_name\":\"Choice of Add Ons Meat Slice for Mala Xiang Guo\",\"value_name\":\"Beef\",\"extra_price\":0},{\"option_name\":\"Choice of Meat Ball for Mala Xiang Guo\",\"value_name\":\"Chicken Mushroom 3 Balls\",\"extra_price\":0},{\"option_name\":\"Choice of Vegetables for Mala Xiang Guo\",\"value_name\":\"Water Grass\",\"extra_price\":0}]'),
(363, 351, 195, 1, 11400.00, '[{\"option_name\":\"Choice of Type for Mala Xiang Guo\",\"value_name\":\"Soup\",\"extra_price\":0},{\"option_name\":\"Choice of Taste\",\"value_name\":\"More Spicy and Numbing Tingle\",\"extra_price\":0},{\"option_name\":\"Choice of Add Ons Meat Slice for Mala Xiang Guo\",\"value_name\":\"Pork\",\"extra_price\":0},{\"option_name\":\"Choice of Meat Ball for Mala Xiang Guo\",\"value_name\":\"Chicken Mushroom 3 Balls\",\"extra_price\":0},{\"option_name\":\"Choice of Vegetables for Mala Xiang Guo\",\"value_name\":\"Lotus Root\",\"extra_price\":0}]'),
(364, 352, 195, 1, 11400.00, '[{\"option_name\":\"Choice of Type for Mala Xiang Guo\",\"value_name\":\"Salad\",\"extra_price\":0,\"price_modifier\":0},{\"option_name\":\"Choice of Taste\",\"value_name\":\"No Spicy and No Numbing Tingle\",\"extra_price\":0,\"price_modifier\":0},{\"option_name\":\"Choice of Add Ons Meat Slice for Mala Xiang Guo\",\"value_name\":\"Chicken\",\"extra_price\":5700,\"price_modifier\":5700},{\"option_name\":\"Choice of Meat Ball for Mala Xiang Guo\",\"value_name\":\"Pork Mushroom 3 Balls\",\"extra_price\":3900,\"price_modifier\":3900},{\"option_name\":\"Choice of Vegetables for Mala Xiang Guo\",\"value_name\":\"Lotus Root\",\"extra_price\":1800,\"price_modifier\":1800}]'),
(365, 353, 195, 1, 11200.00, '[{\"option_name\":\"Choice of Type for Mala Xiang Guo\",\"value_name\":\"Salad\",\"extra_price\":0,\"price_modifier\":0},{\"option_name\":\"Choice of Taste\",\"value_name\":\"No Spicy and No Numbing Tingle\",\"extra_price\":0,\"price_modifier\":0},{\"option_name\":\"Choice of Add Ons Meat Slice for Mala Xiang Guo\",\"value_name\":\"Chicken\",\"extra_price\":5700,\"price_modifier\":5700},{\"option_name\":\"Choice of Meat Ball for Mala Xiang Guo\",\"value_name\":\"Pork Mushroom 3 Balls\",\"extra_price\":3900,\"price_modifier\":3900},{\"option_name\":\"Choice of Vegetables for Mala Xiang Guo\",\"value_name\":\"Water Grass\",\"extra_price\":1600,\"price_modifier\":1600}]'),
(366, 354, 78, 1, 14500.00, NULL),
(367, 355, 195, 1, 11200.00, '[{\"option_name\":\"Choice of Type for Mala Xiang Guo\",\"value_name\":\"Soup\",\"extra_price\":0,\"price_modifier\":0},{\"option_name\":\"Choice of Taste\",\"value_name\":\"No Spicy and No Numbing Tingle\",\"extra_price\":0,\"price_modifier\":0},{\"option_name\":\"Choice of Add Ons Meat Slice for Mala Xiang Guo\",\"value_name\":\"Chicken\",\"extra_price\":5700,\"price_modifier\":5700},{\"option_name\":\"Choice of Meat Ball for Mala Xiang Guo\",\"value_name\":\"Chicken Mushroom 3 Balls\",\"extra_price\":3900,\"price_modifier\":3900},{\"option_name\":\"Choice of Vegetables for Mala Xiang Guo\",\"value_name\":\"Water Grass\",\"extra_price\":1600,\"price_modifier\":1600}]'),
(368, 356, 195, 1, 11050.00, '[{\"option_name\":\"Choice of Type for Mala Xiang Guo\",\"value_name\":\"Salad\",\"extra_price\":0,\"price_modifier\":0},{\"option_name\":\"Choice of Taste\",\"value_name\":\"More Spicy and Numbing Tingle\",\"extra_price\":0,\"price_modifier\":0},{\"option_name\":\"Choice of Add Ons Meat Slice for Mala Xiang Guo\",\"value_name\":\"Chicken\",\"extra_price\":5700,\"price_modifier\":5700},{\"option_name\":\"Choice of Meat Ball for Mala Xiang Guo\",\"value_name\":\"Pork Mushroom 3 Balls\",\"extra_price\":3900,\"price_modifier\":3900},{\"option_name\":\"Choice of Vegetables for Mala Xiang Guo\",\"value_name\":\"Carrot\",\"extra_price\":1450,\"price_modifier\":1450}]'),
(369, 357, 195, 1, 11300.00, '[{\"option_name\":\"Choice of Type for Mala Xiang Guo\",\"value_name\":\"Salad\",\"extra_price\":0,\"price_modifier\":0},{\"option_name\":\"Choice of Taste\",\"value_name\":\"More Spicy and Numbing Tingle\",\"extra_price\":0,\"price_modifier\":0},{\"option_name\":\"Choice of Add Ons Meat Slice for Mala Xiang Guo\",\"value_name\":\"Beef\",\"extra_price\":5700,\"price_modifier\":5700},{\"option_name\":\"Choice of Meat Ball for Mala Xiang Guo\",\"value_name\":\"Pork Mushroom 3 Balls\",\"extra_price\":3900,\"price_modifier\":3900},{\"option_name\":\"Choice of Vegetables for Mala Xiang Guo\",\"value_name\":\"Taiwan Mon Nhyinn\",\"extra_price\":1700,\"price_modifier\":1700}]'),
(370, 358, 194, 1, 15600.00, NULL),
(371, 359, 194, 1, 15600.00, NULL),
(372, 360, 194, 1, 15600.00, NULL),
(373, 361, 220, 1, 14700.00, NULL),
(374, 362, 195, 1, 11050.00, '[{\"option_name\":\"Choice of Type for Mala Xiang Guo\",\"value_name\":\"Salad\",\"extra_price\":0,\"price_modifier\":0},{\"option_name\":\"Choice of Taste\",\"value_name\":\"No Spicy and No Numbing Tingle\",\"extra_price\":0,\"price_modifier\":0},{\"option_name\":\"Choice of Add Ons Meat Slice for Mala Xiang Guo\",\"value_name\":\"Chicken\",\"extra_price\":5700,\"price_modifier\":5700},{\"option_name\":\"Choice of Meat Ball for Mala Xiang Guo\",\"value_name\":\"Pork Mushroom 3 Balls\",\"extra_price\":3900,\"price_modifier\":3900},{\"option_name\":\"Choice of Vegetables for Mala Xiang Guo\",\"value_name\":\"Mon Nhyinn Phyu\",\"extra_price\":1450,\"price_modifier\":1450}]'),
(375, 363, 194, 1, 15600.00, NULL),
(376, 364, 194, 1, 15600.00, NULL),
(377, 364, 195, 1, 12500.00, '[{\"option_name\":\"Choice of Type for Mala Xiang Guo\",\"value_name\":\"Soup\",\"extra_price\":0,\"price_modifier\":0},{\"option_name\":\"Choice of Taste\",\"value_name\":\"No Spicy and No Numbing Tingle\",\"extra_price\":0,\"price_modifier\":0},{\"option_name\":\"Choice of Add Ons Meat Slice for Mala Xiang Guo\",\"value_name\":\"Chicken\",\"extra_price\":5700,\"price_modifier\":5700},{\"option_name\":\"Choice of Meat Ball for Mala Xiang Guo\",\"value_name\":\"Chicken Mushroom 3 Balls\",\"extra_price\":3900,\"price_modifier\":3900},{\"option_name\":\"Choice of Vegetables for Mala Xiang Guo\",\"value_name\":\"Mon Nhyinn Phyu\",\"extra_price\":1450,\"price_modifier\":1450},{\"option_name\":\"Choice of Vegetables for Mala Xiang Guo\",\"value_name\":\"Taro\",\"extra_price\":1450,\"price_modifier\":1450}]'),
(378, 364, 217, 5, 13000.00, NULL),
(379, 365, 195, 1, 12750.00, '[{\"option_name\":\"Choice of Type for Mala Xiang Guo\",\"value_name\":\"Soup\",\"extra_price\":0,\"price_modifier\":0},{\"option_name\":\"Choice of Taste\",\"value_name\":\"No Spicy and No Numbing Tingle\",\"extra_price\":0,\"price_modifier\":0},{\"option_name\":\"Choice of Add Ons Meat Slice for Mala Xiang Guo\",\"value_name\":\"Beef\",\"extra_price\":5700,\"price_modifier\":5700},{\"option_name\":\"Choice of Meat Ball for Mala Xiang Guo\",\"value_name\":\"Chicken Mushroom 3 Balls\",\"extra_price\":3900,\"price_modifier\":3900},{\"option_name\":\"Choice of Vegetables for Mala Xiang Guo\",\"value_name\":\"Taiwan Mon Nhyinn\",\"extra_price\":1700,\"price_modifier\":1700},{\"option_name\":\"Choice of Vegetables for Mala Xiang Guo\",\"value_name\":\"Carrot\",\"extra_price\":1450,\"price_modifier\":1450}]'),
(380, 366, 194, 1, 15600.00, NULL),
(381, 367, 14, 2, 4400.00, NULL),
(382, 368, 194, 1, 15600.00, NULL),
(383, 369, 49, 1, 3000.00, '[{\"option_name\":\"Sugar Level\",\"value_name\":\"Medium\",\"extra_price\":0,\"price_modifier\":0}]'),
(384, 370, 195, 1, 11050.00, '[{\"option_name\":\"Choice of Type for Mala Xiang Guo\",\"value_name\":\"Salad\",\"extra_price\":0,\"price_modifier\":0},{\"option_name\":\"Choice of Taste\",\"value_name\":\"No Spicy and No Numbing Tingle\",\"extra_price\":0,\"price_modifier\":0},{\"option_name\":\"Choice of Add Ons Meat Slice for Mala Xiang Guo\",\"value_name\":\"Chicken\",\"extra_price\":5700,\"price_modifier\":5700},{\"option_name\":\"Choice of Meat Ball for Mala Xiang Guo\",\"value_name\":\"Chicken Mushroom 3 Balls\",\"extra_price\":3900,\"price_modifier\":3900},{\"option_name\":\"Choice of Vegetables for Mala Xiang Guo\",\"value_name\":\"Mon Nhyinn Phyu\",\"extra_price\":1450,\"price_modifier\":1450}]'),
(385, 371, 195, 1, 11050.00, '[{\"option_name\":\"Choice of Type for Mala Xiang Guo\",\"value_name\":\"Soup\",\"extra_price\":0,\"price_modifier\":0},{\"option_name\":\"Choice of Taste\",\"value_name\":\"No Spicy and No Numbing Tingle\",\"extra_price\":0,\"price_modifier\":0},{\"option_name\":\"Choice of Add Ons Meat Slice for Mala Xiang Guo\",\"value_name\":\"Chicken\",\"extra_price\":5700,\"price_modifier\":5700},{\"option_name\":\"Choice of Meat Ball for Mala Xiang Guo\",\"value_name\":\"Chicken Mushroom 3 Balls\",\"extra_price\":3900,\"price_modifier\":3900},{\"option_name\":\"Choice of Vegetables for Mala Xiang Guo\",\"value_name\":\"Mon Nhyinn Phyu\",\"extra_price\":1450,\"price_modifier\":1450}]'),
(386, 372, 195, 1, 11050.00, '[{\"option_name\":\"Choice of Type for Mala Xiang Guo\",\"value_name\":\"Soup\",\"extra_price\":0,\"price_modifier\":0},{\"option_name\":\"Choice of Taste\",\"value_name\":\"No Spicy and No Numbing Tingle\",\"extra_price\":0,\"price_modifier\":0},{\"option_name\":\"Choice of Add Ons Meat Slice for Mala Xiang Guo\",\"value_name\":\"Chicken\",\"extra_price\":5700,\"price_modifier\":5700},{\"option_name\":\"Choice of Meat Ball for Mala Xiang Guo\",\"value_name\":\"Chicken Mushroom 3 Balls\",\"extra_price\":3900,\"price_modifier\":3900},{\"option_name\":\"Choice of Vegetables for Mala Xiang Guo\",\"value_name\":\"Mon Nhyinn Phyu\",\"extra_price\":1450,\"price_modifier\":1450}]'),
(387, 373, 195, 1, 11050.00, '[{\"option_name\":\"Choice of Type for Mala Xiang Guo\",\"value_name\":\"Soup\",\"extra_price\":0,\"price_modifier\":0},{\"option_name\":\"Choice of Taste\",\"value_name\":\"Regular Spicy and Numbing Tingle \",\"extra_price\":0,\"price_modifier\":0},{\"option_name\":\"Choice of Add Ons Meat Slice for Mala Xiang Guo\",\"value_name\":\"Chicken\",\"extra_price\":5700,\"price_modifier\":5700},{\"option_name\":\"Choice of Meat Ball for Mala Xiang Guo\",\"value_name\":\"Chicken Mushroom 3 Balls\",\"extra_price\":3900,\"price_modifier\":3900},{\"option_name\":\"Choice of Vegetables for Mala Xiang Guo\",\"value_name\":\"Mon Nhyinn Phyu\",\"extra_price\":1450,\"price_modifier\":1450}]'),
(388, 374, 82, 1, 10500.00, '[{\"option_name\":\"Meat Type\",\"value_name\":\"Pork\",\"extra_price\":0,\"price_modifier\":0},{\"option_name\":\"Addons\",\"value_name\":\"Extra Meat Ball (Chicken)\",\"extra_price\":2500,\"price_modifier\":2500}]'),
(389, 375, 194, 1, 15600.00, NULL),
(390, 376, 14, 2, 4400.00, NULL),
(391, 377, 14, 1, 4400.00, NULL),
(392, 378, 194, 1, 15600.00, NULL),
(393, 379, 195, 1, 11050.00, '[{\"option_name\":\"Choice of Type for Mala Xiang Guo\",\"value_name\":\"Soup\",\"extra_price\":0,\"price_modifier\":0},{\"option_name\":\"Choice of Taste\",\"value_name\":\"No Spicy and No Numbing Tingle\",\"extra_price\":0,\"price_modifier\":0},{\"option_name\":\"Choice of Add Ons Meat Slice for Mala Xiang Guo\",\"value_name\":\"Pork\",\"extra_price\":5700,\"price_modifier\":5700},{\"option_name\":\"Choice of Meat Ball for Mala Xiang Guo\",\"value_name\":\"Chicken Mushroom 3 Balls\",\"extra_price\":3900,\"price_modifier\":3900},{\"option_name\":\"Choice of Vegetables for Mala Xiang Guo\",\"value_name\":\"Corn\",\"extra_price\":1450,\"price_modifier\":1450}]'),
(394, 379, 217, 1, 13000.00, NULL),
(395, 379, 218, 1, 19300.00, '[{\"option_name\":\"Choice of Rice and Noodles for Set\",\"value_name\":\"Instant Noodles\",\"extra_price\":0,\"price_modifier\":0},{\"option_name\":\"Choice of Soup For Set\",\"value_name\":\"Tom Yum\",\"extra_price\":0,\"price_modifier\":0},{\"option_name\":\"Choice of Vegetables for Set\",\"value_name\":\"Chinese Cabbage \",\"extra_price\":1500,\"price_modifier\":1500},{\"option_name\":\"Choice of Meats for Set\",\"value_name\":\"Chicken Liver and Gizzard\",\"extra_price\":1800,\"price_modifier\":1800}]'),
(396, 380, 194, 1, 15600.00, NULL),
(397, 381, 195, 1, 11050.00, '[{\"option_name\":\"Choice of Type for Mala Xiang Guo\",\"value_name\":\"Soup\",\"extra_price\":0,\"price_modifier\":0},{\"option_name\":\"Choice of Taste\",\"value_name\":\"No Spicy and No Numbing Tingle\",\"extra_price\":0,\"price_modifier\":0},{\"option_name\":\"Choice of Add Ons Meat Slice for Mala Xiang Guo\",\"value_name\":\"Chicken\",\"extra_price\":5700,\"price_modifier\":5700},{\"option_name\":\"Choice of Meat Ball for Mala Xiang Guo\",\"value_name\":\"Pork Mushroom 3 Balls\",\"extra_price\":3900,\"price_modifier\":3900},{\"option_name\":\"Choice of Vegetables for Mala Xiang Guo\",\"value_name\":\"Mon Nhyinn Phyu\",\"extra_price\":1450,\"price_modifier\":1450}]'),
(398, 382, 194, 1, 15600.00, NULL),
(399, 383, 195, 1, 11050.00, '[{\"option_name\":\"Choice of Type for Mala Xiang Guo\",\"value_name\":\"Soup\",\"extra_price\":0,\"price_modifier\":0},{\"option_name\":\"Choice of Taste\",\"value_name\":\"No Spicy and No Numbing Tingle\",\"extra_price\":0,\"price_modifier\":0},{\"option_name\":\"Choice of Add Ons Meat Slice for Mala Xiang Guo\",\"value_name\":\"Chicken\",\"extra_price\":5700,\"price_modifier\":5700},{\"option_name\":\"Choice of Meat Ball for Mala Xiang Guo\",\"value_name\":\"Chicken Mushroom 3 Balls\",\"extra_price\":3900,\"price_modifier\":3900},{\"option_name\":\"Choice of Vegetables for Mala Xiang Guo\",\"value_name\":\"Mon Nhyinn Phyu\",\"extra_price\":1450,\"price_modifier\":1450}]'),
(400, 384, 4, 2, 8200.00, NULL),
(401, 384, 14, 3, 4400.00, NULL),
(402, 384, 77, 1, 16200.00, NULL),
(403, 384, 80, 1, 16500.00, NULL),
(404, 385, 195, 1, 11050.00, '[{\"option_name\":\"Choice of Type for Mala Xiang Guo\",\"value_name\":\"Soup\",\"extra_price\":0,\"price_modifier\":0},{\"option_name\":\"Choice of Taste\",\"value_name\":\"More Spicy and Numbing Tingle\",\"extra_price\":0,\"price_modifier\":0},{\"option_name\":\"Choice of Add Ons Meat Slice for Mala Xiang Guo\",\"value_name\":\"Chicken\",\"extra_price\":5700,\"price_modifier\":5700},{\"option_name\":\"Choice of Meat Ball for Mala Xiang Guo\",\"value_name\":\"Chicken Mushroom 3 Balls\",\"extra_price\":3900,\"price_modifier\":3900},{\"option_name\":\"Choice of Vegetables for Mala Xiang Guo\",\"value_name\":\"Mon Nhyinn Phyu\",\"extra_price\":1450,\"price_modifier\":1450}]'),
(405, 386, 50, 1, 6800.00, NULL),
(406, 387, 163, 1, 3000.00, NULL),
(407, 387, 165, 1, 6800.00, NULL),
(408, 387, 49, 1, 3000.00, '[{\"option_name\":\"Sugar Level\",\"value_name\":\"High\",\"extra_price\":0,\"price_modifier\":0}]'),
(409, 388, 194, 1, 15600.00, NULL),
(410, 389, 14, 1, 4400.00, NULL),
(411, 390, 194, 1, 15600.00, NULL),
(412, 391, 194, 1, 15600.00, NULL),
(413, 391, 195, 1, 11050.00, '[{\"option_name\":\"Choice of Type for Mala Xiang Guo\",\"value_name\":\"Soup\",\"extra_price\":0,\"price_modifier\":0},{\"option_name\":\"Choice of Taste\",\"value_name\":\"No Spicy and No Numbing Tingle\",\"extra_price\":0,\"price_modifier\":0},{\"option_name\":\"Choice of Add Ons Meat Slice for Mala Xiang Guo\",\"value_name\":\"Chicken\",\"extra_price\":5700,\"price_modifier\":5700},{\"option_name\":\"Choice of Meat Ball for Mala Xiang Guo\",\"value_name\":\"Chicken Mushroom 3 Balls\",\"extra_price\":3900,\"price_modifier\":3900},{\"option_name\":\"Choice of Vegetables for Mala Xiang Guo\",\"value_name\":\"Mon Nhyinn Phyu\",\"extra_price\":1450,\"price_modifier\":1450}]'),
(414, 392, 194, 1, 15600.00, NULL),
(415, 393, 194, 1, 15600.00, NULL),
(416, 394, 194, 1, 15600.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `request_notifications`
--

CREATE TABLE `request_notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` varchar(255) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `request_notifications`
--

INSERT INTO `request_notifications` (`notification_id`, `user_id`, `title`, `message`, `is_read`, `created_at`) VALUES
(1, 4, 'New Rider Request', '{\"new_user_id\":\"48\"}', 1, '2025-09-23 07:24:19'),
(2, 4, 'New Vendor Request', '{\"new_user_id\":\"49\"}', 1, '2025-09-23 07:25:10'),
(3, 4, 'New Rider Request', '{\"new_user_id\":\"53\"}', 1, '2025-10-05 14:55:47'),
(4, 4, 'New Rider Request', '{\"new_user_id\":\"56\"}', 1, '2025-10-08 07:46:30'),
(5, 4, 'New Vendor Request', '{\"new_user_id\":\"57\"}', 1, '2025-10-08 08:19:42'),
(6, 4, 'New Vendor Request', '{\"new_user_id\":\"74\"}', 1, '2025-11-22 07:02:44'),
(7, 4, 'New Vendor Request', '{\"new_user_id\":\"75\"}', 1, '2025-11-22 09:02:15'),
(8, 4, 'Settlement Rejected', 'Restaurant \'JO JO Hot Pot & Mala Xiang Guo (Thaketa)\' rejected settlement of 37,128.75 MMK for Week 202539. Reason: aam', 1, '2025-11-24 07:45:45'),
(9, 4, 'Settlement Rejected', 'Restaurant \'JO JO Hot Pot & Mala Xiang Guo (Thaketa)\' rejected settlement of 12,225.00 MMK for Week 202540. Reason: Testing', 1, '2025-11-24 07:47:57'),
(10, 4, 'Settlement Rejected', 'Restaurant \'KFC (Capital Hypermarket)\' rejected settlement of 5.00 MMK for Week 0. Reason: INVALID', 1, '2025-11-24 08:42:34'),
(11, 4, 'New Vendor Request', '{\"new_user_id\":\"78\"}', 1, '2025-11-24 09:17:11'),
(12, 4, 'New Vendor Request', '{\"new_user_id\":\"79\"}', 1, '2025-11-25 06:53:08'),
(13, 4, 'New Vendor Request', '{\"new_user_id\":\"80\"}', 1, '2025-11-25 06:54:00'),
(14, 4, 'New Vendor Request', '{\"new_user_id\":\"82\"}', 1, '2025-11-25 07:06:18'),
(15, 4, 'Settlement Rejected', 'Restaurant \'JO JO Hot Pot & Mala Xiang Guo (Thaketa)\' rejected settlement of 35,100.00 MMK for Week 202544. Reason: I can\'t ', 1, '2025-11-30 12:19:48'),
(16, 4, 'New Vendor Request', '{\"new_user_id\":\"87\"}', 1, '2025-12-08 06:33:43'),
(17, 4, 'New Vendor Request', '{\"new_user_id\":\"88\"}', 1, '2025-12-08 08:57:30'),
(18, 10, 'New Payout Available', 'A payout of 3,579.00 MMK has been initiated for period 2025-12-12 to 2025-12-12. Please review and confirm.', 0, '2025-12-13 03:59:14'),
(19, 4, 'Payout Confirmed by Rider', 'Rider me confirmed payout of 30,000.00 MMK.', 1, '2025-12-13 04:00:59'),
(20, 13, 'Payout Confirmed by Rider', 'Rider me confirmed payout of 30,000.00 MMK.', 0, '2025-12-13 04:00:59'),
(21, 4, 'Payout Confirmed by Rider', 'Rider me confirmed payout of 3,579.00 MMK.', 1, '2025-12-13 04:01:07'),
(22, 13, 'Payout Confirmed by Rider', 'Rider me confirmed payout of 3,579.00 MMK.', 0, '2025-12-13 04:01:07'),
(23, 10, 'New Payout Available', 'A payout of 700.00 MMK has been initiated for period 2025-12-04 to 2025-12-04. Please review and confirm.', 0, '2025-12-13 04:41:09'),
(24, 10, 'New Payout Available', 'A payout of 700.00 MMK has been initiated for period 2025-12-03 to 2025-12-03. Please review and confirm.', 0, '2025-12-13 04:41:09'),
(25, 10, 'New Payout Available', 'A payout of 4,099.00 MMK has been initiated for period 2025-11-30 to 2025-11-30. Please review and confirm.', 0, '2025-12-13 04:41:09'),
(26, 10, 'New Payout Available', 'A payout of 2,800.00 MMK has been initiated for period 2025-11-29 to 2025-11-29. Please review and confirm.', 0, '2025-12-13 04:41:09'),
(27, 10, 'New Payout Available', 'A payout of 3,764.00 MMK has been initiated for period 2025-11-27 to 2025-11-27. Please review and confirm.', 0, '2025-12-13 04:41:09'),
(28, 10, 'New Payout Available', 'A payout of 8,812.00 MMK has been initiated for period 2025-11-26 to 2025-11-26. Please review and confirm.', 0, '2025-12-13 04:41:09'),
(29, 10, 'New Payout Available', 'A payout of 6,917.00 MMK has been initiated for period 2025-11-24 to 2025-11-24. Please review and confirm.', 0, '2025-12-13 04:41:09'),
(30, 9, 'New Payout Available', 'A payout of 700.00 MMK has been initiated for period 2025-11-23 to 2025-11-23. Please review and confirm.', 0, '2025-12-13 04:41:09'),
(31, 10, 'New Payout Available', 'A payout of 7,000.00 MMK has been initiated for period 2025-11-23 to 2025-11-23. Please review and confirm.', 0, '2025-12-13 04:41:09'),
(32, 10, 'New Payout Available', 'A payout of 3,965.00 MMK has been initiated for period 2025-11-22 to 2025-11-22. Please review and confirm.', 0, '2025-12-13 04:41:09'),
(33, 10, 'New Payout Available', 'A payout of 1,400.00 MMK has been initiated for period 2025-11-20 to 2025-11-20. Please review and confirm.', 0, '2025-12-13 04:41:09'),
(34, 10, 'New Payout Available', 'A payout of 700.00 MMK has been initiated for period 2025-11-19 to 2025-11-19. Please review and confirm.', 0, '2025-12-13 04:41:09'),
(35, 10, 'New Payout Available', 'A payout of 4,200.00 MMK has been initiated for period 2025-11-18 to 2025-11-18. Please review and confirm.', 0, '2025-12-13 04:41:09'),
(36, 10, 'New Payout Available', 'A payout of 700.00 MMK has been initiated for period 2025-11-17 to 2025-11-17. Please review and confirm.', 0, '2025-12-13 04:41:09'),
(37, 4, 'Payout Confirmed by Rider', 'Rider me confirmed payout of 2,800.00 MMK.', 1, '2025-12-13 04:41:39'),
(38, 13, 'Payout Confirmed by Rider', 'Rider me confirmed payout of 2,800.00 MMK.', 0, '2025-12-13 04:41:39'),
(39, 4, 'Payout Confirmed by Rider', 'Rider me confirmed payout of 7,000.00 MMK.', 1, '2025-12-13 04:41:52'),
(40, 13, 'Payout Confirmed by Rider', 'Rider me confirmed payout of 7,000.00 MMK.', 0, '2025-12-13 04:41:52'),
(41, 4, 'Payout Confirmed by Rider', 'Rider me confirmed payout of 700.00 MMK.', 1, '2025-12-13 04:41:55'),
(42, 13, 'Payout Confirmed by Rider', 'Rider me confirmed payout of 700.00 MMK.', 0, '2025-12-13 04:41:55'),
(43, 4, 'Rider Rejected Payout', 'Rider me rejected payout of 700.00 MMK. Reason: error', 1, '2025-12-13 04:42:17'),
(44, 13, 'Rider Rejected Payout', 'Rider me rejected payout of 700.00 MMK. Reason: error', 0, '2025-12-13 04:42:17'),
(45, 4, 'Rider Rejected Payout', 'Rider me rejected payout of 700.00 MMK. Reason: i don\'t get it', 1, '2025-12-13 04:43:39'),
(46, 13, 'Rider Rejected Payout', 'Rider me rejected payout of 700.00 MMK. Reason: i don\'t get it', 0, '2025-12-13 04:43:39'),
(47, 10, 'Payout Resent for Review', 'Your rejected payout of 700.00 MMK has been resent for period 2025-12-03 to 2025-12-03. Please review and confirm.', 0, '2025-12-13 04:44:43'),
(48, 4, 'Payout Confirmed by Rider', 'Rider me confirmed payout of 700.00 MMK.', 1, '2025-12-13 04:45:09'),
(49, 13, 'Payout Confirmed by Rider', 'Rider me confirmed payout of 700.00 MMK.', 0, '2025-12-13 04:45:09'),
(50, 10, 'Payout Resent for Review', 'Your rejected payout of 700.00 MMK has been resent for period 2025-12-04 to 2025-12-04. Please review and confirm.', 0, '2025-12-13 04:45:38'),
(51, 4, 'Payout Confirmed by Rider', 'Rider me confirmed payout of 700.00 MMK.', 1, '2025-12-13 04:48:32'),
(52, 13, 'Payout Confirmed by Rider', 'Rider me confirmed payout of 700.00 MMK.', 0, '2025-12-13 04:48:32'),
(53, 4, 'Payout Confirmed by Rider', 'Rider me confirmed payout of 4,200.00 MMK.', 1, '2025-12-13 04:48:45'),
(54, 13, 'Payout Confirmed by Rider', 'Rider me confirmed payout of 4,200.00 MMK.', 0, '2025-12-13 04:48:45'),
(55, 4, 'Payout Confirmed by Rider', 'Rider me confirmed payout of 700.00 MMK.', 1, '2025-12-13 04:48:48'),
(56, 13, 'Payout Confirmed by Rider', 'Rider me confirmed payout of 700.00 MMK.', 0, '2025-12-13 04:48:48'),
(57, 4, 'Payout Confirmed by Rider', 'Rider me confirmed payout of 1,400.00 MMK.', 1, '2025-12-13 04:48:59'),
(58, 13, 'Payout Confirmed by Rider', 'Rider me confirmed payout of 1,400.00 MMK.', 0, '2025-12-13 04:48:59'),
(59, 4, 'Rider Rejected Payout', 'Rider me rejected payout of 4,099.00 MMK. Reason: NOt', 1, '2025-12-13 05:12:27'),
(60, 13, 'Rider Rejected Payout', 'Rider me rejected payout of 4,099.00 MMK. Reason: NOt', 0, '2025-12-13 05:12:27'),
(61, 10, 'Payout Resent for Review', 'Your rejected payout of 4,099.00 MMK has been resent for period 2025-11-30 to 2025-11-30. Please review and confirm.', 0, '2025-12-13 05:13:03'),
(62, 4, 'Payout Confirmed by Rider', 'Rider me confirmed payout of 4,099.00 MMK.', 1, '2025-12-13 05:13:16'),
(63, 13, 'Payout Confirmed by Rider', 'Rider me confirmed payout of 4,099.00 MMK.', 0, '2025-12-13 05:13:16'),
(64, 4, 'Payout Confirmed by Rider', 'Rider Kyaw Gyi confirmed payout of 3,764.00 MMK.', 1, '2025-12-13 05:27:08'),
(65, 13, 'Payout Confirmed by Rider', 'Rider Kyaw Gyi confirmed payout of 3,764.00 MMK.', 0, '2025-12-13 05:27:08'),
(66, 4, 'Payout Confirmed by Rider', 'Rider Kyaw Gyi confirmed payout of 3,965.00 MMK.', 1, '2025-12-13 05:27:12'),
(67, 13, 'Payout Confirmed by Rider', 'Rider Kyaw Gyi confirmed payout of 3,965.00 MMK.', 0, '2025-12-13 05:27:12'),
(68, 4, 'Payout Confirmed by Rider', 'Rider Kyaw Gyi confirmed payout of 8,812.00 MMK.', 1, '2025-12-13 05:27:15'),
(69, 13, 'Payout Confirmed by Rider', 'Rider Kyaw Gyi confirmed payout of 8,812.00 MMK.', 0, '2025-12-13 05:27:15'),
(70, 4, 'Payout Confirmed by Rider', 'Rider Kyaw Gyi confirmed payout of 6,917.00 MMK.', 1, '2025-12-13 05:27:19'),
(71, 13, 'Payout Confirmed by Rider', 'Rider Kyaw Gyi confirmed payout of 6,917.00 MMK.', 0, '2025-12-13 05:27:19'),
(72, 4, 'Settlement Rejected', 'Restaurant \'JO JO Hot Pot & Mala Xiang Guo (Thaketa)\' rejected settlement of 11,700.00 MMK for Week 202550. Reason: invalid', 1, '2025-12-13 13:10:39'),
(73, 4, 'Settlement Rejected', 'Restaurant \'JO JO Hot Pot & Mala Xiang Guo (Thaketa)\' rejected settlement of 11,700.00 MMK for Week 202550. Reason: invalid', 1, '2025-12-13 13:11:16'),
(74, 4, 'Settlement Rejected', 'Restaurant \'JO JO Hot Pot & Mala Xiang Guo (Thaketa)\' rejected settlement of 11,700.00 MMK for Week 202550. Reason: invalid', 1, '2025-12-13 13:12:30'),
(77, 4, 'Partner Request', 'New restaurant registration: JO HO', 1, '2025-12-13 14:27:24'),
(78, 4, 'Partner Request', 'New restaurant registration: JO HO HO', 1, '2025-12-13 14:36:51'),
(79, 4, 'Partner Request', 'New restaurant registration: Foodnme', 1, '2025-12-14 06:48:03'),
(80, 10, 'New Payout Available', 'A payout of 7,858.00 MMK has been initiated for period 2025-12-08 to 2025-12-14. Please review and confirm.', 0, '2025-12-14 12:29:23');

-- --------------------------------------------------------

--
-- Table structure for table `restaurants`
--

CREATE TABLE `restaurants` (
  `restaurant_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `lat` double DEFAULT NULL,
  `lng` double DEFAULT NULL,
  `status` enum('active','inactive','closed') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `cuisine_type` varchar(100) DEFAULT NULL,
  `preparation_time` int(11) DEFAULT 15
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `restaurants`
--

INSERT INTO `restaurants` (`restaurant_id`, `user_id`, `name`, `address`, `phone`, `logo`, `lat`, `lng`, `status`, `created_at`, `cuisine_type`, `preparation_time`) VALUES
(1, 2, 'KFC (Capital Hypermarket)', '123 Bogyoke Rd, Yangon', '09452078041', 'kfc.jpg', 16.803085974636424, 96.19561918465888, 'active', '2025-08-27 05:22:58', 'Chicken', 15),
(2, 11, 'Chapayom(Tamwe)', 'No. 25, Maggin Street, Tamwe Township, Yangon ', '0912345678', 'chapayombg.jpg', 16.805094, 96.176486, 'active', '2025-08-29 14:26:44', 'Drinks', 15),
(3, 15, 'YKKO (Dawbon Capital)', 'No. 14-E, Room (105), Ground Floor, Min Nandar Road, Township, Yangon', '0923456789', 'ykkobg.jpg', 16.80298828702236, 96.19568219665433, 'active', '2025-08-29 14:26:44', 'Kyay Oh', 15),
(4, 86, 'JO JO Hot Pot & Mala Xiang Guo (Thaketa)', 'Ayer Wun Main Rd, Yangon', '09345678903', 'jojo.jpg', 16.804598290762883, 96.19640968131333, 'active', '2025-08-29 14:26:44', 'Hot Pot', 20),
(5, 10, 'Pizza Maru Express (Capital Thaketa)', '12 Inya Rd, Yangon', '09456789012', 'pizzamaru.jpg', 16.815, 96.175, 'active', '2025-08-29 14:26:44', 'Thai', 15),
(6, 34, 'SP Bakery (Capital Hyper Market)', 'No.14 E Min Nandar Rd, Yangon', '09567890123', 'sp.jpg', 16.80501866227191, 96.19521915028481, 'active', '2025-08-29 14:26:44', 'Bakery', 15),
(10, 10, 'Shwe Chan Hot Pot', 'Junction Mawtin,4th Floor ,Lanmadaw Township,Yangon', '09791584666', 'shwechan.png', 16.77784496, 96.14187939, 'inactive', '2025-09-01 07:19:57', 'Hot Pot', 15),
(11, 73, 'Min Lan Seafood', 'Near RC-2, No.16, Corner of Parami Road and, West May Kha, Yangon', '09253042456', 'minlan.png', 16.854938, 96.13934, 'active', '2025-09-01 07:39:15', 'Seafood', 15),
(12, 10, 'Kudos Bakery', 'Ahnawyahtar Street, Pazuntaung Township, Yangon', '09422886660', 'kudos.png', 16.777304, 96.171046, 'active', '2025-09-01 07:39:15', 'Bakery', 15),
(13, 10, 'Hong Kong Paradise', 'Junction City , Level 3, Yangon,Myanmar', '09259516215', 'hkp.png', 16.7795932, 96.153488, 'active', '2025-09-01 08:48:30', 'Chinese', 15),
(14, 10, 'THAI 47-Helpin Branch', 'No.308,Ahlone Road,Dagon Township,Dagon,Myanmar', '09261619833', 'thai47.png', 16.793162, 96.140276, 'active', '2025-09-01 08:48:30', 'Thai', 15),
(15, 10, 'Eleven Corndog & Drink', ' Padonmar St,Sanchaung Township,Yangon', '09952288363', 'eleven.png', 16.8038343, 96.13307339, 'active', '2025-09-01 08:49:07', 'Corndog', 15),
(16, 27, 'Happy Chef Pizza, Burger ,Snack Food & Juice ', 'No.279,Khay Mar Thi Street,C Block,North Okkalapa Township,Yangon', '09268411670', 'happychefpizza.png', 16.898593005238666, 96.15506672364523, 'active', '2025-09-01 09:09:39', 'Pizza', 15),
(17, 10, 'Kaung Sein Ice Cream', '86,Sint Oh Dan Street,Latha Tiwnship,Yangon', '09969986056', 'kaungsein.png', 16.7766827, 96.14908055, 'active', '2025-09-01 09:10:34', 'Ice Cream', 15),
(18, 10, 'Crepe Hearts', 'No(39/41) Sein Gay Har Hleden Centre , Hledan,Kamayut Township,Yangon', '09943852238', 'crepeheart.png', 16.8265514, 96.1286118, 'active', '2025-09-01 09:10:34', 'Crepe', 15),
(19, 76, 'Swell Thai Style', 'N0-1551,Myin Taw Thar Road,near Thaketa Cinema, Yangon', '09756600181', 'swell.png', 16.793909166, 96.20303859, 'active', '2025-09-01 09:25:08', 'Thai', 15),
(20, 10, 'Kyay Oh bayin', 'No(A-3) ,U WiSarYa street,block(34),North Dagon Township,Yangon', '09887799590', 'kobyin.png', 16.86745169, 96.18053845, 'active', '2025-09-01 09:25:08', 'Kyay Oh', 15),
(21, 10, 'Kafe Bar and Lounge', 'No(9),Gabaraye Pogada ROad,Near Shwegone Daing Road,Bahan,Yangon', '09891916688', 'kafe.png', 16.8090919, 96.1538773, 'active', '2025-09-01 09:49:50', 'Italian', 15),
(22, 10, 'fu fu Hotpot & Mala Fragrant pot ', '2nd floor, Junction &8 Centre, Mayangone Township, Yangon', '09251020299', 'fufu.png', 16.865112, 96.140754, 'active', '2025-09-02 07:06:39', 'Hot Pot', 15),
(23, 82, 'Ocha Bubble Tea', '70/1 Thanthumar Rd,Yangon', '09956049384', 'ochabubble.png', 16.8500482, 96.1906739, 'active', '2025-09-02 07:06:39', 'Tea', 15),
(24, 10, 'Ocha Bubble Tea', 'Pinlon Road,North Dagon Township, Yangon', '09770725600', 'ochabubble.png', 16.8724409, 96.1956705, 'active', '2025-09-02 07:27:34', 'Tea', 15),
(25, 72, 'OMUK', '192 Kabar Aye Pagoda rd, Mayangone Township, Yangon', '09751475737', 'omuk.png', 16.827978, 96.154948, 'active', '2025-09-02 07:27:34', 'Korean', 15),
(26, 50, 'ChatraMue Myanmar', '#th floor, Myanmar Plaza', '09999888892', 'chatra.jpg', 16.8279, 96.1542, 'active', '2025-09-02 07:44:17', 'Tea', 15),
(27, 10, 'Lotteria North okkalapa', 'No 608,Thudhamma rd,North Dagon Township,Yangon', '09405600235', 'lotteria.png', 16.89632965503093, 96.15916188131573, 'active', '2025-09-02 07:53:00', 'Chicken', 15),
(28, 10, 'Htet Htet Mala Xiang Guo', 'No.180,Baho st,Nga Moe Yeik  ward,Thingangyun township,Yangon', '09782925689', 'htethtet.png', 16.842262, 96.200588, 'active', '2025-09-02 07:53:09', 'Mala Xiang Guo', 15),
(30, 10, 'Lus Spicy', 'No.(16),Ar Thaw Ka St, Kyauk Myaung Township, Yangon0', '09952949888', 'luspicy.png', 16.806581, 96.179373, 'active', '2025-09-02 08:03:09', 'Chinese', 15),
(31, 10, 'La La Tang Yangon', '3rd floor, No14-E Min Nandar Road, Dawbon Township ,Yangon', '09985388898', 'lalatang.png', 16.802427, 96.195372, 'active', '2025-09-02 08:03:09', 'Chinese', 15),
(35, 57, 'Daw Shwe', 'nohaiohfdlkahkghdakgh;ioaeshgrh', '09450099033', NULL, NULL, NULL, 'active', '2025-10-08 08:19:42', 'chinese', 15),
(36, 58, 'Nilar Biryani', 'No.8 Minnanda Road & Thidanwe Street Corner(1 Market Corner) Nweaye Block Dawbon Tsp Yangon, 11241', '09977223208', 'nilar.jpeg', 16.797567157749196, 96.19731272600717, 'active', '2025-11-05 07:10:58', 'Biryani', 15),
(37, 61, 'Pezzo(Capital Hyper market)', 'No.14 E Min Nandar Rd, Yangon', '09262003646', 'pezzo.png', 16.803586606033328, 96.19549976543709, 'active', '2025-11-05 02:08:20', 'Pizza', 15),
(38, 62, 'ChaTraMue(Capital Hyper market)', 'No.14 E Min Nandar Rd, Yangon', '0948064705', 'chatramue.webp', 16.803160288007394, 96.19533553532482, 'active', '2025-11-05 02:33:18', 'Drinks', 15),
(39, 63, 'Omuk (Capital Hyper market)', 'No.14 E Min Nandar Rd, Yangon', '09251020848', 'omuk.png', 16.803160288007394, 96.19533553532482, 'active', '2025-11-05 02:33:18', 'KoreanFood', 15),
(40, 64, 'Shwe Pu Zun', 'No.14/A Min Nandar Rd, Yangon', '09251084705', 'shwepuzun.jpg', 16.80496163575195, 96.19431555651848, 'active', '2025-11-05 07:33:33', 'Bakery', 15),
(41, 65, 'FUDO Bakery', 'No.7,Nwe Aye Quarter,Minnandar Streer,Daw Bon Tsp,Yangon, 11241', '09985533519', 'fudo.png', 16.797129303340704, 96.19550226837538, 'active', '2025-11-05 07:33:33', 'Bakery', 15),
(42, 66, 'တော်ဝင်မြေအိုးဒံပေါက်', 'no 283 Min Nandar Rd, Yangon', '09443001572', 'tawwin.jpg', 16.800995588867067, 96.19683662234182, 'active', '2025-11-05 08:08:01', 'Biryani', 15),
(43, 67, 'Da Nu Phyu Daw Saw Yee', 'R53X+F9P, Ayer Wun Main Rd, Yangon', '09408308800', 'danuphyu.jpeg', 16.803966151897036, 96.19840025117803, 'active', '2025-11-05 08:08:01', 'Burmese Food', 15),
(44, 68, 'Shwe Kan Kaw Tea Shop', 'Q5QV+CX7, Thumana Rd, Yangon', '09427414782', 'skk.png', 16.78950892882494, 96.19492792506085, 'active', '2025-11-05 09:58:16', 'Burmese Food', 15),
(45, 69, 'MOMOYO (Kyauk Myaung)', 'Corner of Kyar Kwet Thit R53H+PJ9 Road, and Baya Thotedi Street, Yangon', '09688159984', 'momoyo.jpg', 16.804444432234302, 96.17930598557312, 'active', '2025-11-05 22:24:12', 'Drinks', 15),
(46, 60, 'Potao Corner(Capital Dawbon)', 'No.14 E Min Nandar Rd, Yangon', '09960079611', 'potatocorner.jpg', 16.803586606033328, 96.19549976543709, 'active', '2025-11-05 02:08:20', 'Fried Potato', 15),
(47, 59, 'Tealive', 'R5HQ+377, Lay Daungkan Rd, Yangon', '09458712226', 'tealive.jpg', 16.82848051590265, 96.18790984135752, 'active', '2025-11-05 02:09:43', 'Drinks', 15),
(48, 59, 'Bread Talk', '14/E Min Nandar Rd, Yangon', '09255114849', 'breadtalk.jpg', 16.803729691055384, 96.19581923208248, 'active', '2025-11-05 02:17:55', 'Bakery', 15),
(49, 59, 'Bonchon', 'R54G+3C7, Yangon', '09799174424', 'bonchon.jpg', 16.806533577453585, 96.17620468103317, 'active', '2025-11-05 02:25:31', 'Fried Chicken', 15),
(50, 59, 'Krispy Kreme', 'R53W+48F, Yangon', '09448188833', 'krispykreme.jpg', 16.803662705476963, 96.19557874633455, 'active', '2025-11-05 02:29:29', 'Donuts', 15),
(51, 59, 'Seasons', 'The City Mart, Ayer Wun Main Rd, Yangon', '09678749165', 'seasons.jpg', 16.804271873803014, 96.2102945990353, 'active', '2025-11-05 02:49:16', 'Bakery', 15),
(52, 59, 'J\'Donut', 'No(1/19) Junction of Min Nandar and Ayarwon road, 1 Quarter, Thakayta Tsp, Yangon', '09425224774', 'jdonuts.jpg', 16.80461310977951, 96.19572674262417, 'active', '2025-11-05 02:49:16', 'Donuts', 15),
(54, 74, 'Daw Shwe', '', '09262008900', NULL, NULL, NULL, 'active', '2025-11-22 07:02:44', 'burmese', 15),
(55, 75, 'The Best', '', '09774856641', NULL, NULL, NULL, 'active', '2025-11-22 09:02:15', 'burmese', 15),
(56, 78, 'The Best', '', '09536653999', NULL, NULL, NULL, 'active', '2025-11-24 09:17:11', 'burmese', 15),
(57, 79, 'Myat Aung', '', '09452078041', NULL, NULL, NULL, 'active', '2025-11-25 06:53:08', 'burmese', 15),
(58, 80, 'Myat Aung', '', '09780941059', NULL, NULL, NULL, 'active', '2025-11-25 06:54:00', 'burmese', 15),
(59, 82, 'Myat Aung', '', '09254078421', NULL, NULL, NULL, 'active', '2025-11-25 07:06:18', 'burmese', 15),
(60, 87, 'The One', '991 Rama I Rd', '09780774493', NULL, NULL, NULL, 'active', '2025-12-08 06:33:43', 'Vietnamese', 15),
(61, 88, 'Me And Myself', '', '09788345622', NULL, NULL, NULL, 'active', '2025-12-08 08:57:30', 'Vietnamese', 15),
(64, 91, 'JO HO', 'No (8) Ward, South Okkalapa, Thingangyun District, Yangon City, Yangon, 11062, Myanmar', '09458712333', 'assets/images/693d77cc9e0a4_2025-11-30 (1).png', 16.838870437855, 96.172766089439, 'active', '2025-12-13 14:27:24', 'chinese', 15),
(65, 92, 'JO HO HO', '', '09458712333', '693d7a03e079b_2025-11-16 (2).png', 16.840968313623, 96.167986392975, 'active', '2025-12-13 14:36:51', 'chinese', 15);

-- --------------------------------------------------------

--
-- Table structure for table `restaurant_settlements`
--

CREATE TABLE `restaurant_settlements` (
  `id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `released_by` int(11) DEFAULT NULL,
  `week_no` int(11) NOT NULL,
  `status` enum('pending','completed','rejected') NOT NULL DEFAULT 'pending',
  `payment_slip` varchar(255) NOT NULL,
  `rejection_reason` text NOT NULL,
  `confirmed_at` datetime DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `restaurant_settlements`
--

INSERT INTO `restaurant_settlements` (`id`, `restaurant_id`, `amount`, `notes`, `created_at`, `released_by`, `week_no`, `status`, `payment_slip`, `rejection_reason`, `confirmed_at`, `updated_at`) VALUES
(1, 1, 120000.00, 'Weekly settlement (Week 35)', '2025-09-10 16:40:53', 4, 0, 'completed', '', '', '2025-11-24 15:16:16', '2025-11-24 08:46:16'),
(2, 18, 85000.50, 'Weekly settlement (Week 35)', '2025-09-10 16:40:53', 4, 0, 'pending', '', '', NULL, '2025-11-24 07:13:51'),
(3, 1, 5.00, 'released by admin\nReprocessed on 2025-11-24 15:15:21 with new payment slip', '2025-09-10 16:59:31', 4, 0, 'completed', 'reprocessed_20251124_094521_69241b211d98f.png', '', '2025-11-24 15:15:58', '2025-11-24 08:45:58'),
(4, 2, 0.00, 'released by admin', '2025-09-10 17:00:38', 4, 0, 'pending', '', '', NULL, '2025-11-24 07:13:51'),
(5, 25, 60000.00, 'released by admin', '2025-09-10 17:01:09', 4, 0, 'pending', '', '', NULL, '2025-11-24 07:13:51'),
(6, 4, 12225.00, 'Bulk settlement for week 202540\nReprocessed on 2025-11-24 15:18:08 with new payment slip', '2025-10-05 10:04:28', 4, 202540, 'completed', 'reprocessed_20251124_094808_69241bc84be08.png', '', '2025-11-29 10:07:45', '2025-11-29 03:37:45'),
(9, 4, 37128.75, 'Bulk settlement for week 202539\nReprocessed on 2025-11-24 15:18:19 with new payment slip', '2025-10-05 12:44:49', 4, 202539, 'completed', '', '', '2025-11-28 21:42:34', '2025-11-28 15:12:34'),
(11, 1, 32175.00, 'Bulk settlement for week 202538', '2025-10-05 12:53:39', 4, 202538, 'completed', '', '', '2025-11-24 15:16:23', '2025-11-24 08:46:23'),
(12, 4, 95175.00, 'Bulk settlement for week 202541', '2025-10-08 08:26:29', 4, 202541, 'completed', '', '', '2025-11-24 14:15:32', '2025-11-24 07:45:32'),
(13, 26, 16725.00, 'Bulk settlement for week 202540', '2025-10-08 08:26:29', 4, 202540, 'pending', '', '', NULL, '2025-11-24 07:13:51'),
(14, 2, 19051.50, 'Bulk settlement for week 202539', '2025-10-08 08:26:29', 4, 202539, 'pending', '', '', NULL, '2025-11-24 07:13:51'),
(15, 1, 34425.00, 'Bulk settlement for week 202541', '2025-10-12 15:51:36', 1, 202541, 'completed', '', '', '2025-11-24 15:16:19', '2025-11-24 08:46:19'),
(16, 2, 6000.00, 'Bulk settlement for week 202541', '2025-10-13 06:45:42', 1, 202541, 'pending', '', '', NULL, '2025-11-24 07:13:51'),
(17, 1, 252075.00, 'Bulk settlement for week 202542', '2025-10-29 08:29:47', 1, 202542, 'completed', '', '', '2025-11-24 15:16:13', '2025-11-24 08:46:13'),
(18, 4, 23400.00, 'Bulk settlement for week 202542', '2025-10-29 08:31:48', 1, 202542, 'completed', '', '', '2025-11-24 14:15:29', '2025-11-24 07:45:29'),
(19, 4, 140400.00, 'Bulk settlement for week 202543', '2025-10-29 08:31:57', 1, 202543, 'completed', '', '', '2025-11-24 14:15:25', '2025-11-24 07:45:25'),
(20, 1, 11025.00, 'Bulk settlement for week 202544', '2025-10-29 08:34:24', 1, 202544, 'completed', '', '', '2025-11-24 15:16:10', '2025-11-24 08:46:10'),
(22, 4, 35100.00, 'Settlement for week 202544 - Pending confirmation\nReprocessed on 2025-11-30 18:50:19 with new payment slip', '2025-11-30 12:18:44', 4, 202544, 'completed', 'reprocessed_20251130_132019_692c36839173b.png', '', '2025-11-30 18:52:55', '2025-11-30 12:22:55'),
(23, 4, 11700.00, 'Settlement for week 202546 - Pending confirmation', '2025-12-01 08:39:26', 4, 202546, 'completed', '', '', '2025-12-13 19:28:40', '2025-12-13 12:58:40'),
(24, 1, 11025.00, 'Settlement for week 202545 - Pending confirmation', '2025-12-01 08:40:14', 4, 202545, 'pending', '', '', NULL, '2025-12-01 08:40:14'),
(25, 4, 11700.00, 'Settlement for week 202549 - Pending confirmation', '2025-12-08 06:50:20', 4, 202549, 'completed', '', '', '2025-12-13 19:28:38', '2025-12-13 12:58:38'),
(26, 1, 3300.00, 'Settlement for week 202549 - Pending confirmation', '2025-12-09 09:20:58', 4, 202549, 'pending', '', '', NULL, '2025-12-09 09:20:58'),
(27, 4, 58500.00, 'Settlement for week 202545 - Pending confirmation', '2025-12-10 07:15:29', 4, 202545, 'completed', '', '', '2025-12-13 19:28:43', '2025-12-13 12:58:43'),
(28, 4, 11700.00, 'Settlement for week 202550 - Pending confirmation\nReprocessed on 2025-12-13 19:41:06 with new payment slip\nReprocessed on 2025-12-13 19:42:18 with new payment slip', '2025-12-13 12:59:50', 4, 202550, 'rejected', 'reprocessed_20251213_194218_693d663220455.png', 'invalid', NULL, '2025-12-13 13:12:30'),
(29, 25, 13875.00, 'Settlement for week 202546 - Pending confirmation', '2025-12-13 12:59:59', 4, 202546, 'pending', '', '', NULL, '2025-12-13 12:59:59'),
(30, 4, 108525.00, 'Settlement for week 202548 - Pending confirmation', '2025-12-13 13:08:24', 4, 202548, 'pending', '', '', NULL, '2025-12-13 13:08:24');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `status` enum('visible','hidden') DEFAULT 'visible'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `user_id`, `restaurant_id`, `rating`, `comment`, `status`) VALUES
(1, 5, 1, 3, 'The portion size was shockingly small compared to the price I paid. I ordered expecting a full meal, but what I got felt more like a side dish or snack. ', 'visible'),
(2, 12, 4, 5, 'I love how easy it is to find new restaurants through Food&Me. The interface is intuitive and ordering is a breeze.', 'visible'),
(3, 8, 3, 4, 'The food arrived hot and delicious. The delivery was faster than expected. Will definitely order again!', 'visible'),
(4, 35, 4, 2, 'The long waiting ', 'visible'),
(5, 1, 1, 5, 'The food here was absolutely amazing! Every dish was cooked to perfection and the flavors were rich and well-balanced. I especially loved the presentation, which made the experience even better.', 'visible'),
(6, 5, 1, 4, 'Overall, a very good experience. The dishes were tasty, and the service was friendly. There is a little room for improvement in terms of speed, but I would definitely visit again.', 'visible'),
(7, 8, 1, 3, 'The food was okay, not bad but not exceptional either. Some dishes were flavorful, while others felt a bit bland. It was an average dining experience for me.', 'visible'),
(8, 12, 1, 5, 'Absolutely loved everything about this restaurant! The ambiance was cozy, the staff was attentive, and the food was outstanding. I highly recommend trying the chef\'s special.', 'visible'),
(9, 35, 1, 4, 'A very pleasant dining experience overall. The meals were delicious and filling, and the staff made us feel welcome. I would come back here again and suggest it to friends and family.', 'visible'),
(10, 5, 4, 5, 'Delivery is so fast', 'visible'),
(11, 5, 4, 1, 'It is too long', 'visible'),
(12, 5, 1, 3, 'The chicken is too small but the delivery is fast', 'visible'),
(13, 5, 4, 1, 'Late Delivery', 'visible'),
(14, 5, 4, 5, 'The delivery is so fast', 'visible'),
(15, 5, 1, 5, 'the meat was very juicy, and the flavor is all inside the meat it was perfect.', 'visible'),
(16, 5, 4, 2, 'Too much oiil in Hot Pot', 'visible');

-- --------------------------------------------------------

--
-- Table structure for table `riders`
--

CREATE TABLE `riders` (
  `rider_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `riders`
--

INSERT INTO `riders` (`rider_id`, `user_id`, `status`) VALUES
(1, 41, 'active'),
(2, 43, 'active'),
(3, 44, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `rider_deposits`
--

CREATE TABLE `rider_deposits` (
  `id` int(11) NOT NULL,
  `rider_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `deposit_txn_id` varchar(100) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `deposited_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `verified_by` int(11) DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `rejection_reason` text DEFAULT NULL,
  `transaction_slip` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rider_deposits`
--

INSERT INTO `rider_deposits` (`id`, `rider_id`, `order_id`, `deposit_txn_id`, `amount`, `deposited_at`, `verified`, `verified_by`, `verified_at`, `status`, `rejection_reason`, `transaction_slip`) VALUES
(7, 10, 90, 'SLIP20250901', 15000.00, '2025-09-10 16:53:45', 1, 4, '2025-10-15 16:28:45', 'approved', NULL, NULL),
(8, 10, 91, 'SLIP20250901', 10000.00, '2025-09-10 16:53:45', 1, 4, '2025-10-15 16:28:34', 'approved', NULL, NULL),
(10, 10, 46, 'DEP2025101010281610', 2000.00, '2025-10-10 08:28:16', 1, 4, '2025-10-15 16:32:30', 'approved', NULL, NULL),
(11, 10, 47, 'DEP2025101010281610', 2000.00, '2025-10-10 08:28:16', 1, 4, '2025-10-15 16:32:34', 'approved', NULL, NULL),
(12, 10, 53, 'DEP2025101010281610', 2000.00, '2025-10-10 08:28:16', 1, 4, '2025-10-15 16:32:44', 'approved', NULL, NULL),
(13, 10, 54, 'DEP2025101010281610', 2000.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(14, 10, 79, 'DEP2025101010281610', 4346.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(15, 10, 80, 'DEP2025101010281610', 4346.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(16, 10, 81, 'DEP2025101010281610', 3646.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(17, 10, 78, 'DEP2025101010281610', 9500.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(18, 10, 77, 'DEP2025101010281610', 4346.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(19, 10, 76, 'DEP2025101010281610', 4536.00, '2025-10-10 08:28:16', 1, 4, '2025-10-15 16:31:56', 'approved', NULL, NULL),
(20, 10, 75, 'DEP2025101010281610', 4546.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(21, 10, 74, 'DEP2025101010281610', 32667.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(22, 10, 73, 'DEP2025101010281610', 5425036.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(23, 10, 72, 'DEP2025101010281610', 5212.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(24, 10, 71, 'DEP2025101010281610', 5332836.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(25, 10, 63, 'DEP2025101010281610', 6500.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(26, 10, 2, 'DEP2025101010281610', 2500.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(27, 10, 18, 'DEP2025101010281610', 15000.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(28, 10, 19, 'DEP2025101010281610', 2500.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(29, 10, 20, 'DEP2025101010281610', 2500.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(30, 10, 24, 'DEP2025101010281610', 2500.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(31, 10, 25, 'DEP2025101010281610', 2500.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(32, 10, 26, 'DEP2025101010281610', 2000.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(33, 10, 27, 'DEP2025101010281610', 2500.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(34, 10, 59, 'DEP2025101010281610', 7103.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(35, 10, 58, 'DEP2025101010281610', 13000.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(36, 10, 61, 'DEP2025101010281610', 5303.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(37, 10, 62, 'DEP2025101010281610', 5303.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(38, 10, 44, 'DEP2025101010281610', 6000.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(39, 10, 56, 'DEP2025101010281610', 20500.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(40, 10, 43, 'DEP2025101010281610', 1800.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(41, 10, 42, 'DEP2025101010281610', 4000.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(42, 10, 41, 'DEP2025101010281610', 4000.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(43, 10, 40, 'DEP2025101010281610', 4000.00, '2025-10-10 08:28:16', 1, 4, '2025-10-15 16:31:23', 'approved', NULL, NULL),
(44, 10, 39, 'DEP2025101010281610', 4000.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(45, 10, 38, 'DEP2025101010281610', 2500.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(46, 10, 37, 'DEP2025101010281610', 2500.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(47, 10, 34, 'DEP2025101010281610', 2500.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(48, 10, 33, 'DEP2025101010281610', 2500.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(49, 10, 35, 'DEP2025101010281610', 2000.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(50, 10, 32, 'DEP2025101010281610', 2000.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(51, 10, 31, 'DEP2025101010281610', 7500.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(52, 10, 30, 'DEP2025101010281610', 1800.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(53, 10, 29, 'DEP2025101010281610', 2500.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(54, 10, 36, 'DEP2025101010281610', 2500.00, '2025-10-10 08:28:16', 1, 4, '2025-10-15 16:32:18', 'approved', NULL, NULL),
(55, 10, 28, 'DEP2025101010281610', 2500.00, '2025-10-10 08:28:16', 1, 4, '2025-10-15 16:32:07', 'approved', NULL, NULL),
(56, 10, 82, 'DEP2025101010281610', 4346.00, '2025-10-10 08:28:16', 1, 4, '2025-10-15 16:29:31', 'approved', NULL, NULL),
(57, 10, 83, 'DEP2025101010281610', 17546.00, '2025-10-10 08:28:16', 1, 4, '2025-10-15 16:29:44', 'approved', NULL, NULL),
(58, 10, 84, 'DEP2025101010281610', 17554.00, '2025-10-10 08:28:16', 1, 4, '2025-10-15 16:30:14', 'approved', NULL, NULL),
(59, 10, 85, 'DEP2025101010281610', 5333036.00, '2025-10-10 08:28:16', 1, 4, '2025-10-15 16:30:28', 'approved', NULL, NULL),
(60, 10, 86, 'DEP2025101010281610', 17668.00, '2025-10-10 08:28:16', 1, 4, '2025-10-15 16:30:58', 'approved', NULL, NULL),
(61, 10, 87, 'DEP2025101010281610', 4500.00, '2025-10-10 08:28:16', 1, 4, '2025-10-15 16:31:03', 'approved', NULL, NULL),
(62, 10, 95, 'DEP2025101010281610', 12000.00, '2025-10-10 08:28:16', 1, 4, '2025-10-15 16:31:52', 'approved', NULL, NULL),
(63, 10, 97, 'DEP2025101010281610', 12934.00, '2025-10-10 08:28:16', 1, 4, '2025-10-15 16:32:40', 'approved', NULL, NULL),
(64, 10, 104, 'DEP2025101010281610', 12331.00, '2025-10-10 08:28:16', 1, 4, '2025-10-15 16:32:13', 'approved', NULL, NULL),
(65, 10, 123, 'DEP2025101010281610', 16300.00, '2025-10-10 08:28:16', 1, 4, '2025-10-15 16:32:23', 'approved', NULL, NULL),
(66, 10, 137, 'DEP2025101010281610', 16300.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(67, 10, 140, 'DEP2025101010281610', 16300.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(68, 10, 141, 'DEP2025101010281610', 16300.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(69, 10, 148, 'DEP2025101010281610', 16300.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(70, 10, 145, 'DEP2025101010281610', 16300.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(71, 10, 146, 'DEP2025101010281610', 16300.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(72, 10, 155, 'DEP2025101010281610', 15400.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(73, 10, 175, 'DEP2025101010281610', 16800.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(74, 10, 176, 'DEP2025101010281610', 16800.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(75, 10, 170, 'DEP2025101010281610', 29100.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(76, 10, 167, 'DEP2025101010281610', 16300.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(77, 10, 178, 'DEP2025101010281610', 9400.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(78, 10, 184, 'DEP2025101010281610', 15600.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(79, 10, 185, 'DEP2025101010281610', 14700.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(80, 10, 187, 'DEP2025101010281610', 14700.00, '2025-10-10 08:28:16', 1, 4, NULL, 'approved', NULL, NULL),
(81, 10, 186, 'DEP2025101010281610', 8000.00, '2025-10-10 08:28:16', 1, 4, '2025-10-15 16:28:57', 'approved', NULL, NULL),
(82, 10, 183, NULL, 15400.00, '2025-10-10 16:51:54', 1, 4, NULL, 'approved', NULL, NULL),
(83, 10, 191, NULL, 17100.00, '2025-10-10 17:00:55', 1, 4, NULL, 'approved', NULL, NULL),
(84, 10, 192, NULL, 17100.00, '2025-10-10 17:43:45', 1, 4, NULL, 'approved', NULL, NULL),
(85, 10, 47, 'DEP2025101020514910', 2000.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(86, 10, 53, 'DEP2025101020514910', 2000.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(87, 10, 54, 'DEP2025101020514910', 2000.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(88, 10, 79, 'DEP2025101020514910', 4346.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(89, 10, 80, 'DEP2025101020514910', 4346.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(90, 10, 81, 'DEP2025101020514910', 3646.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(91, 10, 78, 'DEP2025101020514910', 9500.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(92, 10, 77, 'DEP2025101020514910', 4346.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(93, 10, 76, 'DEP2025101020514910', 4536.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(94, 10, 75, 'DEP2025101020514910', 4546.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(95, 10, 74, 'DEP2025101020514910', 32667.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(96, 10, 73, 'DEP2025101020514910', 5425036.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(97, 10, 72, 'DEP2025101020514910', 5212.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(98, 10, 71, 'DEP2025101020514910', 5332836.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(99, 10, 63, 'DEP2025101020514910', 6500.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(100, 10, 2, 'DEP2025101020514910', 2500.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(101, 10, 18, 'DEP2025101020514910', 15000.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(102, 10, 19, 'DEP2025101020514910', 2500.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(103, 10, 20, 'DEP2025101020514910', 2500.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(104, 10, 24, 'DEP2025101020514910', 2500.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(105, 10, 25, 'DEP2025101020514910', 2500.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(106, 10, 26, 'DEP2025101020514910', 2000.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(107, 10, 27, 'DEP2025101020514910', 2500.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(108, 10, 59, 'DEP2025101020514910', 7103.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(109, 10, 58, 'DEP2025101020514910', 13000.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(110, 10, 61, 'DEP2025101020514910', 5303.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(111, 10, 62, 'DEP2025101020514910', 5303.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(112, 10, 44, 'DEP2025101020514910', 6000.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(113, 10, 56, 'DEP2025101020514910', 20500.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(114, 10, 43, 'DEP2025101020514910', 1800.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(115, 10, 42, 'DEP2025101020514910', 4000.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(116, 10, 41, 'DEP2025101020514910', 4000.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(117, 10, 40, 'DEP2025101020514910', 4000.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(118, 10, 39, 'DEP2025101020514910', 4000.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(119, 10, 38, 'DEP2025101020514910', 2500.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(120, 10, 37, 'DEP2025101020514910', 2500.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(121, 10, 34, 'DEP2025101020514910', 2500.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(122, 10, 33, 'DEP2025101020514910', 2500.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(123, 10, 35, 'DEP2025101020514910', 2000.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(124, 10, 32, 'DEP2025101020514910', 2000.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(125, 10, 31, 'DEP2025101020514910', 7500.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(126, 10, 30, 'DEP2025101020514910', 1800.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(127, 10, 29, 'DEP2025101020514910', 2500.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(128, 10, 36, 'DEP2025101020514910', 2500.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(129, 10, 28, 'DEP2025101020514910', 2500.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(130, 10, 82, 'DEP2025101020514910', 4346.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(131, 10, 83, 'DEP2025101020514910', 17546.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(132, 10, 84, 'DEP2025101020514910', 17554.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(133, 10, 85, 'DEP2025101020514910', 5333036.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(134, 10, 86, 'DEP2025101020514910', 17668.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(135, 10, 87, 'DEP2025101020514910', 4500.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(136, 10, 95, 'DEP2025101020514910', 12000.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(137, 10, 97, 'DEP2025101020514910', 12934.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(138, 10, 104, 'DEP2025101020514910', 12331.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(139, 10, 123, 'DEP2025101020514910', 16300.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(140, 10, 137, 'DEP2025101020514910', 16300.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(141, 10, 140, 'DEP2025101020514910', 16300.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(142, 10, 141, 'DEP2025101020514910', 16300.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(143, 10, 148, 'DEP2025101020514910', 16300.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(144, 10, 145, 'DEP2025101020514910', 16300.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(145, 10, 146, 'DEP2025101020514910', 16300.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(146, 10, 155, 'DEP2025101020514910', 15400.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(147, 10, 175, 'DEP2025101020514910', 16800.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(148, 10, 176, 'DEP2025101020514910', 16800.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(149, 10, 170, 'DEP2025101020514910', 29100.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(150, 10, 167, 'DEP2025101020514910', 16300.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(151, 10, 178, 'DEP2025101020514910', 9400.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(152, 10, 184, 'DEP2025101020514910', 15600.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(153, 10, 185, 'DEP2025101020514910', 14700.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(154, 10, 187, 'DEP2025101020514910', 14700.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(155, 10, 186, 'DEP2025101020514910', 8000.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(156, 10, 189, 'DEP2025101020514910', 1800.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(157, 10, 190, 'DEP2025101020514910', 14700.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(158, 10, 183, 'DEP2025101020514910', 15400.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(159, 10, 191, 'DEP2025101020514910', 15600.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(160, 10, 192, 'DEP2025101020514910', 15600.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(161, 10, 193, 'DEP2025101020514910', 15600.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(162, 10, 194, 'DEP2025101020514910', 15600.00, '2025-10-10 18:51:49', 1, 4, NULL, 'approved', NULL, NULL),
(163, 10, 195, 'DEP2025101021264010', 15600.00, '2025-10-10 19:26:40', 1, 4, '2025-10-10 19:34:00', 'approved', NULL, NULL),
(164, 10, 196, 'DEP2025101021371910', 17100.00, '2025-10-10 19:37:19', 1, 4, NULL, 'approved', NULL, NULL),
(166, 10, 197, 'DEP2025101021453410', 17100.00, '2025-10-10 19:45:34', 1, 4, NULL, 'approved', NULL, NULL),
(167, 10, 198, 'DEP2025101021483510', 17100.00, '2025-10-10 19:48:35', 1, 4, NULL, 'approved', NULL, NULL),
(168, 10, 199, 'DEP2025101021514510', 17100.00, '2025-10-10 19:51:45', 1, 4, NULL, 'approved', NULL, NULL),
(169, 10, 200, 'DEP2025101021563810', 17100.00, '2025-10-10 19:56:38', 1, 4, NULL, 'approved', NULL, NULL),
(171, 10, 201, 'DEP2025101022011510', 17100.00, '2025-10-10 20:01:15', 1, 4, NULL, 'approved', NULL, NULL),
(172, 10, 202, 'DEP2025101112051310', 17100.00, '2025-10-11 10:05:13', 1, 4, NULL, 'approved', NULL, NULL),
(173, 10, 203, 'DEP2025101112203010', 17100.00, '2025-10-11 10:20:30', 1, 4, NULL, 'approved', NULL, NULL),
(174, 10, 206, 'DEP2025101208011910', 17100.00, '2025-10-12 06:01:19', 1, 4, '2025-10-12 06:07:41', 'approved', NULL, NULL),
(175, 10, 207, 'DEP2025101208011910', 17100.00, '2025-10-12 06:01:19', 1, 4, '2025-10-12 06:03:29', 'approved', NULL, NULL),
(176, 10, 208, 'DEP2025101208011910', 17100.00, '2025-10-12 06:01:19', 1, 4, '2025-10-12 06:13:41', 'approved', NULL, NULL),
(177, 10, 210, 'DEP2025101209525710', 17100.00, '2025-10-12 07:52:57', 1, 4, '2025-10-12 07:53:59', 'approved', NULL, NULL),
(178, 10, 211, 'DEP2025101210052410', 17100.00, '2025-10-12 08:05:24', 1, 4, '2025-10-12 08:06:20', 'approved', NULL, NULL),
(179, 10, 212, 'DEP2025101210220510', 17100.00, '2025-10-12 08:22:05', 1, 4, '2025-10-12 08:22:39', 'approved', NULL, NULL),
(180, 10, 213, 'DEP2025101210370610', 17100.00, '2025-10-12 08:37:06', 1, 4, '2025-10-12 08:42:19', 'approved', NULL, NULL),
(181, 10, 214, 'DEP2025101210424110', 17100.00, '2025-10-12 08:42:41', 1, 4, '2025-10-12 08:43:12', 'approved', NULL, NULL),
(182, 10, 215, 'DEP2025101210495610', 17100.00, '2025-10-12 08:49:56', 1, 4, '2025-10-12 08:53:00', 'approved', NULL, NULL),
(183, 10, 216, 'DEP2025101211004610', 32700.00, '2025-10-12 09:00:46', 1, 4, '2025-10-12 09:01:06', 'approved', NULL, NULL),
(184, 10, 217, 'DEP2025101211121710', 16300.00, '2025-10-12 09:12:17', 1, 4, '2025-10-12 09:12:58', 'approved', NULL, NULL),
(185, 10, 218, 'DEP2025101213454210', 17100.00, '2025-10-12 11:45:42', 1, 4, '2025-10-12 12:03:56', 'approved', NULL, NULL),
(186, 10, 220, 'DEP2025101214081710', 17100.00, '2025-10-12 12:08:17', 1, 4, '2025-10-12 12:09:45', 'approved', NULL, NULL),
(187, 10, 221, 'DEP2025101214142210', 17100.00, '2025-10-12 12:14:22', 1, 4, '2025-10-12 12:22:31', 'approved', NULL, NULL),
(188, 10, 222, 'DEP2025101214224310', 17100.00, '2025-10-12 12:22:43', 1, 4, '2025-10-12 12:29:39', 'approved', NULL, NULL),
(189, 10, 223, 'DEP2025101214304010', 17100.00, '2025-10-12 12:30:40', 1, 4, '2025-10-12 12:31:31', 'approved', NULL, NULL),
(190, 10, 224, 'DEP2025101214471310', 17100.00, '2025-10-12 12:47:13', 1, 4, '2025-10-12 12:47:33', 'approved', NULL, NULL),
(191, 10, 225, 'DEP2025101215082910', 17100.00, '2025-10-12 13:08:29', 1, 4, '2025-10-12 13:09:42', 'approved', NULL, NULL),
(192, 10, 226, 'DEP2025101215150910', 17100.00, '2025-10-12 13:15:09', 1, 4, '2025-10-12 13:15:15', 'approved', NULL, NULL),
(193, 10, 227, 'DEP2025101215454710', 17100.00, '2025-10-12 13:45:47', 1, 4, '2025-10-12 13:46:01', 'approved', NULL, NULL),
(194, 10, 228, 'DEP2025101217022210', 17100.00, '2025-10-12 15:02:22', 1, 4, '2025-10-12 15:02:34', 'approved', NULL, NULL),
(195, 10, 229, 'DEP2025101217041910', 17100.00, '2025-10-12 15:04:19', 1, 4, '2025-10-12 15:15:24', 'approved', NULL, NULL),
(196, 10, 230, 'DEP2025101217154910', 17100.00, '2025-10-12 15:15:49', 1, 4, '2025-10-12 15:15:57', 'approved', NULL, NULL),
(197, 10, 231, 'DEP2025101416271010', 17100.00, '2025-10-14 14:27:10', 1, 4, '2025-10-14 14:27:20', 'approved', NULL, NULL),
(198, 10, 233, 'DEP2025101510245910', 17700.00, '2025-10-15 08:24:59', 1, 4, '2025-10-15 08:25:23', 'approved', NULL, NULL),
(199, 10, 234, 'DEP2025101510453610', 17700.00, '2025-10-15 08:45:36', 1, 4, '2025-10-15 08:45:57', 'approved', NULL, NULL),
(200, 10, 232, 'DEP2025101517571110', 16800.00, '2025-10-15 15:57:11', 1, 4, '2025-10-15 16:34:59', 'approved', NULL, NULL),
(201, 10, 235, 'DEP2025101517571110', 15400.00, '2025-10-15 15:57:11', 1, 4, '2025-10-15 16:34:52', 'approved', NULL, NULL),
(202, 10, 236, 'DEP2025101605431110', 15400.00, '2025-10-16 03:43:11', 1, 4, '2025-10-16 05:30:49', 'approved', NULL, NULL),
(203, 10, 237, 'DEP2025101605431110', 28552.00, '2025-10-16 03:43:11', 1, 4, '2025-10-16 03:43:22', 'approved', NULL, NULL),
(204, 10, 232, 'DEP2025101606074510', 16800.00, '2025-10-16 04:07:45', 1, 4, '2025-10-16 04:34:13', 'approved', NULL, NULL),
(205, 10, 238, 'DEP2025101606074510', 8252.00, '2025-10-16 04:07:45', 1, 4, '2025-10-16 04:32:31', 'approved', 'Invalid Slip', NULL),
(206, 10, 238, 'DEP2025101606343210', 8252.00, '2025-10-16 04:34:32', 1, 4, '2025-10-16 04:34:46', 'approved', NULL, NULL),
(207, 10, 239, 'DEP2025101608454710', 13252.00, '2025-10-16 06:45:47', 1, 4, '2025-10-16 06:47:50', 'approved', NULL, 'slip_10_20251016084547.png'),
(208, 10, 240, 'DEP2025101608482110', 12052.00, '2025-10-16 06:48:21', 1, 4, '2025-10-16 13:51:34', 'approved', NULL, 'slip_10_20251016084821.png'),
(209, 10, 241, 'DEP2025101615522510', 24452.00, '2025-10-16 13:52:25', 1, 4, '2025-10-17 06:37:35', 'approved', NULL, 'slip_10_20251016155225.png'),
(210, 10, 243, 'DEP2025101708374010', 16800.00, '2025-10-17 06:37:40', 1, 4, '2025-10-17 07:14:51', 'approved', NULL, NULL),
(211, 10, 244, 'DEP2025101709231710', 2500.00, '2025-10-17 07:23:17', 1, 4, '2025-10-17 13:55:41', 'approved', NULL, 'uploads/deposit_slips/deposit_10_1760685797.png'),
(212, 10, 245, 'DEP2025101709231710', 31400.00, '2025-10-17 07:23:17', 1, 4, '2025-10-17 13:55:31', 'approved', NULL, 'uploads/deposit_slips/deposit_10_1760685797.png'),
(213, 10, 246, 'DEP2025101709231710', 31900.00, '2025-10-17 07:23:17', 1, 4, '2025-10-17 13:55:22', 'approved', NULL, 'uploads/deposit_slips/deposit_10_1760685797.png'),
(214, 10, 244, 'DEP2025101715424310', 2500.00, '2025-10-17 13:42:43', 1, 4, '2025-10-17 14:37:56', 'approved', 'Invalid slip', 'slip_10_1760708563_2025-10-02 (1).png'),
(215, 10, 245, 'DEP2025101715424310', 31400.00, '2025-10-17 13:42:43', 1, 4, '2025-10-17 13:55:58', 'approved', NULL, 'slip_10_1760708563_2025-10-02 (1).png'),
(216, 10, 246, 'DEP2025101715424310', 31900.00, '2025-10-17 13:42:43', 1, 4, '2025-10-17 13:55:53', 'approved', NULL, 'slip_10_1760708563_2025-10-02 (1).png'),
(217, 10, 247, 'DEP2025101716051410', 8900.00, '2025-10-17 14:05:14', 1, 4, '2025-10-17 14:38:02', 'approved', 'incifficent balance', 'slip_10_1760709914_photo_2025-10-17_20-28-05.jpg'),
(218, 10, 248, 'DEP2025101716051410', 18900.00, '2025-10-17 14:05:14', 1, 4, '2025-10-17 14:07:57', 'approved', NULL, 'slip_10_1760709914_photo_2025-10-17_20-28-05.jpg'),
(219, 10, 249, 'DEP2025101716051410', 5100.00, '2025-10-17 14:35:17', 1, 4, '2025-10-17 14:35:44', 'approved', NULL, 'slip_10_1760711717_2025-10-14 (1).png'),
(220, 10, 250, 'DEP2025101716051410', 5522.00, '2025-10-17 14:05:14', 1, 4, '2025-10-17 14:05:45', 'approved', NULL, 'slip_10_1760709914_photo_2025-10-17_20-28-05.jpg'),
(221, 10, 247, 'DEP2025101716082410', 8900.00, '2025-10-17 14:08:24', 1, 4, '2025-10-17 14:38:10', 'approved', 'invalid slip', 'slip_10_1760710104_photo_2025-10-17_20-28-05.jpg'),
(222, 10, 249, 'DEP2025101716082410', 5100.00, '2025-10-17 14:08:24', 1, 4, '2025-10-17 14:21:15', 'approved', NULL, 'slip_10_1760710104_photo_2025-10-17_20-28-05.jpg'),
(223, 10, 247, 'DEP2025101716235710', 8900.00, '2025-10-17 14:23:57', 1, 4, '2025-10-17 14:38:07', 'approved', 'invalid', 'slip_10_1760711037_photo_2025-10-17_20-28-05.jpg'),
(224, 10, 247, 'DEP2025101716355810', 8900.00, '2025-10-17 14:35:58', 1, 4, '2025-10-17 14:36:07', 'approved', NULL, 'slip_10_1760711758_2025-10-02 (1).png'),
(225, 10, 251, 'DEP2025101716505110', 18422.00, '2025-10-17 14:50:51', 1, 4, '2025-10-17 14:51:16', 'approved', NULL, NULL),
(226, 10, 252, 'DEP2025101716513010', 8122.00, '2025-10-17 14:52:47', 1, 4, '2025-10-17 14:53:11', 'approved', NULL, 'slip_10_1760712767_2025-10-02 (1).png'),
(227, 10, 253, 'DEP2025101716584010', 24022.00, '2025-10-17 14:58:40', 1, 4, '2025-10-17 15:01:14', 'approved', NULL, NULL),
(228, 10, 254, 'DEP2025101717034010', 18422.00, '2025-10-17 15:07:36', 1, 4, '2025-10-17 15:07:49', 'approved', NULL, 'slip_10_1760713656_2025-10-03 (2).png'),
(229, 10, 255, 'DEP2025101717080210', 8122.00, '2025-10-17 15:08:02', 1, 4, '2025-10-17 15:10:30', 'approved', NULL, NULL),
(230, 10, 256, 'DEP2025101717104210', 8122.00, '2025-10-17 15:10:42', 1, 4, '2025-10-17 15:17:42', 'approved', NULL, NULL),
(231, 10, 257, 'DEP2025101717201410', 18422.00, '2025-10-17 15:20:14', 1, 4, '2025-10-17 15:21:31', 'approved', NULL, NULL),
(232, 10, 258, 'DEP2025101717214710', 7822.00, '2025-10-17 15:21:47', 1, 4, '2025-10-17 15:22:27', 'approved', NULL, NULL),
(233, 10, 259, 'DEP2025101717273410', 6522.00, '2025-10-17 15:27:34', 1, 4, '2025-10-17 15:27:47', 'approved', NULL, 'slip_10_1760714854_2025-10-11 (8).png'),
(234, 10, 261, 'DEP2025101717295410', 18222.00, '2025-11-11 07:32:25', 1, 4, '2025-11-11 07:34:33', 'approved', NULL, 'slip_6912e68993875_2025-11-11 (1).png'),
(235, 10, 261, 'DEP2025101809294510', 18222.00, '2025-11-11 07:34:47', 1, 4, '2025-11-11 08:14:40', 'approved', NULL, 'slip_6912e71738879_2025-11-11 (1).png'),
(236, 10, 260, 'DEP2025101809294510', 18422.00, '2025-10-18 07:29:45', 1, 4, '2025-10-18 07:30:22', 'approved', NULL, 'slip_10_1760772585_2025-10-08 (2).png'),
(237, 10, 261, 'DEP2025102111135310', 18222.00, '2025-10-21 09:13:53', 1, 4, '2025-10-21 09:14:38', 'approved', NULL, 'slip_10_1761038033_photo_2025-10-20_18-50-03.jpg'),
(238, 10, 262, 'DEP2025102111135310', 16300.00, '2025-10-21 09:13:53', 1, 4, '2025-10-21 09:14:46', 'approved', NULL, 'slip_10_1761038033_photo_2025-10-20_18-50-03.jpg'),
(239, 10, 263, 'DEP2025102111135310', 16300.00, '2025-10-21 09:13:53', 1, 4, '2025-10-21 09:15:01', 'approved', NULL, 'slip_10_1761038033_photo_2025-10-20_18-50-03.jpg'),
(240, 10, 264, 'DEP2025102209053610', 16300.00, '2025-10-22 07:05:36', 1, 4, '2025-10-22 07:05:57', 'approved', NULL, 'slip_10_1761116736_photo_2025-10-17_20-28-05.jpg'),
(241, 10, 267, 'DEP2025102209053610', 17100.00, '2025-11-11 07:34:59', 1, 4, '2025-11-11 08:14:04', 'approved', NULL, 'slip_6912e723bf46b_2025-11-11 (1).png'),
(242, 10, 268, 'DEP2025102209053610', 17100.00, '2025-10-22 07:05:36', 1, 4, '2025-10-22 09:16:34', 'approved', NULL, 'slip_10_1761116736_photo_2025-10-17_20-28-05.jpg'),
(243, 10, 273, 'DEP2025102209053610', 17700.00, '2025-11-11 07:34:53', 1, 4, '2025-11-11 08:14:45', 'approved', NULL, 'slip_6912e71d14c45_2025-11-11 (1).png'),
(244, 10, 267, 'DEP2025102808560510', 17100.00, '2025-10-28 07:56:05', 1, 4, '2025-10-28 07:56:20', 'approved', NULL, 'slip_10_1761638165_photo_2025-10-17_20-28-05.jpg'),
(245, 10, 270, 'DEP2025102808560510', 17100.00, '2025-10-28 07:56:05', 1, 4, '2025-10-28 07:57:19', 'approved', NULL, 'slip_10_1761638165_photo_2025-10-17_20-28-05.jpg'),
(246, 10, 271, 'DEP2025102808560510', 17700.00, '2025-10-28 07:56:05', 1, 4, '2025-10-28 07:57:40', 'approved', NULL, 'slip_10_1761638165_photo_2025-10-17_20-28-05.jpg'),
(247, 10, 265, 'DEP2025102808560510', 16300.00, '2025-10-28 07:56:05', 1, 4, '2025-10-28 07:57:49', 'approved', NULL, 'slip_10_1761638165_photo_2025-10-17_20-28-05.jpg'),
(248, 10, 273, 'DEP2025102808560510', 17700.00, '2025-10-28 07:56:05', 1, 4, '2025-10-28 07:57:56', 'approved', NULL, 'slip_10_1761638165_photo_2025-10-17_20-28-05.jpg'),
(249, 10, 277, 'DEP2025102808560510', 17100.00, '2025-10-28 07:56:05', 1, 4, '2025-10-28 07:58:01', 'approved', NULL, 'slip_10_1761638165_photo_2025-10-17_20-28-05.jpg'),
(250, 10, 274, 'DEP2025102808560510', 17700.00, '2025-10-28 07:56:05', 1, 4, '2025-10-28 07:58:06', 'approved', NULL, 'slip_10_1761638165_photo_2025-10-17_20-28-05.jpg'),
(251, 10, 278, 'DEP2025102808560510', 16300.00, '2025-10-28 07:56:05', 1, 4, '2025-10-28 07:58:11', 'approved', NULL, 'slip_10_1761638165_photo_2025-10-17_20-28-05.jpg'),
(252, 10, 280, 'DEP2025102908133710', 15400.00, '2025-10-29 07:13:37', 1, 4, '2025-10-29 07:13:53', 'approved', NULL, 'slip_10_1761722017_photo_2025-10-17_20-28-05.jpg'),
(253, 10, 281, 'DEP2025103107515610', 16300.00, '2025-10-31 06:51:56', 1, 4, '2025-10-31 06:52:10', 'approved', NULL, 'slip_10_1761893516_photo_2025-10-20_18-50-03.jpg'),
(254, 10, 282, 'DEP2025103109194810', 17700.00, '2025-10-31 08:19:48', 1, 4, '2025-10-31 08:20:10', 'approved', NULL, 'slip_10_1761898788_690471241704f.png'),
(255, 10, 282, 'DEP2025103109194810', 17700.00, '2025-11-11 07:03:53', 1, 4, '2025-11-11 08:14:36', 'approved', NULL, 'slip_6912dfd9a2ed4_2025-11-11.png'),
(256, 10, 283, 'DEP2025103108375610', 17700.00, '2025-10-31 07:37:56', 1, 4, '2025-10-31 07:38:13', 'approved', NULL, 'slip_10_1761896276_index.jpg'),
(257, 10, 284, 'DEP2025103109275910', 16800.00, '2025-11-11 07:21:20', 1, 4, '2025-11-11 07:23:28', 'approved', NULL, 'slip_6912e3f095db9_2025-11-11.png'),
(258, 10, 284, 'DEP2025110117232310', 16800.00, '2025-11-01 16:23:23', 1, 4, '2025-11-01 16:24:17', 'approved', NULL, 'slip_10_1762014203_2025-10-04 (2).png'),
(259, 10, 285, 'DEP2025110117232310', 16200.00, '2025-11-11 07:35:06', 1, 4, '2025-11-11 07:35:14', 'approved', NULL, 'slip_6912e72a3c3cf_2025-11-11 (1).png'),
(260, 10, 285, 'DEP2025110117541610', 16200.00, '2025-11-01 16:54:16', 1, 4, '2025-11-07 08:44:00', 'approved', NULL, 'slip_10_1762016056_2025-10-01.png'),
(261, 10, 286, 'DEP2025110117541610', 16200.00, '2025-11-01 16:54:16', 1, 4, '2025-11-07 08:44:09', 'approved', NULL, 'slip_10_1762016056_2025-10-01.png'),
(262, 10, 289, 'DEP2025110709463310', 16300.00, '2025-11-07 08:46:33', 1, 4, '2025-11-07 08:47:03', 'approved', NULL, 'slip_10_1762505193_photo_2025-10-20_18-49-05.jpg'),
(263, 10, 290, 'DEP2025110709463310', 16300.00, '2025-11-11 07:03:53', 1, 4, '2025-11-11 07:31:48', 'approved', NULL, 'slip_6912dfd9a2ed4_2025-11-11.png'),
(264, 10, 290, 'DEP2025110709532610', 16300.00, '2025-11-07 08:53:26', 1, 4, '2025-11-07 09:06:24', 'approved', NULL, 'slip_10_1762505606_2025-10-12 (4).png'),
(265, 10, 293, 'DEP2025110709532610', 17700.00, '2025-11-07 08:53:26', 1, 4, '2025-11-07 09:06:29', 'approved', NULL, 'slip_10_1762505606_2025-10-12 (4).png'),
(266, 10, 294, 'DEP2025110710064310', 16300.00, '2025-11-07 09:06:43', 1, 4, '2025-11-07 09:08:13', 'approved', NULL, 'slip_10_1762506403_2025-10-02 (2).png'),
(267, 10, 295, 'DEP2025110912570610', 16800.00, '2025-11-09 11:57:06', 1, 4, '2025-11-09 11:58:38', 'approved', NULL, 'slip_10_1762689426_2025-10-24 (1).png'),
(268, 10, 297, 'DEP2025110912570610', 16300.00, '2025-11-09 11:57:06', 1, 4, '2025-11-10 06:37:09', 'approved', NULL, 'slip_10_1762689426_2025-10-24 (1).png'),
(269, 10, 298, 'DEP2025111008162910', 17700.00, '2025-11-11 06:51:24', 1, 4, '2025-11-11 07:55:29', 'approved', NULL, 'slip_6912dcecf2cca_2025-11-11.png'),
(270, 10, 298, 'DEP2025111009191210', 17700.00, '2025-11-10 08:19:12', 1, 4, '2025-11-10 09:28:01', 'approved', NULL, 'slip_10_1762762752_2025-10-02 (2).png'),
(271, 10, 299, 'DEP2025111009191210', 15400.00, '2025-11-11 07:05:23', 1, 4, '2025-11-11 07:08:43', 'approved', NULL, 'slip_6912e0330e9ab_2025-11-11.png'),
(272, 10, 300, 'DEP2025111109281910', 16800.00, '2025-11-11 08:29:52', 1, 4, '2025-11-11 08:30:04', 'approved', NULL, 'slip_6912f40080a44_2025-11-11 (1).png'),
(273, 10, 301, NULL, 14700.00, '2025-11-11 08:55:28', 1, 4, '2025-11-11 08:55:43', 'approved', NULL, 'slip_6912fa0066ffa_2025-11-11.png'),
(274, 10, 302, NULL, 14700.00, '2025-11-11 08:57:55', 1, 4, '2025-11-11 08:58:04', 'approved', NULL, 'slip_6912fa9375d87_2025-11-11 (1).png'),
(275, 10, 303, 'DEP2025111110152910', 15400.00, '2025-11-11 09:16:33', 1, 4, '2025-11-11 09:16:49', 'approved', NULL, 'slip_6912fef192ae1_2025-11-11 (1).png'),
(276, 10, 304, 'DEP2025111207192310', 15400.00, '2025-11-12 06:19:23', 1, 4, '2025-11-12 07:00:56', 'approved', NULL, 'slip_69142fa75e60d_2025-11-11 (1).png'),
(277, 10, 305, 'DEP2025111208224110', 16800.00, '2025-11-12 07:22:41', 1, 4, '2025-11-12 07:45:52', 'approved', NULL, 'slip_6914391e7b521_2025-11-11 (1).png'),
(278, 10, 306, NULL, 14700.00, '2025-11-12 07:45:00', 1, 4, '2025-11-12 07:45:30', 'approved', NULL, 'slip_69143afca4939_2025-11-11.png'),
(279, 10, 307, 'DEP2025111209194810', 15400.00, '2025-11-12 08:19:48', 1, 4, '2025-11-12 08:49:08', 'approved', NULL, NULL),
(280, 10, 308, NULL, 14700.00, '2025-11-12 08:39:07', 1, 4, '2025-11-12 08:49:45', 'approved', NULL, 'slip_69144a19d8e36_2025-11-11.png'),
(281, 10, 309, 'DEP2025111209563210', 15400.00, '2025-11-12 08:56:32', 1, 4, '2025-11-12 09:28:03', 'approved', NULL, NULL),
(282, 10, 310, NULL, 14700.00, '2025-11-12 09:26:41', 1, 4, '2025-11-12 09:27:59', 'approved', NULL, 'slip_691452d1e6556_IMG_20251111_110749_511.jpg'),
(283, 10, 311, 'DEP2025111210320010', 15400.00, '2025-11-12 09:32:00', 1, 4, '2025-11-12 09:33:01', 'approved', NULL, NULL),
(284, 10, 312, 'DEP2025111411075210', 3900.00, '2025-11-14 10:07:52', 1, 4, '2025-11-14 14:39:57', 'approved', NULL, 'slip_69173e7ecadcd_2025-11-11.png'),
(285, 10, 313, NULL, 16500.00, '2025-11-14 10:13:08', 1, 4, '2025-11-14 10:13:25', 'approved', NULL, 'slip_691700b4afc5a_2025-11-11.png'),
(286, 10, 312, NULL, 1800.00, '2025-11-14 14:33:52', 1, 4, '2025-11-14 14:35:14', 'approved', NULL, 'slip_69173dd06adf4_2025-11-11 (1).png'),
(287, 10, 314, NULL, 14700.00, '2025-11-14 14:33:52', 1, 4, '2025-11-14 14:36:11', 'approved', NULL, 'slip_69173dd06adf4_2025-11-11 (1).png'),
(288, 10, 316, NULL, 14500.00, '2025-11-14 14:51:54', 1, 4, '2025-11-14 15:06:54', 'approved', NULL, 'slip_6917420a27ab6_2025-11-11.png'),
(289, 10, 315, 'DEP2025111416062110', 8900.00, '2025-11-14 15:06:21', 1, 4, '2025-11-14 15:06:50', 'approved', NULL, 'slip_6917456d3e147_2025-11-11 (1).png'),
(290, 10, 318, 'DEP2025111416121910', 2500.00, '2025-11-14 15:12:19', 1, 4, '2025-11-14 15:25:28', 'approved', NULL, 'slip_691749d694900_IMG_20251111_110749_511.jpg'),
(291, 10, 318, 'DEP2025111416230410', 2500.00, '2025-11-14 15:23:04', 1, 4, '2025-11-14 15:23:34', 'approved', NULL, 'slip_69174958a5118_Screenshot_20251019-234513.jpg'),
(292, 10, 317, 'DEP2025111416230410', 15400.00, '2025-11-14 15:23:04', 1, 4, '2025-11-14 15:24:01', 'approved', NULL, 'slip_69174958a5118_Screenshot_20251019-234513.jpg'),
(293, 10, 320, 'DEP2025111417135310', 12100.00, '2025-11-14 16:13:53', 1, 4, '2025-11-16 08:08:47', 'approved', NULL, 'slip_69198672a372f_IMG_20251015_103342_221.jpg'),
(294, 10, 319, 'DEP2025111608345110', 10600.00, '2025-11-16 07:34:51', 1, 4, '2025-11-16 07:35:38', 'approved', NULL, 'slip_69197e9b9ddb5_IMG_20251111_110749_511.jpg'),
(295, 10, 321, 'DEP2025111608345110', 2500.00, '2025-11-16 07:34:51', 1, 4, '2025-11-16 07:36:03', 'approved', NULL, 'slip_69197e9b9ddb5_IMG_20251111_110749_511.jpg'),
(296, 10, 322, 'DEP2025111608425110', 15400.00, '2025-11-16 07:42:51', 1, 4, '2025-11-16 08:01:12', 'approved', NULL, 'slip_6919807b304c8_Screenshot_20251026-223812.jpg'),
(297, 10, 320, 'DEP2025111609055010', 12100.00, '2025-11-16 08:05:50', 1, 4, '2025-11-16 08:07:21', 'approved', NULL, 'slip_691985dec6f4d_ac45b283b879d17b7a4d59b00342ff7e.jpg'),
(298, 10, 323, 'DEP2025111609055010', 8900.00, '2025-11-16 08:05:50', 1, 4, '2025-11-16 08:07:26', 'approved', NULL, 'slip_691985dec6f4d_ac45b283b879d17b7a4d59b00342ff7e.jpg'),
(299, 10, 324, 'DEP2025111610095410', 3700.00, '2025-11-16 09:09:54', 1, 4, '2025-11-16 09:17:40', 'approved', NULL, 'slip_691996af736e0_IMG_20251116_145402_336.jpg'),
(300, 10, 324, 'DEP2025111610161510', 3700.00, '2025-11-16 09:16:15', 1, 4, '2025-11-16 09:16:48', 'approved', NULL, 'slip_6919965f43597_IMG_20251116_145402_336.jpg'),
(301, 10, 325, 'DEP2025111610161510', 8700.00, '2025-11-16 09:16:15', 1, 4, '2025-11-16 09:17:21', 'approved', NULL, 'slip_6919965f43597_IMG_20251116_145402_336.jpg'),
(302, 10, 326, 'DEP2025111613244110', 16900.00, '2025-11-16 12:24:41', 1, 4, '2025-11-16 12:24:57', 'approved', NULL, 'slip_6919c2891b08f_Screenshot_20251026-223812_1.jpg'),
(303, 10, 327, 'DEP2025111613435410', 16900.00, '2025-11-16 12:43:54', 1, 4, '2025-11-16 12:44:44', 'approved', NULL, 'slip_6919c70a0d98f_Screenshot_20251026-223815_1.jpg'),
(304, 10, 329, 'DEP2025111709130610', 17100.00, '2025-11-17 08:13:06', 1, 4, '2025-11-17 08:15:10', 'approved', NULL, 'slip_691ad984a8887_image-1763281661085.jpg'),
(305, 10, 330, 'DEP2025111808252310', 15100.00, '2025-11-18 07:25:23', 1, 4, '2025-11-18 07:32:37', 'approved', NULL, 'slip_691c1f6395693_2025-11-11 (1).png'),
(306, 10, 331, 'DEP2025111808252310', 16300.00, '2025-11-18 07:25:23', 1, 4, '2025-11-18 07:40:40', 'approved', NULL, 'slip_691c21842075b_2025-11-14.png'),
(307, 10, 333, 'DEP2025111814131010', 13700.00, '2025-11-18 07:43:10', 1, 4, '2025-11-18 07:43:39', 'approved', NULL, 'slip_691c238e6a160_Screenshot_20251116-232943.jpg'),
(308, 10, 332, 'DEP2025111814160810', 16300.00, '2025-11-18 07:46:08', 1, 4, '2025-11-18 07:47:31', 'approved', NULL, 'slip_691c2483780b7_IMG_20251116_145402_335.jpg'),
(309, 10, 334, 'DEP2025111914074510', 7200.00, '2025-11-19 07:37:45', 1, 4, '2025-11-19 07:38:04', 'approved', NULL, 'slip_691d73c955c1a_2025-11-11 (1).png'),
(310, 10, 335, 'DEP2025112015094310', 11200.00, '2025-11-20 08:39:43', 1, 4, '2025-11-20 08:39:52', 'approved', NULL, 'slip_691ed3cf088db_2025-11-14.png'),
(311, 10, 336, 'DEP2025112015094310', 5100.00, '2025-11-20 08:39:43', 1, 4, '2025-11-20 08:39:57', 'approved', NULL, 'slip_691ed3cf088db_2025-11-14.png'),
(312, 10, 340, 'DEP2025112312143310', 11700.00, '2025-11-23 05:44:33', 1, 4, '2025-11-23 06:06:00', 'approved', NULL, 'slip_69229f41d0d6e_food.jpg'),
(313, 10, 339, 'DEP2025112312143310', 7665.00, '2025-11-23 05:44:33', 1, 4, '2025-11-23 06:06:04', 'approved', NULL, 'slip_69229f41d0d6e_food.jpg'),
(314, 10, 341, 'DEP2025112312143310', 5100.00, '2025-11-23 05:44:33', 1, 4, '2025-11-23 06:06:09', 'approved', NULL, 'slip_69229f41d0d6e_food.jpg'),
(315, 9, 342, 'DEP202511231235079', 17200.00, '2025-11-23 06:05:07', 1, 4, '2025-11-23 06:05:56', 'approved', NULL, 'slip_6922a413d4c14_IMG_20251111_110749_511.jpg'),
(316, 10, 346, 'DEP2025112320471010', 11750.00, '2025-11-23 14:17:10', 1, 4, '2025-11-23 14:17:40', 'approved', NULL, 'slip_69231766060ab_photo_2025-10-17_20-28-05.jpg'),
(317, 10, 345, 'DEP2025112320471010', 13700.00, '2025-11-23 14:17:10', 1, 4, '2025-11-23 14:17:45', 'approved', NULL, 'slip_69231766060ab_photo_2025-10-17_20-28-05.jpg'),
(318, 10, 343, 'DEP2025112320471010', 13700.00, '2025-11-23 14:17:10', 1, 4, '2025-11-23 14:17:58', 'approved', NULL, 'slip_69231766060ab_photo_2025-10-17_20-28-05.jpg'),
(319, 10, 352, 'DEP2025112320471010', 12100.00, '2025-11-23 14:17:10', 1, 4, '2025-11-23 14:18:03', 'approved', NULL, 'slip_69231766060ab_photo_2025-10-17_20-28-05.jpg'),
(320, 10, 351, 'DEP2025112320471010', 12100.00, '2025-11-23 14:17:10', 1, 4, '2025-11-23 14:18:07', 'approved', NULL, 'slip_69231766060ab_photo_2025-10-17_20-28-05.jpg'),
(321, 10, 350, 'DEP2025112320471010', 11900.00, '2025-11-23 14:17:10', 1, 4, '2025-11-23 14:18:11', 'approved', NULL, 'slip_69231766060ab_photo_2025-10-17_20-28-05.jpg'),
(322, 10, 349, 'DEP2025112320471010', 11900.00, '2025-11-23 14:17:10', 1, 4, '2025-11-23 14:18:16', 'approved', NULL, 'slip_69231766060ab_photo_2025-10-17_20-28-05.jpg'),
(323, 10, 348, 'DEP2025112320471010', 12600.00, '2025-11-23 14:17:10', 1, 4, '2025-11-23 14:18:20', 'approved', NULL, 'slip_69231766060ab_photo_2025-10-17_20-28-05.jpg'),
(324, 10, 347, 'DEP2025112320471010', 13700.00, '2025-11-23 14:17:10', 1, 4, '2025-11-23 14:18:24', 'approved', NULL, 'slip_69231766060ab_photo_2025-10-17_20-28-05.jpg'),
(325, 10, 353, 'DEP2025112713275910', 14656.00, '2025-11-27 06:57:59', 1, 4, '2025-11-27 06:58:48', 'approved', NULL, 'slip_6927f677a57a7_2025-11-11 (1).png'),
(326, 10, 354, 'DEP2025112713275910', 17961.00, '2025-11-27 06:57:59', 1, 4, '2025-11-27 06:58:52', 'approved', NULL, 'slip_6927f677a57a7_2025-11-11 (1).png'),
(327, 10, 359, 'DEP2025112713275910', 16300.00, '2025-11-27 06:57:59', 1, 4, '2025-11-27 06:59:16', 'approved', NULL, 'slip_6927f677a57a7_2025-11-11 (1).png'),
(328, 10, 360, 'DEP2025112713275910', 16300.00, '2025-11-27 06:57:59', 1, 4, '2025-11-27 06:59:43', 'approved', NULL, 'slip_6927f677a57a7_2025-11-11 (1).png'),
(329, 10, 358, 'DEP2025112713275910', 18956.00, '2025-11-27 06:57:59', 1, 4, '2025-11-27 06:59:50', 'approved', NULL, 'slip_6927f677a57a7_2025-11-11 (1).png'),
(330, 10, 357, 'DEP2025112713275910', 14656.00, '2025-11-27 06:57:59', 1, 4, '2025-11-27 06:59:54', 'approved', NULL, 'slip_6927f677a57a7_2025-11-11 (1).png'),
(331, 10, 356, 'DEP2025112713275910', 11750.00, '2025-11-27 06:57:59', 1, 4, '2025-11-27 07:00:56', 'approved', NULL, 'slip_6927f71e63854_2025-11-11.png'),
(332, 10, 363, 'DEP2025112715533610', 16300.00, '2025-11-27 09:23:36', 1, 4, '2025-11-27 09:30:02', 'approved', NULL, 'slip_692819f7a418e_2025-11-11.png'),
(333, 10, 362, 'DEP2025112715574910', 14114.00, '2025-11-27 09:27:49', 1, 4, '2025-11-27 09:28:28', 'approved', NULL, 'slip_6928199568cd4_2025-11-14.png'),
(334, 10, 369, 'DEP2025113018275210', 5100.00, '2025-11-30 11:57:52', 1, 4, '2025-11-30 11:58:41', 'approved', NULL, 'slip_692c3140644e7_2025-11-11 (1).png'),
(335, 10, 368, 'DEP2025113018275210', 16300.00, '2025-11-30 11:57:52', 1, 4, '2025-11-30 11:59:17', 'approved', NULL, 'slip_692c318b4aedd_2025-11-11.png'),
(336, 10, 373, 'DEP2025121013471910', 14449.00, '2025-12-10 07:17:19', 1, 4, '2025-12-10 07:17:47', 'approved', NULL, 'slip_69391e7f3ff9e_2025-11-29 (5).png'),
(337, 10, 370, 'DEP2025121013471910', 11750.00, '2025-12-10 07:17:19', 1, 4, '2025-12-10 07:28:08', 'approved', NULL, 'slip_693920e549d48_2025-11-11 (1).png'),
(338, 10, 377, 'DEP2025121013471910', 5100.00, '2025-12-10 07:17:19', 1, 4, '2025-12-10 07:26:52', 'approved', NULL, 'slip_69391e7f3ff9e_2025-11-29 (5).png'),
(339, 10, 378, 'DEP2025121013471910', 16300.00, '2025-12-10 07:17:19', 1, 4, '2025-12-10 07:26:58', 'approved', NULL, 'slip_69391e7f3ff9e_2025-11-29 (5).png'),
(340, 10, 388, 'DEP2025121310123010', 19179.00, '2025-12-13 03:42:30', 1, 4, '2025-12-13 03:46:24', 'approved', NULL, 'slip_693ce17f803b7_IMG-9b7b0d3429f5bb6c064a8e10e06e5cfc-V.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `rider_payouts`
--

CREATE TABLE `rider_payouts` (
  `id` int(11) NOT NULL,
  `rider_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `period_start` date DEFAULT NULL,
  `period_end` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `payment_slip` varchar(255) DEFAULT NULL,
  `status` enum('pending','completed','rejected') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `released_by` int(11) DEFAULT NULL,
  `confirmed_at` datetime DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `rider_confirmed_at` timestamp NULL DEFAULT NULL,
  `rider_rejected_at` timestamp NULL DEFAULT NULL,
  `rider_rejection_reason` text DEFAULT NULL,
  `rider_notified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rider_payouts`
--

INSERT INTO `rider_payouts` (`id`, `rider_id`, `amount`, `period_start`, `period_end`, `created_at`, `updated_at`, `payment_slip`, `status`, `notes`, `released_by`, `confirmed_at`, `rejection_reason`, `rider_confirmed_at`, `rider_rejected_at`, `rider_rejection_reason`, `rider_notified`) VALUES
(1, 9, 45000.00, '2025-09-01', '2025-09-07', '2025-09-10 16:54:47', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(2, 10, 30000.00, '2025-09-01', '2025-09-07', '2025-09-10 16:54:47', '2025-12-10 01:14:05', NULL, '', '\nArchived - Resent as payout #13', NULL, NULL, NULL, NULL, '2025-12-10 01:14:05', 'asdf', 0),
(3, 10, 12500.00, '2025-10-01', '2025-10-07', '2025-10-10 19:18:39', NULL, NULL, 'rejected', NULL, NULL, NULL, NULL, NULL, '2025-12-08 11:57:36', 'this is data is worng', 0),
(4, 10, 9800.00, '2025-10-08', '2025-10-11', '2025-10-10 19:18:39', '2025-12-10 00:50:11', NULL, 'rejected', NULL, NULL, NULL, NULL, NULL, '2025-12-10 00:50:11', 'asfdsf', 0),
(5, 10, 15200.00, '2025-09-24', '2025-09-30', '2025-10-10 19:18:39', '2025-12-10 00:53:06', NULL, 'rejected', NULL, NULL, NULL, NULL, NULL, '2025-12-10 00:53:06', 'asdfdfggf', 0),
(6, 10, 17274.40, '2025-11-24', '2025-11-29', '2025-12-02 03:31:59', NULL, 'rider_slip_20251202_043159_692e5daf47ac4.png', 'rejected', 'Payout for period 2025-11-24 to 2025-11-29 - Pending confirmation | Payment slip uploaded\nResent by admin on 2025-12-04 15:18:59', 4, NULL, NULL, NULL, '2025-12-08 06:36:49', 'this is dec8 reject', 0),
(10, 10, 2800.00, '2025-11-29', '2025-11-29', '2025-12-04 08:56:17', '2025-12-13 04:41:39', 'payout_20251213_111109_693cee653a0ab.png', 'completed', 'Payout for period 2025-11-29 to 2025-11-29 - Pending confirmation | Payment slip uploaded\nRe-initiated on 2025-12-04 15:26:17 | New payment slip uploaded | Re-initiated', 4, '2025-12-13 11:11:39', NULL, '2025-12-13 04:41:39', NULL, NULL, 1),
(11, 10, 700.00, '2025-11-17', '2025-11-17', '2025-12-10 00:23:31', '2025-12-13 04:41:55', 'payout_20251213_111109_693cee653a0ab.png', 'completed', 'Payout for period 2025-11-17 to 2025-11-17 | Re-initiated', 4, '2025-12-13 11:11:55', NULL, '2025-12-13 04:41:55', NULL, NULL, 0),
(12, 10, 7000.00, '2025-11-23', '2025-11-23', '2025-12-10 00:33:17', '2025-12-13 04:41:52', 'payout_20251213_111109_693cee653a0ab.png', 'completed', 'Payout for period 2025-11-23 to 2025-11-23 | Re-initiated', 4, '2025-12-13 11:11:52', NULL, '2025-12-13 04:41:52', NULL, NULL, 0),
(13, 10, 30000.00, '2025-09-01', '2025-09-07', '2025-12-10 01:14:49', '2025-12-13 04:00:59', 'resend_20251210_074449_6938c98910c32.png', 'completed', 'Resent by admin. Original payout was rejected. New payment slip uploaded.', 4, '2025-12-13 10:30:59', NULL, '2025-12-13 04:00:59', NULL, NULL, 0),
(14, 10, 3579.00, '2025-12-12', '2025-12-12', '2025-12-13 03:59:14', '2025-12-13 04:01:07', 'payout_20251213_102914_693ce49282aba.png', 'completed', 'Payout for period 2025-12-12 to 2025-12-12', 4, '2025-12-13 10:31:07', NULL, '2025-12-13 04:01:07', NULL, NULL, 0),
(15, 10, 700.00, '2025-12-04', '2025-12-04', '2025-12-13 04:41:09', '2025-12-13 04:42:17', 'payout_20251213_111109_693cee653a0ab.png', '', 'Payout for period 2025-12-04 to 2025-12-04\nArchived - Resent as payout #27', 4, NULL, NULL, NULL, '2025-12-13 04:42:17', 'error', 0),
(16, 10, 700.00, '2025-12-03', '2025-12-03', '2025-12-13 04:41:09', '2025-12-13 04:43:39', 'payout_20251213_111109_693cee653a0ab.png', '', 'Payout for period 2025-12-03 to 2025-12-03\nArchived - Resent as payout #26', 4, NULL, NULL, NULL, '2025-12-13 04:43:39', 'i don\'t get it', 0),
(17, 10, 4099.00, '2025-11-30', '2025-11-30', '2025-12-13 04:41:09', '2025-12-13 05:12:27', 'payout_20251213_111109_693cee653a0ab.png', '', 'Payout for period 2025-11-30 to 2025-11-30\nArchived - Resent as payout #28', 4, NULL, NULL, NULL, '2025-12-13 05:12:27', 'NOt', 0),
(18, 10, 3764.00, '2025-11-27', '2025-11-27', '2025-12-13 04:41:09', '2025-12-13 05:27:08', 'payout_20251213_111109_693cee653a0ab.png', 'completed', 'Payout for period 2025-11-27 to 2025-11-27', 4, '2025-12-13 11:57:08', NULL, '2025-12-13 05:27:08', NULL, NULL, 0),
(19, 10, 8812.00, '2025-11-26', '2025-11-26', '2025-12-13 04:41:09', '2025-12-13 05:27:15', 'payout_20251213_111109_693cee653a0ab.png', 'completed', 'Payout for period 2025-11-26 to 2025-11-26', 4, '2025-12-13 11:57:15', NULL, '2025-12-13 05:27:15', NULL, NULL, 0),
(20, 10, 6917.00, '2025-11-24', '2025-11-24', '2025-12-13 04:41:09', '2025-12-13 05:27:19', 'payout_20251213_111109_693cee653a0ab.png', 'completed', 'Payout for period 2025-11-24 to 2025-11-24', 4, '2025-12-13 11:57:19', NULL, '2025-12-13 05:27:19', NULL, NULL, 0),
(21, 9, 700.00, '2025-11-23', '2025-11-23', '2025-12-13 04:41:09', NULL, 'payout_20251213_111109_693cee653a0ab.png', 'pending', 'Payout for period 2025-11-23 to 2025-11-23', 4, NULL, NULL, NULL, NULL, NULL, 0),
(22, 10, 3965.00, '2025-11-22', '2025-11-22', '2025-12-13 04:41:09', '2025-12-13 05:27:12', 'payout_20251213_111109_693cee653a0ab.png', 'completed', 'Payout for period 2025-11-22 to 2025-11-22', 4, '2025-12-13 11:57:12', NULL, '2025-12-13 05:27:12', NULL, NULL, 0),
(23, 10, 1400.00, '2025-11-20', '2025-11-20', '2025-12-13 04:41:09', '2025-12-13 04:48:59', 'payout_20251213_111109_693cee653a0ab.png', 'completed', 'Payout for period 2025-11-20 to 2025-11-20', 4, '2025-12-13 11:18:59', NULL, '2025-12-13 04:48:59', NULL, NULL, 0),
(24, 10, 700.00, '2025-11-19', '2025-11-19', '2025-12-13 04:41:09', '2025-12-13 04:48:48', 'payout_20251213_111109_693cee653a0ab.png', 'completed', 'Payout for period 2025-11-19 to 2025-11-19', 4, '2025-12-13 11:18:48', NULL, '2025-12-13 04:48:48', NULL, NULL, 0),
(25, 10, 4200.00, '2025-11-18', '2025-11-18', '2025-12-13 04:41:09', '2025-12-13 04:48:45', 'payout_20251213_111109_693cee653a0ab.png', 'completed', 'Payout for period 2025-11-18 to 2025-11-18', 4, '2025-12-13 11:18:45', NULL, '2025-12-13 04:48:45', NULL, NULL, 0),
(26, 10, 700.00, '2025-12-03', '2025-12-03', '2025-12-13 04:44:43', '2025-12-13 04:45:09', 'resend_20251213_111443_693cef3b12e74.png', 'completed', 'Resent by admin. Original payout was rejected. New payment slip uploaded.', 4, '2025-12-13 11:15:09', NULL, '2025-12-13 04:45:09', NULL, NULL, 0),
(27, 10, 700.00, '2025-12-04', '2025-12-04', '2025-12-13 04:45:38', '2025-12-13 04:48:32', 'resend_20251213_111538_693cef72770dc.png', 'completed', 'Resent by admin. Original payout was rejected. New payment slip uploaded.', 4, '2025-12-13 11:18:32', NULL, '2025-12-13 04:48:32', NULL, NULL, 0),
(28, 10, 4099.00, '2025-11-30', '2025-11-30', '2025-12-13 05:13:03', '2025-12-13 05:13:16', 'resend_20251213_114303_693cf5df78b92.png', 'completed', 'Resent by admin. Original payout was rejected. New payment slip uploaded.', 4, '2025-12-13 11:43:16', NULL, '2025-12-13 05:13:16', NULL, NULL, 0),
(29, 10, 7858.00, '2025-12-08', '2025-12-14', '2025-12-14 12:29:23', NULL, 'payout_20251214_185923_693eada33c751.jpg', 'pending', 'Payout for period 2025-12-08 to 2025-12-14', 4, NULL, NULL, NULL, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `saved_carts`
--

CREATE TABLE `saved_carts` (
  `saved_cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `status` enum('active','ordered','archived') DEFAULT 'active',
  `cart_name` varchar(100) DEFAULT 'My Cart',
  `cart_data` longtext NOT NULL COMMENT 'JSON serialized cart items',
  `item_count` int(11) DEFAULT 0,
  `total_amount` decimal(10,2) DEFAULT 0.00,
  `delivery_fee` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1 COMMENT '1=current working cart, 0=saved for later',
  `last_accessed` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `saved_carts`
--

INSERT INTO `saved_carts` (`saved_cart_id`, `user_id`, `restaurant_id`, `order_id`, `status`, `cart_name`, `cart_data`, `item_count`, `total_amount`, `delivery_fee`, `created_at`, `updated_at`, `is_active`, `last_accessed`) VALUES
(1, 5, 1, NULL, 'archived', 'Dinner', '{\"restaurant_id\":1,\"items\":{\"c4af0bb12cdcf35dc180ef6e6b59f686\":{\"id\":3,\"name\":\"Popcorn Chicken\",\"base_price\":5500,\"price\":5500,\"qty\":1,\"restaurant_id\":1,\"options\":[],\"options_key\":\"c4af0bb12cdcf35dc180ef6e6b59f686\"}},\"saved_cart_id\":1}', 1, 5500.00, 700.00, '2025-12-04 13:49:33', '2025-12-04 15:25:25', 0, '2025-12-04 13:56:17'),
(2, 5, 4, NULL, 'archived', 'Lunch', '{\"restaurant_id\":19,\"items\":{\"c555a32d258763baceff2ef94c1cf5d4\":{\"id\":86,\"name\":\"mala Xiang Guo\",\"base_price\":17000,\"price\":17000,\"qty\":1,\"restaurant_id\":19,\"options\":[],\"options_key\":\"c555a32d258763baceff2ef94c1cf5d4\"}},\"saved_cart_id\":2}', 1, 17000.00, 700.00, '2025-12-04 13:51:30', '2025-12-04 15:37:16', 1, '2025-12-04 15:28:06'),
(3, 5, 1, NULL, 'archived', 'Cart for Dec 04, 20:33', '{\"restaurant_id\":4,\"items\":{\"8ce6f2c5d6ae86eb0b1b16d3340d89f3\":{\"id\":195,\"name\":\"Mala Xiang Guo(Customize)\",\"base_price\":0,\"price\":11050,\"qty\":1,\"restaurant_id\":4,\"options\":[{\"option_id\":7,\"option_name\":\"Choice of Type for Mala Xiang Guo\",\"value_id\":7,\"value_name\":\"Soup\",\"price_modifier\":\"0.00\"},{\"option_id\":8,\"option_name\":\"Choice of Taste\",\"value_id\":9,\"value_name\":\"No Spicy and No Numbing Tingle\",\"price_modifier\":\"0.00\"},{\"option_id\":9,\"option_name\":\"Choice of Add Ons Meat Slice for Mala Xiang Guo\",\"value_id\":14,\"value_name\":\"Pork\",\"price_modifier\":\"5700.00\"},{\"option_id\":10,\"option_name\":\"Choice of Meat Ball for Mala Xiang Guo\",\"value_id\":16,\"value_name\":\"Chicken Mushroom 3 Balls\",\"price_modifier\":\"3900.00\"},{\"option_id\":11,\"option_name\":\"Choice of Vegetables for Mala Xiang Guo\",\"value_id\":28,\"value_name\":\"Corn\",\"price_modifier\":\"1450.00\"}],\"options_key\":\"8ce6f2c5d6ae86eb0b1b16d3340d89f3\"}},\"saved_cart_id\":3}', 1, 11050.00, 3605.00, '2025-12-04 14:03:11', '2025-12-04 15:25:25', 0, '2025-12-04 14:08:25'),
(4, 5, 1, NULL, 'archived', 'Cart Dec 04, 20:46', '{\"restaurant_id\":4,\"items\":{\"5d2bb65f6b161408da07c492a0eaa878\":{\"id\":194,\"name\":\"Mala Xiang Guo(Set Menu)\",\"base_price\":15600,\"price\":15600,\"qty\":1,\"restaurant_id\":4,\"options\":[],\"options_key\":\"5d2bb65f6b161408da07c492a0eaa878\"}},\"saved_cart_id\":4}', 1, 15600.00, 700.00, '2025-12-04 14:16:06', '2025-12-04 15:37:16', 1, '2025-12-04 15:26:40'),
(6, 5, 1, NULL, 'archived', 'Cart Dec 04, 21:54', '{\"restaurant_id\":1,\"items\":{\"c4af0bb12cdcf35dc180ef6e6b59f686\":{\"id\":3,\"name\":\"Popcorn Chicken\",\"base_price\":5500,\"price\":5500,\"qty\":1,\"restaurant_id\":1,\"options\":[],\"options_key\":\"c4af0bb12cdcf35dc180ef6e6b59f686\"}}}', 1, 5500.00, 700.00, '2025-12-04 15:24:35', '2025-12-04 15:37:16', 1, NULL),
(7, 5, 19, NULL, 'archived', 'Cart Dec 04, 21:58', '{\"restaurant_id\":\"19\",\"items\":{\"776a97f3232cd10c8a759bcef52ffcfb\":{\"id\":87,\"name\":\"Cola Chicken Wing\",\"base_price\":9800,\"price\":9800,\"qty\":1,\"restaurant_id\":19,\"options\":[],\"options_key\":\"776a97f3232cd10c8a759bcef52ffcfb\"}},\"delivery_fee\":700,\"saved_cart_id\":7}', 1, 9800.00, 700.00, '2025-12-04 15:28:11', '2025-12-04 16:17:48', 1, '2025-12-04 16:16:22'),
(8, 5, 4, 380, 'ordered', 'Cart Dec 04, 22:08', '{\"items\":{\"51de5298ae60bca1d0820b08e84098c9\":{\"id\":195,\"name\":\"Mala Xiang Guo(Customize)\",\"base_price\":0,\"price\":11900,\"qty\":2,\"restaurant_id\":4,\"options\":[{\"option_id\":7,\"option_name\":\"Choice of Type for Mala Xiang Guo\",\"value_id\":7,\"value_name\":\"Soup\",\"price_modifier\":0},{\"option_id\":8,\"option_name\":\"Choice of Taste\",\"value_id\":10,\"value_name\":\"Regular Spicy and Numbing Tingle \",\"price_modifier\":0},{\"option_id\":9,\"option_name\":\"Choice of Add Ons Meat Slice for Mala Xiang Guo\",\"value_id\":15,\"value_name\":\"Seafood\",\"price_modifier\":6300},{\"option_id\":10,\"option_name\":\"Choice of Meat Ball for Mala Xiang Guo\",\"value_id\":16,\"value_name\":\"Chicken Mushroom 3 Balls\",\"price_modifier\":3900},{\"option_id\":11,\"option_name\":\"Choice of Vegetables for Mala Xiang Guo\",\"value_id\":21,\"value_name\":\"Taiwan Mon Nhyinn\",\"price_modifier\":1700}],\"options_key\":\"51de5298ae60bca1d0820b08e84098c9\"}},\"delivery_fee\":700,\"restaurant_id\":4,\"saved_cart_id\":8}', 2, 23800.00, 700.00, '2025-12-04 15:38:06', '2025-12-04 16:17:48', 1, '2025-12-04 16:17:40'),
(9, 5, 1, NULL, 'archived', 'Cart Dec 04, 22:31', '{\"items\":{\"2c20b20925e16946fd600a3e2a3b8cd8\":{\"id\":220,\"name\":\"Trio Snack Box\",\"base_price\":14700,\"price\":14700,\"qty\":1,\"restaurant_id\":1,\"options\":[],\"options_key\":\"2c20b20925e16946fd600a3e2a3b8cd8\"}},\"delivery_fee\":700,\"restaurant_id\":1,\"saved_cart_id\":9}', 1, 14700.00, 700.00, '2025-12-04 16:01:10', '2025-12-04 16:17:48', 1, '2025-12-04 16:17:07'),
(10, 5, 1, NULL, 'archived', 'Cart Dec 04, 22:52', '{\"restaurant_id\":1,\"items\":{\"28586416de80afa975d65653b296a1a8\":{\"id\":14,\"name\":\"Large Fries\",\"base_price\":4400,\"price\":4400,\"qty\":1,\"restaurant_id\":1,\"options\":[],\"options_key\":\"28586416de80afa975d65653b296a1a8\"}},\"delivery_fee\":3621}', 1, 4400.00, 3621.00, '2025-12-04 16:22:45', '2025-12-04 16:24:16', 1, '2025-12-04 16:23:24'),
(11, 5, 4, 381, 'ordered', 'Cart Dec 04, 22:54', '{\"restaurant_id\":\"4\",\"items\":{\"3d0a59e51d544123fd324c694a3371e9\":{\"id\":195,\"name\":\"Mala Xiang Guo(Customize)\",\"base_price\":0,\"price\":11650,\"qty\":1,\"restaurant_id\":4,\"options\":[{\"option_id\":7,\"option_name\":\"Choice of Type for Mala Xiang Guo\",\"value_id\":7,\"value_name\":\"Soup\",\"price_modifier\":\"0.00\"},{\"option_id\":8,\"option_name\":\"Choice of Taste\",\"value_id\":9,\"value_name\":\"No Spicy and No Numbing Tingle\",\"price_modifier\":\"0.00\"},{\"option_id\":9,\"option_name\":\"Choice of Add Ons Meat Slice for Mala Xiang Guo\",\"value_id\":12,\"value_name\":\"Chicken\",\"price_modifier\":\"5700.00\"},{\"option_id\":10,\"option_name\":\"Choice of Meat Ball for Mala Xiang Guo\",\"value_id\":16,\"value_name\":\"Chicken Mushroom 3 Balls\",\"price_modifier\":\"3900.00\"},{\"option_id\":11,\"option_name\":\"Choice of Vegetables for Mala Xiang Guo\",\"value_id\":24,\"value_name\":\"Wood Ear Mushroom\",\"price_modifier\":\"2050.00\"}],\"options_key\":\"3d0a59e51d544123fd324c694a3371e9\"}},\"delivery_fee\":3605}', 1, 11650.00, 3605.00, '2025-12-04 16:24:08', '2025-12-04 16:24:38', 1, '2025-12-04 16:24:32'),
(17, 5, 2, 382, 'ordered', 'For lunch', '{\"restaurant_id\":\"2\",\"items\":{\"cf9cc787d52b43070a8d8b45c4d72af2\":{\"id\":15,\"name\":\"coffee\",\"base_price\":8000,\"price\":8000,\"qty\":1,\"restaurant_id\":2,\"options\":[],\"options_key\":\"cf9cc787d52b43070a8d8b45c4d72af2\"}},\"delivery_fee\":1400,\"saved_cart_id\":17}', 1, 8000.00, 1400.00, '2025-12-04 17:08:31', '2025-12-05 08:58:24', 1, '2025-12-05 08:41:04'),
(18, 5, 3, NULL, 'archived', 'Cart Dec 05, 12:11', '{\"restaurant_id\":\"3\",\"items\":{\"8b35edb520b808ec103705fc6a721f52\":{\"id\":82,\"name\":\"Kyay Oh Sichet Regular\",\"base_price\":8000,\"price\":10500,\"qty\":1,\"restaurant_id\":3,\"options\":[{\"option_id\":1,\"option_name\":\"Meat Type\",\"value_id\":1,\"value_name\":\"Chicken\",\"price_modifier\":\"0.00\"},{\"option_id\":2,\"option_name\":\"Addons\",\"value_id\":3,\"value_name\":\"Extra Meat Ball (Chicken)\",\"price_modifier\":\"2500.00\"}],\"options_key\":\"8b35edb520b808ec103705fc6a721f52\"}},\"delivery_fee\":700,\"saved_cart_id\":18}', 1, 10500.00, 700.00, '2025-12-05 05:41:50', '2025-12-05 08:58:24', 1, '2025-12-05 08:23:22'),
(19, 5, 4, NULL, 'archived', 'Cart Dec 05, 14:54', '{\"restaurant_id\":\"4\",\"items\":{\"5d2bb65f6b161408da07c492a0eaa878\":{\"id\":194,\"name\":\"Mala Xiang Guo(Set Menu)\",\"base_price\":15600,\"price\":15600,\"qty\":1,\"restaurant_id\":4,\"options\":[],\"options_key\":\"5d2bb65f6b161408da07c492a0eaa878\"}},\"delivery_fee\":700,\"saved_cart_id\":19}', 1, 15600.00, 700.00, '2025-12-05 08:24:32', '2025-12-05 08:58:24', 1, '2025-12-05 08:58:19'),
(20, 5, 31, NULL, 'archived', 'Cart Dec 05, 15:04', '{\"restaurant_id\":\"31\",\"items\":{\"e4e9913f0de38913c2016d11643d8578\":{\"id\":119,\"name\":\"sichuan mala\",\"base_price\":12000,\"price\":12000,\"qty\":1,\"restaurant_id\":31,\"options\":[],\"options_key\":\"e4e9913f0de38913c2016d11643d8578\"}},\"delivery_fee\":700}', 1, 12000.00, 700.00, '2025-12-05 08:34:23', '2025-12-05 08:58:24', 1, '2025-12-05 08:42:20'),
(36, 5, 1, 386, 'ordered', 'For Dinner', '{\"restaurant_id\":\"1\",\"items\":{\"711eb4513ca2041455de40d337f38152\":{\"id\":79,\"name\":\"Zinger Stacker Burger Combo\",\"base_price\":20300,\"price\":20300,\"qty\":1,\"restaurant_id\":1,\"image\":\"zingerstackercombo.png\",\"options\":[],\"options_key\":\"711eb4513ca2041455de40d337f38152\"},\"71f884312767d1249c9093a3aad9b168\":{\"id\":4,\"name\":\"Chicken Nuggets \",\"base_price\":8200,\"price\":8200,\"qty\":4,\"restaurant_id\":1,\"image\":\"chickennuggets.png\",\"options\":[],\"options_key\":\"71f884312767d1249c9093a3aad9b168\"},\"3679512eb9aed62d3ec4a3592eb9830a\":{\"id\":77,\"name\":\"Zinger Burger Combo\",\"base_price\":16200,\"price\":16200,\"qty\":1,\"restaurant_id\":1,\"image\":\"zingercombo.png\",\"options\":[],\"options_key\":\"3679512eb9aed62d3ec4a3592eb9830a\"},\"2c20b20925e16946fd600a3e2a3b8cd8\":{\"id\":220,\"name\":\"Trio Snack Box\",\"base_price\":14700,\"price\":14700,\"qty\":1,\"restaurant_id\":1,\"image\":\"69218b9396b1d.png\",\"options\":[],\"options_key\":\"2c20b20925e16946fd600a3e2a3b8cd8\"}},\"delivery_fee\":700,\"saved_cart_id\":36}', 7, 84000.00, 700.00, '2025-12-06 10:33:26', '2025-12-10 07:41:39', 1, '2025-12-10 07:11:12'),
(37, 5, 4, NULL, 'archived', 'Cart Dec 06, 17:03', '{\"restaurant_id\":\"4\",\"items\":{\"5d2bb65f6b161408da07c492a0eaa878\":{\"id\":194,\"name\":\"Mala Xiang Guo(Set Menu)\",\"base_price\":15600,\"price\":15600,\"qty\":1,\"restaurant_id\":4,\"image\":\"xiangguo.jpg\",\"options\":[],\"options_key\":\"5d2bb65f6b161408da07c492a0eaa878\"}},\"delivery_fee\":700,\"saved_cart_id\":37}', 1, 15600.00, 700.00, '2025-12-06 10:33:43', '2025-12-10 07:41:39', 1, '2025-12-07 16:41:09'),
(38, 5, 2, NULL, 'archived', 'Cart Dec 07, 15:21', '{\"restaurant_id\":\"2\",\"items\":{\"cf9cc787d52b43070a8d8b45c4d72af2\":{\"id\":15,\"name\":\"coffee\",\"base_price\":8000,\"price\":8000,\"qty\":1,\"restaurant_id\":2,\"image\":\"coffee.jfif\",\"options\":[],\"options_key\":\"cf9cc787d52b43070a8d8b45c4d72af2\"}},\"delivery_fee\":1400,\"saved_cart_id\":38}', 1, 8000.00, 0.00, '2025-12-07 08:51:44', '2025-12-10 07:41:39', 1, '2025-12-10 07:08:52'),
(40, 86, 4, 383, 'ordered', 'Cart Dec 07, 22:26', '{\"restaurant_id\":\"4\",\"items\":{\"5bb52d432846e13a917ad18f1dc29b57\":{\"id\":195,\"name\":\"Mala Xiang Guo(Customize)\",\"base_price\":0,\"price\":11050,\"qty\":1,\"restaurant_id\":4,\"image\":\"xiangguo.jpg\",\"options\":[{\"option_id\":7,\"option_name\":\"Choice of Type for Mala Xiang Guo\",\"value_id\":7,\"value_name\":\"Soup\",\"price_modifier\":\"0.00\"},{\"option_id\":8,\"option_name\":\"Choice of Taste\",\"value_id\":9,\"value_name\":\"No Spicy and No Numbing Tingle\",\"price_modifier\":\"0.00\"},{\"option_id\":9,\"option_name\":\"Choice of Add Ons Meat Slice for Mala Xiang Guo\",\"value_id\":12,\"value_name\":\"Chicken\",\"price_modifier\":\"5700.00\"},{\"option_id\":10,\"option_name\":\"Choice of Meat Ball for Mala Xiang Guo\",\"value_id\":16,\"value_name\":\"Chicken Mushroom 3 Balls\",\"price_modifier\":\"3900.00\"},{\"option_id\":11,\"option_name\":\"Choice of Vegetables for Mala Xiang Guo\",\"value_id\":20,\"value_name\":\"Mon Nhyinn Phyu\",\"price_modifier\":\"1450.00\"}],\"options_key\":\"5bb52d432846e13a917ad18f1dc29b57\"}},\"delivery_fee\":3605,\"saved_cart_id\":40}', 1, 11050.00, 3605.00, '2025-12-07 15:56:41', '2025-12-07 16:54:54', 1, '2025-12-07 16:54:47'),
(41, 86, 1, NULL, 'archived', 'Cart Dec 07, 22:31', '{\"restaurant_id\":\"1\",\"items\":{\"71f884312767d1249c9093a3aad9b168\":{\"id\":4,\"name\":\"Chicken Nuggets \",\"base_price\":8200,\"price\":8200,\"qty\":2,\"restaurant_id\":1,\"image\":\"chickennuggets.png\",\"options\":[],\"options_key\":\"71f884312767d1249c9093a3aad9b168\"},\"28586416de80afa975d65653b296a1a8\":{\"id\":14,\"name\":\"Large Fries\",\"base_price\":4400,\"price\":4400,\"qty\":3,\"restaurant_id\":1,\"image\":\"lgfries.png\",\"options\":[],\"options_key\":\"28586416de80afa975d65653b296a1a8\"},\"3679512eb9aed62d3ec4a3592eb9830a\":{\"id\":77,\"name\":\"Zinger Burger Combo\",\"base_price\":16200,\"price\":16200,\"qty\":1,\"restaurant_id\":1,\"image\":\"zingercombo.png\",\"options\":[],\"options_key\":\"3679512eb9aed62d3ec4a3592eb9830a\"},\"4b8a6055ddaeeb2bf00ede939d952252\":{\"id\":80,\"name\":\"Double Down Combo\",\"base_price\":16500,\"price\":16500,\"qty\":1,\"restaurant_id\":1,\"image\":\"doubledowncombo.png\",\"options\":[],\"options_key\":\"4b8a6055ddaeeb2bf00ede939d952252\"}},\"delivery_fee\":3621,\"saved_cart_id\":41}', 7, 62300.00, 3621.00, '2025-12-07 16:01:20', '2025-12-07 16:54:54', 1, '2025-12-07 16:54:27'),
(42, 86, 2, NULL, 'archived', 'Cart Dec 07, 23:06', '{\"restaurant_id\":\"2\",\"items\":{\"cf9cc787d52b43070a8d8b45c4d72af2\":{\"id\":15,\"name\":\"coffee\",\"base_price\":8000,\"price\":8000,\"qty\":1,\"restaurant_id\":2,\"image\":\"coffee.jfif\",\"options\":[],\"options_key\":\"cf9cc787d52b43070a8d8b45c4d72af2\"}},\"delivery_fee\":2784,\"saved_cart_id\":\"42\"}', 1, 8000.00, 2784.00, '2025-12-07 16:36:40', '2025-12-07 16:54:54', 1, '2025-12-07 16:36:50'),
(43, 86, 1, 384, 'ordered', 'Cart Dec 07, 23:31', '{\"restaurant_id\":\"1\",\"items\":{\"71f884312767d1249c9093a3aad9b168\":{\"id\":4,\"name\":\"Chicken Nuggets \",\"base_price\":8200,\"price\":8200,\"qty\":2,\"restaurant_id\":1,\"image\":\"chickennuggets.png\",\"options\":[],\"options_key\":\"71f884312767d1249c9093a3aad9b168\"},\"28586416de80afa975d65653b296a1a8\":{\"id\":14,\"name\":\"Large Fries\",\"base_price\":4400,\"price\":4400,\"qty\":3,\"restaurant_id\":1,\"image\":\"lgfries.png\",\"options\":[],\"options_key\":\"28586416de80afa975d65653b296a1a8\"},\"3679512eb9aed62d3ec4a3592eb9830a\":{\"id\":77,\"name\":\"Zinger Burger Combo\",\"base_price\":16200,\"price\":16200,\"qty\":1,\"restaurant_id\":1,\"image\":\"zingercombo.png\",\"options\":[],\"options_key\":\"3679512eb9aed62d3ec4a3592eb9830a\"},\"4b8a6055ddaeeb2bf00ede939d952252\":{\"id\":80,\"name\":\"Double Down Combo\",\"base_price\":16500,\"price\":16500,\"qty\":1,\"restaurant_id\":1,\"image\":\"doubledowncombo.png\",\"options\":[],\"options_key\":\"4b8a6055ddaeeb2bf00ede939d952252\"}},\"delivery_fee\":700,\"saved_cart_id\":43}', 7, 62300.00, 700.00, '2025-12-07 17:01:59', '2025-12-07 17:07:13', 1, '2025-12-07 17:07:08'),
(44, 86, 4, NULL, 'active', 'Cart Dec 07, 23:38', '{\"restaurant_id\":\"4\",\"items\":{\"1bb26147ac2792a5db991b565c0de090\":{\"id\":195,\"name\":\"Mala Xiang Guo(Customize)\",\"base_price\":0,\"price\":11050,\"qty\":1,\"restaurant_id\":4,\"image\":\"xiangguo.jpg\",\"options\":[{\"option_id\":7,\"option_name\":\"Choice of Type for Mala Xiang Guo\",\"value_id\":7,\"value_name\":\"Soup\",\"price_modifier\":\"0.00\"},{\"option_id\":8,\"option_name\":\"Choice of Taste\",\"value_id\":11,\"value_name\":\"More Spicy and Numbing Tingle\",\"price_modifier\":\"0.00\"},{\"option_id\":9,\"option_name\":\"Choice of Add Ons Meat Slice for Mala Xiang Guo\",\"value_id\":12,\"value_name\":\"Chicken\",\"price_modifier\":\"5700.00\"},{\"option_id\":10,\"option_name\":\"Choice of Meat Ball for Mala Xiang Guo\",\"value_id\":16,\"value_name\":\"Chicken Mushroom 3 Balls\",\"price_modifier\":\"3900.00\"},{\"option_id\":11,\"option_name\":\"Choice of Vegetables for Mala Xiang Guo\",\"value_id\":20,\"value_name\":\"Mon Nhyinn Phyu\",\"price_modifier\":\"1450.00\"}],\"options_key\":\"1bb26147ac2792a5db991b565c0de090\"}},\"delivery_fee\":700}', 1, 11050.00, 700.00, '2025-12-07 17:08:05', '2025-12-07 17:08:05', 1, '2025-12-07 17:08:05'),
(45, 5, 23, NULL, 'archived', 'Cart Dec 10, 14:11', '{\"restaurant_id\":\"23\",\"items\":{\"fc9dddd7113d1bd34942eef0f1bc81ae\":{\"id\":50,\"name\":\"Mango Smoothie\",\"base_price\":6800,\"price\":6800,\"qty\":1,\"restaurant_id\":23,\"image\":\"mango-smoothie.jpg\",\"options\":[],\"options_key\":\"fc9dddd7113d1bd34942eef0f1bc81ae\"}},\"delivery_fee\":1400}', 1, 6800.00, 1400.00, '2025-12-10 07:41:22', '2025-12-10 07:41:39', 1, '2025-12-10 07:41:22'),
(46, 5, 23, 387, 'ordered', 'Cart Dec 10, 15:07', '{\"restaurant_id\":\"23\",\"items\":{\"965fa730a20fb093f0f852f9b44ff2c8\":{\"id\":163,\"name\":\"Bubble Milk Tea\",\"base_price\":3000,\"price\":3000,\"qty\":1,\"restaurant_id\":23,\"image\":\"bubblemilk-tea.jpg\",\"options\":[],\"options_key\":\"965fa730a20fb093f0f852f9b44ff2c8\"},\"37c0a0965183cb3b7c092be2d1a07d7c\":{\"id\":165,\"name\":\"Double Chocolate\",\"base_price\":6800,\"price\":6800,\"qty\":1,\"restaurant_id\":23,\"image\":\"ocha-double-chocolate.jpg\",\"options\":[],\"options_key\":\"37c0a0965183cb3b7c092be2d1a07d7c\"},\"8493f150effeb5d32417e9f3a55b5e2f\":{\"id\":49,\"name\":\"Bubble Milk Tea\",\"base_price\":3000,\"price\":3000,\"qty\":1,\"restaurant_id\":23,\"image\":\"bubblemilk-tea.jpg\",\"options\":[{\"option_id\":6,\"option_name\":\"Sugar Level\",\"value_id\":5,\"value_name\":\"High\",\"price_modifier\":\"0.00\"}],\"options_key\":\"8493f150effeb5d32417e9f3a55b5e2f\"}},\"delivery_fee\":1400,\"saved_cart_id\":46}', 3, 12800.00, 1400.00, '2025-12-10 08:37:01', '2025-12-10 09:07:42', 1, '2025-12-10 09:07:28'),
(47, 5, 20, NULL, 'archived', 'Cart Dec 10, 15:19', '{\"restaurant_id\":\"20\",\"items\":{\"dd6378255f17bcc1d0b67addbfb5dd17\":{\"id\":190,\"name\":\"Dry Kyay Oh Salad\",\"base_price\":10800,\"price\":10800,\"qty\":1,\"restaurant_id\":20,\"image\":\"kob2.png\",\"options\":[],\"options_key\":\"dd6378255f17bcc1d0b67addbfb5dd17\"}},\"delivery_fee\":700,\"saved_cart_id\":47}', 1, 10800.00, 700.00, '2025-12-10 08:49:47', '2025-12-10 09:07:42', 1, '2025-12-10 09:01:15'),
(48, 5, 31, NULL, 'archived', 'Cart Dec 11, 11:51', '{\"restaurant_id\":\"31\",\"items\":{\"e4e9913f0de38913c2016d11643d8578\":{\"id\":119,\"name\":\"sichuan mala\",\"base_price\":12000,\"price\":12000,\"qty\":1,\"restaurant_id\":31,\"image\":\"lat-1.jpg\",\"options\":[],\"options_key\":\"e4e9913f0de38913c2016d11643d8578\"}},\"delivery_fee\":700,\"saved_cart_id\":48}', 1, 12000.00, 700.00, '2025-12-11 05:21:31', '2025-12-12 16:59:02', 1, '2025-12-12 16:57:18'),
(49, 5, 1, NULL, 'archived', 'Cart Dec 12, 23:19', '{\"restaurant_id\":\"1\",\"items\":{\"71f884312767d1249c9093a3aad9b168\":{\"id\":4,\"name\":\"Chicken Nuggets \",\"base_price\":8200,\"price\":8200,\"qty\":1,\"restaurant_id\":1,\"image\":\"chickennuggets.png\",\"options\":[],\"options_key\":\"71f884312767d1249c9093a3aad9b168\"}},\"delivery_fee\":3601,\"saved_cart_id\":49}', 1, 8200.00, 3601.00, '2025-12-12 16:49:42', '2025-12-12 16:59:02', 1, '2025-12-12 16:56:37'),
(50, 5, 4, 388, 'ordered', 'Cart Dec 12, 23:25', '{\"restaurant_id\":\"4\",\"items\":{\"5d2bb65f6b161408da07c492a0eaa878\":{\"id\":194,\"name\":\"Mala Xiang Guo(Set Menu)\",\"base_price\":15600,\"price\":15600,\"qty\":1,\"restaurant_id\":4,\"image\":\"xiangguo.jpg\",\"options\":[],\"options_key\":\"5d2bb65f6b161408da07c492a0eaa878\"}},\"delivery_fee\":3579,\"saved_cart_id\":50}', 1, 15600.00, 3579.00, '2025-12-12 16:55:48', '2025-12-12 16:59:02', 1, '2025-12-12 16:58:35'),
(51, 5, 1, 389, 'ordered', 'Cart Dec 13, 12:25', '{\"restaurant_id\":\"1\",\"items\":{\"28586416de80afa975d65653b296a1a8\":{\"id\":14,\"name\":\"Large Fries\",\"base_price\":4400,\"price\":4400,\"qty\":1,\"restaurant_id\":1,\"image\":\"lgfries.png\",\"options\":[],\"options_key\":\"28586416de80afa975d65653b296a1a8\"}},\"delivery_fee\":700,\"saved_cart_id\":51}', 1, 4400.00, 700.00, '2025-12-13 05:55:35', '2025-12-13 05:58:22', 1, '2025-12-13 05:57:06'),
(52, 5, 4, 391, 'ordered', 'Cart Dec 13, 12:38', '{\"restaurant_id\":\"4\",\"items\":{\"5d2bb65f6b161408da07c492a0eaa878\":{\"id\":194,\"name\":\"Mala Xiang Guo(Set Menu)\",\"base_price\":15600,\"price\":15600,\"qty\":1,\"restaurant_id\":4,\"image\":\"xiangguo.jpg\",\"options\":[],\"options_key\":\"5d2bb65f6b161408da07c492a0eaa878\"},\"5bb52d432846e13a917ad18f1dc29b57\":{\"id\":195,\"name\":\"Mala Xiang Guo(Customize)\",\"base_price\":0,\"price\":11050,\"qty\":1,\"restaurant_id\":4,\"image\":\"xiangguo.jpg\",\"options\":[{\"option_id\":7,\"option_name\":\"Choice of Type for Mala Xiang Guo\",\"value_id\":7,\"value_name\":\"Soup\",\"price_modifier\":\"0.00\"},{\"option_id\":8,\"option_name\":\"Choice of Taste\",\"value_id\":9,\"value_name\":\"No Spicy and No Numbing Tingle\",\"price_modifier\":\"0.00\"},{\"option_id\":9,\"option_name\":\"Choice of Add Ons Meat Slice for Mala Xiang Guo\",\"value_id\":12,\"value_name\":\"Chicken\",\"price_modifier\":\"5700.00\"},{\"option_id\":10,\"option_name\":\"Choice of Meat Ball for Mala Xiang Guo\",\"value_id\":16,\"value_name\":\"Chicken Mushroom 3 Balls\",\"price_modifier\":\"3900.00\"},{\"option_id\":11,\"option_name\":\"Choice of Vegetables for Mala Xiang Guo\",\"value_id\":20,\"value_name\":\"Mon Nhyinn Phyu\",\"price_modifier\":\"1450.00\"}],\"options_key\":\"5bb52d432846e13a917ad18f1dc29b57\"}},\"delivery_fee\":3579,\"saved_cart_id\":52}', 2, 26650.00, 3579.00, '2025-12-13 06:08:58', '2025-12-14 08:48:20', 1, '2025-12-14 08:48:02'),
(53, 5, 4, NULL, 'active', 'Cart Dec 14, 16:16', '{\"restaurant_id\":\"4\",\"items\":{\"5d2bb65f6b161408da07c492a0eaa878\":{\"id\":194,\"name\":\"Mala Xiang Guo(Set Menu)\",\"base_price\":15600,\"price\":15600,\"qty\":1,\"restaurant_id\":4,\"image\":\"xiangguo.jpg\",\"options\":[],\"options_key\":\"5d2bb65f6b161408da07c492a0eaa878\"}},\"delivery_fee\":700}', 1, 15600.00, 700.00, '2025-12-14 09:46:36', '2025-12-14 09:46:36', 1, '2025-12-14 09:46:36'),
(54, 94, 4, 393, 'ordered', 'Cart Dec 14, 16:29', '{\"restaurant_id\":\"4\",\"items\":{\"5d2bb65f6b161408da07c492a0eaa878\":{\"id\":194,\"name\":\"Mala Xiang Guo(Set Menu)\",\"base_price\":15600,\"price\":15600,\"qty\":1,\"restaurant_id\":4,\"image\":\"xiangguo.jpg\",\"options\":[],\"options_key\":\"5d2bb65f6b161408da07c492a0eaa878\"}},\"delivery_fee\":700,\"saved_cart_id\":54}', 1, 15600.00, 700.00, '2025-12-14 09:59:58', '2025-12-14 13:59:00', 1, '2025-12-14 13:58:22'),
(55, 94, 1, NULL, 'active', 'Cart Dec 14, 20:30', '{\"restaurant_id\":\"1\",\"items\":{\"c4af0bb12cdcf35dc180ef6e6b59f686\":{\"id\":3,\"name\":\"Popcorn Chicken\",\"base_price\":5500,\"price\":5500,\"qty\":1,\"restaurant_id\":1,\"image\":\"popcornchicken.png\\r\\n\",\"options\":[],\"options_key\":\"c4af0bb12cdcf35dc180ef6e6b59f686\"}},\"delivery_fee\":700}', 1, 5500.00, 700.00, '2025-12-14 14:00:54', '2025-12-14 14:00:54', 1, '2025-12-14 14:00:54'),
(56, 94, 4, NULL, 'active', 'Cart Dec 14, 20:31', '{\"restaurant_id\":\"4\",\"items\":{\"5d2bb65f6b161408da07c492a0eaa878\":{\"id\":194,\"name\":\"Mala Xiang Guo(Set Menu)\",\"base_price\":15600,\"price\":15600,\"qty\":1,\"restaurant_id\":4,\"image\":\"xiangguo.jpg\",\"options\":[],\"options_key\":\"5d2bb65f6b161408da07c492a0eaa878\"}},\"delivery_fee\":700,\"saved_cart_id\":56}', 1, 15600.00, 700.00, '2025-12-14 14:01:13', '2025-12-14 14:03:41', 1, '2025-12-14 14:03:41');

-- --------------------------------------------------------

--
-- Table structure for table `settlement_notifications`
--

CREATE TABLE `settlement_notifications` (
  `id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `settlement_id` int(11) NOT NULL,
  `status` enum('pending','confirmed','rejected') DEFAULT 'pending',
  `rejection_reason` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `confirmed_at` datetime DEFAULT NULL,
  `rejected_at` datetime DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  `week_no` int(11) DEFAULT NULL,
  `payment_slip` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settlement_notifications`
--

INSERT INTO `settlement_notifications` (`id`, `restaurant_id`, `settlement_id`, `status`, `rejection_reason`, `created_at`, `confirmed_at`, `rejected_at`, `amount`, `week_no`, `payment_slip`) VALUES
(1, 4, 11, 'confirmed', NULL, '2025-11-12 15:10:24', '2025-11-12 15:25:23', NULL, 11700.00, 202544, NULL),
(2, 4, 12, 'confirmed', 'asdfghj', '2025-11-23 17:44:32', '2025-11-24 14:15:32', '2025-11-23 18:07:57', 11700.00, 202542, 'reprocessed_20251123_121432_6922ec9857a01.png'),
(3, 4, 13, 'confirmed', NULL, '2025-11-12 15:37:10', '2025-11-12 15:45:56', NULL, 140400.00, 202541, NULL),
(4, 1, 14, 'rejected', 'asdghjkl', '2025-11-12 15:52:18', NULL, '2025-11-12 15:52:36', 1350.00, 202542, NULL),
(5, 1, 15, 'confirmed', 'akledfmddf;lg', '2025-11-13 13:11:27', '2025-11-24 15:16:19', '2025-11-23 11:30:14', 1350.00, 202546, NULL),
(6, 1, 16, 'pending', NULL, '2025-11-13 13:13:54', NULL, NULL, 42525.00, 202541, NULL),
(7, 4, 17, 'confirmed', NULL, '2025-11-13 13:50:06', '2025-11-23 16:54:33', NULL, 11700.00, 202543, NULL),
(8, 4, 18, 'confirmed', NULL, '2025-11-21 12:02:39', '2025-11-24 14:15:29', NULL, 23400.00, 202546, NULL),
(9, 2, 21, 'pending', NULL, '2025-11-21 12:30:05', NULL, NULL, 6000.00, 202541, NULL),
(10, 4, 23, 'confirmed', NULL, '2025-11-23 17:36:09', '2025-12-13 19:28:40', NULL, 90.35, 202547, 'reprocessed_20251123_120609_6922eaa17b9a4.png'),
(11, 2, 24, 'pending', NULL, '2025-11-23 00:31:41', NULL, NULL, 114.12, 202547, NULL),
(12, 1, 25, 'rejected', 'kesdfjd;jfk', '2025-11-23 00:51:37', NULL, '2025-11-23 11:30:04', 107.42, 202547, NULL),
(13, 4, 27, 'confirmed', 'so so so', '2025-11-24 12:55:57', '2025-12-13 19:28:43', '2025-11-24 12:56:14', 23400.00, 202542, 'reprocessed_20251124_072557_6923fa758138b.png'),
(14, 1, 28, 'pending', NULL, '2025-12-13 19:42:18', NULL, NULL, 2700.00, 202542, 'reprocessed_20251213_194218_693d663220455.png'),
(15, 1, 29, 'rejected', 'zsdfghj', '2025-11-23 11:30:55', NULL, '2025-11-23 12:19:29', 12971.54, 202547, NULL),
(16, 1, 30, 'pending', NULL, '2025-11-23 11:31:56', NULL, NULL, 13725.00, 202546, NULL),
(17, 1, 31, 'rejected', 'asddlll;', '2025-11-23 12:20:32', NULL, '2025-11-23 12:27:35', 12971.54, 202547, NULL),
(18, 1, 32, 'pending', NULL, '2025-11-23 12:27:55', NULL, NULL, 12971.54, 202547, NULL),
(19, 1, 33, 'pending', NULL, '2025-11-23 12:35:03', NULL, NULL, 12971.54, 202547, NULL),
(20, 4, 34, 'confirmed', NULL, '2025-11-23 16:16:02', '2025-11-23 16:24:31', NULL, 180.69, 202547, NULL),
(21, 4, 35, 'rejected', 'assss', '2025-11-23 18:09:13', NULL, '2025-11-23 18:20:13', 23400.00, 202542, NULL),
(22, 4, 36, 'pending', NULL, '2025-11-24 12:43:58', NULL, NULL, 11700.00, 202548, 'reprocessed_20251124_071103_6923f6f7edcb2.png'),
(23, 4, 37, 'rejected', 'this is invalid', '2025-11-24 12:44:15', NULL, '2025-11-24 12:44:21', 11700.00, 202548, 'reprocessed_20251124_071415_6923f7b71bbd9.png'),
(24, 4, 38, 'pending', NULL, '2025-11-24 12:55:15', NULL, NULL, 23400.00, 202542, NULL),
(26, 4, 22, 'confirmed', NULL, '2025-11-30 18:50:19', '2025-11-30 18:52:55', NULL, 35100.00, 202544, 'reprocessed_20251130_132019_692c36839173b.png'),
(27, 4, 23, 'confirmed', NULL, '2025-12-01 15:09:26', '2025-12-13 19:28:40', NULL, 11700.00, 202546, ''),
(28, 1, 24, 'pending', NULL, '2025-12-01 15:10:14', NULL, NULL, 11025.00, 202545, ''),
(29, 4, 25, 'confirmed', NULL, '2025-12-08 13:20:20', '2025-12-13 19:28:38', NULL, 11700.00, 202549, ''),
(30, 1, 26, 'pending', NULL, '2025-12-09 15:50:58', NULL, NULL, 3300.00, 202549, ''),
(31, 4, 27, 'confirmed', NULL, '2025-12-10 13:45:29', '2025-12-13 19:28:43', NULL, 58500.00, 202545, ''),
(32, 4, 28, 'rejected', 'invalid', '2025-12-13 19:42:18', NULL, '2025-12-13 19:42:30', 11700.00, 202550, 'reprocessed_20251213_194218_693d663220455.png'),
(33, 25, 29, 'pending', NULL, '2025-12-13 19:29:59', NULL, NULL, 13875.00, 202546, ''),
(34, 4, 30, 'pending', NULL, '2025-12-13 19:38:24', NULL, NULL, 108525.00, 202548, '');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','vendor','admin','delivery') NOT NULL DEFAULT 'customer',
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `account_locked_until` datetime DEFAULT NULL,
  `login_attempts` int(11) NOT NULL,
  `last_failed_attempt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `phone`, `password`, `role`, `address`, `created_at`, `is_verified`, `account_locked_until`, `login_attempts`, `last_failed_attempt`) VALUES
(1, 'Alice Customer', 'alice@example.com', '09254078011', '$2y$10$OndTUrKGlZSorTcvW8uPxeNN8vhMMmGHrmZSQ8p594VaI.SB65NSm', 'customer', 'Yangon', '2025-08-25 12:40:50', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(2, 'Victor Vendor', 'vendor@example.com', '09777433350', '$2y$10$OndTUrKGlZSorTcvW8uPxeNN8vhMMmGHrmZSQ8p594VaI.SB65NSm', 'vendor', 'Yangon', '2025-08-25 12:40:50', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(3, 'Danny Delivery', 'delivery@example.com', '09232322229', '$2y$10$OndTUrKGlZSorTcvW8uPxeNN8vhMMmGHrmZSQ8p594VaI.SB65NSm', 'delivery', 'Yangon', '2025-08-25 12:40:50', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(4, 'Main Admin', 'foodnmea@gmail.com', '09421008644', '$2y$10$wtZ7uWFuTsRP.2xxOot0L.JGQ3HdrbKojGyPKFYgFdviZY9tVmUIW', 'admin', 'Yangon', '2025-08-25 12:40:50', 1, NULL, 0, NULL),
(5, 'Hla Myo Hein', 'hlamyohein081@gmail.com', '09774856854', '$2y$10$lTD7OfQxhBQTLaTq/2/y/eQLXay1TeVn17w6pLeljaaI79FOD9O0a', 'customer', 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', '2025-08-25 14:43:31', 1, NULL, 0, NULL),
(8, 'Thuya', 'hmh123@gmail.com', '09262007800', '$2y$10$ZsEwLWk1tZjHcAF1pLR2kury/ql8nZthHqJ.s.HCiuJSIGIQn9Aai', 'customer', 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', '2025-08-26 07:28:12', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(9, 'hla myo ', 'deli123@gmail.com', '09773493439', '$2y$10$OndTUrKGlZSorTcvW8uPxeNN8vhMMmGHrmZSQ8p594VaI.SB65NSm', 'delivery', 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', '2025-08-26 08:50:19', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(10, 'Kyaw Gyi', 'minmin121@gmail.com', '09421084701', '$2y$10$7AIik9OdrduyCNpV6qCKRO9LE3JPPbZ1XYfdJtSx7NDBNYuJSeDay', 'delivery', 'Yangon', '2025-08-27 04:13:31', 1, NULL, 0, NULL),
(11, 'Aye Aye Myat', 'myat@gmail.com', '09775689833', '$2y$10$BVAiaiwm.3e8sV9o7oApmOK8kkSCwO7O81a5Pgn29B1O9QR4chCSi', 'vendor', 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', '2025-08-27 06:40:19', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(12, 'Kaung Htet', 'kaung@gmail.com', '09214748364', '$2y$10$93m.0K7MUdGoW.GQDh4Ds.rfGoMw64iPcQwOM61BlLzOZzg0KZdVe', 'customer', 'Hlaing, Maubin, Ayeyarwady, Myanmar', '2025-08-29 13:14:15', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(13, 'Hsu Lad Pyae', 'hmh081@gmail.com', '09774000332', '$2y$10$jl.5QUay3j5O5F6JFN36hukWsmudqVNAuXQ8tBXgMuyiYF1osowte', 'admin', 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', '2025-08-31 07:43:38', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(15, 'Vendor', 'vendor@gmail.com', '09458712399', '$2y$10$BVAiaiwm.3e8sV9o7oApmOK8kkSCwO7O81a5Pgn29B1O9QR4chCSi', 'vendor', 'Tat Phwet Street, Thaketa, Botahtaung District, Yangon City, Yangon, 11231, Myanmar', '2025-08-31 11:33:30', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(16, 'Yee Yee San', 'san@gmail.com', '09251020414', '$2y$10$4bW1l8aPgfKfdeBF.5OUQOvOftLZoOKYKqbWkX7o6Ic4WbI3x4tmi', 'vendor', 'Avenue Wine Bar & Bistro, New University Avenue Road, Bahan, Kamayut District, Yangon City, Yangon, 11201, Myanmar', '2025-09-02 09:14:19', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(27, 'Ye Min Thu', 'ridertesting@gmail.com', '09452080422', '$2y$10$/FnYjP7RasucctwJdATPg.XMlkLnvvLrnpu0UhFdG45l4/q9DWIEi', 'vendor', 'Botahtaung,Yangon', '2025-09-18 14:27:04', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(34, 'Hla Myo Hein', 'riderdeli@gmail.com', '09897898631', '$2y$10$Er.srYruEaRP.N65J5a5C.WHPOC..xsUsjHWJ.qZm8XG2WwQxTMMm', 'vendor', 'Thaketa,Yangon', '2025-09-18 14:34:50', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(35, 'hla hla ', 'hlamyo@gmail.com', '09880422213', '$2y$10$pQYjUpsoPmZGfYOZUZ1wWeG5fq6qqcl9zklmD2QH0s..2bdaEqBy6', 'customer', 'Yangon', '2025-09-18 14:37:02', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(36, 'Hla Myo Hein', 'hlamyohein0811@gmail.com', '09254084705', '$2y$10$6wYFdNuiRXWxb4JJIa5pTOsNu5pbsLpJfBaM8z6eKOBE0sfcGKqO6', 'delivery', 'Yangon', '2025-09-19 06:26:59', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(37, 'Hla Myo Hein', 'hlamyohein08112@gmail.com', '09458078911', '$2y$10$FuMlgvAB5CIHxFDJa4TZ5OvfwgTZvGQsXkgZBwvqJmRCJfYDQCux2', 'delivery', 'Yangon', '2025-09-19 07:57:15', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(41, 'Rider', 'hlamyohein112@gmail.com', '09774326100', '$2y$10$XftRRfKAs6OSkP4NU1g/qukZLv6t3Yc0hyx6g7L73gh6XYMSEqK66', 'delivery', 'Yangon', '2025-09-19 09:05:06', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(43, 'Hla Myo', 'hlamyohein08112222@gmail.com', '09792156738', '$2y$10$21GQz1yA529e3E9z3E3KmuzDNaqVM5vI7AKjEbjA304BEubJOzMdW', 'delivery', 'Yangon', '2025-09-19 09:22:50', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(44, 'Kyaw Gyi', 'kyaw@gmail.com', '09880221567', '$2y$10$Njik5O9A.Zgw/wIcoRcj2eZHnSFPASEq/uFqY.tZ/Af1vx/GHHloy', 'delivery', 'Yangon', '2025-09-20 11:34:52', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(45, 'Hla Myo Hein', 'hlamy@gmail.com', '09880332784', '$2y$10$TDaFEEuCU2TNxYau.xr4WOIJQdFAPrQvMotpKH.whNI8HJ2ka.VbW', 'delivery', 'Yangon', '2025-09-23 06:40:49', 0, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(48, 'Hla', 'hlahla@gmail.com', '09777258211', '$2y$10$buk1QXqOeAyWbN4XjtoqVetBjyhr4nXUPCShMwtK4cNmjC8Zm/QVi', 'delivery', 'Yangon', '2025-09-23 07:24:19', 0, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(50, 'Sabel San', 'sabelsan2114@gmail.com', '09777468115', '$2y$10$WmssYq4fiQRPwX9LeOoU4eJ3RsOV5Q33Tpx68QCEy05XrFu/cEN0K', 'vendor', 'Yangon', '2025-10-05 13:12:01', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(57, 'Testing', 'aye@gmail.com', '09774856853', '$2y$10$.IC0uJgqu7B8Ff5yDTNlLeuSMoSY.uoObYP4mZxcF1BCBmDsBe6Ie', 'vendor', 'Yangon', '2025-10-08 08:19:42', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(58, 'BUIDIN B', 'buildingb@gmail.com', '09251010109', '$2y$10$.IC0uJgqu7B8Ff5yDTNlLeuSMoSY.uoObYP4mZxcF1BCBmDsBe6Ie', 'vendor', 'Yangon', '2025-11-05 07:06:04', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(59, 'Liam mak', 'myataung242@gmail.com', '09759748055', '$2y$10$/NN2V.4efHxh0GEpc6PHCuqUvN2mMhF8wrrpmCJ19UPvwhjqSXO0u', 'customer', 'Hlaing,Yangon', '2025-11-05 01:09:18', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(60, 'Potato Corner', 'potato@gmail.com', '09767678787', '$2y$10$OndTUrKGlZSorTcvW8uPxeNN8vhMMmGHrmZSQ8p594VaI.SB65NSm', 'vendor', 'Yangon', '2025-11-05 01:13:59', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(61, 'Pezzo', 'pezzo@gmail.com', '09787878787', '$2y$10$OndTUrKGlZSorTcvW8uPxeNN8vhMMmGHrmZSQ8p594VaI.SB65NSm', 'vendor', 'Yangon', '2025-11-05 01:13:59', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(62, 'ChaTraMue', 'chatramue@gmail.com', '09976543278', '$2y$10$OndTUrKGlZSorTcvW8uPxeNN8vhMMmGHrmZSQ8p594VaI.SB65NSm', 'vendor', 'Yangon', '2025-11-05 02:15:50', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(63, 'Omuk', 'omuk@gmail.com', '09965643123', '$2y$10$OndTUrKGlZSorTcvW8uPxeNN8vhMMmGHrmZSQ8p594VaI.SB65NSm', 'vendor', 'Yangon', '2025-11-05 02:15:50', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(64, 'Shwe Pu Zun', 'shwepuzun@gmail.com', '09789765567', '$2y$10$OndTUrKGlZSorTcvW8uPxeNN8vhMMmGHrmZSQ8p594VaI.SB65NSm', 'vendor', 'Yangon', '2025-11-05 07:19:46', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(65, 'FUDO', 'fudo@gmail.com', '09675428970', '$2y$10$OndTUrKGlZSorTcvW8uPxeNN8vhMMmGHrmZSQ8p594VaI.SB65NSm', 'vendor', 'Yangon', '2025-11-05 07:19:46', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(66, 'Taw Win', 'tawwin@gmail.com', '09567849356', '$2y$10$OndTUrKGlZSorTcvW8uPxeNN8vhMMmGHrmZSQ8p594VaI.SB65NSm', 'vendor', 'Yangon', '2025-11-05 07:45:36', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(67, 'DaNuPhyu', 'danuphyu@gmail.com', '09878909876', '$2y$10$OndTUrKGlZSorTcvW8uPxeNN8vhMMmGHrmZSQ8p594VaI.SB65NSm', 'vendor', 'Yangon', '2025-11-05 07:45:36', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(68, 'SKK', 'skk@gmail.com', '09989706054', '$2y$10$OndTUrKGlZSorTcvW8uPxeNN8vhMMmGHrmZSQ8p594VaI.SB65NSm', 'vendor', 'Yangon', '2025-11-05 09:53:42', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(69, 'Momoyo', 'momoyo@gmail.com', '09976702456', '$2y$10$OndTUrKGlZSorTcvW8uPxeNN8vhMMmGHrmZSQ8p594VaI.SB65NSm', 'vendor', 'Yangon', '2025-11-05 22:20:24', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(70, 'Ye Yint Minn', 'yinty5602@gmail.com', '09767046280', '$2y$10$2KHoT5LOdgFEKDkQcsNa5uEOc6PWfeHHgK5GdD/4NpwtgdzbcSQIe', 'customer', 'HLaing ,Yangon', '2025-11-05 00:57:47', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(72, 'Omuk', 'omukk@gmail.com', '09774856323', '$2y$10$BFhoV0TRynr5NhRmMmevke/br.BbpFV8TvONl18160DkhetJB3SvG', 'vendor', 'Yangon', '2025-11-14 16:05:24', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(73, 'Min Lann', 'minlann@gmail.com', '09880333399', '$2y$10$BFhoV0TRynr5NhRmMmevke/br.BbpFV8TvONl18160DkhetJB3SvG', 'vendor', 'Yangon', '2025-11-16 08:53:09', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(74, 'Testing', 'monhinnnga@gmail.com', '09969173010', '$2y$10$iCO4Mn38tsLUNjFMIUHT/.lejFp9K0ngn207h4ZXBHzqnCxPZMVtO', 'vendor', 'Yangon', '2025-11-22 07:02:44', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(75, 'Restaurant', 'testingrestaurant@gmail.com', '09774856853', '$2y$10$vcOzsvae/UZ1NBBwS.DSAOg8sIuJgFDKxOZA8z21iBXG8U4JCxSs2', 'vendor', 'Yangon', '2025-11-22 09:02:15', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(76, 'Swell Vendor', 'swell@gmail.com', '09123456789', '$2y$10$BFhoV0TRynr5NhRmMmevke/br.BbpFV8TvONl18160DkhetJB3SvG', 'vendor', 'Yangon', '2025-11-22 09:41:07', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(78, 'Sabel San', 'hmh@gmail.com', '09999999999', '$2y$10$Vy47x7kVKF.lNiQ.EPGWce/IZI4oHy/pmWL.afx7RpN65hrsNgbTa', 'vendor', 'Sout Dagon,Yangon', '2025-11-24 09:17:11', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(79, 'Myat', 'myataung@gmail.com', '09969173010', '$2y$10$HY1caFoIj2qGQdSXD15Zsu8jsoCKOswEk2xY0/Z2Ol/lrEudh.C.i', 'vendor', 'Yangon', '2025-11-25 06:53:08', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(80, 'Myat', 'myataungko@gmail.com', '09969173010', '$2y$10$2oC3g8YmxRTB8Ko3X9v.yObE72SBMHmcj0/EkdZvgvu2kdyf3bnXm', 'vendor', 'Yangon', '2025-11-25 06:54:00', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(82, 'Myat', 'myataungko111@gmail.com', '09969173010', '$2y$10$m8t9Kl0EjXAoHLHvFstFD.UwdqIgdZABmVsJRvSvAcTVMKO7f3y0u', 'vendor', 'Yangon', '2025-11-25 07:06:18', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(83, 'Naing Htet Linn', 'niexlily@gmail.com', '09420267052', '$2y$12$b2dBxm7pwpISK2jQ.YL3OuWiWsDXoYjv.zC5Nn8mA9r.H3L6xr4wO', 'customer', 'Sout Okkalapa,Yangon', '2025-11-28 06:40:28', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(86, 'Hla Myo Hein', 'hmhisontheground@gmail.com', '09480647058', '$2y$12$yraO85cJN1pMRSmr94FUtueczXUnM9FYQRDjNspGyAbbVl7oXjqEi', 'vendor', 'Thaketa,Yangon', '2025-11-30 10:48:49', 1, NULL, 0, NULL),
(87, 'Hla Myo Hein', 'minmin@gmail.com', '09969173010', '$2y$10$3ZmuYIjAQuOfkz/R1x.JGOY6O.62FIVWuIxOetjwE7G/WHl7pJlZa', 'vendor', 'Thaketa,Yangon', '2025-12-08 06:33:43', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(88, 'MEEEEE', 'meandmyself@gmail.com', '09458712399', '$2y$10$3g8zQi2x1V57oLi2F3goXe8QRAZogucVIW4f1bTcyIHpVRn90A9Re', 'vendor', 'Yangon', '2025-12-08 08:57:30', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(91, 'Finale', 'hlahla123@gmail.com', '09458712333', '$2y$10$.qz1hJhrvdKBuJ2xCXc4U.s4UEHkdV56iEmlkjOyJrrOaLVzaFrzu', 'vendor', 'No (8) Ward, South Okkalapa, Thingangyun District, Yangon City, Yangon, 11062, Myanmar', '2025-12-13 14:27:24', 1, NULL, 0, NULL),
(92, 'YEEEEE', 'yee@gmail.com', '09458712333', '$2y$10$gaTHbxfBxsWqMsCVBYuHS.061ag6sAZfmsB787WYkKd0PcVfrTDVe', 'vendor', 'Yangon', '2025-12-13 14:36:51', 1, NULL, 0, NULL),
(94, 'MIn Min', 'minmin120231@gmail.com', '0925102041444', '$2y$12$lqDrik3OtcqKL5hqcjR/LOz3UmZ4OXaY6YTM4H2Vq8tq/3cyDS0LC', 'delivery', NULL, '2025-12-14 09:57:32', 1, NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_favorites`
--

CREATE TABLE `user_favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_favorites`
--

INSERT INTO `user_favorites` (`id`, `user_id`, `restaurant_id`, `created_at`) VALUES
(16, 86, 42, '2025-11-30 11:49:30'),
(22, 5, 41, '2025-12-04 09:40:03'),
(23, 5, 1, '2025-12-04 09:40:08'),
(24, 5, 4, '2025-12-04 09:40:16');

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `user_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `last_activity` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`user_id`, `session_token`, `user_agent`, `ip_address`, `expires_at`, `last_activity`, `created_at`) VALUES
(2, 'e4ef3f301438407d47d11952a16581452067120d2b169328264576ef03bdb992', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '::1', '2025-12-28 13:14:29', '2025-11-28 06:44:29', '2025-11-28 06:44:29'),
(4, '2fba3992ee89eac97874dc2619fc59f1bd3632f17f851563439a3d6298d6acbb', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '::1', '2026-01-13 18:18:03', '2025-12-14 11:48:03', '2025-12-14 11:48:03'),
(5, '699c4636b1f35c1ce186b1d680a1e4975eac434aa9b79a51fbca81e60aa0a974', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '::1', '2026-01-13 16:16:05', '2025-12-14 09:46:05', '2025-12-14 09:46:05'),
(10, '1b1b5e30ae960c1b0283e126d02975b1d57b4f04219a2ee3cad5d02071cf62ca', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '::1', '2025-12-28 13:59:05', '2025-11-28 07:29:05', '2025-11-28 07:29:05'),
(16, 'c4dd60d64c0171a1f422a197809bd1af12fa86df39687a2042e917f89621d5d4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '::1', '2025-12-28 14:16:26', '2025-11-28 07:46:26', '2025-11-28 07:46:26'),
(58, 'cd6bb5cf7752eece7233da49dfd9b8697b7c8f26971674ae80c48a2c0886715d', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '::1', '2025-12-15 19:27:25', '2025-11-15 12:57:25', '2025-11-15 12:57:25'),
(59, 'ad7a1789f22a16174e3fc94c6c8cb99163044bf366be754e8107003269d4a7b5', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '::1', '2025-12-15 19:39:25', '2025-11-15 13:09:25', '2025-11-15 13:09:25'),
(66, '9d6e9e1de8e1b942e742f94e6c22543984925a5b8cad6cfc00c010bff3ca3835', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '::1', '2025-12-15 21:59:41', '2025-11-15 15:29:41', '2025-11-15 15:29:41'),
(67, '7e0f8e68da7e7a80f822e4f572af73996c6c1f7725f0cbe31e5f0f712d4da327', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '::1', '2025-12-15 21:31:21', '2025-11-15 15:01:21', '2025-11-15 15:01:21'),
(68, '45561de22c7942e6ee643881a3011ba6e8b3e45b4d09c2b06f28d8661d10af4b', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '::1', '2025-12-16 05:13:27', '2025-11-15 22:43:27', '2025-11-15 22:43:27'),
(69, '87fbd0feeb01fcd7a3849db9bbd4579988d78a166e0d973c6a02e3c88b9ca7f0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '::1', '2025-12-16 05:14:03', '2025-11-15 22:44:03', '2025-11-15 22:44:03'),
(70, '70cac33327d2ad1d80bcd82cc17b47ceab9dc9f92aa67ace9c526f5f387dd324', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '::1', '2025-12-16 05:44:20', '2025-11-15 23:14:20', '2025-11-15 23:14:20'),
(71, '093d7c7088436de407e850d492ac68b275079d1ef2190a25d6551d22e3302541', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '::1', '2025-12-16 06:13:35', '2025-11-15 23:43:35', '2025-11-15 23:43:35'),
(72, '0709c22498d498c77ad55b9c82fe7cb0ef5231150ae10e8fd774be759c5d15ae', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '::1', '2025-12-16 06:18:38', '2025-11-15 23:48:38', '2025-11-15 23:48:38'),
(73, '3100b57a993f6f23d7c8808c2dbf3538cae23221fde54b069e167a2ea27c0911', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '::1', '2025-12-16 07:32:51', '2025-11-16 01:02:51', '2025-11-16 01:02:51'),
(75, 'f1bb2b8f00235e406501975e76c42335247630fb4ee531eb0f683e15b9333e3a', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '::1', '2025-12-16 07:51:05', '2025-11-16 01:21:05', '2025-11-16 01:21:05'),
(76, '05578823010a67dcd2303f103fc369f97ab47ecbb6f128276b66233afc7130cb', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '::1', '2025-12-16 07:50:54', '2025-11-16 01:20:54', '2025-11-16 01:20:54'),
(77, 'bd031f39baf84c0f63e62f7b41245130a2cf7fa2f92453461357d85f7122d0d9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '::1', '2025-12-16 07:58:41', '2025-11-16 01:28:41', '2025-11-16 01:28:41'),
(78, '5429a83ee944b6bd99aaf3cf0df915729d7fa301065015aed4313bd064238b80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '::1', '2025-12-16 08:21:11', '2025-11-16 01:51:11', '2025-11-16 01:51:11'),
(79, '5cdb7e391239dc356c65a8d9322761cbc621918da80a9f2555b423ea4f836922', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '::1', '2025-12-16 08:25:33', '2025-11-16 01:55:33', '2025-11-16 01:55:33'),
(80, '681d3989d0aa2ff37e46e73875b7eaceb2ef29fec4394528ec41242dabade9bc', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '::1', '2025-12-16 16:00:27', '2025-11-16 09:30:27', '2025-11-16 09:30:27'),
(81, 'a08be9de4165d8c1ffe1a4b3577933561a78833840fec2a7b07e38b50a21b5c9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '::1', '2025-12-16 15:53:08', '2025-11-16 09:23:08', '2025-11-16 09:23:08'),
(82, '53927fe791e374f1d7ee946b8971b0e7fdcb573766888e1f7a93a4a4fa975e93', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '::1', '2025-12-16 15:57:31', '2025-11-16 09:27:31', '2025-11-16 09:27:31'),
(83, 'd8d836ca4877d87cc24b14cbd4beba6de6d791708838e7104f4d435a8e1a2522', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '::1', '2025-12-16 15:58:17', '2025-11-16 09:28:17', '2025-11-16 09:28:17'),
(84, 'b24375e4f3edc58177e537a0df0acb6131d2c9cd1b4426761b89d671533400a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '::1', '2025-12-28 13:28:03', '2025-11-28 06:58:03', '2025-11-28 06:58:03'),
(85, 'ccb9d0313085ea4c338743219dcafabde3640003b6b77506d6476acd96d2a8a9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '::1', '2025-12-16 17:35:00', '2025-11-16 11:05:00', '2025-11-16 11:05:00'),
(86, '4a88b7ca49b1696988fe988e862f036da8d86c4a82f49e26f0b6033e24df0ea7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '::1', '2026-01-13 20:29:21', '2025-12-14 13:59:21', '2025-12-14 13:59:21'),
(91, 'e79954bd4909d840dba77c8f62e5018b0654147618a3e61208463e210e5e9524', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '::1', '2025-12-17 01:32:16', '2025-11-16 19:02:16', '2025-11-16 19:02:16'),
(92, '22e2cdfba19c5c04699f507b417a17120a9477578e01a9896cb120f687d64919', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '::1', '2025-12-17 01:31:57', '2025-11-16 19:01:57', '2025-11-16 19:01:57'),
(94, '7aa6a7e58af9589a199e13b44c29844ddd0e6f743c229a4e05ccddd82f0bbeb8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '::1', '2026-01-13 20:28:09', '2025-12-14 13:58:09', '2025-12-14 13:58:09'),
(999, 'test_token', 'Test Browser', '127.0.0.1', '2025-12-01 17:12:49', '2025-11-30 10:42:49', '2025-11-30 10:42:49');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `delivery`
--
ALTER TABLE `delivery`
  ADD PRIMARY KEY (`delivery_id`),
  ADD UNIQUE KEY `uniq_delivery_order` (`order_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `delivery_boy_id` (`delivery_boy_id`);

--
-- Indexes for table `delivery_tracking`
--
ALTER TABLE `delivery_tracking`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `fk_delivery_tracking_delivery_boy` (`delivery_boy_id`);

--
-- Indexes for table `driver_locations`
--
ALTER TABLE `driver_locations`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`expense_id`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `restaurant_id` (`restaurant_id`);

--
-- Indexes for table `menu_item_options`
--
ALTER TABLE `menu_item_options`
  ADD PRIMARY KEY (`item_id`,`option_id`),
  ADD KEY `fk_mi_options_option` (`option_id`);

--
-- Indexes for table `menu_options`
--
ALTER TABLE `menu_options`
  ADD PRIMARY KEY (`option_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_ID` (`user_id`),
  ADD KEY `resta_id` (`restaurant_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `option_values`
--
ALTER TABLE `option_values`
  ADD PRIMARY KEY (`value_id`),
  ADD KEY `option_id` (`option_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `restaurant_id` (`restaurant_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `fk_order_items_item` (`item_id`);

--
-- Indexes for table `request_notifications`
--
ALTER TABLE `request_notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_` (`user_id`);

--
-- Indexes for table `restaurants`
--
ALTER TABLE `restaurants`
  ADD PRIMARY KEY (`restaurant_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `restaurant_settlements`
--
ALTER TABLE `restaurant_settlements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `restaurant_id` (`restaurant_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `restaurant_id` (`restaurant_id`);

--
-- Indexes for table `riders`
--
ALTER TABLE `riders`
  ADD PRIMARY KEY (`rider_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `rider_deposits`
--
ALTER TABLE `rider_deposits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rider_id` (`rider_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `rider_payouts`
--
ALTER TABLE `rider_payouts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rider_id` (`rider_id`),
  ADD KEY `idx_rider_payouts_status` (`status`),
  ADD KEY `idx_rider_payouts_rider` (`rider_id`);

--
-- Indexes for table `saved_carts`
--
ALTER TABLE `saved_carts`
  ADD PRIMARY KEY (`saved_cart_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `restaurant_id` (`restaurant_id`),
  ADD KEY `user_active` (`user_id`,`is_active`),
  ADD KEY `idx_saved_carts_status` (`status`),
  ADD KEY `idx_saved_carts_order_id` (`order_id`);

--
-- Indexes for table `settlement_notifications`
--
ALTER TABLE `settlement_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `restaurant_id` (`restaurant_id`),
  ADD KEY `settlement_id` (`settlement_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_favorites`
--
ALTER TABLE `user_favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_favorite` (`user_id`,`restaurant_id`),
  ADD KEY `fk_favorite_restaurant` (`restaurant_id`),
  ADD KEY `idx_user_favorites` (`user_id`,`restaurant_id`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`user_id`,`session_token`),
  ADD KEY `session_token` (`session_token`),
  ADD KEY `expires_at` (`expires_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `delivery`
--
ALTER TABLE `delivery`
  MODIFY `delivery_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=291;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `expense_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=222;

--
-- AUTO_INCREMENT for table `menu_options`
--
ALTER TABLE `menu_options`
  MODIFY `option_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=206;

--
-- AUTO_INCREMENT for table `option_values`
--
ALTER TABLE `option_values`
  MODIFY `value_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=395;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=417;

--
-- AUTO_INCREMENT for table `request_notifications`
--
ALTER TABLE `request_notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `restaurants`
--
ALTER TABLE `restaurants`
  MODIFY `restaurant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `restaurant_settlements`
--
ALTER TABLE `restaurant_settlements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `riders`
--
ALTER TABLE `riders`
  MODIFY `rider_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `rider_deposits`
--
ALTER TABLE `rider_deposits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=341;

--
-- AUTO_INCREMENT for table `rider_payouts`
--
ALTER TABLE `rider_payouts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `saved_carts`
--
ALTER TABLE `saved_carts`
  MODIFY `saved_cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `settlement_notifications`
--
ALTER TABLE `settlement_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT for table `user_favorites`
--
ALTER TABLE `user_favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `delivery`
--
ALTER TABLE `delivery`
  ADD CONSTRAINT `delivery_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `delivery_ibfk_2` FOREIGN KEY (`delivery_boy_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `delivery_tracking`
--
ALTER TABLE `delivery_tracking`
  ADD CONSTRAINT `fk_delivery_tracking_delivery_boy` FOREIGN KEY (`delivery_boy_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_delivery_tracking_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `driver_locations`
--
ALTER TABLE `driver_locations`
  ADD CONSTRAINT `fk_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `menu_items_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`restaurant_id`);

--
-- Constraints for table `menu_item_options`
--
ALTER TABLE `menu_item_options`
  ADD CONSTRAINT `fk_mi_options_item` FOREIGN KEY (`item_id`) REFERENCES `menu_items` (`item_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_mi_options_option` FOREIGN KEY (`option_id`) REFERENCES `menu_options` (`option_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `order_id` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `resta_id` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`restaurant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_ID` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `option_values`
--
ALTER TABLE `option_values`
  ADD CONSTRAINT `option_values_ibfk_1` FOREIGN KEY (`option_id`) REFERENCES `menu_options` (`option_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`restaurant_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_item` FOREIGN KEY (`item_id`) REFERENCES `menu_items` (`item_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `menu_items` (`item_id`);

--
-- Constraints for table `request_notifications`
--
ALTER TABLE `request_notifications`
  ADD CONSTRAINT `user_` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `restaurants`
--
ALTER TABLE `restaurants`
  ADD CONSTRAINT `restaurants_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `restaurant_settlements`
--
ALTER TABLE `restaurant_settlements`
  ADD CONSTRAINT `rs_rest_fk` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`restaurant_id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`restaurant_id`);

--
-- Constraints for table `riders`
--
ALTER TABLE `riders`
  ADD CONSTRAINT `fk_riders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `rider_deposits`
--
ALTER TABLE `rider_deposits`
  ADD CONSTRAINT `rd_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `rd_rider_fk` FOREIGN KEY (`rider_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `rider_payouts`
--
ALTER TABLE `rider_payouts`
  ADD CONSTRAINT `rp_rider_fk` FOREIGN KEY (`rider_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `saved_carts`
--
ALTER TABLE `saved_carts`
  ADD CONSTRAINT `fk_saved_carts_restaurant` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`restaurant_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_saved_carts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_favorites`
--
ALTER TABLE `user_favorites`
  ADD CONSTRAINT `fk_favorite_restaurant` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`restaurant_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_favorite_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
