-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 31, 2025 at 02:43 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rent_ease`
--

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(1, 6, 'User nischalkatwal is interested in your property titled \'jhapa\'.', 0, '2025-06-18 19:43:32'),
(2, 6, 'User nischalkatwal is interested in your property titled \'prabesh\'.', 0, '2025-06-18 19:44:11'),
(3, 6, 'User nischalkatwal is interested in your property titled \'asdfasd\'. Contact: 015172175.', 0, '2025-06-18 19:58:54'),
(4, 6, 'User Prabesh Bhujel is interested in your property titled \'asdfasd\'. Contact: 9814990706.', 0, '2025-06-18 20:09:14'),
(5, 6, 'User Prabesh Bhujel is interested in your property titled \'prabesh\'. Contact: 9814990706.', 0, '2025-06-18 20:11:41'),
(6, 6, 'User nischalkatwal is interested in your property titled \'asdfasd\'. Contact: 015172175.', 0, '2025-06-18 20:24:37'),
(7, 8, 'User Diwakar Bhatt is interested in your property titled \'single room\'. Contact: 9827374757.', 0, '2025-06-19 06:23:29'),
(8, 10, 'User nischal is interested in your property titled \'vishal\'. Contact: 9814000000.', 0, '2025-08-25 18:39:28'),
(9, 13, 'User Nischal Katwal is interested in your property titled \'Single Room\'. Contact: 9805953987.', 0, '2025-08-25 22:31:28'),
(10, 13, 'User Nischal Katwal is interested in your property titled \'2 BHK\'. Contact: 9805953987.', 0, '2025-08-25 22:32:26'),
(11, 13, 'User Nischal Katwal is interested in your property titled \'2 BHK\'. Contact: 9805953987.', 0, '2025-08-25 22:49:39');

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

CREATE TABLE `properties` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `main_photo` varchar(255) NOT NULL,
  `additional_photos` text DEFAULT NULL,
  `property_type` varchar(100) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `bedrooms` int(11) DEFAULT NULL,
  `bathrooms` int(11) DEFAULT NULL,
  `kitchen` enum('Yes','No') DEFAULT NULL,
  `living_room` varchar(255) DEFAULT NULL,
  `contact_number` varchar(50) DEFAULT NULL,
  `property_location` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `facilities` text DEFAULT NULL,
  `extra_file` varchar(255) DEFAULT NULL,
  `document_file` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `price` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`id`, `user_id`, `title`, `main_photo`, `additional_photos`, `property_type`, `location`, `bedrooms`, `bathrooms`, `kitchen`, `living_room`, `contact_number`, `property_location`, `description`, `facilities`, `extra_file`, `document_file`, `status`, `created_at`, `price`) VALUES
(39, 13, '2 BHK', '68acdb5a7dab1_room 7.jpg', '68acdb5a7dc67_room 7.1.jpg', '2 BHK', 'Sallaghaari', 1, 1, '', '1', '9808824108', 'Sallaghaari bhaktapur chwok najikaii', 'Water 24hr hot cold water \r\nParking available \r\nOnly for family', 'Bus,Swimming Pool,Party Club', '', '68acdb5a7ddaa_room 7.1.jpg', 'approved', '2025-08-25 21:53:30', 18000),
(40, 13, '1 BHK', '68acdbe0cb243_room 8.jpg', '68acdbe0cb736_room 8.2.jpg', '1 Bhk', 'Santinagar', 1, 1, '', '0', '9823074340', 'Santinagar', 'Water: available (24hr)☑️\r\n📋️Rent : 20k☑️\r\n🛵parking: Available ☑️\r\n🏛️Rooms : there are total rooms 3', 'Swimming Pool,Party Club,School', '', '68acdbe0cbbc7_room 8.2.jpg', 'approved', '2025-08-25 21:55:44', 20000),
(41, 13, 'Two Room', '68acdc5c3ed76_room 9.jpg', '68acdc5c3ef75_room 9.1.jpg', 'Two Room', 'Chaumati', 1, 1, '', '0', '9823716269', 'chaumati najikk', 'Parking available \r\nWater available \r\nFamily pathaunuss', 'Bus,Swimming Pool,Party Club', '', '68acdc5c3f0b7_room 9.jpg', 'approved', '2025-08-25 21:57:48', 15000),
(42, 13, '2 BHK', '68acdcdddecf1_room 10.jpg', '68acdcdddf120_room 10.1.jpg', '2 BHK', 'Gothatar', 2, 1, '', '1', '9842598100', 'Gothatar tej binayak chowk', '24/7 Water\r\nBike parking spaces\r\nKta kta ni milxa...', 'Bus,Party Club,School', '', '68acdcdddf3b2_room 10.jpg', 'approved', '2025-08-25 21:59:57', 13000),
(43, 13, 'Single Room', '68aceea29547f_room 11.jpg', '68aceea295668_room 11.1.jpg', 'Single Room', 'Gongabu', 1, 1, '', '0', '9860832874', 'Gongabu magdi chok single room', 'pani  tanker ko  jar ko 10 rupya ma kinu parni', 'Party Club,Hospital', '', '68aceea295817_room 11.1.jpg', 'approved', '2025-08-25 23:15:46', 5000),
(44, 12, '2 BHK', '68ad0378ee700_room 3.jpg', '68ad0378ee8c6_room 3.2.jpg', '2 BHK', 'Bhaktapur', 2, 1, '', '1', '9829976675', 'Srijana nagar bkt', 'Nice ROOM', 'Hospital,Internet', '', '68ad0378eea46_Room 2.1.jpg', 'approved', '2025-08-26 00:44:40', 30000),
(45, 12, '2 BHK', '68ad03c252acd_room 3.2.jpg', '68ad03c252cc9_room 3.jpg', '2 BHK', 'Bhaktapur', 2, 1, '', '1', '9809476641', 'Srijana nagar bkt', 'NICE ROOM', 'Swimming Pool', '', '68ad03c252e6c_room 3.jpg', 'approved', '2025-08-26 00:45:54', 20000);

-- --------------------------------------------------------

--
-- Table structure for table `property_similarities`
--

CREATE TABLE `property_similarities` (
  `property_id` int(11) NOT NULL,
  `similar_property_id` int(11) NOT NULL,
  `score` decimal(6,4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recommendations`
