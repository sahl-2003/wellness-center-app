-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 02, 2025 at 07:05 PM
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
-- Database: `greenlife`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `therapist_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `status` enum('pending','confirmed','cancelled') NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `user_id`, `therapist_id`, `service_id`, `title`, `description`, `appointment_date`, `appointment_time`, `status`, `created_at`, `start_time`, `end_time`, `notes`) VALUES
(21, 19, 18, 2, '', '', '2025-06-23', '16:30:00', 'cancelled', '2025-06-22 02:37:49', '16:30:00', '17:30:00', ''),
(25, 19, 18, 4, '', '', '2025-06-25', '16:30:00', '', '2025-06-22 02:45:32', '16:30:00', '17:45:00', ''),
(26, 19, 8, 2, '', '', '2025-06-26', '14:45:00', 'confirmed', '2025-06-22 03:10:01', '16:48:00', '18:45:00', ''),
(28, 19, 8, 3, '', '', '2025-06-30', '10:30:00', 'confirmed', '2025-06-22 03:18:42', '09:30:00', '10:30:00', ''),
(29, 19, 8, 6, '', '', '2025-06-26', '14:45:00', 'confirmed', '2025-06-22 03:26:17', '14:30:00', '15:30:00', ''),
(31, 1, 18, 2, '', '', '2025-06-30', '16:30:00', 'cancelled', '2025-06-22 20:23:15', '16:30:00', '17:30:00', ''),
(33, 1, 8, 4, '', '', '2025-06-30', '10:30:00', 'confirmed', '2025-06-28 10:50:23', '10:30:00', '11:45:00', ''),
(34, 1, 8, 2, '', '', '2025-06-30', '15:30:00', 'pending', '2025-06-29 13:57:01', '15:30:00', '16:30:00', ''),
(35, 21, 8, 1, '', '', '2025-07-07', '10:30:00', 'confirmed', '2025-07-01 10:24:51', '10:30:00', '11:30:00', '');

-- --------------------------------------------------------

--
-- Table structure for table `client_profiles`
--

CREATE TABLE `client_profiles` (
  `profile_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `client_profiles`
--

INSERT INTO `client_profiles` (`profile_id`, `user_id`, `full_name`, `date_of_birth`, `gender`, `phone`, `address`, `profile_picture`) VALUES
(2, 1, 'sahl', '2005-03-17', 'male', '', '', '/green2/uploads/profiles/client_1_1750429443.jpg'),
(3, 11, 'yoosuf', '0000-00-00', '', '', '', '/green2/uploads/profiles/client_11_1750335667.jpg'),
(4, 9, 'thanish', '0000-00-00', '', '', '', '/green2/uploads/profiles/client_9_1750341964.jpg'),
(5, 19, 'iskan', '0000-00-00', 'male', '', '', '/green2/uploads/profiles/client_19_1750542945.jpg'),
(6, 21, 'safiya', '0000-00-00', '', '', '', '/green2/uploads/profiles/client_21_1751345918.png');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `phone`, `subject`, `message`, `is_read`, `created_at`) VALUES
(3, 'hikma', 'h@gmail.com', '1234566', 'booking', 'how should i book', 1, '2025-06-21 19:46:52'),
(4, 'b', 'v@gmail.com', '', 'feedback', 'hj', 1, '2025-06-21 20:20:06'),
(5, 'hikma', 'a@gmail.com', '0725678982', 'feedback', 'it\'s very good', 1, '2025-07-01 04:46:43');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `sender_id`, `receiver_id`, `content`, `is_read`, `created_at`) VALUES
(14, 8, 9, 'hi', 0, '2025-06-21 18:08:24'),
(15, 8, 1, 'iam fine', 1, '2025-06-21 18:08:43'),
(21, 1, 18, 'h,', 1, '2025-06-21 20:04:13'),
(23, 1, 18, 'cdfBXShx', 1, '2025-06-21 20:10:41'),
(26, 1, 18, 'gfghj', 1, '2025-06-21 20:19:11'),
(27, 8, 21, 'hi i accept your appointment', 1, '2025-07-01 04:56:55'),
(28, 21, 8, 'kk thanks', 0, '2025-07-01 04:57:34');

-- --------------------------------------------------------

--
-- Table structure for table `message_replies`
--

CREATE TABLE `message_replies` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `reply_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `message_replies`
--

INSERT INTO `message_replies` (`id`, `message_id`, `admin_id`, `reply_text`, `created_at`) VALUES
(1, 1, 2, 'hi', '2025-06-21 10:26:48'),
(2, 2, 2, 'hi', '2025-06-21 18:06:26'),
(3, 4, 2, 'hiiii', '2025-07-01 03:56:26'),
(4, 5, 2, 'kk thank you', '2025-07-01 04:47:16');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `service_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `duration` int(11) NOT NULL COMMENT 'Duration in minutes',
  `price` decimal(10,2) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `image_path` varchar(255) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`service_id`, `name`, `description`, `duration`, `price`, `category`, `is_active`, `created_at`, `updated_at`, `image_path`) VALUES
