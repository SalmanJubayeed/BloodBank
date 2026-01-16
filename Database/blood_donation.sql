-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 29, 2025 at 05:13 PM
-- Server version: 10.4.6-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `blood_donation`
--

-- --------------------------------------------------------

--
-- Stand-in structure for view `active_requests`
-- (See below for the actual view)
--
CREATE TABLE `active_requests` (
`request_id` int(11)
,`blood_group` varchar(5)
,`units_needed` int(11)
,`urgency_level` enum('Low','Medium','High','Critical')
,`hospital_name` varchar(100)
,`contact_person` varchar(100)
,`contact_phone` varchar(15)
,`needed_by` date
,`additional_notes` text
,`created_at` timestamp
,`recipient_name` varchar(100)
,`recipient_phone` varchar(15)
,`recipient_email` varchar(100)
,`total_applications` bigint(21)
,`pending_applications` decimal(22,0)
,`approved_applications` decimal(22,0)
);

-- --------------------------------------------------------

--
-- Table structure for table `blood_compatibility`
--

CREATE TABLE `blood_compatibility` (
  `id` int(11) NOT NULL,
  `donor_type` varchar(5) NOT NULL,
  `recipient_type` varchar(5) NOT NULL,
  `compatible` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `blood_compatibility`
--

INSERT INTO `blood_compatibility` (`id`, `donor_type`, `recipient_type`, `compatible`) VALUES
(1, 'O-', 'O-', 1),
(2, 'O-', 'O+', 1),
(3, 'O-', 'A-', 1),
(4, 'O-', 'A+', 1),
(5, 'O-', 'B-', 1),
(6, 'O-', 'B+', 1),
(7, 'O-', 'AB-', 1),
(8, 'O-', 'AB+', 1),
(9, 'O+', 'O+', 1),
(10, 'O+', 'A+', 1),
(11, 'O+', 'B+', 1),
(12, 'O+', 'AB+', 1),
(13, 'A-', 'A-', 1),
(14, 'A-', 'A+', 1),
(15, 'A-', 'AB-', 1),
(16, 'A-', 'AB+', 1),
(17, 'A+', 'A+', 1),
(18, 'A+', 'AB+', 1),
(19, 'B-', 'B-', 1),
(20, 'B-', 'B+', 1),
(21, 'B-', 'AB-', 1),
(22, 'B-', 'AB+', 1),
(23, 'B+', 'B+', 1),
(24, 'B+', 'AB+', 1),
(25, 'AB-', 'AB-', 1),
(26, 'AB-', 'AB+', 1),
(27, 'AB+', 'AB+', 1);

-- --------------------------------------------------------

--
-- Table structure for table `blood_requests`
--

CREATE TABLE `blood_requests` (
  `request_id` int(11) NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `blood_group` varchar(5) NOT NULL,
  `units_needed` int(11) DEFAULT 1,
  `urgency_level` enum('Low','Medium','High','Critical') DEFAULT 'Medium',
  `hospital_name` varchar(100) DEFAULT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `contact_phone` varchar(15) DEFAULT NULL,
  `needed_by` date DEFAULT NULL,
  `additional_notes` text DEFAULT NULL,
  `status` enum('Open','Fulfilled','Cancelled') DEFAULT 'Open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `blood_requests`
--

INSERT INTO `blood_requests` (`request_id`, `recipient_id`, `blood_group`, `units_needed`, `urgency_level`, `hospital_name`, `contact_person`, `contact_phone`, `needed_by`, `additional_notes`, `status`, `created_at`, `updated_at`) VALUES
(1, 4, 'B+', 2, 'High', 'City General Hospital', 'Dr. Smith', '555-1001', '2025-09-16', 'Urgent surgery scheduled. Patient has rare complications.', 'Open', '2025-09-13 13:51:13', '2025-09-13 13:51:13'),
(2, 5, 'AB-', 1, 'Critical', 'Memorial Medical Center', 'Dr. Johnson', '555-1002', '2025-09-14', 'Emergency case. Patient in ICU.', 'Open', '2025-09-13 13:51:13', '2025-09-13 13:51:13'),
(3, 7, 'O+', 1, 'High', 'Chittagong Medical Hospital', 'Abir', '01456382903', '2025-09-14', '', 'Open', '2025-09-13 14:03:53', '2025-09-13 14:03:53'),
(4, 10, 'O+', 1, 'High', 'USTC Hospital', 'Donald Blake', '01704317784', '2025-11-26', 'Contact the recipient as soon as possible', 'Open', '2025-11-24 19:43:09', '2025-11-24 19:43:09');

-- --------------------------------------------------------

--
-- Stand-in structure for view `compatible_donors`
-- (See below for the actual view)
--
CREATE TABLE `compatible_donors` (
`recipient_type` varchar(5)
,`user_id` int(11)
,`name` varchar(100)
,`email` varchar(100)
,`blood_group` varchar(5)
,`phone` varchar(15)
,`age` int(11)
,`gender` enum('Male','Female','Other')
,`created_at` timestamp
);

-- --------------------------------------------------------

--
-- Table structure for table `donation_applications`
--

CREATE TABLE `donation_applications` (
  `application_id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `donor_id` int(11) NOT NULL,
  `message` text DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `responded_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `donation_applications`
--

INSERT INTO `donation_applications` (`application_id`, `request_id`, `donor_id`, `message`, `status`, `applied_at`, `responded_at`) VALUES
(1, 1, 1, 'I am available to donate immediately. I have donated before and am in good health.', 'Pending', '2025-09-13 13:51:13', NULL),
(2, 1, 2, 'I can help! I live nearby and can come to the hospital today.', 'Pending', '2025-09-13 13:51:13', NULL),
(3, 2, 3, 'As O- donor, I can help with this critical case. Please let me know the details.', 'Pending', '2025-09-13 13:51:13', NULL),
(4, 3, 6, '', 'Approved', '2025-09-13 14:11:25', '2025-09-13 14:11:51'),
(5, 2, 9, 'I am a regular donor as I donate blood every 4months if possible. I already donated blood more than 5 times. You can feel free to contact me at any time. Thank you', 'Pending', '2025-11-24 19:38:22', NULL),
(6, 4, 9, '', 'Approved', '2025-11-24 19:53:26', '2025-11-24 19:53:46');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('donor','recipient') NOT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `blood_group` varchar(5) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `role`, `age`, `gender`, `blood_group`, `phone`, `address`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'John Donor', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donor', 28, 'Male', 'O+', '555-0101', '123 Main St, City, State', 1, '2025-09-13 13:51:13', '2025-09-13 13:51:13'),
(2, 'Sarah Helper', 'sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donor', 32, 'Female', 'A+', '555-0102', '456 Oak Ave, City, State', 1, '2025-09-13 13:51:13', '2025-09-13 13:51:13'),
(3, 'Mike Universal', 'mike@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donor', 25, 'Male', 'O-', '555-0103', '789 Pine Rd, City, State', 1, '2025-09-13 13:51:13', '2025-09-13 13:51:13'),
(4, 'Lisa Patient', 'lisa@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'recipient', 45, 'Female', 'B+', '555-0201', '321 Elm St, City, State', 1, '2025-09-13 13:51:13', '2025-09-13 13:51:13'),
(5, 'David Recipient', 'david@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'recipient', 38, 'Male', 'AB-', '555-0202', '654 Birch Ln, City, State', 1, '2025-09-13 13:51:13', '2025-09-13 13:51:13'),
(6, 'Rocky', 'rocky@gmail.com', '$2y$10$IZpW8J//yd9kA.kFHjK92eIA3O8ZtKhA8o9SSbkYAm5hSDFliMrqq', 'donor', 21, 'Male', 'O+', '01323674391', 'Ak khan Road, Chattogram', 1, '2025-09-13 13:59:20', '2025-09-13 13:59:20'),
(7, 'Faraz', 'faraz@gmail.com', '$2y$10$nk.mcJk6r4fTjqoYkwwyQuBpbctrDrCJfqTotewpKu.qYOtBQpHXe', 'recipient', 23, 'Male', 'O+', '01632874931', 'Ashkar Dighi, Chattogram', 1, '2025-09-13 14:00:44', '2025-09-13 14:00:44'),
(8, 'Tamim', 'tamim@gmail.com', '$2y$10$NKrWFdRM9L6nYBthKBJ.3uWd5.v0SzIY9XNUhxu6DMH4CpFdo.VAS', 'donor', 25, 'Male', 'AB+', '01345367854', '', 1, '2025-09-28 15:01:01', '2025-09-28 15:01:01'),
(9, 'Salman Jubayeed', 'jobayeedsalman@gmail.com', '$2y$10$dKC4Z862w1FiPYgt88NZw.38YSdNRaiHaLM6Pn2lAhOMfO/5k.3.u', 'donor', 23, 'Male', 'O+', '01540336996', 'West, Kadhurkhil, Boalkhali, Chattogram', 1, '2025-11-24 19:36:29', '2025-11-24 19:36:29'),
(10, 'Chris Hemsworth', 'thor@gmail.com', '$2y$10$YBIsQ7s7V25ip9hffvGIlOMX0u4Kx6kb/XqPlwFjHeUoqdGpYDNDq', 'recipient', 33, 'Male', 'O+', '01634856089', 'Urkirchor, Raozan, Chattogram', 1, '2025-11-24 19:39:59', '2025-11-24 19:39:59');

-- --------------------------------------------------------

--
-- Structure for view `active_requests`
--
DROP TABLE IF EXISTS `active_requests`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `active_requests`  AS  select `br`.`request_id` AS `request_id`,`br`.`blood_group` AS `blood_group`,`br`.`units_needed` AS `units_needed`,`br`.`urgency_level` AS `urgency_level`,`br`.`hospital_name` AS `hospital_name`,`br`.`contact_person` AS `contact_person`,`br`.`contact_phone` AS `contact_phone`,`br`.`needed_by` AS `needed_by`,`br`.`additional_notes` AS `additional_notes`,`br`.`created_at` AS `created_at`,`u`.`name` AS `recipient_name`,`u`.`phone` AS `recipient_phone`,`u`.`email` AS `recipient_email`,count(`da`.`application_id`) AS `total_applications`,sum(case when `da`.`status` = 'Pending' then 1 else 0 end) AS `pending_applications`,sum(case when `da`.`status` = 'Approved' then 1 else 0 end) AS `approved_applications` from ((`blood_requests` `br` join `users` `u` on(`br`.`recipient_id` = `u`.`user_id`)) left join `donation_applications` `da` on(`br`.`request_id` = `da`.`request_id`)) where `br`.`status` = 'Open' group by `br`.`request_id` order by case `br`.`urgency_level` when 'Critical' then 1 when 'High' then 2 when 'Medium' then 3 when 'Low' then 4 end,`br`.`needed_by`,`br`.`created_at` desc ;

-- --------------------------------------------------------

--
-- Structure for view `compatible_donors`
--
DROP TABLE IF EXISTS `compatible_donors`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `compatible_donors`  AS  select distinct `bc`.`recipient_type` AS `recipient_type`,`u`.`user_id` AS `user_id`,`u`.`name` AS `name`,`u`.`email` AS `email`,`u`.`blood_group` AS `blood_group`,`u`.`phone` AS `phone`,`u`.`age` AS `age`,`u`.`gender` AS `gender`,`u`.`created_at` AS `created_at` from (`users` `u` join `blood_compatibility` `bc` on(`u`.`blood_group` = `bc`.`donor_type`)) where `u`.`role` = 'donor' and `u`.`is_active` = 1 and `bc`.`compatible` = 1 ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `blood_compatibility`
--
ALTER TABLE `blood_compatibility`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_donor_type` (`donor_type`),
  ADD KEY `idx_recipient_type` (`recipient_type`);

--
-- Indexes for table `blood_requests`
--
ALTER TABLE `blood_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `idx_status_urgency` (`status`,`urgency_level`),
  ADD KEY `idx_blood_group` (`blood_group`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_requests_recipient_status` (`recipient_id`,`status`);

--
-- Indexes for table `donation_applications`
--
ALTER TABLE `donation_applications`
  ADD PRIMARY KEY (`application_id`),
  ADD UNIQUE KEY `unique_application` (`request_id`,`donor_id`),
  ADD KEY `idx_request_status` (`request_id`,`status`),
  ADD KEY `idx_donor_status` (`donor_id`,`status`),
  ADD KEY `idx_applications_donor_request` (`donor_id`,`request_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_role_active` (`role`,`is_active`),
  ADD KEY `idx_users_blood_group` (`blood_group`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `blood_compatibility`
--
ALTER TABLE `blood_compatibility`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `blood_requests`
--
ALTER TABLE `blood_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `donation_applications`
--
ALTER TABLE `donation_applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `blood_requests`
--
ALTER TABLE `blood_requests`
  ADD CONSTRAINT `blood_requests_ibfk_1` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `donation_applications`
--
ALTER TABLE `donation_applications`
  ADD CONSTRAINT `donation_applications_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `blood_requests` (`request_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `donation_applications_ibfk_2` FOREIGN KEY (`donor_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