--

CREATE TABLE `recommendations` (
  `property_id` int(11) NOT NULL,
  `recommended_id` int(11) NOT NULL,
  `similarity` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `review_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shift_requests`
--

CREATE TABLE `shift_requests` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `pickup_location` varchar(255) NOT NULL,
  `dropoff_location` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `booking_type` varchar(50) NOT NULL,
  `schedule_date` date NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `response_message` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shift_requests`
--

INSERT INTO `shift_requests` (`id`, `full_name`, `pickup_location`, `dropoff_location`, `phone`, `email`, `booking_type`, `schedule_date`, `message`, `created_at`, `response_message`, `user_id`, `status`) VALUES
(2, 'Nischal Katwal', 'Baneshwor', 'Thimi', '9800000000', 'nnkatwal950@gmail.com', 'Schedule for Later', '2025-06-20', 'I want a small vechale', '2025-06-19 05:48:08', 'Your shift request has been accepted. You will get a call soon.', NULL, 'Accepted'),
(3, 'Diwakar Bhatt', 'Imadol', 'Baneshwor', '9817273747', 'dkbhatt@gmail.com', 'Schedule for Later', '2025-06-20', 'I want a big truck', '2025-06-19 06:17:16', 'Your shift request has been accepted. You will get a call soon.', NULL, 'Accepted');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password`, `created_at`) VALUES
(6, 'Prabesh Bhujel', 'pabeshbhujel02@gmail.com', '9814990706', '$2y$10$Ggk.Q1bufQp7vVAKgcGZbujwDwMB1aTWP2bPudrEhDeUmZKQG4FgO', '2025-06-18 02:15:57'),
(7, 'nischalkatwal', 'nischal123@gmail.com', '015172175', '$2y$10$zthW7RhAS5iB1Wh89wW1U.Npe/1QkSKaLehA0gTlMlHFiGHKh8aL.', '2025-06-18 19:28:49'),
(8, 'Nischal Ktwal', 'nnkatwal@gmail.com', '9800000000', '$2y$10$GQdDPe0aqXXhUNpqRVEXReonIN.5Wg9iUtkp/oMHD8g/oupr8lUYW', '2025-06-19 00:21:49'),
(10, 'Prabesh Bhujel', 'prabeshbhujel02@gmail.com', '9814990706', '$2y$10$sWKDWkSRkWXguNYJMXxph.BYE/2z9yMj2Ldo6rr44FqjVi/sXKDlu', '2025-08-24 16:24:17'),
(11, 'nischal', 'nischalkatwal@gmail.com', '9814000000', '$2y$10$9QFfSoo/GepPrIvRCqJcX.LaN98TpzetWNsiFMB0tGJ.D0OYTOiF6', '2025-08-25 18:38:55'),
(12, 'Nischal Katwal', 'nixx@gmail.com', '9805953987', '$2y$10$xYpQVYTv9EWsPGLC9oUbKe4VYm02VxcMOQKFeNuk/Od371F30hn26', '2025-08-25 20:45:33'),
(13, 'Prabesh Bhujel', 'prabesh@gmail.com', '9812349428', '$2y$10$Qe8Sg/.8RPVjsKvHk1epJO4/k.XFhjggmgsqOCyqOePqbTpFqiPRG', '2025-08-25 21:34:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `properties` ADD FULLTEXT KEY `title` (`title`,`location`);
ALTER TABLE `properties` ADD FULLTEXT KEY `title_2` (`title`);
ALTER TABLE `properties` ADD FULLTEXT KEY `location` (`location`);

--
-- Indexes for table `property_similarities`
--
ALTER TABLE `property_similarities`
  ADD PRIMARY KEY (`property_id`,`similar_property_id`),
  ADD KEY `similar_property_id` (`similar_property_id`);

--
-- Indexes for table `recommendations`
--
ALTER TABLE `recommendations`
  ADD PRIMARY KEY (`property_id`,`recommended_id`),
  ADD KEY `recommended_id` (`recommended_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `shift_requests`
--
ALTER TABLE `shift_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `properties`
--
ALTER TABLE `properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `shift_requests`
--
ALTER TABLE `shift_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `property_similarities`
--
ALTER TABLE `property_similarities`
  ADD CONSTRAINT `fk_sim_p1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sim_p2` FOREIGN KEY (`similar_property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `recommendations`
--
ALTER TABLE `recommendations`
  ADD CONSTRAINT `recommendations_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recommendations_ibfk_2` FOREIGN KEY (`recommended_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
