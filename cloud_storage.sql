-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 11, 2025 at 12:43 PM
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
-- Database: `cloud_storage`
--

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE `files` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `files`
--

INSERT INTO `files` (`id`, `user_id`, `folder_id`, `file_name`, `uploaded_at`) VALUES
(791, 100, 259, '3.jpg', '2025-02-10 08:03:30'),
(792, 100, 259, 'IMG_20231004_204329_689.jpg', '2025-02-10 08:03:30'),
(793, 100, 259, 'IMG_20231010_164745_812.jpg', '2025-02-10 08:03:30'),
(794, 100, 259, 'IMG_20231011_205934_850.jpg', '2025-02-10 08:03:30'),
(795, 100, 259, 'IMG_20240223_230348_752.jpg', '2025-02-10 08:03:30'),
(796, 100, 259, 'Screenshot_20240117-084115.jpg', '2025-02-10 08:03:30'),
(797, 100, 260, 'vecteezy_data-neural-network-ai-technology-cloud-computing-bits_21723025.mp4', '2025-02-10 08:03:58'),
(798, 101, 261, '2.jpg', '2025-02-10 08:07:55'),
(799, 101, 261, '50px.png', '2025-02-10 08:07:55'),
(800, 101, 261, '100px.png', '2025-02-10 08:07:55'),
(801, 101, 261, '500px.png', '2025-02-10 08:07:55'),
(802, 101, 261, 'aaa.jpg', '2025-02-10 08:07:55'),
(803, 101, 261, 'BEHIND TEXT.jpg', '2025-02-10 08:07:55'),
(804, 101, 261, 'logo.png', '2025-02-10 08:07:55'),
(805, 101, 261, 'main (2).jpg', '2025-02-10 08:07:55'),
(806, 101, 261, 'main.jpg', '2025-02-10 08:07:55'),
(807, 101, 261, 'main.png', '2025-02-10 08:07:55'),
(808, 101, 261, 'main3.jpg', '2025-02-10 08:07:55'),
(809, 101, 261, 'main3.png', '2025-02-10 08:07:55'),
(810, 101, 261, 'main3saxsaxsa.jpg', '2025-02-10 08:07:55'),
(811, 101, 261, 'PROFILE.jpg', '2025-02-10 08:07:55'),
(812, 101, 261, 'resume.jpg', '2025-02-10 08:07:55'),
(813, 101, 261, 'sxasxsa.jpg', '2025-02-10 08:07:55'),
(814, 101, 261, 'WALLPAPER.png', '2025-02-10 08:07:55'),
(815, 102, 270, '2.jpg', '2025-02-11 11:10:23'),
(816, 102, 270, '50px.png', '2025-02-11 11:10:23'),
(817, 102, 270, '100px.png', '2025-02-11 11:10:23'),
(818, 102, 270, '500px.png', '2025-02-11 11:10:23'),
(819, 102, 270, 'aaa.jpg', '2025-02-11 11:10:23'),
(820, 102, 270, 'BEHIND TEXT.jpg', '2025-02-11 11:10:23'),
(821, 102, 270, 'logo.png', '2025-02-11 11:10:23'),
(822, 102, 270, 'main (2).jpg', '2025-02-11 11:10:23'),
(823, 102, 270, 'main.jpg', '2025-02-11 11:10:23'),
(824, 102, 270, 'main.png', '2025-02-11 11:10:23'),
(825, 102, 270, 'main3.jpg', '2025-02-11 11:10:23'),
(826, 102, 270, 'main3.png', '2025-02-11 11:10:23'),
(827, 102, 270, 'main3saxsaxsa.jpg', '2025-02-11 11:10:23'),
(828, 102, 270, 'PROFILE.jpg', '2025-02-11 11:10:23'),
(829, 102, 270, 'resume.jpg', '2025-02-11 11:10:23'),
(830, 102, 270, 'sxasxsa.jpg', '2025-02-11 11:10:23'),
(831, 102, 270, 'WALLPAPER.png', '2025-02-11 11:10:23');