(1, 'yoga & meditation', 'A calming and restorative session designed to soothe the mind and gently stretch the body. The class begins with guided mindfulness meditation to quiet mental chatter and cultivate present-moment awareness. This is followed by a series of gentle Hatha yoga postures (asanas) and breathing exercises (pranayama) to release tension, improve flexibility, and create a state of deep relaxation.\r\n', 60, 50.00, 'yoga & meditaion', 1, '2025-06-17 20:45:10', '2025-06-21 14:06:23', 'uploads/services/service_68531266271dc0.46638879.png'),
(2, 'ayurveda', 'An in-depth consultation to assess your unique mind-body constitution (Prakriti) and current imbalances (Vikriti). Our expert Ayurvedic practitioner will use traditional diagnostic methods like pulse and tongue analysis to create a personalized roadmap for your Panchakarma (detoxification) journey. ', 60, 2500.00, 'ayurvedic', 1, '2025-06-20 14:26:20', '2025-06-22 17:25:51', 'uploads/services/service_68556f8cf04ce1.92368985.jpg'),
(3, 'Shirodhara', 'A classic Ayurvedic therapy that involves gently pouring a continuous stream of warm, medicated oil over the forehead. It is deeply relaxing, calming the nervous system and promoting mental clarity.', 60, 4500.00, 'Ayurvedic Therapy', 1, '2025-06-21 13:21:41', '2025-06-21 13:21:41', 'image/ayur.jpg'),
(4, 'Hatha Yoga Group Session', 'A foundational yoga class focusing on basic postures (asanas) and breathing techniques (pranayama). Perfect for beginners or those looking to refine their practice in a supportive group environment.', 75, 1500.00, 'Yoga and Meditation Classes', 1, '2025-06-21 13:21:41', '2025-06-21 13:21:41', 'image/h1.jpg'),
(5, 'Personalised Diet Plan', 'A one-on-one consultation with a certified nutritionist to create a tailored diet plan based on your health goals, lifestyle, and dietary needs. Includes a follow-up session.', 90, 6000.00, 'Nutrition and Diet Consultation', 1, '2025-06-21 13:21:41', '2025-06-21 13:21:41', 'image/n2.png'),
(6, 'Sports Injury Rehabilitation', 'A targeted physiotherapy session designed to treat and rehabilitate sports-related injuries. Our experts use a combination of manual therapy, exercises, and modern equipment to help you recover faster.', 45, 3500.00, 'Physiotherapy', 1, '2025-06-21 13:21:41', '2025-06-21 13:21:41', 'image/t1.jpg'),
(7, 'Deep Tissue Massage', 'A therapeutic massage focused on realigning deeper layers of muscles and connective tissue. It is especially helpful for chronic aches and pains and contracted areas such as a stiff neck and upper back, low back pain, and sore shoulders.', 60, 5000.00, 'Massage Therapy', 1, '2025-06-21 13:21:41', '2025-06-21 19:56:32', 'image/m1.png');

-- --------------------------------------------------------

--
-- Table structure for table `therapists`
--

