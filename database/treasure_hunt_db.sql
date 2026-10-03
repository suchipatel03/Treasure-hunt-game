-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 03, 2026 at 09:31 AM
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
-- Database: `treasure_hunt_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `game_progress`
--

CREATE TABLE `game_progress` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `guest_name` varchar(50) DEFAULT NULL,
  `guest_age` int(11) DEFAULT NULL,
  `current_level` int(11) DEFAULT 1,
  `coins` int(11) DEFAULT 0,
  `free_hint_available` tinyint(1) DEFAULT 1,
  `hints_bought` int(11) NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `level_progress`
--

CREATE TABLE `level_progress` (
  `id` int(11) NOT NULL,
  `progress_id` int(11) NOT NULL,
  `level_number` int(11) NOT NULL,
  `completed` tinyint(1) DEFAULT 0,
  `hint_used` tinyint(1) DEFAULT 0,
  `attempts` int(11) DEFAULT 0,
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `level_number` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `answer` varchar(255) NOT NULL,
  `hint_text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`id`, `level_number`, `question_text`, `answer`, `hint_text`) VALUES
(1, 1, 'Level 1: I have keys but no locks, I have space but no room, and you can enter but cannot go inside. What am I?', 'keyboard', 'Hint: You use it every day to type.'),
(2, 2, 'Level 2: I have no brush, yet I paint every picture you see.\r\nI have no voice, yet I show you words silently.\r\nI can display millions of colors, but I create none of them.\r\nI sit before you, but I am not a window.\r\nWhat am I?', 'Monitor', 'Hint: You look at me while using a computer.'),
(3, 3, 'Level 3: Unlike my temporary friend, I remember even after the lights go out.\r\nI can hold photographs, videos, games, and documents.\r\nYou may delete what I remember, but I do not forget because of sleep.\r\nWhat am I?', 'Hard Drive', 'Hint: I store your files for the long term.'),
(4, 4, 'Level 4: Every computer on my road may have an address.\r\nI am made of numbers, yet I am not a phone number.\r\nWithout me, finding the correct machine would be difficult.\r\nWhat am I?', 'IP Address', 'Hint: Computers use me to identify a device on a network.'),
(5, 5, 'Level 5 - FINAL: I am not a lock, but I protect secrets.\r\nI take readable information and turn it into something that looks meaningless.\r\nWith the correct key, the secret can return to its original form.\r\nWhat am I?', 'Encryption', 'No hint for this level. Solve it by YOURSELF!!!');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(80) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `age` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `game_progress`
--
ALTER TABLE `game_progress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `level_progress`
--
ALTER TABLE `level_progress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `progress_id` (`progress_id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `level_number` (`level_number`);

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
-- AUTO_INCREMENT for table `game_progress`
--
ALTER TABLE `game_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `level_progress`
--
ALTER TABLE `level_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `game_progress`
--
ALTER TABLE `game_progress`
  ADD CONSTRAINT `game_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `level_progress`
--
ALTER TABLE `level_progress`
  ADD CONSTRAINT `level_progress_ibfk_1` FOREIGN KEY (`progress_id`) REFERENCES `game_progress` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