-- --------------------------------------------------------

--
-- Table structure for table `folders`
--

CREATE TABLE `folders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `folder_name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `folders`
--

INSERT INTO `folders` (`id`, `user_id`, `folder_name`, `created_at`) VALUES
(259, 100, 'Pictures', '2025-02-10 08:03:01'),
(260, 100, 'Videos', '2025-02-10 08:03:09'),
(261, 101, 'Pictures', '2025-02-10 08:07:45'),
(262, 101, 'PDF', '2025-02-10 08:09:23'),
(263, 101, 'chan', '2025-02-10 08:09:27'),
(264, 101, 'Documents', '2025-02-10 08:09:32'),
(265, 101, 'ssss', '2025-02-10 08:09:38'),
(266, 101, 'Videos', '2025-02-10 08:09:45'),
(267, 101, 'SQL', '2025-02-10 08:10:01'),
(268, 101, 'excel', '2025-02-10 08:10:13'),
(269, 101, 'you\'', '2025-02-10 08:10:28'),
(270, 102, 'For Share', '2025-02-11 11:09:47');

-- --------------------------------------------------------

--
-- Table structure for table `shared_folders`
--

CREATE TABLE `shared_folders` (
  `id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  `shared_by` int(11) NOT NULL,
  `shared_with` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shared_folders`
--

INSERT INTO `shared_folders` (`id`, `folder_id`, `shared_by`, `shared_with`) VALUES
(72, 270, 102, 101);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `middlename` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `qr_code` varchar(255) NOT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstname`, `middlename`, `lastname`, `username`, `email`, `password`, `qr_code`, `profile_pic`, `created_at`) VALUES
(100, 'Ethan', '', 'Hisona', 'Ethan', 'hisona@gmail.com', '$2y$10$YmI/RDS/NjX3hVIG07n9duhDiIx1.QSxR9/QCknVNdxuzna.RDa4q', 'b17599a7d478dd79f1010e2a6fc65f0d', 'uploads/profile_pics/user_67a9b27a567f6.jpg', '2025-02-10 08:02:02'),
(101, 'Christian Earl', '', 'Siong', 'Christian Earl', 'christiansiong9@gmail.com', '$2y$10$izGMYl85eS.wapZbs4EnGuNf3i4DDzxTvK1IHbeA4iktxyJOPGXhO', '0a7723688c10218938e7d836c507a9d5', 'uploads/profile_pics/user_67a9b34f16f6e.jpg', '2025-02-10 08:05:35'),
(102, 'Paul Mark', '', 'Villareal', 'Paul Mark', 'paulmark@mail.com', '$2y$10$zcqpLMEUI1pFaOKGh6Dkj.ajHVMtKqQuta3mAePKUiP9UAIr1dl42', 'd0f9db593d2962fd1dbe981b9b3e51b0', 'uploads/profile_pics/user_67ab2fce6afb3.jpg', '2025-02-11 11:09:02');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `folder_id` (`folder_id`);

--
-- Indexes for table `folders`
--
ALTER TABLE `folders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `shared_folders`
--
ALTER TABLE `shared_folders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `folder_id` (`folder_id`),
  ADD KEY `shared_by` (`shared_by`),
  ADD KEY `shared_with` (`shared_with`);

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
-- AUTO_INCREMENT for table `files`
--
ALTER TABLE `files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=832;

--
-- AUTO_INCREMENT for table `folders`
--
ALTER TABLE `folders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=271;

--
-- AUTO_INCREMENT for table `shared_folders`
--
ALTER TABLE `shared_folders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `files`
--
ALTER TABLE `files`
  ADD CONSTRAINT `files_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `files_ibfk_2` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `folders`
--
ALTER TABLE `folders`
  ADD CONSTRAINT `folders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shared_folders`
--
ALTER TABLE `shared_folders`
  ADD CONSTRAINT `shared_folders_ibfk_1` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shared_folders_ibfk_2` FOREIGN KEY (`shared_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shared_folders_ibfk_3` FOREIGN KEY (`shared_with`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