CREATE TABLE `therapists` (
  `therapist_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `qualifications` text DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `therapists`
--

INSERT INTO `therapists` (`therapist_id`, `user_id`, `specialization`, `qualifications`, `bio`, `profile_picture`) VALUES
(6, 16, 'Ayurvedic Specialist', 'Bachelor of Ayurvedic Medicine and Surgery (BAMS), University of Colombo, Sri Lanka. Certified Ayurvedic Practitioner with 8 years of experience.', 'Dr. Priyanka Mendis is a dedicated Ayurvedic specialist with over 8 years of experience in traditional Sri Lankan healing practices. She specializes in Panchakarma treatments, herbal medicine, and lifestyle counseling. Dr. Mendis believes in the holistic approach to wellness, combining ancient wisdom with modern understanding of health and wellness.', 'uploads/profiles/profile_16_1750533176.jpg'),
(7, 17, 'massage therapy', 'Diploma in Therapeutic Massage, Sri Lanka Institute of Massage Therapy. Certified Reflexologist with 6 years of experience in various massage techniques.', 'Dr. Rajith Perera is an experienced massage therapist specializing in therapeutic massage, deep tissue massage, and reflexology. With 6 years of practice, he has helped numerous clients with stress relief, pain management, and overall wellness. His gentle approach and deep understanding of human anatomy make him a trusted wellness practitioner.', 'uploads/profiles/profile_17_1750533222.jpg'),
(8, 18, 'yoga & meditation', 'Master of Yoga Therapy, Sivananda Yoga Vedanta Centre, India. Certified Yoga Instructor with 7 years of experience in therapeutic yoga and meditation.', 'Dr. Anjali Fernando is a certified yoga therapist and meditation instructor with 7 years of experience. She specializes in therapeutic yoga for stress management, back pain, and mental wellness. Dr. Fernando combines traditional yoga practices with modern therapeutic approaches to help clients achieve physical and mental balance.', 'uploads/profiles/profile_18_1750533326.jpg'),
(9, 8, 'Acupuncture & Traditional', 'Master of Traditional Chinese Medicine, Beijing University of Chinese Medicine. Licensed Acupuncturist with 5 years of clinical experience.', 'Dr. Chamara Silva is a licensed acupuncturist and Traditional Chinese Medicine practitioner with 5 years of clinical experience. He specializes in pain management, stress relief, and chronic condition treatment through acupuncture and herbal medicine. Dr. Silva combines traditional Chinese healing methods with modern medical understanding to provide comprehensive wellness solutions.', 'uploads/profiles/profile_8_1750529272.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `therapist_availability`
--

CREATE TABLE `therapist_availability` (
  `id` int(11) NOT NULL,
  `therapist_id` int(11) NOT NULL,
  `day_of_week` varchar(10) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `therapist_availability`
--

INSERT INTO `therapist_availability` (`id`, `therapist_id`, `day_of_week`, `start_time`, `end_time`, `created_at`) VALUES
(1, 8, 'Monday', '15:30:00', '16:00:00', '2025-06-19 08:35:46'),
(2, 8, 'Monday', '10:30:00', '11:30:00', '2025-06-19 09:00:07'),
(6, 8, 'Thursday', '14:45:00', '15:30:00', '2025-06-21 18:16:44'),
(7, 18, 'Monday', '16:30:00', '17:15:00', '2025-06-21 19:54:17');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `pwd` varchar(225) NOT NULL,
  `phone` int(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('client','therapist','admin') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `pwd`, `phone`, `email`, `role`) VALUES
(1, 's', '$2y$10$KwEu.rC8n/YVpv0i46rMiebLFW1Lidc4rsrBjG07mFpjJlxN4NUQq', 2147483647, 'a@gmail.com', 'client'),
(2, 'sa', '$2y$10$7Jvp8983olBD.pX6BsyliONiYP4TFJZO2u1oOtvtH.XP38SztEKDG', 947698, 'yns@gmail.com', 'admin'),
(8, 'Dr.arkan', '$2y$10$elw9ddvmklt5oQJWAuSPaOnotGgZ6fmrP2wxArTpUT5BE.OBFeo6y', 23456789, 'ar@gmail.com', 'therapist'),
(9, 'thanish 1', '$2y$10$MmeLYIDSw2Cid1AFxKV/qu6yixTMmyitNhIidrX4oDr1DzzvB64x.', 345627835, 'th@gmail.com', 'client'),
(11, 'yoosuf', '$2y$10$Oa6Nn7taPWQNe8b8GEIjCuKYu0h5yiNUw.7g8E/HHvLnKsen.saA6', 526852125, 'y@gmail.com', 'client'),
(16, 'Dr. Priyanka Mendis', '$2y$10$Mwq3T/YCQc/Xzir.4r4OtO61dx3Gxro4.po7t2bOpMBP93UGKxEam', 725678983, 'Priyanka@gmail.com', 'therapist'),
(17, 'Dr. Rajith Perera', '$2y$10$KygSBnsaAPAsvZL36d/E5e2AlqNA/5CB7YxbmUgugXXoGwCihOx1e', 785678953, 'Rajith@gmail.com', 'therapist'),
(18, 'Dr. Anjali Fernando', '$2y$10$dg1Qlem0JaAnVEgtirqGhu4Mrm4Sfz2.BDajVvmUX9FKpjIIHUk46', 755608982, 'Anjali@gmail.com', 'therapist'),
(19, 'iskan', '$2y$10$vReHIdT7keb7qVR5iq6DYOeaUvb9M0E5SFE1BT.viYB4hTdlLX6Ky', 74674323, 'iskan@gmail.com', 'client'),
(20, 'baanu', '$2y$10$Rlg7djrVYIm58NDi5nSzaO.rfNlpOEEL/XMZBajTNnC2PFqRcR4IG', 76372863, 'baanu@gmail.com', 'client'),
(21, 'safiya', '$2y$10$JPJHWFctoszm7U6qRXhuf.fqA/lyrU7sjeVAqpKnbWmijp66GRXSC', 727807572, 'safiya@gmail.com', 'client');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `therapist_id_2` (`therapist_id`,`appointment_date`,`start_time`),
  ADD KEY `fk_service` (`service_id`);

--
-- Indexes for table `client_profiles`
--
ALTER TABLE `client_profiles`
  ADD PRIMARY KEY (`profile_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `message_replies`
--
ALTER TABLE `message_replies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`service_id`);

--
-- Indexes for table `therapists`
--
ALTER TABLE `therapists`
  ADD PRIMARY KEY (`therapist_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `therapist_availability`
--
ALTER TABLE `therapist_availability`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_overlap_slots` (`therapist_id`,`day_of_week`,`start_time`,`end_time`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `client_profiles`
--
ALTER TABLE `client_profiles`
  MODIFY `profile_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `message_replies`
--
ALTER TABLE `message_replies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `therapists`
--
ALTER TABLE `therapists`
  MODIFY `therapist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `therapist_availability`
--
ALTER TABLE `therapist_availability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
