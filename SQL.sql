-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Дек 02 2025 г., 20:34
-- Версия сервера: 5.7.27-30-log
-- Версия PHP: 8.3.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `partners1p`
--

-- --------------------------------------------------------

--
-- Структура таблицы `game_sessions`
--

CREATE TABLE `game_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `tournament_id` int(11) DEFAULT NULL,
  `game_result` enum('win','lose','abandoned') DEFAULT NULL,
  `points_earned` int(11) DEFAULT '0',
  `game_duration` int(11) DEFAULT '0',
  `played_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` int(11) NOT NULL DEFAULT '0',
  `last_attempt` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `payment_history`
--

CREATE TABLE `payment_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `method` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `external_id` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `payment_history`
--

INSERT INTO `payment_history` (`id`, `user_id`, `amount`, `method`, `status`, `external_id`, `created_at`) VALUES
(1, 18, -10.00, 'tournament_fee', 'completed', 'tournament_2', '2025-11-18 19:06:15'),
(2, 18, -15.00, 'tournament_fee', 'completed', 'tournament_7', '2025-11-18 21:12:46'),
(3, 65, -10.00, 'tournament_fee', 'completed', 'tournament_11', '2025-11-18 23:47:37'),
(4, 18, -10.00, 'tournament_fee', 'completed', 'tournament_11', '2025-11-19 08:52:55'),
(5, 18, -100.00, 'tournament_fee', 'completed', 'tournament_12', '2025-11-19 09:05:28'),
(6, 18, -100.00, 'tournament_fee', 'completed', 'tournament_15', '2025-11-19 11:05:07'),
(7, 18, -100.00, 'tournament_fee', 'completed', 'tournament_17', '2025-11-19 11:15:06'),
(8, 65, -10.00, 'tournament_fee', 'completed', 'tournament_30', '2025-11-19 19:14:51'),
(9, 18, -10.00, 'tournament_fee', 'completed', 'tournament_30', '2025-11-19 19:15:15'),
(10, 14, -10.00, 'tournament_fee', 'completed', 'tournament_30', '2025-11-19 19:15:52'),
(11, 17, -10.00, 'tournament_fee', 'completed', 'tournament_30', '2025-11-19 19:17:26'),
(12, 65, -10.00, 'tournament_fee', 'completed', 'tournament_34', '2025-11-19 23:09:58'),
(13, 17, -10.00, 'tournament_fee', 'completed', 'tournament_34', '2025-11-19 23:10:15'),
(14, 18, -10.00, 'tournament_fee', 'completed', 'tournament_34', '2025-11-19 23:10:35'),
(15, 14, -10.00, 'tournament_fee', 'completed', 'tournament_34', '2025-11-19 23:10:58'),
(16, 65, -10.00, 'tournament_fee', 'completed', 'tournament_35', '2025-11-20 10:49:09'),
(17, 17, -10.00, 'tournament_fee', 'completed', 'tournament_35', '2025-11-20 10:49:28'),
(18, 18, -10.00, 'tournament_fee', 'completed', 'tournament_35', '2025-11-20 10:49:48'),
(19, 14, -10.00, 'tournament_fee', 'completed', 'tournament_35', '2025-11-20 10:50:46'),
(20, 17, -10.00, 'tournament_fee', 'completed', 'tournament_39', '2025-11-21 08:59:14'),
(21, 18, -10.00, 'tournament_fee', 'completed', 'tournament_39', '2025-11-21 08:59:33'),
(22, 14, -10.00, 'tournament_fee', 'completed', 'tournament_39', '2025-11-21 08:59:54'),
(23, 14, -10.00, 'tournament_fee', 'completed', 'tournament_83', '2025-11-21 20:42:16'),
(24, 14, -10.00, 'tournament_fee', 'completed', 'tournament_72', '2025-11-21 20:42:19'),
(25, 14, -10.00, 'tournament_fee', 'completed', 'tournament_73', '2025-11-21 20:42:23'),
(26, 14, -20.00, 'tournament_fee', 'completed', 'tournament_74', '2025-11-21 20:42:27'),
(27, 14, -20.00, 'tournament_fee', 'completed', 'tournament_75', '2025-11-21 20:42:31'),
(28, 14, -20.00, 'tournament_fee', 'completed', 'tournament_76', '2025-11-21 20:42:35'),
(29, 14, -30.00, 'tournament_fee', 'completed', 'tournament_77', '2025-11-21 20:42:39'),
(30, 14, -30.00, 'tournament_fee', 'completed', 'tournament_78', '2025-11-21 20:42:42'),
(31, 14, -30.00, 'tournament_fee', 'completed', 'tournament_79', '2025-11-21 20:42:46'),
(32, 14, -40.00, 'tournament_fee', 'completed', 'tournament_80', '2025-11-21 20:42:50'),
(33, 14, -40.00, 'tournament_fee', 'completed', 'tournament_81', '2025-11-21 20:42:54'),
(34, 14, -40.00, 'tournament_fee', 'completed', 'tournament_82', '2025-11-21 20:42:58'),
(35, 17, -10.00, 'tournament_fee', 'completed', 'tournament_83', '2025-11-21 22:01:48'),
(36, 17, -10.00, 'tournament_fee', 'completed', 'tournament_72', '2025-11-21 22:01:56'),
(37, 17, -10.00, 'tournament_fee', 'completed', 'tournament_73', '2025-11-21 22:24:06'),
(38, 17, -20.00, 'tournament_fee', 'completed', 'tournament_74', '2025-11-21 23:34:30'),
(39, 17, -20.00, 'tournament_fee', 'completed', 'tournament_75', '2025-11-21 23:34:36'),
(40, 17, -20.00, 'tournament_fee', 'completed', 'tournament_76', '2025-11-21 23:35:29'),
(41, 17, -30.00, 'tournament_fee', 'completed', 'tournament_77', '2025-11-21 23:35:32'),
(42, 17, -30.00, 'tournament_fee', 'completed', 'tournament_78', '2025-11-21 23:35:37'),
(43, 17, -30.00, 'tournament_fee', 'completed', 'tournament_79', '2025-11-21 23:35:40'),
(44, 18, -10.00, 'tournament_fee', 'completed', 'tournament_83', '2025-11-21 23:42:49'),
(45, 18, -10.00, 'tournament_fee', 'completed', 'tournament_72', '2025-11-21 23:42:52'),
(46, 18, -10.00, 'tournament_fee', 'completed', 'tournament_73', '2025-11-21 23:43:09'),
(47, 18, -20.00, 'tournament_fee', 'completed', 'tournament_74', '2025-11-21 23:43:12'),
(48, 18, -20.00, 'tournament_fee', 'completed', 'tournament_75', '2025-11-21 23:43:16'),
(49, 18, -20.00, 'tournament_fee', 'completed', 'tournament_76', '2025-11-21 23:43:19'),
(50, 18, -30.00, 'tournament_fee', 'completed', 'tournament_77', '2025-11-21 23:43:22'),
(51, 18, -30.00, 'tournament_fee', 'completed', 'tournament_78', '2025-11-21 23:43:26'),
(52, 18, -30.00, 'tournament_fee', 'completed', 'tournament_79', '2025-11-21 23:43:29'),
(53, 18, -40.00, 'tournament_fee', 'completed', 'tournament_80', '2025-11-21 23:43:33'),
(54, 18, -40.00, 'tournament_fee', 'completed', 'tournament_81', '2025-11-21 23:43:36'),
(55, 18, -40.00, 'tournament_fee', 'completed', 'tournament_82', '2025-11-21 23:43:40'),
(56, 65, -10.00, 'tournament_fee', 'completed', 'tournament_83', '2025-11-21 23:44:55'),
(57, 65, -10.00, 'tournament_fee', 'completed', 'tournament_72', '2025-11-21 23:44:58'),
(58, 65, -10.00, 'tournament_fee', 'completed', 'tournament_73', '2025-11-21 23:45:01'),
(59, 65, -20.00, 'tournament_fee', 'completed', 'tournament_74', '2025-11-21 23:45:04'),
(60, 65, -20.00, 'tournament_fee', 'completed', 'tournament_75', '2025-11-21 23:45:07'),
(61, 65, -20.00, 'tournament_fee', 'completed', 'tournament_76', '2025-11-21 23:45:10'),
(62, 65, -30.00, 'tournament_fee', 'completed', 'tournament_77', '2025-11-21 23:45:13'),
(63, 65, -30.00, 'tournament_fee', 'completed', 'tournament_78', '2025-11-21 23:45:17'),
(64, 65, -30.00, 'tournament_fee', 'completed', 'tournament_79', '2025-11-21 23:45:19'),
(65, 65, -40.00, 'tournament_fee', 'completed', 'tournament_80', '2025-11-21 23:45:22'),
(66, 65, -40.00, 'tournament_fee', 'completed', 'tournament_81', '2025-11-22 00:10:35'),
(67, 14, -10.00, 'tournament_fee', 'completed', 'tournament_85', '2025-11-22 10:30:37'),
(68, 14, -10.00, 'tournament_fee', 'completed', 'tournament_86', '2025-11-22 10:30:42'),
(69, 14, -10.00, 'tournament_fee', 'completed', 'tournament_87', '2025-11-22 10:30:54'),
(70, 14, -20.00, 'tournament_fee', 'completed', 'tournament_88', '2025-11-22 10:30:59'),
(71, 14, -20.00, 'tournament_fee', 'completed', 'tournament_89', '2025-11-22 10:31:03'),
(72, 14, -20.00, 'tournament_fee', 'completed', 'tournament_90', '2025-11-22 10:31:07'),
(73, 14, -30.00, 'tournament_fee', 'completed', 'tournament_91', '2025-11-22 10:31:11'),
(74, 14, -30.00, 'tournament_fee', 'completed', 'tournament_92', '2025-11-22 10:31:15'),
(75, 14, -30.00, 'tournament_fee', 'completed', 'tournament_93', '2025-11-22 10:31:19'),
(76, 14, -40.00, 'tournament_fee', 'completed', 'tournament_94', '2025-11-22 10:31:23'),
(77, 18, -10.00, 'tournament_fee', 'completed', 'tournament_85', '2025-11-22 16:15:01'),
(78, 18, -10.00, 'tournament_fee', 'completed', 'tournament_86', '2025-11-22 16:15:04'),
(79, 18, -10.00, 'tournament_fee', 'completed', 'tournament_87', '2025-11-22 16:15:07'),
(80, 18, -20.00, 'tournament_fee', 'completed', 'tournament_88', '2025-11-22 16:15:08'),
(81, 18, -20.00, 'tournament_fee', 'completed', 'tournament_90', '2025-11-22 16:15:12'),
(82, 18, -30.00, 'tournament_fee', 'completed', 'tournament_92', '2025-11-22 16:15:16'),
(83, 18, -30.00, 'tournament_fee', 'completed', 'tournament_93', '2025-11-22 16:15:18'),
(84, 18, -40.00, 'tournament_fee', 'completed', 'tournament_94', '2025-11-22 16:15:19'),
(85, 18, -40.00, 'tournament_fee', 'completed', 'tournament_95', '2025-11-22 16:15:22'),
(86, 18, -40.00, 'tournament_fee', 'completed', 'tournament_96', '2025-11-22 16:15:24'),
(87, 65, -40.00, 'tournament_fee', 'completed', 'tournament_82', '2025-11-22 16:16:03'),
(88, 65, -10.00, 'tournament_fee', 'completed', 'tournament_85', '2025-11-22 16:16:06'),
(89, 65, -10.00, 'tournament_fee', 'completed', 'tournament_86', '2025-11-22 16:16:08'),
(90, 65, -10.00, 'tournament_fee', 'completed', 'tournament_87', '2025-11-22 16:16:09'),
(91, 65, -20.00, 'tournament_fee', 'completed', 'tournament_88', '2025-11-22 16:16:14'),
(92, 65, -20.00, 'tournament_fee', 'completed', 'tournament_89', '2025-11-22 16:16:15'),
(93, 65, -20.00, 'tournament_fee', 'completed', 'tournament_90', '2025-11-22 16:16:18'),
(94, 65, -30.00, 'tournament_fee', 'completed', 'tournament_91', '2025-11-22 16:16:20'),
(95, 65, -30.00, 'tournament_fee', 'completed', 'tournament_92', '2025-11-22 16:16:23'),
(96, 65, -30.00, 'tournament_fee', 'completed', 'tournament_93', '2025-11-22 16:16:24'),
(97, 65, -40.00, 'tournament_fee', 'completed', 'tournament_94', '2025-11-22 16:16:26'),
(98, 65, -40.00, 'tournament_fee', 'completed', 'tournament_95', '2025-11-22 16:16:28'),
(99, 65, -40.00, 'tournament_fee', 'completed', 'tournament_96', '2025-11-22 16:16:31'),
(100, 17, -40.00, 'tournament_fee', 'completed', 'tournament_80', '2025-11-22 16:17:36'),
(101, 17, -40.00, 'tournament_fee', 'completed', 'tournament_81', '2025-11-22 16:17:41'),
(102, 17, -40.00, 'tournament_fee', 'completed', 'tournament_82', '2025-11-22 16:17:44'),
(103, 17, -10.00, 'tournament_fee', 'completed', 'tournament_86', '2025-11-22 16:17:49'),
(104, 17, -10.00, 'tournament_fee', 'completed', 'tournament_87', '2025-11-22 16:17:57'),
(105, 17, -20.00, 'tournament_fee', 'completed', 'tournament_88', '2025-11-22 16:18:00'),
(106, 17, -20.00, 'tournament_fee', 'completed', 'tournament_89', '2025-11-22 16:18:05'),
(107, 17, -20.00, 'tournament_fee', 'completed', 'tournament_90', '2025-11-22 16:18:09'),
(108, 17, -30.00, 'tournament_fee', 'completed', 'tournament_91', '2025-11-22 16:18:14'),
(109, 14, -40.00, 'tournament_fee', 'completed', 'tournament_95', '2025-11-22 16:19:24'),
(110, 14, -40.00, 'tournament_fee', 'completed', 'tournament_96', '2025-11-22 16:19:27'),
(111, 18, -30.00, 'tournament_fee', 'completed', 'tournament_91', '2025-11-22 19:49:05'),
(112, 18, -20.00, 'tournament_fee', 'completed', 'tournament_89', '2025-11-22 19:49:10'),
(113, 17, -10.00, 'tournament_fee', 'completed', 'tournament_85', '2025-11-22 20:20:11'),
(114, 17, -30.00, 'tournament_fee', 'completed', 'tournament_92', '2025-11-22 20:20:23'),
(115, 17, -30.00, 'tournament_fee', 'completed', 'tournament_93', '2025-11-22 20:20:27'),
(116, 17, -40.00, 'tournament_fee', 'completed', 'tournament_94', '2025-11-22 20:21:54'),
(117, 17, -40.00, 'tournament_fee', 'completed', 'tournament_95', '2025-11-22 20:22:06'),
(118, 17, -40.00, 'tournament_fee', 'completed', 'tournament_96', '2025-11-22 20:22:15'),
(119, 17, -10.00, 'tournament_fee', 'completed', 'tournament_97', '2025-11-23 09:55:41'),
(120, 17, -10.00, 'tournament_fee', 'completed', 'tournament_98', '2025-11-23 09:55:45'),
(121, 17, -10.00, 'tournament_fee', 'completed', 'tournament_99', '2025-11-23 09:55:57'),
(122, 17, -20.00, 'tournament_fee', 'completed', 'tournament_100', '2025-11-23 09:56:06'),
(123, 17, -20.00, 'tournament_fee', 'completed', 'tournament_101', '2025-11-23 09:56:11'),
(124, 17, -20.00, 'tournament_fee', 'completed', 'tournament_102', '2025-11-23 09:56:18'),
(125, 17, -30.00, 'tournament_fee', 'completed', 'tournament_103', '2025-11-23 09:56:22'),
(126, 17, -30.00, 'tournament_fee', 'completed', 'tournament_104', '2025-11-23 09:56:26'),
(127, 17, -30.00, 'tournament_fee', 'completed', 'tournament_105', '2025-11-23 09:56:29'),
(128, 17, -40.00, 'tournament_fee', 'completed', 'tournament_106', '2025-11-23 09:56:33'),
(129, 17, -40.00, 'tournament_fee', 'completed', 'tournament_107', '2025-11-23 09:56:38'),
(130, 14, -10.00, 'tournament_fee', 'completed', 'tournament_97', '2025-11-23 10:09:31'),
(131, 14, -10.00, 'tournament_fee', 'completed', 'tournament_98', '2025-11-23 10:09:32'),
(132, 14, -10.00, 'tournament_fee', 'completed', 'tournament_99', '2025-11-23 10:09:34'),
(133, 14, -20.00, 'tournament_fee', 'completed', 'tournament_100', '2025-11-23 10:09:35'),
(134, 14, -20.00, 'tournament_fee', 'completed', 'tournament_101', '2025-11-23 10:09:38'),
(135, 14, -20.00, 'tournament_fee', 'completed', 'tournament_102', '2025-11-23 10:09:40'),
(136, 14, -30.00, 'tournament_fee', 'completed', 'tournament_103', '2025-11-23 10:09:42'),
(137, 14, -30.00, 'tournament_fee', 'completed', 'tournament_104', '2025-11-23 10:09:44'),
(138, 14, -40.00, 'tournament_fee', 'completed', 'tournament_106', '2025-11-23 10:09:48'),
(139, 14, -40.00, 'tournament_fee', 'completed', 'tournament_107', '2025-11-23 10:09:50'),
(140, 14, -40.00, 'tournament_fee', 'completed', 'tournament_108', '2025-11-23 10:09:51'),
(141, 14, -30.00, 'tournament_fee', 'completed', 'tournament_105', '2025-11-23 10:11:16'),
(142, 17, -40.00, 'tournament_fee', 'completed', 'tournament_108', '2025-11-23 11:11:42'),
(143, 65, -10.00, 'tournament_fee', 'completed', 'tournament_97', '2025-11-23 19:30:11'),
(144, 65, -10.00, 'tournament_fee', 'completed', 'tournament_98', '2025-11-23 19:30:36'),
(145, 65, -10.00, 'tournament_fee', 'completed', 'tournament_99', '2025-11-23 19:31:10'),
(146, 65, -20.00, 'tournament_fee', 'completed', 'tournament_100', '2025-11-23 19:31:14'),
(147, 65, -20.00, 'tournament_fee', 'completed', 'tournament_101', '2025-11-23 19:32:13'),
(148, 65, -20.00, 'tournament_fee', 'completed', 'tournament_102', '2025-11-23 19:32:19'),
(149, 65, -30.00, 'tournament_fee', 'completed', 'tournament_103', '2025-11-23 19:33:05'),
(150, 65, -30.00, 'tournament_fee', 'completed', 'tournament_104', '2025-11-23 19:49:17'),
(151, 65, -30.00, 'tournament_fee', 'completed', 'tournament_105', '2025-11-23 19:49:28'),
(152, 65, -40.00, 'tournament_fee', 'completed', 'tournament_106', '2025-11-23 20:16:54'),
(153, 65, -40.00, 'tournament_fee', 'completed', 'tournament_107', '2025-11-23 20:17:57'),
(154, 65, -40.00, 'tournament_fee', 'completed', 'tournament_108', '2025-11-23 20:18:49'),
(155, 18, -10.00, 'tournament_fee', 'completed', 'tournament_97', '2025-11-23 20:48:35'),
(156, 18, -10.00, 'tournament_fee', 'completed', 'tournament_98', '2025-11-23 20:48:42'),
(157, 18, -10.00, 'tournament_fee', 'completed', 'tournament_99', '2025-11-23 20:49:13'),
(158, 18, -20.00, 'tournament_fee', 'completed', 'tournament_100', '2025-11-23 20:50:32'),
(159, 18, -20.00, 'tournament_fee', 'completed', 'tournament_101', '2025-11-23 20:51:15'),
(160, 18, -20.00, 'tournament_fee', 'completed', 'tournament_102', '2025-11-23 20:51:23'),
(161, 18, -30.00, 'tournament_fee', 'completed', 'tournament_103', '2025-11-23 20:51:36'),
(162, 18, -30.00, 'tournament_fee', 'completed', 'tournament_104', '2025-11-23 20:52:07'),
(163, 18, -30.00, 'tournament_fee', 'completed', 'tournament_105', '2025-11-23 21:05:06'),
(164, 18, -40.00, 'tournament_fee', 'completed', 'tournament_106', '2025-11-23 21:05:12'),
(165, 18, -40.00, 'tournament_fee', 'completed', 'tournament_107', '2025-11-23 21:05:20'),
(166, 18, -40.00, 'tournament_fee', 'completed', 'tournament_108', '2025-11-23 21:05:26'),
(167, 14, -10.00, 'tournament_fee', 'completed', 'tournament_109', '2025-11-24 07:58:44'),
(168, 14, -10.00, 'tournament_fee', 'completed', 'tournament_110', '2025-11-24 07:58:50'),
(169, 14, -10.00, 'tournament_fee', 'completed', 'tournament_111', '2025-11-24 07:59:27'),
(170, 14, -20.00, 'tournament_fee', 'completed', 'tournament_112', '2025-11-24 08:02:01'),
(171, 14, -20.00, 'tournament_fee', 'completed', 'tournament_113', '2025-11-24 08:02:15'),
(172, 14, -20.00, 'tournament_fee', 'completed', 'tournament_114', '2025-11-24 09:23:07'),
(173, 14, -30.00, 'tournament_fee', 'completed', 'tournament_115', '2025-11-24 09:23:27'),
(174, 14, -30.00, 'tournament_fee', 'completed', 'tournament_116', '2025-11-24 09:23:49'),
(175, 14, -30.00, 'tournament_fee', 'completed', 'tournament_117', '2025-11-24 09:24:06'),
(176, 14, -40.00, 'tournament_fee', 'completed', 'tournament_118', '2025-11-24 09:24:49'),
(177, 14, -40.00, 'tournament_fee', 'completed', 'tournament_119', '2025-11-24 09:24:55'),
(178, 14, -40.00, 'tournament_fee', 'completed', 'tournament_120', '2025-11-24 09:25:04'),
(179, 65, -10.00, 'tournament_fee', 'completed', 'tournament_109', '2025-11-24 09:26:44'),
(180, 65, -10.00, 'tournament_fee', 'completed', 'tournament_110', '2025-11-24 09:26:50'),
(181, 65, -10.00, 'tournament_fee', 'completed', 'tournament_111', '2025-11-24 09:26:58'),
(182, 65, -20.00, 'tournament_fee', 'completed', 'tournament_112', '2025-11-24 09:27:05'),
(183, 65, -20.00, 'tournament_fee', 'completed', 'tournament_113', '2025-11-24 09:27:12'),
(184, 65, -20.00, 'tournament_fee', 'completed', 'tournament_114', '2025-11-24 09:27:19'),
(185, 65, -30.00, 'tournament_fee', 'completed', 'tournament_115', '2025-11-24 09:27:55'),
(186, 65, -30.00, 'tournament_fee', 'completed', 'tournament_116', '2025-11-24 09:28:34'),
(187, 65, -30.00, 'tournament_fee', 'completed', 'tournament_117', '2025-11-24 09:28:40'),
(188, 65, -40.00, 'tournament_fee', 'completed', 'tournament_118', '2025-11-24 09:28:48'),
(189, 65, -40.00, 'tournament_fee', 'completed', 'tournament_119', '2025-11-24 09:28:56'),
(190, 65, -40.00, 'tournament_fee', 'completed', 'tournament_120', '2025-11-24 09:29:04'),
(191, 17, -10.00, 'tournament_fee', 'completed', 'tournament_109', '2025-11-24 09:30:31'),
(192, 17, -10.00, 'tournament_fee', 'completed', 'tournament_110', '2025-11-24 09:30:36'),
(193, 17, -10.00, 'tournament_fee', 'completed', 'tournament_111', '2025-11-24 09:30:46'),
(194, 17, -20.00, 'tournament_fee', 'completed', 'tournament_112', '2025-11-24 09:30:52'),
(195, 17, -20.00, 'tournament_fee', 'completed', 'tournament_113', '2025-11-24 09:30:59'),
(196, 17, -20.00, 'tournament_fee', 'completed', 'tournament_114', '2025-11-24 09:31:06'),
(197, 17, -30.00, 'tournament_fee', 'completed', 'tournament_115', '2025-11-24 09:31:15'),
(198, 17, -30.00, 'tournament_fee', 'completed', 'tournament_116', '2025-11-24 09:31:23'),
(199, 17, -30.00, 'tournament_fee', 'completed', 'tournament_117', '2025-11-24 09:31:31'),
(200, 17, -40.00, 'tournament_fee', 'completed', 'tournament_118', '2025-11-24 09:31:38'),
(201, 17, -40.00, 'tournament_fee', 'completed', 'tournament_119', '2025-11-24 09:31:47'),
(202, 17, -40.00, 'tournament_fee', 'completed', 'tournament_120', '2025-11-24 09:31:54'),
(203, 18, -10.00, 'tournament_fee', 'completed', 'tournament_109', '2025-11-24 09:35:33'),
(204, 18, -10.00, 'tournament_fee', 'completed', 'tournament_110', '2025-11-24 09:35:38'),
(205, 18, -10.00, 'tournament_fee', 'completed', 'tournament_111', '2025-11-24 09:35:46'),
(206, 18, -20.00, 'tournament_fee', 'completed', 'tournament_112', '2025-11-24 09:35:52'),
(207, 18, -20.00, 'tournament_fee', 'completed', 'tournament_113', '2025-11-24 09:35:58'),
(208, 18, -20.00, 'tournament_fee', 'completed', 'tournament_114', '2025-11-24 09:36:05'),
(209, 18, -30.00, 'tournament_fee', 'completed', 'tournament_115', '2025-11-24 09:36:12'),
(210, 18, -30.00, 'tournament_fee', 'completed', 'tournament_116', '2025-11-24 09:36:18'),
(211, 18, -30.00, 'tournament_fee', 'completed', 'tournament_117', '2025-11-24 09:36:26'),
(212, 18, -40.00, 'tournament_fee', 'completed', 'tournament_118', '2025-11-24 09:36:33'),
(213, 18, -40.00, 'tournament_fee', 'completed', 'tournament_119', '2025-11-24 09:36:40'),
(214, 18, -40.00, 'tournament_fee', 'completed', 'tournament_120', '2025-11-24 09:36:46'),
(215, 17, -100.00, 'tournament_fee', 'completed', 'tournament_122', '2025-11-26 00:09:47'),
(216, 17, -100.00, 'tournament_fee', 'completed', 'tournament_123', '2025-11-26 00:09:53'),
(217, 17, -100.00, 'tournament_fee', 'completed', 'tournament_124', '2025-11-26 00:09:59'),
(218, 17, -200.00, 'tournament_fee', 'completed', 'tournament_125', '2025-11-26 00:10:09'),
(219, 18, -100.00, 'tournament_fee', 'completed', 'tournament_122', '2025-11-26 12:49:05'),
(220, 18, -100.00, 'tournament_fee', 'completed', 'tournament_123', '2025-11-26 12:49:19'),
(221, 18, -100.00, 'tournament_fee', 'completed', 'tournament_124', '2025-11-26 12:49:25'),
(222, 18, -200.00, 'tournament_fee', 'completed', 'tournament_125', '2025-11-26 12:49:32'),
(223, 18, -200.00, 'tournament_fee', 'completed', 'tournament_126', '2025-11-26 12:49:37'),
(224, 18, -200.00, 'tournament_fee', 'completed', 'tournament_127', '2025-11-26 12:49:51'),
(225, 18, -300.00, 'tournament_fee', 'completed', 'tournament_128', '2025-11-26 12:49:57'),
(226, 18, -300.00, 'tournament_fee', 'completed', 'tournament_129', '2025-11-26 12:50:10'),
(227, 18, -300.00, 'tournament_fee', 'completed', 'tournament_130', '2025-11-26 12:50:15'),
(228, 18, -400.00, 'tournament_fee', 'completed', 'tournament_131', '2025-11-26 12:50:21'),
(229, 18, -400.00, 'tournament_fee', 'completed', 'tournament_132', '2025-11-26 12:50:26'),
(230, 18, -400.00, 'tournament_fee', 'completed', 'tournament_133', '2025-11-26 12:50:32'),
(231, 65, -100.00, 'tournament_fee', 'completed', 'tournament_122', '2025-11-26 13:40:39'),
(232, 65, -100.00, 'tournament_fee', 'completed', 'tournament_123', '2025-11-26 13:40:52'),
(233, 65, -100.00, 'tournament_fee', 'completed', 'tournament_134', '2025-11-26 16:01:04'),
(234, 17, -100.00, 'tournament_fee', 'completed', 'tournament_134', '2025-11-26 16:01:29'),
(235, 18, -100.00, 'tournament_fee', 'completed', 'tournament_134', '2025-11-26 16:01:46'),
(236, 14, -100.00, 'tournament_fee', 'completed', 'tournament_134', '2025-11-26 16:02:08'),
(237, 65, -10.00, 'tournament_fee', 'completed', 'tournament_135', '2025-11-26 19:32:41'),
(238, 17, -10.00, 'tournament_fee', 'completed', 'tournament_135', '2025-11-26 19:33:01'),
(239, 18, -10.00, 'tournament_fee', 'completed', 'tournament_135', '2025-11-26 19:33:22'),
(240, 14, -10.00, 'tournament_fee', 'completed', 'tournament_135', '2025-11-26 19:33:46'),
(241, 65, -300.00, 'tournament_fee', 'completed', 'tournament_128', '2025-11-27 10:37:18'),
(242, 65, -10.00, 'tournament_fee', 'completed', 'tournament_151', '2025-11-27 16:29:49'),
(243, 65, -10.00, 'tournament_fee', 'completed', 'tournament_152', '2025-11-27 16:34:31'),
(244, 17, -10.00, 'tournament_fee', 'completed', 'tournament_151', '2025-11-27 16:34:59'),
(245, 17, -10.00, 'tournament_fee', 'completed', 'tournament_152', '2025-11-27 16:35:12'),
(246, 18, -10.00, 'tournament_fee', 'completed', 'tournament_152', '2025-11-27 16:35:38'),
(247, 14, -10.00, 'tournament_fee', 'completed', 'tournament_152', '2025-11-27 16:36:09'),
(248, 65, -10.00, 'tournament_fee', 'completed', 'tournament_153', '2025-11-27 21:56:50'),
(249, 65, -100.00, 'tournament_fee', 'completed', 'tournament_154', '2025-11-28 00:10:29'),
(250, 65, -100.00, 'tournament_fee', 'completed', 'tournament_155', '2025-11-28 09:26:49'),
(251, 65, -100.00, 'tournament_fee', 'completed', 'tournament_156', '2025-11-28 09:27:00'),
(252, 65, -200.00, 'tournament_fee', 'completed', 'tournament_159', '2025-11-28 14:51:38'),
(253, 17, -100.00, 'tournament_fee', 'completed', 'tournament_154', '2025-11-28 17:20:37'),
(254, 17, -100.00, 'tournament_fee', 'completed', 'tournament_155', '2025-11-28 17:20:54'),
(255, 17, -100.00, 'tournament_fee', 'completed', 'tournament_156', '2025-11-28 17:21:07'),
(256, 17, -200.00, 'tournament_fee', 'completed', 'tournament_157', '2025-11-28 17:21:15'),
(257, 14, -300.00, 'tournament_fee', 'completed', 'tournament_160', '2025-11-29 11:45:23'),
(258, 14, -300.00, 'tournament_fee', 'completed', 'tournament_161', '2025-11-29 11:46:10'),
(259, 14, -300.00, 'tournament_fee', 'completed', 'tournament_162', '2025-11-29 12:53:52'),
(260, 17, -300.00, 'tournament_fee', 'completed', 'tournament_162', '2025-11-29 14:35:55'),
(261, 17, -400.00, 'tournament_fee', 'completed', 'tournament_163', '2025-11-29 14:36:00'),
(262, 17, -400.00, 'tournament_fee', 'completed', 'tournament_164', '2025-11-29 14:36:05'),
(263, 17, -400.00, 'tournament_fee', 'completed', 'tournament_165', '2025-11-29 14:57:59'),
(264, 17, -100.00, 'tournament_fee', 'completed', 'tournament_166', '2025-11-29 14:58:12'),
(265, 17, -100.00, 'tournament_fee', 'completed', 'tournament_167', '2025-11-29 15:15:54'),
(266, 17, -100.00, 'tournament_fee', 'completed', 'tournament_168', '2025-11-29 15:15:59'),
(267, 17, -200.00, 'tournament_fee', 'completed', 'tournament_169', '2025-11-29 15:16:04'),
(268, 17, -200.00, 'tournament_fee', 'completed', 'tournament_170', '2025-11-29 15:16:15'),
(269, 17, -200.00, 'tournament_fee', 'completed', 'tournament_171', '2025-11-29 15:16:27'),
(270, 17, -300.00, 'tournament_fee', 'completed', 'tournament_172', '2025-11-29 15:16:41'),
(271, 17, -300.00, 'tournament_fee', 'completed', 'tournament_173', '2025-11-29 15:16:49'),
(272, 17, -300.00, 'tournament_fee', 'completed', 'tournament_174', '2025-11-29 15:17:00'),
(273, 17, -400.00, 'tournament_fee', 'completed', 'tournament_175', '2025-11-29 15:17:09'),
(274, 17, -400.00, 'tournament_fee', 'completed', 'tournament_176', '2025-11-29 15:17:16'),
(275, 17, -400.00, 'tournament_fee', 'completed', 'tournament_177', '2025-11-29 15:17:24'),
(276, 65, -400.00, 'tournament_fee', 'completed', 'tournament_163', '2025-11-29 17:36:55'),
(277, 65, -400.00, 'tournament_fee', 'completed', 'tournament_164', '2025-11-29 17:37:01'),
(278, 65, -400.00, 'tournament_fee', 'completed', 'tournament_165', '2025-11-29 17:37:07'),
(279, 65, -100.00, 'tournament_fee', 'completed', 'tournament_166', '2025-11-29 17:37:12'),
(280, 65, -100.00, 'tournament_fee', 'completed', 'tournament_167', '2025-11-29 17:37:22'),
(281, 65, -100.00, 'tournament_fee', 'completed', 'tournament_168', '2025-11-29 17:37:28'),
(282, 65, -200.00, 'tournament_fee', 'completed', 'tournament_169', '2025-11-29 17:37:33'),
(283, 65, -200.00, 'tournament_fee', 'completed', 'tournament_170', '2025-11-29 17:37:39'),
(284, 65, -200.00, 'tournament_fee', 'completed', 'tournament_171', '2025-11-29 17:37:44'),
(285, 65, -300.00, 'tournament_fee', 'completed', 'tournament_172', '2025-11-29 17:37:50'),
(286, 65, -300.00, 'tournament_fee', 'completed', 'tournament_173', '2025-11-29 17:37:55'),
(287, 65, -300.00, 'tournament_fee', 'completed', 'tournament_174', '2025-11-29 17:38:01'),
(288, 65, -400.00, 'tournament_fee', 'completed', 'tournament_175', '2025-11-29 17:38:06'),
(289, 65, -400.00, 'tournament_fee', 'completed', 'tournament_176', '2025-11-29 17:38:11'),
(290, 65, -400.00, 'tournament_fee', 'completed', 'tournament_177', '2025-11-29 17:38:16'),
(291, 18, -400.00, 'tournament_fee', 'completed', 'tournament_163', '2025-11-29 17:39:10'),
(292, 18, -400.00, 'tournament_fee', 'completed', 'tournament_164', '2025-11-29 17:39:13'),
(293, 18, -400.00, 'tournament_fee', 'completed', 'tournament_165', '2025-11-29 17:39:15'),
(294, 18, -100.00, 'tournament_fee', 'completed', 'tournament_166', '2025-11-29 17:39:17'),
(295, 18, -100.00, 'tournament_fee', 'completed', 'tournament_167', '2025-11-29 17:39:19'),
(296, 18, -100.00, 'tournament_fee', 'completed', 'tournament_168', '2025-11-29 17:39:21'),
(297, 18, -200.00, 'tournament_fee', 'completed', 'tournament_169', '2025-11-29 17:39:23'),
(298, 18, -200.00, 'tournament_fee', 'completed', 'tournament_170', '2025-11-29 17:39:25'),
(299, 18, -200.00, 'tournament_fee', 'completed', 'tournament_171', '2025-11-29 17:39:30'),
(300, 18, -300.00, 'tournament_fee', 'completed', 'tournament_172', '2025-11-29 17:39:32'),
(301, 18, -300.00, 'tournament_fee', 'completed', 'tournament_173', '2025-11-29 17:39:37'),
(302, 18, -300.00, 'tournament_fee', 'completed', 'tournament_174', '2025-11-29 17:39:41'),
(303, 18, -400.00, 'tournament_fee', 'completed', 'tournament_175', '2025-11-29 17:39:44'),
(304, 18, -400.00, 'tournament_fee', 'completed', 'tournament_176', '2025-11-29 17:39:49'),
(305, 18, -400.00, 'tournament_fee', 'completed', 'tournament_177', '2025-11-29 17:39:52'),
(306, 14, -400.00, 'tournament_fee', 'completed', 'tournament_163', '2025-11-29 17:40:20'),
(307, 14, -400.00, 'tournament_fee', 'completed', 'tournament_164', '2025-11-29 17:40:22'),
(308, 14, -100.00, 'tournament_fee', 'completed', 'tournament_166', '2025-11-29 17:40:28'),
(309, 14, -400.00, 'tournament_fee', 'completed', 'tournament_165', '2025-11-29 17:40:31'),
(310, 14, -100.00, 'tournament_fee', 'completed', 'tournament_167', '2025-11-29 17:40:34'),
(311, 14, -100.00, 'tournament_fee', 'completed', 'tournament_168', '2025-11-29 17:40:37'),
(312, 14, -200.00, 'tournament_fee', 'completed', 'tournament_169', '2025-11-29 17:40:39'),
(313, 14, -200.00, 'tournament_fee', 'completed', 'tournament_170', '2025-11-29 17:40:41'),
(314, 14, -200.00, 'tournament_fee', 'completed', 'tournament_171', '2025-11-29 17:40:44'),
(315, 14, -300.00, 'tournament_fee', 'completed', 'tournament_172', '2025-11-29 17:40:47'),
(316, 14, -300.00, 'tournament_fee', 'completed', 'tournament_173', '2025-11-29 17:40:49'),
(317, 14, -300.00, 'tournament_fee', 'completed', 'tournament_174', '2025-11-29 17:40:52'),
(318, 14, -400.00, 'tournament_fee', 'completed', 'tournament_175', '2025-11-29 17:40:55'),
(319, 14, -400.00, 'tournament_fee', 'completed', 'tournament_176', '2025-11-29 17:40:57'),
(320, 14, -400.00, 'tournament_fee', 'completed', 'tournament_177', '2025-11-29 17:41:00'),
(321, 65, -100.00, 'tournament_fee', 'completed', 'tournament_178', '2025-11-30 12:16:57'),
(322, 65, -100.00, 'tournament_fee', 'completed', 'tournament_179', '2025-11-30 12:17:04'),
(323, 65, -100.00, 'tournament_fee', 'completed', 'tournament_180', '2025-11-30 12:17:11'),
(324, 65, -200.00, 'tournament_fee', 'completed', 'tournament_181', '2025-11-30 12:17:16'),
(325, 17, -100.00, 'tournament_fee', 'completed', 'tournament_178', '2025-11-30 19:24:23'),
(326, 17, -100.00, 'tournament_fee', 'completed', 'tournament_179', '2025-11-30 19:24:29'),
(327, 17, -100.00, 'tournament_fee', 'completed', 'tournament_180', '2025-11-30 19:24:35'),
(328, 17, -200.00, 'tournament_fee', 'completed', 'tournament_181', '2025-11-30 19:24:41'),
(329, 17, -200.00, 'tournament_fee', 'completed', 'tournament_183', '2025-12-01 09:33:18'),
(330, 17, -300.00, 'tournament_fee', 'completed', 'tournament_184', '2025-12-01 09:33:22'),
(331, 17, -300.00, 'tournament_fee', 'completed', 'tournament_185', '2025-12-01 09:33:25'),
(332, 17, -300.00, 'tournament_fee', 'completed', 'tournament_186', '2025-12-01 09:35:07'),
(333, 17, -400.00, 'tournament_fee', 'completed', 'tournament_187', '2025-12-01 09:35:09'),
(334, 17, -400.00, 'tournament_fee', 'completed', 'tournament_188', '2025-12-01 09:35:14'),
(335, 17, -400.00, 'tournament_fee', 'completed', 'tournament_189', '2025-12-01 09:35:19'),
(336, 17, -100.00, 'tournament_fee', 'completed', 'tournament_190', '2025-12-01 09:35:25'),
(337, 17, -100.00, 'tournament_fee', 'completed', 'tournament_191', '2025-12-01 09:35:56'),
(338, 17, -100.00, 'tournament_fee', 'completed', 'tournament_192', '2025-12-01 09:36:29'),
(339, 65, -200.00, 'tournament_fee', 'completed', 'tournament_183', '2025-12-01 09:37:02'),
(340, 65, -300.00, 'tournament_fee', 'completed', 'tournament_184', '2025-12-01 09:37:10'),
(341, 65, -300.00, 'tournament_fee', 'completed', 'tournament_185', '2025-12-01 09:37:34'),
(342, 65, -300.00, 'tournament_fee', 'completed', 'tournament_186', '2025-12-01 09:37:53'),
(343, 65, -400.00, 'tournament_fee', 'completed', 'tournament_187', '2025-12-01 09:38:07'),
(344, 65, -400.00, 'tournament_fee', 'completed', 'tournament_188', '2025-12-01 09:38:36'),
(345, 65, -400.00, 'tournament_fee', 'completed', 'tournament_189', '2025-12-01 09:38:38'),
(346, 65, -100.00, 'tournament_fee', 'completed', 'tournament_190', '2025-12-01 09:38:41'),
(347, 65, -100.00, 'tournament_fee', 'completed', 'tournament_191', '2025-12-01 09:38:43'),
(348, 65, -100.00, 'tournament_fee', 'completed', 'tournament_192', '2025-12-01 09:38:46'),
(349, 18, -200.00, 'tournament_fee', 'completed', 'tournament_183', '2025-12-01 09:39:27'),
(350, 18, -300.00, 'tournament_fee', 'completed', 'tournament_184', '2025-12-01 09:39:43'),
(351, 18, -300.00, 'tournament_fee', 'completed', 'tournament_185', '2025-12-01 09:39:58'),
(352, 18, -300.00, 'tournament_fee', 'completed', 'tournament_186', '2025-12-01 09:40:07'),
(353, 18, -400.00, 'tournament_fee', 'completed', 'tournament_187', '2025-12-01 09:40:16'),
(354, 18, -400.00, 'tournament_fee', 'completed', 'tournament_188', '2025-12-01 09:40:30'),
(355, 18, -400.00, 'tournament_fee', 'completed', 'tournament_189', '2025-12-01 09:40:34'),
(356, 18, -100.00, 'tournament_fee', 'completed', 'tournament_190', '2025-12-01 09:40:42'),
(357, 18, -100.00, 'tournament_fee', 'completed', 'tournament_191', '2025-12-01 09:41:55'),
(358, 18, -100.00, 'tournament_fee', 'completed', 'tournament_192', '2025-12-01 09:45:17'),
(359, 18, -200.00, 'tournament_fee', 'completed', 'tournament_193', '2025-12-01 09:45:31'),
(360, 14, -200.00, 'tournament_fee', 'completed', 'tournament_183', '2025-12-01 09:46:32'),
(361, 14, -300.00, 'tournament_fee', 'completed', 'tournament_184', '2025-12-01 09:46:42'),
(362, 14, -300.00, 'tournament_fee', 'completed', 'tournament_185', '2025-12-01 09:47:05'),
(363, 14, -300.00, 'tournament_fee', 'completed', 'tournament_186', '2025-12-01 10:02:36'),
(364, 14, -400.00, 'tournament_fee', 'completed', 'tournament_187', '2025-12-01 10:06:35'),
(365, 14, -400.00, 'tournament_fee', 'completed', 'tournament_188', '2025-12-01 10:06:47'),
(366, 14, -400.00, 'tournament_fee', 'completed', 'tournament_189', '2025-12-01 10:08:40'),
(367, 14, -100.00, 'tournament_fee', 'completed', 'tournament_190', '2025-12-01 10:09:09'),
(368, 14, -100.00, 'tournament_fee', 'completed', 'tournament_191', '2025-12-01 10:11:48'),
(369, 14, -100.00, 'tournament_fee', 'completed', 'tournament_192', '2025-12-01 10:23:13'),
(370, 14, -200.00, 'tournament_fee', 'completed', 'tournament_193', '2025-12-01 10:25:02'),
(371, 14, -200.00, 'tournament_fee', 'completed', 'tournament_194', '2025-12-01 10:25:12'),
(372, 65, -400.00, 'tournament_fee', 'completed', 'tournament_201', '2025-12-01 21:10:46'),
(373, 65, -400.00, 'tournament_fee', 'completed', 'tournament_200', '2025-12-01 21:10:48'),
(374, 65, -400.00, 'tournament_fee', 'completed', 'tournament_199', '2025-12-01 21:10:52'),
(375, 65, -300.00, 'tournament_fee', 'completed', 'tournament_198', '2025-12-01 21:10:57'),
(376, 65, -300.00, 'tournament_fee', 'completed', 'tournament_197', '2025-12-01 21:11:02'),
(377, 65, -300.00, 'tournament_fee', 'completed', 'tournament_196', '2025-12-01 21:11:07'),
(378, 65, -200.00, 'tournament_fee', 'completed', 'tournament_195', '2025-12-01 21:11:08'),
(379, 65, -200.00, 'tournament_fee', 'completed', 'tournament_194', '2025-12-01 21:11:10'),
(380, 65, -200.00, 'tournament_fee', 'completed', 'tournament_193', '2025-12-01 21:11:13'),
(381, 17, -300.00, 'tournament_fee', 'completed', 'tournament_197', '2025-12-02 13:19:15'),
(382, 17, -300.00, 'tournament_fee', 'completed', 'tournament_198', '2025-12-02 13:19:19'),
(383, 17, -400.00, 'tournament_fee', 'completed', 'tournament_199', '2025-12-02 13:19:23'),
(384, 17, -400.00, 'tournament_fee', 'completed', 'tournament_200', '2025-12-02 13:19:26');

-- --------------------------------------------------------

--
-- Структура таблицы `pending_registrations`
--

CREATE TABLE `pending_registrations` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `confirmation_code` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `remember_tokens`
--

CREATE TABLE `remember_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `remember_tokens`
--

INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES
(0, 38, 'efeef4671bed14445c9f34651b96ba768a06acc82694dbf96d1fbc92f5cf4c41', '2025-12-07 17:49:37', '2025-11-07 14:49:37'),
(0, 57, '6bfe319c40137ac447ed54aaddff56f4cec4c1214ddcc70db483c2ccbb9cf2c1', '2025-12-07 18:12:42', '2025-11-07 15:12:42'),
(0, 56, 'bc20d1b322c544f6b017f7e7f9ceca22f613d5176ca1303feb03a1e24e08e0a4', '2025-12-07 18:37:46', '2025-11-07 15:37:46');

-- --------------------------------------------------------

--
-- Структура таблицы `saved_games`
--

CREATE TABLE `saved_games` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `game_data` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `tournaments`
--

CREATE TABLE `tournaments` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `entry_fee` decimal(10,2) DEFAULT '0.00',
  `prize_pool` decimal(10,2) DEFAULT '0.00',
  `max_players` int(11) DEFAULT '8',
  `difficulty` enum('easy','medium','hard','tournament') DEFAULT 'medium',
  `status` enum('registration','active','completed','cancelled') DEFAULT 'registration',
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `tournaments`
--

INSERT INTO `tournaments` (`id`, `name`, `description`, `entry_fee`, `prize_pool`, `max_players`, `difficulty`, `status`, `start_time`, `end_time`, `created_at`, `updated_at`) VALUES
(188, 'Цветовая дифференциация штанов', 'Выше статус, - выше заработок', 400.00, 4000.00, 10, 'easy', 'completed', '2025-12-01 20:00:00', '2025-12-01 22:00:01', '2025-11-30 00:00:01', '2025-12-01 22:59:20'),
(189, 'Плюкане любят скорость', 'Быстрей решишь, - быстрей разбогатеешь', 400.00, 4000.00, 10, 'easy', 'completed', '2025-12-01 22:00:00', NULL, '2025-11-30 00:00:01', '2025-12-02 00:00:01'),
(190, 'Пацак и ночью работает', 'Вложи 10 - получи больше', 100.00, 1000.00, 10, 'easy', 'completed', '2025-12-02 00:00:00', NULL, '2025-12-01 00:00:01', '2025-12-02 02:00:01'),
(191, 'КУ, Чатланин!', 'Раздаём чатлы', 100.00, 1000.00, 10, 'easy', 'completed', '2025-12-02 02:00:00', NULL, '2025-12-01 00:00:01', '2025-12-02 04:00:01'),
(192, 'Доброе утро, Плюк', 'Ранний заработок - весь день кормит', 100.00, 1000.00, 10, 'easy', 'completed', '2025-12-02 04:00:00', NULL, '2025-12-01 00:00:01', '2025-12-02 06:00:03'),
(193, 'Затяни цапу', 'Увеличь свой доход', 200.00, 2000.00, 10, 'easy', 'completed', '2025-12-02 06:00:00', NULL, '2025-12-01 00:00:01', '2025-12-02 08:00:01'),
(194, 'Луц колонка', '20%-30%-50%', 200.00, 2000.00, 10, 'easy', 'completed', '2025-12-02 08:00:00', NULL, '2025-12-01 00:00:01', '2025-12-02 10:00:01'),
(195, 'Заправляемся чатлами', 'Можно заработать до 100 чатлов', 200.00, 2000.00, 10, 'easy', 'completed', '2025-12-02 10:00:00', NULL, '2025-12-01 00:00:01', '2025-12-02 12:00:01'),
(196, 'Это Вам не пластиковая каша', 'Утраиваем призы', 300.00, 3000.00, 10, 'easy', 'completed', '2025-12-02 12:00:00', NULL, '2025-12-01 00:00:01', '2025-12-02 14:00:01'),
(197, 'Долетят только трое', 'Чатлы делим на троих', 300.00, 3000.00, 10, 'easy', 'completed', '2025-12-02 14:00:00', NULL, '2025-12-01 00:00:01', '2025-12-02 16:00:02'),
(198, 'Дневной забег', 'Стань первым за 10 минут', 300.00, 3000.00, 10, 'easy', 'completed', '2025-12-02 16:00:00', NULL, '2025-12-01 00:00:01', '2025-12-02 18:00:01'),
(199, 'А у Вас какая система?', 'Хорошая стратегия, - большой доход', 400.00, 4000.00, 10, 'easy', 'completed', '2025-12-02 18:00:00', NULL, '2025-12-01 00:00:01', '2025-12-02 20:00:01'),
(200, 'Цветовая дифференциация штанов', 'Выше статус, - выше заработок', 400.00, 4000.00, 10, 'easy', 'active', '2025-12-02 20:00:00', NULL, '2025-12-01 00:00:01', '2025-12-02 20:00:01'),
(201, 'Плюкане любят скорость', 'Быстрей решишь, - быстрей разбогатеешь', 400.00, 4000.00, 10, 'easy', 'registration', '2025-12-02 22:00:00', NULL, '2025-12-01 00:00:01', '2025-12-01 00:00:01'),
(202, 'Пацак и ночью работает', 'Вложи 10 - получи больше', 100.00, 1000.00, 10, 'easy', 'registration', '2025-12-03 00:00:00', NULL, '2025-12-02 00:00:01', '2025-12-02 00:00:01'),
(203, 'КУ, Чатланин!', 'Раздаём чатлы', 100.00, 1000.00, 10, 'easy', 'registration', '2025-12-03 02:00:00', NULL, '2025-12-02 00:00:01', '2025-12-02 00:00:01'),
(204, 'Доброе утро, Плюк', 'Ранний заработок - весь день кормит', 100.00, 1000.00, 10, 'easy', 'registration', '2025-12-03 04:00:00', NULL, '2025-12-02 00:00:01', '2025-12-02 00:00:01'),
(205, 'Затяни цапу', 'Увеличь свой доход', 200.00, 2000.00, 10, 'easy', 'registration', '2025-12-03 06:00:00', NULL, '2025-12-02 00:00:01', '2025-12-02 00:00:01'),
(206, 'Луц колонка', '20%-30%-50%', 200.00, 2000.00, 10, 'easy', 'registration', '2025-12-03 08:00:00', NULL, '2025-12-02 00:00:01', '2025-12-02 00:00:01'),
(207, 'Заправляемся чатлами', 'Можно заработать до 100 чатлов', 200.00, 2000.00, 10, 'easy', 'registration', '2025-12-03 10:00:00', NULL, '2025-12-02 00:00:01', '2025-12-02 00:00:01'),
(208, 'Это Вам не пластиковая каша', 'Утраиваем призы', 300.00, 3000.00, 10, 'easy', 'registration', '2025-12-03 12:00:00', NULL, '2025-12-02 00:00:01', '2025-12-02 00:00:01'),
(209, 'Долетят только трое', 'Чатлы делим на троих', 300.00, 3000.00, 10, 'easy', 'registration', '2025-12-03 14:00:00', NULL, '2025-12-02 00:00:01', '2025-12-02 00:00:01'),
(210, 'Дневной забег', 'Стань первым за 10 минут', 300.00, 3000.00, 10, 'easy', 'registration', '2025-12-03 16:00:00', NULL, '2025-12-02 00:00:01', '2025-12-02 00:00:01'),
(211, 'А у Вас какая система?', 'Хорошая стратегия, - большой доход', 400.00, 4000.00, 10, 'easy', 'registration', '2025-12-03 18:00:00', NULL, '2025-12-02 00:00:01', '2025-12-02 00:00:01'),
(212, 'Цветовая дифференциация штанов', 'Выше статус, - выше заработок', 400.00, 4000.00, 10, 'easy', 'registration', '2025-12-03 20:00:00', NULL, '2025-12-02 00:00:01', '2025-12-02 00:00:01'),
(213, 'Плюкане любят скорость', 'Быстрей решишь, - быстрей разбогатеешь', 400.00, 4000.00, 10, 'easy', 'registration', '2025-12-03 22:00:00', NULL, '2025-12-02 00:00:01', '2025-12-02 00:00:01');

-- --------------------------------------------------------

--
-- Структура таблицы `tournament_games`
--

CREATE TABLE `tournament_games` (
  `id` int(11) NOT NULL,
  `tournament_id` int(11) DEFAULT NULL,
  `game_id` varchar(100) DEFAULT NULL,
  `player1_id` int(11) DEFAULT NULL,
  `player2_id` int(11) DEFAULT NULL,
  `board_data` json DEFAULT NULL,
  `status` enum('pending','active','completed') DEFAULT 'pending',
  `player1_score` int(11) DEFAULT '0',
  `player2_score` int(11) DEFAULT '0',
  `winner_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `tournament_game_stats`
--

CREATE TABLE `tournament_game_stats` (
  `id` int(11) NOT NULL,
  `tournament_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `game_id` varchar(50) NOT NULL,
  `chatls_earned` int(11) DEFAULT '0' COMMENT 'Чатлы, заработанные в этой игре',
  `time_seconds` int(11) DEFAULT '0' COMMENT 'Время игры в секундах',
  `mistakes` int(11) DEFAULT '0' COMMENT 'Количество ошибок',
  `hints_used` int(11) DEFAULT '0' COMMENT 'Использовано подсказок',
  `won_game` tinyint(1) DEFAULT '0' COMMENT 'Победа в игре (1 - да, 0 - нет)',
  `played_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `tournament_registrations`
--

CREATE TABLE `tournament_registrations` (
  `id` int(11) NOT NULL,
  `tournament_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `registered_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('registered','playing','completed','disqualified') DEFAULT 'registered',
  `games_played` int(11) DEFAULT '0',
  `last_game_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `tournament_registrations`
--

INSERT INTO `tournament_registrations` (`id`, `tournament_id`, `user_id`, `registered_at`, `status`, `games_played`, `last_game_at`) VALUES
(359, 188, 17, '2025-12-01 09:35:14', 'registered', 0, NULL),
(360, 189, 17, '2025-12-01 09:35:19', 'registered', 0, NULL),
(361, 190, 17, '2025-12-01 09:35:25', 'registered', 0, NULL),
(362, 191, 17, '2025-12-01 09:35:56', 'registered', 0, NULL),
(363, 192, 17, '2025-12-01 09:36:29', 'registered', 0, NULL),
(369, 188, 65, '2025-12-01 09:38:36', 'registered', 0, NULL),
(370, 189, 65, '2025-12-01 09:38:38', 'registered', 0, NULL),
(371, 190, 65, '2025-12-01 09:38:41', 'registered', 0, NULL),
(372, 191, 65, '2025-12-01 09:38:43', 'registered', 0, NULL),
(373, 192, 65, '2025-12-01 09:38:46', 'registered', 0, NULL),
(379, 188, 18, '2025-12-01 09:40:30', 'registered', 0, NULL),
(380, 189, 18, '2025-12-01 09:40:34', 'registered', 0, NULL),
(381, 190, 18, '2025-12-01 09:40:42', 'registered', 0, NULL),
(382, 191, 18, '2025-12-01 09:41:55', 'registered', 0, NULL),
(383, 192, 18, '2025-12-01 09:45:17', 'registered', 0, NULL),
(384, 193, 18, '2025-12-01 09:45:31', 'registered', 0, NULL),
(390, 188, 14, '2025-12-01 10:06:47', 'registered', 0, NULL),
(391, 189, 14, '2025-12-01 10:08:40', 'registered', 0, NULL),
(392, 190, 14, '2025-12-01 10:09:09', 'registered', 0, NULL),
(393, 191, 14, '2025-12-01 10:11:48', 'registered', 0, NULL),
(394, 192, 14, '2025-12-01 10:23:13', 'registered', 0, NULL),
(395, 193, 14, '2025-12-01 10:25:02', 'registered', 0, NULL),
(396, 194, 14, '2025-12-01 10:25:12', 'registered', 0, NULL),
(397, 201, 65, '2025-12-01 21:10:46', 'registered', 0, NULL),
(398, 200, 65, '2025-12-01 21:10:48', 'registered', 0, NULL),
(399, 199, 65, '2025-12-01 21:10:52', 'registered', 0, NULL),
(400, 198, 65, '2025-12-01 21:10:57', 'registered', 0, NULL),
(401, 197, 65, '2025-12-01 21:11:02', 'registered', 0, NULL),
(402, 196, 65, '2025-12-01 21:11:07', 'registered', 0, NULL),
(403, 195, 65, '2025-12-01 21:11:08', 'registered', 0, NULL),
(404, 194, 65, '2025-12-01 21:11:10', 'registered', 0, NULL),
(405, 193, 65, '2025-12-01 21:11:13', 'registered', 0, NULL),
(406, 197, 17, '2025-12-02 13:19:15', 'registered', 0, NULL),
(407, 198, 17, '2025-12-02 13:19:19', 'registered', 0, NULL),
(408, 199, 17, '2025-12-02 13:19:23', 'registered', 0, NULL),
(409, 200, 17, '2025-12-02 13:19:26', 'registered', 0, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `tournament_results`
--

CREATE TABLE `tournament_results` (
  `id` int(11) NOT NULL,
  `tournament_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `position` int(11) NOT NULL DEFAULT '999',
  `score` int(11) DEFAULT '0',
  `prize` int(11) DEFAULT '0',
  `entry_fee` int(11) DEFAULT '0',
  `total_points` int(11) DEFAULT '0',
  `games_played` int(11) DEFAULT '0',
  `games_won` int(11) DEFAULT '0',
  `win_rate` decimal(5,2) DEFAULT '0.00',
  `best_time` int(11) DEFAULT '0',
  `avg_time` int(11) DEFAULT '0',
  `total_time` int(11) DEFAULT '0',
  `total_mistakes` int(11) DEFAULT '0',
  `total_hints` int(11) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `completed_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `tournament_results`
--

INSERT INTO `tournament_results` (`id`, `tournament_id`, `user_id`, `position`, `score`, `prize`, `entry_fee`, `total_points`, `games_played`, `games_won`, `win_rate`, `best_time`, `avg_time`, `total_time`, `total_mistakes`, `total_hints`, `created_at`, `completed_at`, `updated_at`) VALUES
(24, 188, 14, 1, 24606, 2000, 0, 0, 0, 0, 0.00, 0, 0, 0, 0, 0, '2025-12-01 19:00:01', NULL, '2025-12-01 19:00:01'),
(25, 188, 17, 2, 15915, 1200, 0, 0, 0, 0, 0.00, 0, 0, 0, 0, 0, '2025-12-01 19:00:01', NULL, '2025-12-01 19:00:01'),
(26, 188, 65, 3, 5213, 800, 0, 0, 0, 0, 0.00, 0, 0, 0, 0, 0, '2025-12-01 19:00:01', NULL, '2025-12-01 19:00:01'),
(27, 188, 18, 4, 0, 0, 0, 0, 0, 0, 0.00, 0, 0, 0, 0, 0, '2025-12-01 19:00:01', NULL, '2025-12-01 19:00:01'),
(28, 189, 14, 1, 26606, 2000, 0, 0, 0, 0, 0.00, 0, 0, 0, 0, 0, '2025-12-01 21:00:01', NULL, '2025-12-01 21:00:01'),
(29, 189, 17, 2, 17115, 1200, 0, 0, 0, 0, 0.00, 0, 0, 0, 0, 0, '2025-12-01 21:00:01', NULL, '2025-12-01 21:00:01'),
(30, 189, 65, 3, 6013, 800, 0, 0, 0, 0, 0.00, 0, 0, 0, 0, 0, '2025-12-01 21:00:01', NULL, '2025-12-01 21:00:01'),
(31, 189, 18, 4, 0, 0, 0, 0, 0, 0, 0.00, 0, 0, 0, 0, 0, '2025-12-01 21:00:01', NULL, '2025-12-01 21:00:01'),
(32, 190, 14, 1, 28606, 500, 0, 0, 0, 0, 0.00, 0, 0, 0, 0, 0, '2025-12-01 23:00:01', NULL, '2025-12-01 23:00:01'),
(33, 190, 17, 2, 18315, 300, 0, 0, 0, 0, 0.00, 0, 0, 0, 0, 0, '2025-12-01 23:00:01', NULL, '2025-12-01 23:00:01'),
(34, 190, 65, 3, 6813, 200, 0, 0, 0, 0, 0.00, 0, 0, 0, 0, 0, '2025-12-01 23:00:01', NULL, '2025-12-01 23:00:01'),
(35, 190, 18, 4, 0, 0, 0, 0, 0, 0, 0.00, 0, 0, 0, 0, 0, '2025-12-01 23:00:01', NULL, '2025-12-01 23:00:01'),
(36, 191, 14, 1, 29106, 500, 0, 0, 0, 0, 0.00, 0, 0, 0, 0, 0, '2025-12-02 01:00:01', NULL, '2025-12-02 01:00:01'),
(37, 191, 17, 2, 18615, 300, 0, 0, 0, 0, 0.00, 0, 0, 0, 0, 0, '2025-12-02 01:00:01', NULL, '2025-12-02 01:00:01'),
(38, 191, 65, 3, 7013, 200, 0, 0, 0, 0, 0.00, 0, 0, 0, 0, 0, '2025-12-02 01:00:01', NULL, '2025-12-02 01:00:01'),
(39, 191, 18, 4, 0, 0, 0, 0, 0, 0, 0.00, 0, 0, 0, 0, 0, '2025-12-02 01:00:01', NULL, '2025-12-02 01:00:01'),
(40, 192, 14, 1, 29606, 500, 0, 0, 0, 0, 0.00, 0, 0, 0, 0, 0, '2025-12-02 03:00:03', NULL, '2025-12-02 03:00:03'),
(41, 192, 17, 2, 18915, 300, 0, 0, 0, 0, 0.00, 0, 0, 0, 0, 0, '2025-12-02 03:00:03', NULL, '2025-12-02 03:00:03'),
(42, 192, 65, 3, 7213, 200, 0, 0, 0, 0, 0.00, 0, 0, 0, 0, 0, '2025-12-02 03:00:03', NULL, '2025-12-02 03:00:03'),
(43, 192, 18, 4, 0, 0, 0, 0, 0, 0, 0.00, 0, 0, 0, 0, 0, '2025-12-02 03:00:03', NULL, '2025-12-02 03:00:03'),
(44, 193, 14, 1, 30106, 1000, 0, 0, 0, 0, 0.00, 0, 0, 0, 0, 0, '2025-12-02 05:00:01', NULL, '2025-12-02 05:00:01'),
(45, 193, 65, 2, 7413, 600, 0, 0, 0, 0, 0.00, 0, 0, 0, 0, 0, '2025-12-02 05:00:01', NULL, '2025-12-02 05:00:01'),
(46, 193, 18, 3, 0, 400, 0, 0, 0, 0, 0.00, 0, 0, 0, 0, 0, '2025-12-02 05:00:01', NULL, '2025-12-02 05:00:01'),
(47, 194, 14, 1, 31106, 1000, 0, 0, 0, 0, 0.00, 0, 0, 0, 0, 0, '2025-12-02 07:00:01', NULL, '2025-12-02 07:00:01'),
(48, 194, 65, 2, 8013, 600, 0, 0, 0, 0, 0.00, 0, 0, 0, 0, 0, '2025-12-02 07:00:01', NULL, '2025-12-02 07:00:01'),
(49, 195, 65, 1, 8613, 1000, 0, 0, 0, 0, 0.00, 0, 0, 0, 0, 0, '2025-12-02 09:00:01', NULL, '2025-12-02 09:00:01'),
(50, 196, 65, 1, 9657, 1500, 0, 0, 0, 0, 0.00, 0, 0, 0, 0, 0, '2025-12-02 11:00:01', NULL, '2025-12-02 11:00:01'),
(51, 197, 65, 1, 9670, 1500, 0, 0, 0, 0, 0.00, 0, 0, 0, 0, 0, '2025-12-02 13:00:02', NULL, '2025-12-02 13:00:02'),
(52, 197, 17, 2, 0, 900, 0, 0, 0, 0, 0.00, 0, 0, 0, 0, 0, '2025-12-02 13:00:02', NULL, '2025-12-02 13:00:02'),
(53, 198, 17, 1, 18715, 1500, 0, 0, 0, 0, 0.00, 0, 0, 0, 0, 0, '2025-12-02 15:00:01', NULL, '2025-12-02 15:00:01'),
(54, 198, 65, 2, 11170, 900, 0, 0, 0, 0, 0.00, 0, 0, 0, 0, 0, '2025-12-02 15:00:01', NULL, '2025-12-02 15:00:01'),
(55, 199, 17, 1, 20215, 2000, 0, 0, 0, 0, 0.00, 0, 0, 0, 0, 0, '2025-12-02 17:00:01', NULL, '2025-12-02 17:00:01'),
(56, 199, 65, 2, 12070, 1200, 0, 0, 0, 0, 0.00, 0, 0, 0, 0, 0, '2025-12-02 17:00:01', NULL, '2025-12-02 17:00:01');

-- --------------------------------------------------------

--
-- Структура таблицы `tournament_results_old`
--

CREATE TABLE `tournament_results_old` (
  `id` int(11) NOT NULL DEFAULT '0',
  `tournament_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `position` int(11) DEFAULT NULL,
  `score` int(11) DEFAULT '0',
  `prize` decimal(10,2) DEFAULT '0.00',
  `total_points` int(11) DEFAULT '0',
  `games_won` int(11) DEFAULT '0',
  `win_rate` decimal(5,2) DEFAULT '0.00',
  `best_time` int(11) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `completed_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `tournament_results_old`
--

INSERT INTO `tournament_results_old` (`id`, `tournament_id`, `user_id`, `position`, `score`, `prize`, `total_points`, `games_won`, `win_rate`, `best_time`, `created_at`, `completed_at`) VALUES
(284, 151, 17, 1, 2762, 50.00, 0, 0, 0.00, 0, '2025-11-27 15:36:26', '2025-11-27 18:36:26'),
(285, 151, 65, 2, 0, 30.00, 0, 0, 0.00, 0, '2025-11-27 15:36:26', '2025-11-27 18:36:26'),
(286, 152, 18, 1, 15552, 50.00, 0, 0, 0.00, 0, '2025-11-27 15:40:01', '2025-11-27 18:40:01'),
(287, 152, 65, 2, 8506, 30.00, 0, 0, 0.00, 0, '2025-11-27 15:40:01', '2025-11-27 18:40:01'),
(288, 152, 17, 3, 2812, 20.00, 0, 0, 0.00, 0, '2025-11-27 15:40:01', '2025-11-27 18:40:01'),
(289, 152, 14, 4, 0, 0.00, 0, 0, 0.00, 0, '2025-11-27 15:40:01', '2025-11-27 18:40:01'),
(290, 153, 65, 1, 8580, 50.00, 0, 0, 0.00, 0, '2025-11-27 21:00:01', '2025-11-28 00:00:01'),
(291, 153, 65, 1, 8580, 50.00, 0, 0, 0.00, 0, '2025-11-27 21:00:01', '2025-11-28 00:00:01'),
(292, 154, 17, 1, 0, 505.00, 0, 0, 0.00, 0, '2025-11-28 23:00:01', '2025-11-29 02:00:01'),
(293, 154, 65, 2, 0, 303.00, 0, 0, 0.00, 0, '2025-11-28 23:00:01', '2025-11-29 02:00:01'),
(294, 155, 65, 1, 8383, 505.00, 0, 0, 0.00, 0, '2025-11-29 01:00:01', '2025-11-29 04:00:01'),
(295, 155, 17, 2, 2920, 303.00, 0, 0, 0.00, 0, '2025-11-29 01:00:01', '2025-11-29 04:00:01'),
(296, 156, 65, 1, 8888, 505.00, 0, 0, 0.00, 0, '2025-11-29 03:00:01', '2025-11-29 06:00:01'),
(297, 156, 17, 2, 3223, 303.00, 0, 0, 0.00, 0, '2025-11-29 03:00:01', '2025-11-29 06:00:01'),
(298, 157, 17, 1, 3526, 1000.00, 0, 0, 0.00, 0, '2025-11-29 05:00:01', '2025-11-29 08:00:01'),
(299, 159, 65, 1, 0, 1000.00, 0, 0, 0.00, 0, '2025-11-29 09:00:01', '2025-11-29 12:00:01'),
(300, 160, 14, 1, 2521, 1500.00, 0, 0, 0.00, 0, '2025-11-29 11:00:01', '2025-11-29 14:00:01'),
(301, 161, 14, 1, 4046, 1500.00, 0, 0, 0.00, 0, '2025-11-29 13:00:01', '2025-11-29 16:00:01'),
(302, 162, 14, 1, 1346, 1500.00, 0, 0, 0.00, 0, '2025-11-29 15:00:01', '2025-11-29 18:00:01'),
(303, 162, 17, 2, 66, 900.00, 0, 0, 0.00, 0, '2025-11-29 15:00:01', '2025-11-29 18:00:01'),
(304, 163, 14, 1, 2856, 2000.00, 0, 0, 0.00, 0, '2025-11-29 17:00:01', '2025-11-29 20:00:01'),
(305, 163, 17, 2, 966, 1200.00, 0, 0, 0.00, 0, '2025-11-29 17:00:01', '2025-11-29 20:00:01'),
(306, 163, 18, 3, 0, 800.00, 0, 0, 0.00, 0, '2025-11-29 17:00:01', '2025-11-29 20:00:01'),
(307, 163, 65, 4, 0, 0.00, 0, 0, 0.00, 0, '2025-11-29 17:00:01', '2025-11-29 20:00:01'),
(308, 163, 14, 1, 2856, 2000.00, 0, 0, 0.00, 0, '2025-11-29 17:00:01', '2025-11-29 20:00:01'),
(309, 163, 17, 2, 966, 1200.00, 0, 0, 0.00, 0, '2025-11-29 17:00:01', '2025-11-29 20:00:01'),
(310, 163, 18, 3, 0, 800.00, 0, 0, 0.00, 0, '2025-11-29 17:00:01', '2025-11-29 20:00:01'),
(311, 163, 65, 4, 0, 0.00, 0, 0, 0.00, 0, '2025-11-29 17:00:01', '2025-11-29 20:00:01'),
(312, 164, 18, 1, 13007, 2000.00, 0, 0, 0.00, 0, '2025-11-29 19:00:01', '2025-11-29 22:00:01'),
(313, 164, 14, 2, 6897, 1200.00, 0, 0, 0.00, 0, '2025-11-29 19:00:01', '2025-11-29 22:00:01'),
(314, 164, 17, 3, 3366, 800.00, 0, 0, 0.00, 0, '2025-11-29 19:00:01', '2025-11-29 22:00:01'),
(315, 164, 65, 4, 0, 0.00, 0, 0, 0.00, 0, '2025-11-29 19:00:01', '2025-11-29 22:00:01'),
(316, 165, 18, 1, 15007, 2000.00, 0, 0, 0.00, 0, '2025-11-29 21:00:01', '2025-11-30 00:00:01'),
(317, 165, 14, 2, 8097, 1200.00, 0, 0, 0.00, 0, '2025-11-29 21:00:01', '2025-11-30 00:00:01'),
(318, 165, 17, 3, 4166, 800.00, 0, 0, 0.00, 0, '2025-11-29 21:00:01', '2025-11-30 00:00:01'),
(319, 165, 65, 4, 0, 0.00, 0, 0, 0.00, 0, '2025-11-29 21:00:01', '2025-11-30 00:00:01'),
(320, 166, 18, 1, 17007, 505.00, 0, 0, 0.00, 0, '2025-11-29 23:00:01', '2025-11-30 02:00:01'),
(321, 166, 14, 2, 9297, 303.00, 0, 0, 0.00, 0, '2025-11-29 23:00:01', '2025-11-30 02:00:01'),
(322, 166, 17, 3, 4966, 202.00, 0, 0, 0.00, 0, '2025-11-29 23:00:01', '2025-11-30 02:00:01'),
(323, 166, 65, 4, 0, 0.00, 0, 0, 0.00, 0, '2025-11-29 23:00:01', '2025-11-30 02:00:01'),
(324, 167, 18, 1, 17512, 505.00, 0, 0, 0.00, 0, '2025-11-30 01:00:01', '2025-11-30 04:00:01'),
(325, 167, 14, 2, 9600, 303.00, 0, 0, 0.00, 0, '2025-11-30 01:00:01', '2025-11-30 04:00:01'),
(326, 167, 17, 3, 5168, 202.00, 0, 0, 0.00, 0, '2025-11-30 01:00:01', '2025-11-30 04:00:01'),
(327, 167, 65, 4, 0, 0.00, 0, 0, 0.00, 0, '2025-11-30 01:00:01', '2025-11-30 04:00:01'),
(328, 168, 18, 1, 18017, 505.00, 0, 0, 0.00, 0, '2025-11-30 03:00:01', '2025-11-30 06:00:01'),
(329, 168, 14, 2, 9903, 303.00, 0, 0, 0.00, 0, '2025-11-30 03:00:01', '2025-11-30 06:00:01'),
(330, 168, 17, 3, 5370, 202.00, 0, 0, 0.00, 0, '2025-11-30 03:00:01', '2025-11-30 06:00:01'),
(331, 168, 65, 4, 0, 0.00, 0, 0, 0.00, 0, '2025-11-30 03:00:01', '2025-11-30 06:00:01'),
(332, 169, 18, 1, 18522, 1000.00, 0, 0, 0.00, 0, '2025-11-30 05:00:01', '2025-11-30 08:00:01'),
(333, 169, 14, 2, 10206, 600.00, 0, 0, 0.00, 0, '2025-11-30 05:00:01', '2025-11-30 08:00:01'),
(334, 169, 17, 3, 5572, 400.00, 0, 0, 0.00, 0, '2025-11-30 05:00:01', '2025-11-30 08:00:01'),
(335, 169, 65, 4, 0, 0.00, 0, 0, 0.00, 0, '2025-11-30 05:00:01', '2025-11-30 08:00:01'),
(336, 169, 18, 1, 18522, 1000.00, 0, 0, 0.00, 0, '2025-11-30 05:00:01', '2025-11-30 08:00:01'),
(337, 169, 14, 2, 10206, 600.00, 0, 0, 0.00, 0, '2025-11-30 05:00:01', '2025-11-30 08:00:01'),
(338, 169, 17, 3, 5572, 400.00, 0, 0, 0.00, 0, '2025-11-30 05:00:01', '2025-11-30 08:00:01'),
(339, 169, 65, 4, 0, 0.00, 0, 0, 0.00, 0, '2025-11-30 05:00:01', '2025-11-30 08:00:01'),
(340, 170, 18, 1, 20522, 1000.00, 0, 0, 0.00, 0, '2025-11-30 07:00:01', '2025-11-30 10:00:01'),
(341, 170, 14, 2, 11406, 600.00, 0, 0, 0.00, 0, '2025-11-30 07:00:01', '2025-11-30 10:00:01'),
(342, 170, 17, 3, 6372, 400.00, 0, 0, 0.00, 0, '2025-11-30 07:00:01', '2025-11-30 10:00:01'),
(343, 170, 65, 4, 0, 0.00, 0, 0, 0.00, 0, '2025-11-30 07:00:01', '2025-11-30 10:00:01'),
(344, 171, 18, 1, 21556, 1000.00, 0, 0, 0.00, 0, '2025-11-30 09:00:01', '2025-11-30 12:00:01'),
(345, 171, 14, 2, 12006, 600.00, 0, 0, 0.00, 0, '2025-11-30 09:00:01', '2025-11-30 12:00:01'),
(346, 171, 17, 3, 6772, 400.00, 0, 0, 0.00, 0, '2025-11-30 09:00:01', '2025-11-30 12:00:01'),
(347, 171, 65, 4, 0, 0.00, 0, 0, 0.00, 0, '2025-11-30 09:00:01', '2025-11-30 12:00:01'),
(348, 172, 18, 1, 22556, 1500.00, 0, 0, 0.00, 0, '2025-11-30 11:00:01', '2025-11-30 14:00:01'),
(349, 172, 14, 2, 12606, 900.00, 0, 0, 0.00, 0, '2025-11-30 11:00:01', '2025-11-30 14:00:01'),
(350, 172, 17, 3, 7172, 600.00, 0, 0, 0.00, 0, '2025-11-30 11:00:01', '2025-11-30 14:00:01'),
(351, 172, 65, 4, 5713, 0.00, 0, 0, 0.00, 0, '2025-11-30 11:00:01', '2025-11-30 14:00:01'),
(352, 173, 18, 1, 24056, 1500.00, 0, 0, 0.00, 0, '2025-11-30 13:00:01', '2025-11-30 16:00:01'),
(353, 173, 14, 2, 13506, 900.00, 0, 0, 0.00, 0, '2025-11-30 13:00:01', '2025-11-30 16:00:01'),
(354, 173, 17, 3, 7787, 600.00, 0, 0, 0.00, 0, '2025-11-30 13:00:01', '2025-11-30 16:00:01'),
(355, 173, 65, 4, 0, 0.00, 0, 0, 0.00, 0, '2025-11-30 13:00:01', '2025-11-30 16:00:01'),
(356, 174, 18, 1, 25556, 1500.00, 0, 0, 0.00, 0, '2025-11-30 15:00:01', '2025-11-30 18:00:01'),
(357, 174, 14, 2, 14406, 900.00, 0, 0, 0.00, 0, '2025-11-30 15:00:01', '2025-11-30 18:00:01'),
(358, 174, 17, 3, 8387, 600.00, 0, 0, 0.00, 0, '2025-11-30 15:00:01', '2025-11-30 18:00:01'),
(359, 174, 65, 4, 0, 0.00, 0, 0, 0.00, 0, '2025-11-30 15:00:01', '2025-11-30 18:00:01'),
(360, 175, 18, 1, 27056, 2000.00, 0, 0, 0.00, 0, '2025-11-30 17:00:01', '2025-11-30 20:00:01'),
(361, 175, 14, 2, 15306, 1200.00, 0, 0, 0.00, 0, '2025-11-30 17:00:01', '2025-11-30 20:00:01'),
(362, 175, 17, 3, 8487, 800.00, 0, 0, 0.00, 0, '2025-11-30 17:00:01', '2025-11-30 20:00:01'),
(363, 175, 65, 4, 0, 0.00, 0, 0, 0.00, 0, '2025-11-30 17:00:01', '2025-11-30 20:00:01'),
(364, 176, 18, 1, 29056, 2000.00, 0, 0, 0.00, 0, '2025-11-30 19:00:01', '2025-11-30 22:00:01'),
(365, 176, 14, 2, 16506, 1200.00, 0, 0, 0.00, 0, '2025-11-30 19:00:01', '2025-11-30 22:00:01'),
(366, 176, 17, 3, 9287, 800.00, 0, 0, 0.00, 0, '2025-11-30 19:00:01', '2025-11-30 22:00:01'),
(367, 176, 65, 4, 0, 0.00, 0, 0, 0.00, 0, '2025-11-30 19:00:01', '2025-11-30 22:00:01'),
(368, 176, 18, 1, 29056, 2000.00, 0, 0, 0.00, 0, '2025-11-30 19:00:01', '2025-11-30 22:00:01'),
(369, 176, 14, 2, 16506, 1200.00, 0, 0, 0.00, 0, '2025-11-30 19:00:01', '2025-11-30 22:00:01'),
(370, 176, 17, 3, 9287, 800.00, 0, 0, 0.00, 0, '2025-11-30 19:00:01', '2025-11-30 22:00:01'),
(371, 176, 65, 4, 0, 0.00, 0, 0, 0.00, 0, '2025-11-30 19:00:01', '2025-11-30 22:00:01'),
(372, 177, 18, 1, 33056, 2000.00, 0, 0, 0.00, 0, '2025-11-30 21:00:01', '2025-12-01 00:00:01'),
(373, 177, 14, 2, 18906, 1200.00, 0, 0, 0.00, 0, '2025-11-30 21:00:01', '2025-12-01 00:00:01'),
(374, 177, 17, 3, 10915, 800.00, 0, 0, 0.00, 0, '2025-11-30 21:00:01', '2025-12-01 00:00:01'),
(375, 177, 65, 4, 0, 0.00, 0, 0, 0.00, 0, '2025-11-30 21:00:01', '2025-12-01 00:00:01'),
(376, 178, 17, 1, 11715, 500.00, 0, 0, 0.00, 0, '2025-11-30 23:00:01', '2025-12-01 02:00:01'),
(377, 178, 65, 2, 0, 300.00, 0, 0, 0.00, 0, '2025-11-30 23:00:01', '2025-12-01 02:00:01'),
(378, 179, 17, 1, 12215, 500.00, 0, 0, 0.00, 0, '2025-12-01 01:00:02', '2025-12-01 04:00:02'),
(379, 179, 65, 2, 6013, 300.00, 0, 0, 0.00, 0, '2025-12-01 01:00:02', '2025-12-01 04:00:02'),
(380, 180, 17, 1, 12715, 500.00, 0, 0, 0.00, 0, '2025-12-01 03:00:01', '2025-12-01 06:00:01'),
(381, 180, 65, 2, 6313, 300.00, 0, 0, 0.00, 0, '2025-12-01 03:00:01', '2025-12-01 06:00:01'),
(382, 181, 17, 1, 13215, 1000.00, 0, 0, 0.00, 0, '2025-12-01 05:00:01', '2025-12-01 08:00:01'),
(383, 181, 65, 2, 6613, 600.00, 0, 0, 0.00, 0, '2025-12-01 05:00:01', '2025-12-01 08:00:01'),
(384, 183, 14, 1, 17106, 1000.00, 0, 0, 0.00, 0, '2025-12-01 09:00:01', '2025-12-01 12:00:01'),
(385, 183, 65, 2, 4664, 600.00, 0, 0, 0.00, 0, '2025-12-01 09:00:01', '2025-12-01 12:00:01'),
(386, 183, 17, 3, 0, 400.00, 0, 0, 0.00, 0, '2025-12-01 09:00:01', '2025-12-01 12:00:01'),
(387, 183, 18, 4, 0, 0.00, 0, 0, 0.00, 0, '2025-12-01 09:00:01', '2025-12-01 12:00:01'),
(388, 184, 14, 1, 18106, 1500.00, 0, 0, 0.00, 0, '2025-12-01 11:00:01', '2025-12-01 14:00:01'),
(389, 184, 17, 2, 12015, 900.00, 0, 0, 0.00, 0, '2025-12-01 11:00:01', '2025-12-01 14:00:01'),
(390, 184, 65, 3, 5264, 600.00, 0, 0, 0.00, 0, '2025-12-01 11:00:01', '2025-12-01 14:00:01'),
(391, 184, 18, 4, 0, 0.00, 0, 0, 0.00, 0, '2025-12-01 11:00:01', '2025-12-01 14:00:01'),
(392, 185, 14, 1, 19606, 1500.00, 0, 0, 0.00, 0, '2025-12-01 13:00:01', '2025-12-01 16:00:01'),
(393, 185, 17, 2, 12915, 900.00, 0, 0, 0.00, 0, '2025-12-01 13:00:01', '2025-12-01 16:00:01'),
(394, 185, 65, 3, 5864, 600.00, 0, 0, 0.00, 0, '2025-12-01 13:00:01', '2025-12-01 16:00:01'),
(395, 185, 18, 4, 0, 0.00, 0, 0, 0.00, 0, '2025-12-01 13:00:01', '2025-12-01 16:00:01');

-- --------------------------------------------------------

--
-- Структура таблицы `tournament_seen`
--

CREATE TABLE `tournament_seen` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `tournament_id` int(11) DEFAULT NULL,
  `seen_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_admin` tinyint(1) DEFAULT '0',
  `total_games` int(11) DEFAULT '0',
  `games_won` int(11) DEFAULT '0',
  `total_points` int(11) DEFAULT '0',
  `rating` int(11) DEFAULT '0',
  `best_time_easy` int(11) DEFAULT NULL,
  `best_time_medium` int(11) DEFAULT NULL,
  `best_time_hard` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `created_at`, `is_admin`, `total_games`, `games_won`, `total_points`, `rating`, `best_time_easy`, `best_time_medium`, `best_time_hard`) VALUES
(14, 'Alfa', 'shop.abby@yandex.ru', '$2y$10$bWRce3vK4UI5CIB4K6hm/e.qPnzNcVC1g2lQndfkM1tSVJDM9oTs6', '2025-09-23 09:13:06', 0, 0, 0, 0, 0, NULL, NULL, NULL),
(17, 'Qwe34', 'dmitriy.truschelev@gmail.com', '$2y$10$BSP5o00xnkX4giTHPI3ij.BdaAHBwRQWbUiqj/YOZ92WNjaN93JHO', '2025-09-26 11:31:30', 0, 0, 0, 0, 0, NULL, NULL, NULL),
(18, 'Rez23', 'rezident235@rambler.ru', '$2y$10$Tq5KWqxsiHbJunzSSo0MDe.Yd05LzeLK95RhS19b5sSrCengcVxgG', '2025-10-24 13:56:14', 0, 0, 0, 0, 0, NULL, NULL, NULL),
(19, 'ty45', 'port@yandex.ru', '$2y$10$hQ55jXZ3G8P42RC0aJg9kejVrgGlyRuVBFdV.IKyX123.ycFgUl.6', '2025-10-30 08:28:11', 0, 0, 0, 0, 0, NULL, NULL, NULL),
(20, 'Gogen67', 'uschelev@gmail.com', '$2y$10$MkuGjdqt0JPXzAd/U/AwWOg55ngd6hAUlgd8hntSBpEgppNvPz/hi', '2025-10-30 08:38:39', 0, 0, 0, 0, 0, NULL, NULL, NULL),
(21, 'Ryjol', 'bby@yandex.ru', '$2y$10$E11E1Z3VhMfzPO8gYtl.m.OiedTV69jpubTlJBbydofPXH9RBp7sK', '2025-10-30 08:45:13', 0, 0, 0, 0, 0, NULL, NULL, NULL),
(22, 'Farto43', 'por@yandex.ru', '$2y$10$YdMXlKw7uDD71u7KYG0coOng0FbwsitwMHQ97wXLM4ToceJK3rScy', '2025-10-30 08:56:41', 0, 0, 0, 0, 0, NULL, NULL, NULL),
(23, 'Bolt45', 'ent235@rambler.ru', '$2y$10$oQvymUVnvttDztTYg33NW.RgjDhlbjNjOZ4.4yI6e5WAGGoUX3CWS', '2025-10-30 09:02:55', 0, 0, 0, 0, 0, NULL, NULL, NULL),
(24, 'Werty61', 'op.abby@yandex.ru', '$2y$10$pOFV8Esc4bfniYVM7BUT/ewL924K5mq10IIfXFWtmgUhCQm91r2QK', '2025-10-30 09:12:00', 0, 0, 0, 0, 0, NULL, NULL, NULL),
(25, 'Toium', 'tyui76@yandex.ru', '$2y$10$32k8U2j3PmTZ9rKKEMxf2eKWhNdZ0MwXrUkYRIrykKg3r2QoAOSve', '2025-10-30 09:17:46', 0, 0, 0, 0, 0, NULL, NULL, NULL),
(26, 'Nniop67', '67Qwertyelev@gmail.com', '$2y$10$OSXSz92VjyEIEDxBJ6slpeaCdkGZEYBAO1VsAooPsVSofZ41Qlh..', '2025-10-30 09:40:56', 0, 0, 0, 0, 0, NULL, NULL, NULL),
(27, 'ffffy78', 'partro@gmail.com', '$2y$10$NKPRYIDI4PQsE4RzbvtopuylFbXq7j6AHM42eZBRX52MkQYJUJ0pu', '2025-10-30 09:49:13', 0, 0, 0, 0, 0, NULL, NULL, NULL),
(28, 'MARTA34', 'itriy.truschelev@gmail.com', '$2y$10$8NQZ6B5Qv6TdFRAPA.K5FOeGt12CDt48bk/my4pr.8Ol8lPdVuiEW', '2025-10-30 09:54:48', 0, 0, 0, 0, 0, NULL, NULL, NULL),
(29, 'WENUA45', 'zident235@rambler.ru', '$2y$10$JsTD7aynLV9VVrwXkJG8UeZJp6pR5IUacyb9I2492HEIyXgzNO3Vi', '2025-10-30 10:16:31', 0, 0, 0, 0, 0, NULL, NULL, NULL),
(30, 'Gleb775', 'by@yandex.ru', '$2y$10$kS6g8v6ERV4r7u7zzojLJ.uRPkRo/KXHdqdWM46eGM510WvRSgvKq', '2025-10-30 10:24:34', 0, 0, 0, 0, 0, NULL, NULL, NULL),
(31, 'Xety45r', 'rtners1pro@gmail.com', '$2y$10$7hyeCBZCtXX2tnRegJbXkeSElOJuLIrscEMXwJH4csWEJ2/fEDCjy', '2025-10-30 10:30:06', 0, 0, 0, 0, 0, NULL, NULL, NULL),
(32, 'mutho', 'rs1pro@gmail.com', '$2y$10$qw5wlul2fCuTFmmF9Yk0O.2c0DqNi70ADZCtOsF2l7po6R5uHo2kO', '2025-10-30 10:37:03', 0, 0, 0, 0, 0, NULL, NULL, NULL),
(33, 'CCerg23', 'p.abby@yandex.ru', '$2y$10$yQ4PfEuTa0XzBr.ufMsDe.XA0n99NRr5K3BGOCWcIwBgvXaWE9ToG', '2025-10-30 10:44:36', 0, 0, 0, 0, 0, NULL, NULL, NULL),
(34, '45ARA78', 'zident25@rambler.ru', '$2y$10$H2y4hrXoNDHXWjMpD2cOFOoU2/vq8uk1gMGI4JIz/2NoXxZcw8LsO', '2025-10-30 11:05:55', 0, 0, 0, 0, 0, NULL, NULL, NULL),
(35, 'Katya67', 's1pro@gmail.com', '$2y$10$/Zb2NchA.iZG5W.SoTbGa.UTHoLu1RfmFuep.3ffPg9OOvZZ74Bya', '2025-10-30 11:17:23', 0, 0, 0, 0, 0, NULL, NULL, NULL),
(36, 'boba156', 'et76support@yandex.ru', '$2y$10$7kEGro04iqrYPgXA9t1LAe0QyDVzc9etxvGMSQdVRmJoBnnqAzrdW', '2025-10-30 11:37:35', 0, 0, 0, 0, 0, NULL, NULL, NULL),
(37, 'Ryoko', 'hop.abby@yandex.ru', '$2y$10$SUUfUKVNsssFSCwcStINp.3Dz2WfZsnCMW64q09XYTshtn1QYIrUC', '2025-10-30 12:09:33', 0, 0, 0, 0, 0, NULL, NULL, NULL),
(38, 'Viktop9', 'ev@gmail.com', '$2y$10$5R9BxBGaW/rN9l.zWay9Z.q3onfnL8gxVo7MJnAGzvF8KmR/k32Pq', '2025-10-30 19:21:13', 0, 0, 0, 0, 0, NULL, NULL, NULL),
(39, 'KLYPSO6', 'ketsupport@yandex.ru', '$2y$10$wpxIY2R/ScAQ7uHnVD0ez.Ja2hGrRVA1YUqKqbVSs9Bs6NdTDOElS', '2025-10-31 06:08:07', 0, 0, 0, 0, 0, NULL, NULL, NULL),
(40, '19Byon', 'etsupport@yandex.ru', '$2y$10$uczLTcPcooawWDDbixHpEOC3Ntun215kvZ8N1Si..f/UrvUE3Ksji', '2025-10-31 06:22:34', 0, 0, 0, 0, 0, NULL, NULL, NULL),
(41, 'Dima21', 'itriytruschelev@gmail.com', '$2y$10$xg4nck1bjFPgZZofJu3MjO5sxdcQqdzY1yEn.hKrFJUP2mZji0W4y', '2025-10-31 06:30:44', 0, 0, 0, 0, 0, NULL, NULL, NULL),
(42, 'Seryi', 'arket.support@yandex.ru', '$2y$10$9Q70IrXFu0LiXb/K861IDeH2hdjyMgQxZ92z9o2Nforzy5LxGUgn6', '2025-10-31 06:48:46', 0, 0, 0, 0, 0, NULL, NULL, NULL),
(43, 'Evro66', 'etsupp88ort@yandex.ru', '$2y$10$znnu926r/ZJCk.zXis2mJ.vOeCnzJoojFIg4SPsmIxuajwOKDbkX.', '2025-10-31 07:01:03', 0, 0, 0, 0, 0, NULL, NULL, NULL),
(44, 'PuPi', 'mitriyruschelev@gmail.com', '$2y$10$nhY4SkKSSy/4CvshEfWV5uGBC8M.ULNsgPCn6IyCq9mdJU2yNmPMm', '2025-10-31 07:12:16', 0, 0, 0, 0, 0, NULL, NULL, NULL),
(45, 'Fartu55', 'dmitriyruschelev@gmail.com', '$2y$10$bI6biLObubpNZcZQeiJXduS9n8ITXe2TcgRKVq389fsVcHE5VvoBq', '2025-10-31 07:44:37', 0, 0, 0, 0, 0, NULL, NULL, NULL),
(46, 'BoBa45', 'shop34abby@yandex.ru', '$2y$10$tAfmsFW1Fzv7f3PS.ypngOFMTe6gNxWuNy5sqQSSOLF8hx.J.I2YG', '2025-10-31 07:54:37', 0, 0, 0, 0, 0, NULL, NULL, NULL),
(47, 'Nina98', 'partners89pro@gmail.com', '$2y$10$syk55NAx72UQlSc/8hIuiOjWs3vnkIe5TotX.ZEcS8Y2dkWeb8XlO', '2025-10-31 08:41:57', 0, 0, 0, 0, 0, NULL, NULL, NULL),
(48, 'xery456', 'rmitretruschelev@gmail.com', '$2y$10$lW2nIgVq5y/GoOkIcQutT.nSFCxXqymCLZz9YqnCutqAEXrrJGH7m', '2025-10-31 09:05:46', 0, 0, 0, 0, 0, NULL, NULL, NULL),
(49, 'ZEro45', '456truschelev@gmail.com', '$2y$10$vaiCftR4RPMen6GB1eqrS.W3Nt3ovsxurXzMvZ95DTJC0ati5meWm', '2025-10-31 09:25:32', 0, 0, 0, 0, 0, NULL, NULL, NULL),
(50, 'Cudoluk', '567support@yandex.ru', '$2y$10$RY9R3YN8MPmHyDeTTcx2wupJay7ApFPI8iFWq6wAI41x7/maCFYDi', '2025-10-31 10:06:29', 0, 0, 0, 0, 0, NULL, NULL, NULL),
(51, 'mmiop34', 'market45pport@yandex.ru', '$2y$10$yjHF5T68IijXjAeWKsTXhum3av4c.iksNG94Qqf3soKZORdvLixGS', '2025-10-31 10:42:10', 0, 0, 0, 0, 0, NULL, NULL, NULL),
(52, 'astra22', 'part456o@gmail.com', '$2y$10$xwb4SIa11g2zRprn52zeGuFpsYu9gUKUm99ctUoqUa1Ejm0NhNSEC', '2025-10-31 11:07:39', 0, 0, 0, 0, 0, NULL, NULL, NULL),
(54, 'Grom56', 're444444@rambler.ru', '$2y$10$skT0EUUSl82Q8Qcb/EgIi.1ZT24nzpn9muH4HPzrpRbkn/7AgLU8q', '2025-11-07 10:42:54', 0, 0, 0, 0, 0, NULL, NULL, NULL),
(55, 'Hugojg', '555ort@yandex.ru', '$2y$10$s8DMnpYMTutat.0MvzgTfe9zdqu/D3YygCG2oP.7GumshgKcGXpfi', '2025-11-07 10:51:24', 0, 0, 0, 0, 0, NULL, NULL, NULL),
(56, 'MiuMi', 'par567pro@gmail.com', '$2y$10$W.bZGR6zDxttnoNUJVSZPOIz1Ovn.pjn2eQ4aSzvy.w51FWOnWjnq', '2025-11-07 11:18:56', 0, 0, 0, 0, 0, NULL, NULL, NULL),
(57, 'Sikoty1', '888schelev@gmail.com', '$2y$10$TZ.Cjdp1mvW.8Q8j51XX4OJPZWDrfCxwJbuLsYoWdhJw0D/.au4pC', '2025-11-07 11:26:39', 0, 0, 0, 0, 0, NULL, NULL, NULL),
(65, 'Admin', 'admin@plyuk.site', '$2y$10$G6h9yMV5hAWeeUX/KceGFOLgRbPUKjImaf6twW4BOBofmjsJwl2IO', '2025-11-18 20:29:19', 1, 0, 0, 0, 0, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `user_achievements`
--

CREATE TABLE `user_achievements` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `achievement_id` varchar(50) NOT NULL,
  `unlocked` tinyint(1) DEFAULT '0',
  `unlocked_at` datetime DEFAULT NULL,
  `progress` int(11) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `user_achievements`
--

INSERT INTO `user_achievements` (`id`, `user_id`, `achievement_id`, `unlocked`, `unlocked_at`, `progress`, `created_at`, `updated_at`) VALUES
(1859, 19, 'first_win', 1, '2025-10-30 08:36:58', 1, '2025-10-30 08:36:58', '2025-10-30 08:36:58'),
(1860, 19, 'no_mistakes', 1, '2025-10-30 08:36:58', 1, '2025-10-30 08:36:58', '2025-10-30 08:36:58'),
(1861, 19, 'no_hints', 0, NULL, 0, '2025-10-30 08:36:58', '2025-10-30 08:36:58'),
(1862, 19, 'perfectionist', 0, NULL, 0, '2025-10-30 08:36:58', '2025-10-30 08:36:58'),
(1863, 19, 'speedster_easy', 0, NULL, 0, '2025-10-30 08:36:58', '2025-10-30 08:36:58'),
(1864, 19, 'speedster_medium', 0, NULL, 0, '2025-10-30 08:36:58', '2025-10-30 08:36:58'),
(1865, 19, 'speedster_hard', 0, NULL, 0, '2025-10-30 08:36:58', '2025-10-30 08:36:58'),
(1866, 19, 'veteran', 0, NULL, 1, '2025-10-30 08:36:58', '2025-10-30 08:36:58'),
(1867, 19, 'master', 0, NULL, 1, '2025-10-30 08:36:58', '2025-10-30 08:36:58'),
(1868, 19, 'professional', 0, NULL, 1, '2025-10-30 08:36:58', '2025-10-30 08:36:58'),
(1869, 20, 'first_win', 1, '2025-10-30 08:43:47', 1, '2025-10-30 08:43:47', '2025-10-30 08:43:47'),
(1870, 20, 'no_mistakes', 0, NULL, 0, '2025-10-30 08:43:47', '2025-10-30 08:43:47'),
(1871, 20, 'no_hints', 1, '2025-10-30 08:43:47', 1, '2025-10-30 08:43:47', '2025-10-30 08:43:47'),
(1872, 20, 'perfectionist', 0, NULL, 0, '2025-10-30 08:43:47', '2025-10-30 08:43:47'),
(1873, 20, 'speedster_easy', 0, NULL, 0, '2025-10-30 08:43:47', '2025-10-30 08:43:47'),
(1874, 20, 'speedster_medium', 0, NULL, 0, '2025-10-30 08:43:47', '2025-10-30 08:43:47'),
(1875, 20, 'speedster_hard', 0, NULL, 0, '2025-10-30 08:43:47', '2025-10-30 08:43:47'),
(1876, 20, 'veteran', 0, NULL, 1, '2025-10-30 08:43:47', '2025-10-30 08:43:47'),
(1877, 20, 'master', 0, NULL, 1, '2025-10-30 08:43:47', '2025-10-30 08:43:47'),
(1878, 20, 'professional', 0, NULL, 1, '2025-10-30 08:43:47', '2025-10-30 08:43:47'),
(1879, 21, 'first_win', 1, '2025-10-30 08:55:20', 1, '2025-10-30 08:55:20', '2025-10-30 08:55:20'),
(1880, 21, 'no_mistakes', 0, NULL, 0, '2025-10-30 08:55:20', '2025-10-30 08:55:20'),
(1881, 21, 'no_hints', 1, '2025-10-30 08:55:20', 1, '2025-10-30 08:55:20', '2025-10-30 08:55:20'),
(1882, 21, 'perfectionist', 0, NULL, 0, '2025-10-30 08:55:20', '2025-10-30 08:55:20'),
(1883, 21, 'speedster_easy', 0, NULL, 0, '2025-10-30 08:55:20', '2025-10-30 08:55:20'),
(1884, 21, 'speedster_medium', 0, NULL, 0, '2025-10-30 08:55:20', '2025-10-30 08:55:20'),
(1885, 21, 'speedster_hard', 0, NULL, 0, '2025-10-30 08:55:20', '2025-10-30 08:55:20'),
(1886, 21, 'veteran', 0, NULL, 1, '2025-10-30 08:55:20', '2025-10-30 08:55:20'),
(1887, 21, 'master', 0, NULL, 1, '2025-10-30 08:55:20', '2025-10-30 08:55:20'),
(1888, 21, 'professional', 0, NULL, 1, '2025-10-30 08:55:20', '2025-10-30 08:55:20'),
(1889, 22, 'first_win', 1, '2025-10-30 09:01:29', 1, '2025-10-30 09:01:29', '2025-10-30 09:01:29'),
(1890, 22, 'no_mistakes', 0, NULL, 0, '2025-10-30 09:01:29', '2025-10-30 09:01:29'),
(1891, 22, 'no_hints', 1, '2025-10-30 09:01:29', 1, '2025-10-30 09:01:29', '2025-10-30 09:01:29'),
(1892, 22, 'perfectionist', 0, NULL, 0, '2025-10-30 09:01:29', '2025-10-30 09:01:29'),
(1893, 22, 'speedster_easy', 1, '2025-10-30 09:01:29', 300, '2025-10-30 09:01:29', '2025-10-30 09:01:29'),
(1894, 22, 'speedster_medium', 0, NULL, 0, '2025-10-30 09:01:29', '2025-10-30 09:01:29'),
(1895, 22, 'speedster_hard', 0, NULL, 0, '2025-10-30 09:01:29', '2025-10-30 09:01:29'),
(1896, 22, 'veteran', 0, NULL, 1, '2025-10-30 09:01:29', '2025-10-30 09:01:29'),
(1897, 22, 'master', 0, NULL, 1, '2025-10-30 09:01:29', '2025-10-30 09:01:29'),
(1898, 22, 'professional', 0, NULL, 1, '2025-10-30 09:01:29', '2025-10-30 09:01:29'),
(1899, 23, 'first_win', 1, '2025-10-30 09:10:24', 1, '2025-10-30 09:10:24', '2025-10-30 09:10:24'),
(1900, 23, 'no_mistakes', 0, NULL, 0, '2025-10-30 09:10:24', '2025-10-30 09:10:24'),
(1901, 23, 'no_hints', 1, '2025-10-30 09:10:24', 1, '2025-10-30 09:10:24', '2025-10-30 09:10:24'),
(1902, 23, 'perfectionist', 0, NULL, 0, '2025-10-30 09:10:24', '2025-10-30 09:10:24'),
(1903, 23, 'speedster_easy', 0, NULL, 0, '2025-10-30 09:10:24', '2025-10-30 09:10:24'),
(1904, 23, 'speedster_medium', 0, NULL, 0, '2025-10-30 09:10:24', '2025-10-30 09:10:24'),
(1905, 23, 'speedster_hard', 0, NULL, 0, '2025-10-30 09:10:24', '2025-10-30 09:10:24'),
(1906, 23, 'veteran', 0, NULL, 1, '2025-10-30 09:10:24', '2025-10-30 09:10:24'),
(1907, 23, 'master', 0, NULL, 1, '2025-10-30 09:10:24', '2025-10-30 09:10:24'),
(1908, 23, 'professional', 0, NULL, 1, '2025-10-30 09:10:24', '2025-10-30 09:10:24'),
(1909, 24, 'first_win', 1, '2025-10-30 09:15:59', 1, '2025-10-30 09:15:59', '2025-10-30 09:15:59'),
(1910, 24, 'no_mistakes', 0, NULL, 0, '2025-10-30 09:15:59', '2025-10-30 09:15:59'),
(1911, 24, 'no_hints', 1, '2025-10-30 09:15:59', 1, '2025-10-30 09:15:59', '2025-10-30 09:15:59'),
(1912, 24, 'perfectionist', 0, NULL, 0, '2025-10-30 09:15:59', '2025-10-30 09:15:59'),
(1913, 24, 'speedster_easy', 1, '2025-10-30 09:15:59', 300, '2025-10-30 09:15:59', '2025-10-30 09:15:59'),
(1914, 24, 'speedster_medium', 0, NULL, 0, '2025-10-30 09:15:59', '2025-10-30 09:15:59'),
(1915, 24, 'speedster_hard', 0, NULL, 0, '2025-10-30 09:15:59', '2025-10-30 09:15:59'),
(1916, 24, 'veteran', 0, NULL, 1, '2025-10-30 09:15:59', '2025-10-30 09:15:59'),
(1917, 24, 'master', 0, NULL, 1, '2025-10-30 09:15:59', '2025-10-30 09:15:59'),
(1918, 24, 'professional', 0, NULL, 1, '2025-10-30 09:15:59', '2025-10-30 09:15:59'),
(1939, 27, 'first_win', 1, '2025-10-30 09:53:17', 1, '2025-10-30 09:53:18', '2025-10-30 09:53:18'),
(1940, 27, 'no_mistakes', 1, '2025-10-30 09:53:17', 1, '2025-10-30 09:53:18', '2025-10-30 09:53:18'),
(1941, 27, 'no_hints', 1, '2025-10-30 09:53:17', 1, '2025-10-30 09:53:18', '2025-10-30 09:53:18'),
(1942, 27, 'perfectionist', 1, '2025-10-30 09:53:17', 1, '2025-10-30 09:53:18', '2025-10-30 09:53:18'),
(1943, 27, 'speedster_easy', 1, '2025-10-30 09:53:17', 300, '2025-10-30 09:53:18', '2025-10-30 09:53:18'),
(1944, 27, 'speedster_medium', 0, NULL, 0, '2025-10-30 09:53:18', '2025-10-30 09:53:18'),
(1945, 27, 'speedster_hard', 0, NULL, 0, '2025-10-30 09:53:18', '2025-10-30 09:53:18'),
(1946, 27, 'veteran', 0, NULL, 1, '2025-10-30 09:53:18', '2025-10-30 09:53:18'),
(1947, 27, 'master', 0, NULL, 1, '2025-10-30 09:53:18', '2025-10-30 09:53:18'),
(1948, 27, 'professional', 0, NULL, 1, '2025-10-30 09:53:18', '2025-10-30 09:53:18'),
(1949, 28, 'first_win', 1, '2025-10-30 10:05:33', 1, '2025-10-30 10:05:33', '2025-10-30 10:05:33'),
(1950, 28, 'no_mistakes', 1, '2025-10-30 10:05:33', 1, '2025-10-30 10:05:33', '2025-10-30 10:05:33'),
(1951, 28, 'no_hints', 1, '2025-10-30 10:05:33', 1, '2025-10-30 10:05:33', '2025-10-30 10:05:33'),
(1952, 28, 'perfectionist', 1, '2025-10-30 10:05:33', 1, '2025-10-30 10:05:33', '2025-10-30 10:05:33'),
(1953, 28, 'speedster_easy', 1, '2025-10-30 10:05:33', 300, '2025-10-30 10:05:33', '2025-10-30 10:05:33'),
(1954, 28, 'speedster_medium', 0, NULL, 0, '2025-10-30 10:05:33', '2025-10-30 10:05:33'),
(1955, 28, 'speedster_hard', 0, NULL, 0, '2025-10-30 10:05:33', '2025-10-30 10:05:33'),
(1956, 28, 'veteran', 0, NULL, 1, '2025-10-30 10:05:33', '2025-10-30 10:05:33'),
(1957, 28, 'master', 0, NULL, 1, '2025-10-30 10:05:33', '2025-10-30 10:05:33'),
(1958, 28, 'professional', 0, NULL, 1, '2025-10-30 10:05:33', '2025-10-30 10:05:33'),
(1959, 29, 'first_win', 1, '2025-10-30 10:23:43', 1, '2025-10-30 10:23:43', '2025-10-30 10:23:43'),
(1960, 29, 'no_mistakes', 0, NULL, 0, '2025-10-30 10:23:43', '2025-10-30 10:23:43'),
(1961, 29, 'no_hints', 1, '2025-10-30 10:23:43', 1, '2025-10-30 10:23:43', '2025-10-30 10:23:43'),
(1962, 29, 'perfectionist', 0, NULL, 0, '2025-10-30 10:23:43', '2025-10-30 10:23:43'),
(1963, 29, 'speedster_easy', 0, NULL, 0, '2025-10-30 10:23:43', '2025-10-30 10:23:43'),
(1964, 29, 'speedster_medium', 0, NULL, 0, '2025-10-30 10:23:43', '2025-10-30 10:23:43'),
(1965, 29, 'speedster_hard', 0, NULL, 0, '2025-10-30 10:23:43', '2025-10-30 10:23:43'),
(1966, 29, 'veteran', 0, NULL, 1, '2025-10-30 10:23:43', '2025-10-30 10:23:43'),
(1967, 29, 'master', 0, NULL, 1, '2025-10-30 10:23:43', '2025-10-30 10:23:43'),
(1968, 29, 'professional', 0, NULL, 1, '2025-10-30 10:23:43', '2025-10-30 10:23:43'),
(1979, 31, 'first_win', 1, '2025-10-30 10:35:39', 1, '2025-10-30 10:35:39', '2025-10-30 10:35:39'),
(1980, 31, 'no_mistakes', 1, '2025-10-30 10:35:39', 1, '2025-10-30 10:35:39', '2025-10-30 10:35:39'),
(1981, 31, 'no_hints', 1, '2025-10-30 10:35:39', 1, '2025-10-30 10:35:39', '2025-10-30 10:35:39'),
(1982, 31, 'perfectionist', 1, '2025-10-30 10:35:39', 1, '2025-10-30 10:35:39', '2025-10-30 10:35:39'),
(1983, 31, 'speedster_easy', 0, NULL, 0, '2025-10-30 10:35:39', '2025-10-30 10:35:39'),
(1984, 31, 'speedster_medium', 0, NULL, 0, '2025-10-30 10:35:39', '2025-10-30 10:35:39'),
(1985, 31, 'speedster_hard', 0, NULL, 0, '2025-10-30 10:35:39', '2025-10-30 10:35:39'),
(1986, 31, 'veteran', 0, NULL, 1, '2025-10-30 10:35:39', '2025-10-30 10:35:39'),
(1987, 31, 'master', 0, NULL, 1, '2025-10-30 10:35:39', '2025-10-30 10:35:39'),
(1988, 31, 'professional', 0, NULL, 1, '2025-10-30 10:35:39', '2025-10-30 10:35:39'),
(1989, 32, 'first_win', 1, '2025-10-30 10:43:39', 1, '2025-10-30 10:43:40', '2025-10-30 10:43:40'),
(1990, 32, 'no_mistakes', 0, NULL, 0, '2025-10-30 10:43:40', '2025-10-30 10:43:40'),
(1991, 32, 'no_hints', 1, '2025-10-30 10:43:39', 1, '2025-10-30 10:43:40', '2025-10-30 10:43:40'),
(1992, 32, 'perfectionist', 0, NULL, 0, '2025-10-30 10:43:40', '2025-10-30 10:43:40'),
(1993, 32, 'speedster_easy', 0, NULL, 0, '2025-10-30 10:43:40', '2025-10-30 10:43:40'),
(1994, 32, 'speedster_medium', 0, NULL, 0, '2025-10-30 10:43:40', '2025-10-30 10:43:40'),
(1995, 32, 'speedster_hard', 0, NULL, 0, '2025-10-30 10:43:40', '2025-10-30 10:43:40'),
(1996, 32, 'veteran', 0, NULL, 1, '2025-10-30 10:43:40', '2025-10-30 10:43:40'),
(1997, 32, 'master', 0, NULL, 1, '2025-10-30 10:43:40', '2025-10-30 10:43:40'),
(1998, 32, 'professional', 0, NULL, 1, '2025-10-30 10:43:40', '2025-10-30 10:43:40'),
(1999, 33, 'first_win', 1, '2025-10-30 11:04:26', 1, '2025-10-30 11:04:26', '2025-10-30 11:04:26'),
(2000, 33, 'no_mistakes', 0, NULL, 0, '2025-10-30 11:04:26', '2025-10-30 11:04:26'),
(2001, 33, 'no_hints', 1, '2025-10-30 11:04:26', 1, '2025-10-30 11:04:26', '2025-10-30 11:04:26'),
(2002, 33, 'perfectionist', 0, NULL, 0, '2025-10-30 11:04:26', '2025-10-30 11:04:26'),
(2003, 33, 'speedster_easy', 0, NULL, 0, '2025-10-30 11:04:26', '2025-10-30 11:04:26'),
(2004, 33, 'speedster_medium', 0, NULL, 0, '2025-10-30 11:04:26', '2025-10-30 11:04:26'),
(2005, 33, 'speedster_hard', 0, NULL, 0, '2025-10-30 11:04:26', '2025-10-30 11:04:26'),
(2006, 33, 'veteran', 0, NULL, 1, '2025-10-30 11:04:26', '2025-10-30 11:04:26'),
(2007, 33, 'master', 0, NULL, 1, '2025-10-30 11:04:26', '2025-10-30 11:04:26'),
(2008, 33, 'professional', 0, NULL, 1, '2025-10-30 11:04:26', '2025-10-30 11:04:26'),
(2029, 30, 'first_win', 1, '2025-10-30 10:29:18', 1, '2025-10-30 11:34:35', '2025-10-30 11:34:35'),
(2030, 30, 'no_mistakes', 1, '2025-10-30 10:29:18', 1, '2025-10-30 11:34:35', '2025-10-30 11:34:35'),
(2031, 30, 'no_hints', 1, '2025-10-30 10:29:18', 1, '2025-10-30 11:34:35', '2025-10-30 11:34:35'),
(2032, 30, 'perfectionist', 1, '2025-10-30 10:29:18', 1, '2025-10-30 11:34:35', '2025-10-30 11:34:35'),
(2033, 30, 'speedster_easy', 1, '2025-10-30 10:29:18', 300, '2025-10-30 11:34:35', '2025-10-30 11:34:35'),
(2034, 30, 'speedster_medium', 0, NULL, 0, '2025-10-30 11:34:35', '2025-10-30 11:34:35'),
(2035, 30, 'speedster_hard', 0, NULL, 0, '2025-10-30 11:34:35', '2025-10-30 11:34:35'),
(2036, 30, 'veteran', 0, NULL, 2, '2025-10-30 11:34:35', '2025-10-30 11:34:35'),
(2037, 30, 'master', 0, NULL, 2, '2025-10-30 11:34:35', '2025-10-30 11:34:35'),
(2038, 30, 'professional', 0, NULL, 2, '2025-10-30 11:34:35', '2025-10-30 11:34:35'),
(2039, 36, 'first_win', 1, '2025-10-30 12:07:36', 1, '2025-10-30 12:07:37', '2025-10-30 12:07:37'),
(2040, 36, 'no_mistakes', 0, NULL, 0, '2025-10-30 12:07:37', '2025-10-30 12:07:37'),
(2041, 36, 'no_hints', 1, '2025-10-30 12:07:36', 1, '2025-10-30 12:07:37', '2025-10-30 12:07:37'),
(2042, 36, 'perfectionist', 0, NULL, 0, '2025-10-30 12:07:37', '2025-10-30 12:07:37'),
(2043, 36, 'speedster_easy', 0, NULL, 0, '2025-10-30 12:07:37', '2025-10-30 12:07:37'),
(2044, 36, 'speedster_medium', 0, NULL, 0, '2025-10-30 12:07:37', '2025-10-30 12:07:37'),
(2045, 36, 'speedster_hard', 0, NULL, 0, '2025-10-30 12:07:37', '2025-10-30 12:07:37'),
(2046, 36, 'veteran', 0, NULL, 1, '2025-10-30 12:07:37', '2025-10-30 12:07:37'),
(2047, 36, 'master', 0, NULL, 1, '2025-10-30 12:07:37', '2025-10-30 12:07:37'),
(2048, 36, 'professional', 0, NULL, 1, '2025-10-30 12:07:37', '2025-10-30 12:07:37'),
(2049, 37, 'first_win', 1, '2025-10-30 12:19:21', 1, '2025-10-30 12:19:22', '2025-10-30 12:19:22'),
(2050, 37, 'no_mistakes', 0, NULL, 0, '2025-10-30 12:19:22', '2025-10-30 12:19:22'),
(2051, 37, 'no_hints', 0, NULL, 0, '2025-10-30 12:19:22', '2025-10-30 12:19:22'),
(2052, 37, 'perfectionist', 0, NULL, 0, '2025-10-30 12:19:22', '2025-10-30 12:19:22'),
(2053, 37, 'speedster_easy', 0, NULL, 0, '2025-10-30 12:19:22', '2025-10-30 12:19:22'),
(2054, 37, 'speedster_medium', 0, NULL, 0, '2025-10-30 12:19:22', '2025-10-30 12:19:22'),
(2055, 37, 'speedster_hard', 0, NULL, 0, '2025-10-30 12:19:22', '2025-10-30 12:19:22'),
(2056, 37, 'veteran', 0, NULL, 1, '2025-10-30 12:19:22', '2025-10-30 12:19:22'),
(2057, 37, 'master', 0, NULL, 1, '2025-10-30 12:19:22', '2025-10-30 12:19:22'),
(2058, 37, 'professional', 0, NULL, 1, '2025-10-30 12:19:22', '2025-10-30 12:19:22'),
(2099, 39, 'first_win', 1, '2025-10-31 06:13:16', 1, '2025-10-31 06:13:16', '2025-10-31 06:13:16'),
(2100, 39, 'no_mistakes', 1, '2025-10-31 06:13:16', 1, '2025-10-31 06:13:16', '2025-10-31 06:13:16'),
(2101, 39, 'no_hints', 0, NULL, 0, '2025-10-31 06:13:16', '2025-10-31 06:13:16'),
(2102, 39, 'perfectionist', 0, NULL, 0, '2025-10-31 06:13:16', '2025-10-31 06:13:16'),
(2103, 39, 'speedster_easy', 0, NULL, 0, '2025-10-31 06:13:16', '2025-10-31 06:13:16'),
(2104, 39, 'speedster_medium', 0, NULL, 0, '2025-10-31 06:13:16', '2025-10-31 06:13:16'),
(2105, 39, 'speedster_hard', 0, NULL, 0, '2025-10-31 06:13:16', '2025-10-31 06:13:16'),
(2106, 39, 'veteran', 0, NULL, 1, '2025-10-31 06:13:16', '2025-10-31 06:13:16'),
(2107, 39, 'master', 0, NULL, 1, '2025-10-31 06:13:16', '2025-10-31 06:13:16'),
(2108, 39, 'professional', 0, NULL, 1, '2025-10-31 06:13:16', '2025-10-31 06:13:16'),
(2109, 40, 'first_win', 1, '2025-10-31 06:28:55', 1, '2025-10-31 06:28:55', '2025-10-31 06:28:55'),
(2110, 40, 'no_mistakes', 1, '2025-10-31 06:28:55', 1, '2025-10-31 06:28:55', '2025-10-31 06:28:55'),
(2111, 40, 'no_hints', 0, NULL, 0, '2025-10-31 06:28:55', '2025-10-31 06:28:55'),
(2112, 40, 'perfectionist', 0, NULL, 0, '2025-10-31 06:28:55', '2025-10-31 06:28:55'),
(2113, 40, 'speedster_easy', 0, NULL, 0, '2025-10-31 06:28:55', '2025-10-31 06:28:55'),
(2114, 40, 'speedster_medium', 0, NULL, 0, '2025-10-31 06:28:55', '2025-10-31 06:28:55'),
(2115, 40, 'speedster_hard', 0, NULL, 0, '2025-10-31 06:28:55', '2025-10-31 06:28:55'),
(2116, 40, 'veteran', 0, NULL, 1, '2025-10-31 06:28:55', '2025-10-31 06:28:55'),
(2117, 40, 'master', 0, NULL, 1, '2025-10-31 06:28:55', '2025-10-31 06:28:55'),
(2118, 40, 'professional', 0, NULL, 1, '2025-10-31 06:28:55', '2025-10-31 06:28:55'),
(2119, 41, 'first_win', 1, '2025-10-31 06:38:32', 1, '2025-10-31 06:38:33', '2025-10-31 06:38:33'),
(2120, 41, 'no_mistakes', 0, NULL, 0, '2025-10-31 06:38:33', '2025-10-31 06:38:33'),
(2121, 41, 'no_hints', 1, '2025-10-31 06:38:32', 1, '2025-10-31 06:38:33', '2025-10-31 06:38:33'),
(2122, 41, 'perfectionist', 0, NULL, 0, '2025-10-31 06:38:33', '2025-10-31 06:38:33'),
(2123, 41, 'speedster_easy', 0, NULL, 0, '2025-10-31 06:38:33', '2025-10-31 06:38:33'),
(2124, 41, 'speedster_medium', 0, NULL, 0, '2025-10-31 06:38:33', '2025-10-31 06:38:33'),
(2125, 41, 'speedster_hard', 0, NULL, 0, '2025-10-31 06:38:33', '2025-10-31 06:38:33'),
(2126, 41, 'veteran', 0, NULL, 1, '2025-10-31 06:38:33', '2025-10-31 06:38:33'),
(2127, 41, 'master', 0, NULL, 1, '2025-10-31 06:38:33', '2025-10-31 06:38:33'),
(2128, 41, 'professional', 0, NULL, 1, '2025-10-31 06:38:33', '2025-10-31 06:38:33'),
(2139, 43, 'first_win', 1, '2025-10-31 07:07:18', 1, '2025-10-31 07:07:19', '2025-10-31 07:07:19'),
(2140, 43, 'no_mistakes', 0, NULL, 0, '2025-10-31 07:07:19', '2025-10-31 07:07:19'),
(2141, 43, 'no_hints', 0, NULL, 0, '2025-10-31 07:07:19', '2025-10-31 07:07:19'),
(2142, 43, 'perfectionist', 0, NULL, 0, '2025-10-31 07:07:19', '2025-10-31 07:07:19'),
(2143, 43, 'speedster_easy', 0, NULL, 0, '2025-10-31 07:07:19', '2025-10-31 07:07:19'),
(2144, 43, 'speedster_medium', 0, NULL, 0, '2025-10-31 07:07:19', '2025-10-31 07:07:19'),
(2145, 43, 'speedster_hard', 0, NULL, 0, '2025-10-31 07:07:19', '2025-10-31 07:07:19'),
(2146, 43, 'veteran', 0, NULL, 1, '2025-10-31 07:07:19', '2025-10-31 07:07:19'),
(2147, 43, 'master', 0, NULL, 1, '2025-10-31 07:07:19', '2025-10-31 07:07:19'),
(2148, 43, 'professional', 0, NULL, 1, '2025-10-31 07:07:19', '2025-10-31 07:07:19'),
(2179, 46, 'first_win', 1, '2025-10-31 08:00:58', 1, '2025-10-31 08:09:45', '2025-10-31 08:09:45'),
(2180, 46, 'no_mistakes', 0, NULL, 0, '2025-10-31 08:09:45', '2025-10-31 08:09:45'),
(2181, 46, 'no_hints', 1, '2025-10-31 08:09:44', 1, '2025-10-31 08:09:45', '2025-10-31 08:09:45'),
(2182, 46, 'perfectionist', 0, NULL, 0, '2025-10-31 08:09:45', '2025-10-31 08:09:45'),
(2183, 46, 'speedster_easy', 0, NULL, 0, '2025-10-31 08:09:45', '2025-10-31 08:09:45'),
(2184, 46, 'speedster_medium', 0, NULL, 0, '2025-10-31 08:09:45', '2025-10-31 08:09:45'),
(2185, 46, 'speedster_hard', 0, NULL, 0, '2025-10-31 08:09:45', '2025-10-31 08:09:45'),
(2186, 46, 'veteran', 0, NULL, 2, '2025-10-31 08:09:45', '2025-10-31 08:09:45'),
(2187, 46, 'master', 0, NULL, 2, '2025-10-31 08:09:45', '2025-10-31 08:09:45'),
(2188, 46, 'professional', 0, NULL, 2, '2025-10-31 08:09:45', '2025-10-31 08:09:45'),
(2199, 48, 'first_win', 1, '2025-10-31 09:13:56', 1, '2025-10-31 09:13:57', '2025-10-31 09:13:57'),
(2200, 48, 'no_mistakes', 0, NULL, 0, '2025-10-31 09:13:57', '2025-10-31 09:13:57'),
(2201, 48, 'no_hints', 1, '2025-10-31 09:13:56', 1, '2025-10-31 09:13:57', '2025-10-31 09:13:57'),
(2202, 48, 'perfectionist', 0, NULL, 0, '2025-10-31 09:13:57', '2025-10-31 09:13:57'),
(2203, 48, 'speedster_easy', 0, NULL, 0, '2025-10-31 09:13:57', '2025-10-31 09:13:57'),
(2204, 48, 'speedster_medium', 0, NULL, 0, '2025-10-31 09:13:57', '2025-10-31 09:13:57'),
(2205, 48, 'speedster_hard', 0, NULL, 0, '2025-10-31 09:13:57', '2025-10-31 09:13:57'),
(2206, 48, 'veteran', 0, NULL, 1, '2025-10-31 09:13:57', '2025-10-31 09:13:57'),
(2207, 48, 'master', 0, NULL, 1, '2025-10-31 09:13:57', '2025-10-31 09:13:57'),
(2208, 48, 'professional', 0, NULL, 1, '2025-10-31 09:13:57', '2025-10-31 09:13:57'),
(2219, 49, 'first_win', 1, '2025-10-31 09:37:11', 1, '2025-10-31 10:03:00', '2025-10-31 10:03:00'),
(2220, 49, 'no_mistakes', 1, '2025-10-31 10:02:59', 1, '2025-10-31 10:03:00', '2025-10-31 10:03:00'),
(2221, 49, 'no_hints', 1, '2025-10-31 09:37:11', 1, '2025-10-31 10:03:00', '2025-10-31 10:03:00'),
(2222, 49, 'perfectionist', 1, '2025-10-31 10:02:59', 1, '2025-10-31 10:03:00', '2025-10-31 10:03:00'),
(2223, 49, 'speedster_easy', 0, NULL, 0, '2025-10-31 10:03:00', '2025-10-31 10:03:00'),
(2224, 49, 'speedster_medium', 0, NULL, 0, '2025-10-31 10:03:00', '2025-10-31 10:03:00'),
(2225, 49, 'speedster_hard', 0, NULL, 0, '2025-10-31 10:03:00', '2025-10-31 10:03:00'),
(2226, 49, 'veteran', 0, NULL, 2, '2025-10-31 10:03:00', '2025-10-31 10:03:00'),
(2227, 49, 'master', 0, NULL, 2, '2025-10-31 10:03:00', '2025-10-31 10:03:00'),
(2228, 49, 'professional', 0, NULL, 2, '2025-10-31 10:03:00', '2025-10-31 10:03:00'),
(2239, 51, 'first_win', 1, '2025-10-31 10:47:24', 1, '2025-10-31 10:47:23', '2025-10-31 10:47:23'),
(2240, 51, 'no_mistakes', 1, '2025-10-31 10:47:24', 1, '2025-10-31 10:47:23', '2025-10-31 10:47:23'),
(2241, 51, 'no_hints', 1, '2025-10-31 10:47:24', 1, '2025-10-31 10:47:23', '2025-10-31 10:47:23'),
(2242, 51, 'perfectionist', 1, '2025-10-31 10:47:24', 1, '2025-10-31 10:47:23', '2025-10-31 10:47:23'),
(2243, 51, 'speedster_easy', 0, NULL, 0, '2025-10-31 10:47:23', '2025-10-31 10:47:23'),
(2244, 51, 'speedster_medium', 0, NULL, 0, '2025-10-31 10:47:23', '2025-10-31 10:47:23'),
(2245, 51, 'speedster_hard', 0, NULL, 0, '2025-10-31 10:47:23', '2025-10-31 10:47:23'),
(2246, 51, 'veteran', 0, NULL, 1, '2025-10-31 10:47:23', '2025-10-31 10:47:23'),
(2247, 51, 'master', 0, NULL, 1, '2025-10-31 10:47:23', '2025-10-31 10:47:23'),
(2248, 51, 'professional', 0, NULL, 1, '2025-10-31 10:47:23', '2025-10-31 10:47:23'),
(2599, 52, 'first_win', 1, '2025-10-31 11:16:20', 1, '2025-11-05 10:24:51', '2025-11-05 10:24:51'),
(2600, 52, 'no_mistakes', 1, '2025-10-31 13:26:30', 1, '2025-11-05 10:24:51', '2025-11-05 10:24:51'),
(2601, 52, 'no_hints', 1, '2025-10-31 11:16:20', 1, '2025-11-05 10:24:51', '2025-11-05 10:24:51'),
(2602, 52, 'perfectionist', 1, '2025-11-05 10:16:01', 1, '2025-11-05 10:24:51', '2025-11-05 10:24:51'),
(2603, 52, 'speedster_easy', 0, NULL, 0, '2025-11-05 10:24:51', '2025-11-05 10:24:51'),
(2604, 52, 'speedster_medium', 0, NULL, 0, '2025-11-05 10:24:51', '2025-11-05 10:24:51'),
(2605, 52, 'speedster_hard', 0, NULL, 0, '2025-11-05 10:24:51', '2025-11-05 10:24:51'),
(2606, 52, 'veteran', 0, NULL, 4, '2025-11-05 10:24:51', '2025-11-05 10:24:51'),
(2607, 52, 'master', 0, NULL, 4, '2025-11-05 10:24:51', '2025-11-05 10:24:51'),
(2608, 52, 'professional', 0, NULL, 4, '2025-11-05 10:24:51', '2025-11-05 10:24:51'),
(2629, 42, 'first_win', 1, '2025-10-31 06:53:39', 1, '2025-11-06 08:23:39', '2025-11-06 08:23:39'),
(2630, 42, 'no_mistakes', 1, '2025-11-06 08:23:39', 1, '2025-11-06 08:23:39', '2025-11-06 08:23:39'),
(2631, 42, 'no_hints', 1, '2025-10-31 06:53:39', 1, '2025-11-06 08:23:39', '2025-11-06 08:23:39'),
(2632, 42, 'perfectionist', 1, '2025-11-06 08:23:39', 1, '2025-11-06 08:23:39', '2025-11-06 08:23:39'),
(2633, 42, 'speedster_easy', 1, '2025-10-31 06:53:39', 300, '2025-11-06 08:23:39', '2025-11-06 08:23:39'),
(2634, 42, 'speedster_medium', 0, NULL, 0, '2025-11-06 08:23:39', '2025-11-06 08:23:39'),
(2635, 42, 'speedster_hard', 0, NULL, 0, '2025-11-06 08:23:39', '2025-11-06 08:23:39'),
(2636, 42, 'veteran', 0, NULL, 2, '2025-11-06 08:23:39', '2025-11-06 08:23:39'),
(2637, 42, 'master', 0, NULL, 2, '2025-11-06 08:23:39', '2025-11-06 08:23:39'),
(2638, 42, 'professional', 0, NULL, 2, '2025-11-06 08:23:39', '2025-11-06 08:23:39'),
(2659, 44, 'first_win', 1, '2025-10-31 07:21:07', 1, '2025-11-07 07:01:14', '2025-11-07 07:01:14'),
(2660, 44, 'no_mistakes', 1, '2025-10-31 07:21:07', 1, '2025-11-07 07:01:14', '2025-11-07 07:01:14'),
(2661, 44, 'no_hints', 1, '2025-11-07 07:01:13', 1, '2025-11-07 07:01:14', '2025-11-07 07:01:14'),
(2662, 44, 'perfectionist', 1, '2025-11-07 07:01:13', 1, '2025-11-07 07:01:14', '2025-11-07 07:01:14'),
(2663, 44, 'speedster_easy', 0, NULL, 0, '2025-11-07 07:01:14', '2025-11-07 07:01:14'),
(2664, 44, 'speedster_medium', 0, NULL, 0, '2025-11-07 07:01:14', '2025-11-07 07:01:14'),
(2665, 44, 'speedster_hard', 0, NULL, 0, '2025-11-07 07:01:14', '2025-11-07 07:01:14'),
(2666, 44, 'veteran', 0, NULL, 2, '2025-11-07 07:01:14', '2025-11-07 07:01:14'),
(2667, 44, 'master', 0, NULL, 2, '2025-11-07 07:01:14', '2025-11-07 07:01:14'),
(2668, 44, 'professional', 0, NULL, 2, '2025-11-07 07:01:14', '2025-11-07 07:01:14'),
(2739, 45, 'first_win', 1, '2025-10-31 07:50:25', 1, '2025-11-07 14:38:06', '2025-11-07 14:38:06'),
(2740, 45, 'no_mistakes', 1, '2025-11-07 14:27:57', 1, '2025-11-07 14:38:06', '2025-11-07 14:38:06'),
(2741, 45, 'no_hints', 1, '2025-10-31 07:50:25', 1, '2025-11-07 14:38:06', '2025-11-07 14:38:06'),
(2742, 45, 'perfectionist', 1, '2025-11-07 14:38:05', 1, '2025-11-07 14:38:06', '2025-11-07 14:38:06'),
(2743, 45, 'speedster_easy', 1, '2025-11-07 14:27:57', 300, '2025-11-07 14:38:06', '2025-11-07 14:38:06'),
(2744, 45, 'speedster_medium', 0, NULL, 0, '2025-11-07 14:38:06', '2025-11-07 14:38:06'),
(2745, 45, 'speedster_hard', 0, NULL, 0, '2025-11-07 14:38:06', '2025-11-07 14:38:06'),
(2746, 45, 'veteran', 0, NULL, 4, '2025-11-07 14:38:06', '2025-11-07 14:38:06'),
(2747, 45, 'master', 0, NULL, 4, '2025-11-07 14:38:06', '2025-11-07 14:38:06'),
(2748, 45, 'professional', 0, NULL, 4, '2025-11-07 14:38:06', '2025-11-07 14:38:06'),
(2759, 38, 'first_win', 1, '2025-10-30 19:28:29', 1, '2025-11-07 14:53:46', '2025-11-07 14:53:46'),
(2760, 38, 'no_mistakes', 1, '2025-11-01 09:01:25', 1, '2025-11-07 14:53:46', '2025-11-07 14:53:46'),
(2761, 38, 'no_hints', 1, '2025-10-30 19:37:21', 1, '2025-11-07 14:53:46', '2025-11-07 14:53:46'),
(2762, 38, 'perfectionist', 1, '2025-11-01 09:01:25', 1, '2025-11-07 14:53:46', '2025-11-07 14:53:46'),
(2763, 38, 'speedster_easy', 1, '2025-11-07 14:53:44', 300, '2025-11-07 14:53:46', '2025-11-07 14:53:46'),
(2764, 38, 'speedster_medium', 0, NULL, 0, '2025-11-07 14:53:46', '2025-11-07 14:53:46'),
(2765, 38, 'speedster_hard', 0, NULL, 0, '2025-11-07 14:53:46', '2025-11-07 14:53:46'),
(2766, 38, 'veteran', 0, NULL, 16, '2025-11-07 14:53:46', '2025-11-07 14:53:46'),
(2767, 38, 'master', 0, NULL, 16, '2025-11-07 14:53:46', '2025-11-07 14:53:46'),
(2768, 38, 'professional', 0, NULL, 16, '2025-11-07 14:53:46', '2025-11-07 14:53:46'),
(2769, 57, 'first_win', 1, '2025-11-07 11:32:48', 1, '2025-11-07 15:18:03', '2025-11-07 15:18:03'),
(2770, 57, 'no_mistakes', 1, '2025-11-07 11:32:48', 1, '2025-11-07 15:18:03', '2025-11-07 15:18:03'),
(2771, 57, 'no_hints', 1, '2025-11-07 15:18:02', 1, '2025-11-07 15:18:03', '2025-11-07 15:18:03'),
(2772, 57, 'perfectionist', 0, NULL, 0, '2025-11-07 15:18:03', '2025-11-07 15:18:03'),
(2773, 57, 'speedster_easy', 1, '2025-11-07 11:32:48', 300, '2025-11-07 15:18:03', '2025-11-07 15:18:03'),
(2774, 57, 'speedster_medium', 0, NULL, 0, '2025-11-07 15:18:03', '2025-11-07 15:18:03'),
(2775, 57, 'speedster_hard', 0, NULL, 0, '2025-11-07 15:18:03', '2025-11-07 15:18:03'),
(2776, 57, 'veteran', 0, NULL, 2, '2025-11-07 15:18:03', '2025-11-07 15:18:03'),
(2777, 57, 'master', 0, NULL, 2, '2025-11-07 15:18:03', '2025-11-07 15:18:03'),
(2778, 57, 'professional', 0, NULL, 2, '2025-11-07 15:18:03', '2025-11-07 15:18:03'),
(2839, 56, 'first_win', 1, '2025-11-07 15:43:10', 1, '2025-11-07 17:11:54', '2025-11-07 17:11:54'),
(2840, 56, 'no_mistakes', 1, '2025-11-07 15:49:22', 1, '2025-11-07 17:11:54', '2025-11-07 17:11:54'),
(2841, 56, 'no_hints', 1, '2025-11-07 15:43:10', 1, '2025-11-07 17:11:54', '2025-11-07 17:11:54'),
(2842, 56, 'perfectionist', 1, '2025-11-07 15:49:22', 1, '2025-11-07 17:11:54', '2025-11-07 17:11:54'),
(2843, 56, 'speedster_easy', 0, NULL, 0, '2025-11-07 17:11:54', '2025-11-07 17:11:54'),
(2844, 56, 'speedster_medium', 0, NULL, 0, '2025-11-07 17:11:54', '2025-11-07 17:11:54'),
(2845, 56, 'speedster_hard', 0, NULL, 0, '2025-11-07 17:11:54', '2025-11-07 17:11:54'),
(2846, 56, 'veteran', 0, NULL, 7, '2025-11-07 17:11:54', '2025-11-07 17:11:54'),
(2847, 56, 'master', 0, NULL, 7, '2025-11-07 17:11:54', '2025-11-07 17:11:54'),
(2848, 56, 'professional', 0, NULL, 7, '2025-11-07 17:11:54', '2025-11-07 17:11:54'),
(2879, 35, 'first_win', 1, '2025-10-30 11:23:33', 1, '2025-11-07 19:35:18', '2025-11-07 19:35:18'),
(2880, 35, 'no_mistakes', 1, '2025-11-07 19:07:10', 1, '2025-11-07 19:35:18', '2025-11-07 19:35:18'),
(2881, 35, 'no_hints', 1, '2025-10-30 11:23:33', 1, '2025-11-07 19:35:18', '2025-11-07 19:35:18'),
(2882, 35, 'perfectionist', 1, '2025-11-07 19:07:10', 1, '2025-11-07 19:35:18', '2025-11-07 19:35:18'),
(2883, 35, 'speedster_easy', 1, '2025-11-07 19:12:42', 300, '2025-11-07 19:35:18', '2025-11-07 19:35:18'),
(2884, 35, 'speedster_medium', 0, NULL, 0, '2025-11-07 19:35:18', '2025-11-07 19:35:18'),
(2885, 35, 'speedster_hard', 0, NULL, 0, '2025-11-07 19:35:18', '2025-11-07 19:35:18'),
(2886, 35, 'veteran', 0, NULL, 5, '2025-11-07 19:35:18', '2025-11-07 19:35:18'),
(2887, 35, 'master', 0, NULL, 5, '2025-11-07 19:35:18', '2025-11-07 19:35:18'),
(2888, 35, 'professional', 0, NULL, 5, '2025-11-07 19:35:18', '2025-11-07 19:35:18'),
(2899, 26, 'first_win', 1, '2025-10-30 09:45:37', 1, '2025-11-08 10:15:52', '2025-11-08 10:15:52'),
(2900, 26, 'no_mistakes', 1, '2025-11-07 14:47:19', 1, '2025-11-08 10:15:52', '2025-11-08 10:15:52'),
(2901, 26, 'no_hints', 1, '2025-10-30 09:45:37', 1, '2025-11-08 10:15:52', '2025-11-08 10:15:52'),
(2902, 26, 'perfectionist', 1, '2025-11-07 14:47:19', 1, '2025-11-08 10:15:52', '2025-11-08 10:15:52'),
(2903, 26, 'speedster_easy', 1, '2025-10-30 09:45:37', 300, '2025-11-08 10:15:52', '2025-11-08 10:15:52'),
(2904, 26, 'speedster_medium', 0, NULL, 0, '2025-11-08 10:15:52', '2025-11-08 10:15:52'),
(2905, 26, 'speedster_hard', 0, NULL, 0, '2025-11-08 10:15:52', '2025-11-08 10:15:52'),
(2906, 26, 'veteran', 0, NULL, 4, '2025-11-08 10:15:52', '2025-11-08 10:15:52'),
(2907, 26, 'master', 0, NULL, 4, '2025-11-08 10:15:52', '2025-11-08 10:15:52'),
(2908, 26, 'professional', 0, NULL, 4, '2025-11-08 10:15:52', '2025-11-08 10:15:52'),
(2929, 34, 'first_win', 1, '2025-10-30 11:14:05', 1, '2025-11-08 15:59:21', '2025-11-08 15:59:21'),
(2930, 34, 'no_mistakes', 1, '2025-10-30 11:14:05', 1, '2025-11-08 15:59:21', '2025-11-08 15:59:21'),
(2931, 34, 'no_hints', 1, '2025-10-30 11:14:05', 1, '2025-11-08 15:59:21', '2025-11-08 15:59:21'),
(2932, 34, 'perfectionist', 1, '2025-10-30 11:14:05', 1, '2025-11-08 15:59:21', '2025-11-08 15:59:21'),
(2933, 34, 'speedster_easy', 1, '2025-11-08 15:59:22', 300, '2025-11-08 15:59:21', '2025-11-08 15:59:21'),
(2934, 34, 'speedster_medium', 0, NULL, 0, '2025-11-08 15:59:21', '2025-11-08 15:59:21'),
(2935, 34, 'speedster_hard', 0, NULL, 0, '2025-11-08 15:59:21', '2025-11-08 15:59:21'),
(2936, 34, 'veteran', 0, NULL, 2, '2025-11-08 15:59:21', '2025-11-08 15:59:21'),
(2937, 34, 'master', 0, NULL, 2, '2025-11-08 15:59:21', '2025-11-08 15:59:21'),
(2938, 34, 'professional', 0, NULL, 2, '2025-11-08 15:59:21', '2025-11-08 15:59:21'),
(2939, 25, 'first_win', 1, '2025-10-30 09:38:35', 1, '2025-11-08 16:11:16', '2025-11-08 16:11:16'),
(2940, 25, 'no_mistakes', 1, '2025-10-30 09:38:35', 1, '2025-11-08 16:11:16', '2025-11-08 16:11:16'),
(2941, 25, 'no_hints', 1, '2025-10-30 09:38:35', 1, '2025-11-08 16:11:16', '2025-11-08 16:11:16'),
(2942, 25, 'perfectionist', 1, '2025-10-30 09:38:35', 1, '2025-11-08 16:11:16', '2025-11-08 16:11:16'),
(2943, 25, 'speedster_easy', 1, '2025-10-30 09:38:35', 300, '2025-11-08 16:11:16', '2025-11-08 16:11:16'),
(2944, 25, 'speedster_medium', 0, NULL, 0, '2025-11-08 16:11:16', '2025-11-08 16:11:16'),
(2945, 25, 'speedster_hard', 0, NULL, 0, '2025-11-08 16:11:16', '2025-11-08 16:11:16'),
(2946, 25, 'veteran', 0, NULL, 2, '2025-11-08 16:11:16', '2025-11-08 16:11:16'),
(2947, 25, 'master', 0, NULL, 2, '2025-11-08 16:11:16', '2025-11-08 16:11:16'),
(2948, 25, 'professional', 0, NULL, 2, '2025-11-08 16:11:16', '2025-11-08 16:11:16'),
(2949, 50, 'first_win', 1, '2025-10-31 10:13:28', 1, '2025-11-08 16:44:56', '2025-11-08 16:44:56'),
(2950, 50, 'no_mistakes', 1, '2025-10-31 10:13:28', 1, '2025-11-08 16:44:56', '2025-11-08 16:44:56'),
(2951, 50, 'no_hints', 1, '2025-10-31 10:13:28', 1, '2025-11-08 16:44:56', '2025-11-08 16:44:56'),
(2952, 50, 'perfectionist', 1, '2025-10-31 10:13:28', 1, '2025-11-08 16:44:56', '2025-11-08 16:44:56'),
(2953, 50, 'speedster_easy', 1, '2025-11-08 16:44:56', 300, '2025-11-08 16:44:56', '2025-11-08 16:44:56'),
(2954, 50, 'speedster_medium', 0, NULL, 0, '2025-11-08 16:44:56', '2025-11-08 16:44:56'),
(2955, 50, 'speedster_hard', 0, NULL, 0, '2025-11-08 16:44:56', '2025-11-08 16:44:56'),
(2956, 50, 'veteran', 0, NULL, 4, '2025-11-08 16:44:56', '2025-11-08 16:44:56'),
(2957, 50, 'master', 0, NULL, 4, '2025-11-08 16:44:56', '2025-11-08 16:44:56'),
(2958, 50, 'professional', 0, NULL, 4, '2025-11-08 16:44:56', '2025-11-08 16:44:56'),
(2979, 47, 'first_win', 1, '2025-10-31 08:54:55', 1, '2025-11-08 17:04:46', '2025-11-08 17:04:46'),
(2980, 47, 'no_mistakes', 1, '2025-11-08 17:04:46', 1, '2025-11-08 17:04:46', '2025-11-08 17:04:46'),
(2981, 47, 'no_hints', 1, '2025-10-31 08:54:55', 1, '2025-11-08 17:04:46', '2025-11-08 17:04:46'),
(2982, 47, 'perfectionist', 1, '2025-11-08 17:04:46', 1, '2025-11-08 17:04:46', '2025-11-08 17:04:46'),
(2983, 47, 'speedster_easy', 1, '2025-11-08 17:04:46', 300, '2025-11-08 17:04:46', '2025-11-08 17:04:46'),
(2984, 47, 'speedster_medium', 0, NULL, 0, '2025-11-08 17:04:46', '2025-11-08 17:04:46'),
(2985, 47, 'speedster_hard', 0, NULL, 0, '2025-11-08 17:04:46', '2025-11-08 17:04:46'),
(2986, 47, 'veteran', 0, NULL, 4, '2025-11-08 17:04:46', '2025-11-08 17:04:46'),
(2987, 47, 'master', 0, NULL, 4, '2025-11-08 17:04:46', '2025-11-08 17:04:46'),
(2988, 47, 'professional', 0, NULL, 4, '2025-11-08 17:04:46', '2025-11-08 17:04:46'),
(3019, 55, 'first_win', 1, '2025-11-08 18:42:30', 1, '2025-11-08 19:14:02', '2025-11-08 19:14:02'),
(3020, 55, 'no_mistakes', 1, '2025-11-08 18:46:39', 1, '2025-11-08 19:14:02', '2025-11-08 19:14:02'),
(3021, 55, 'no_hints', 1, '2025-11-08 18:42:30', 1, '2025-11-08 19:14:02', '2025-11-08 19:14:02'),
(3022, 55, 'perfectionist', 1, '2025-11-08 18:46:39', 1, '2025-11-08 19:14:02', '2025-11-08 19:14:02'),
(3023, 55, 'speedster_easy', 1, '2025-11-08 18:46:39', 300, '2025-11-08 19:14:02', '2025-11-08 19:14:02'),
(3024, 55, 'speedster_medium', 0, NULL, 0, '2025-11-08 19:14:02', '2025-11-08 19:14:02'),
(3025, 55, 'speedster_hard', 0, NULL, 0, '2025-11-08 19:14:02', '2025-11-08 19:14:02'),
(3026, 55, 'veteran', 0, NULL, 4, '2025-11-08 19:14:02', '2025-11-08 19:14:02'),
(3027, 55, 'master', 0, NULL, 4, '2025-11-08 19:14:02', '2025-11-08 19:14:02'),
(3028, 55, 'professional', 0, NULL, 4, '2025-11-08 19:14:02', '2025-11-08 19:14:02'),
(3029, 54, 'first_win', 1, '2025-11-08 19:26:28', 1, '2025-11-08 19:26:28', '2025-11-08 19:26:28'),
(3030, 54, 'no_mistakes', 0, NULL, 0, '2025-11-08 19:26:28', '2025-11-08 19:26:28'),
(3031, 54, 'no_hints', 1, '2025-11-08 19:26:28', 1, '2025-11-08 19:26:28', '2025-11-08 19:26:28'),
(3032, 54, 'perfectionist', 0, NULL, 0, '2025-11-08 19:26:28', '2025-11-08 19:26:28'),
(3033, 54, 'speedster_easy', 0, NULL, 0, '2025-11-08 19:26:28', '2025-11-08 19:26:28'),
(3034, 54, 'speedster_medium', 0, NULL, 0, '2025-11-08 19:26:28', '2025-11-08 19:26:28'),
(3035, 54, 'speedster_hard', 0, NULL, 0, '2025-11-08 19:26:28', '2025-11-08 19:26:28'),
(3036, 54, 'veteran', 0, NULL, 1, '2025-11-08 19:26:28', '2025-11-08 19:26:28'),
(3037, 54, 'master', 0, NULL, 1, '2025-11-08 19:26:28', '2025-11-08 19:26:28'),
(3038, 54, 'professional', 0, NULL, 1, '2025-11-08 19:26:28', '2025-11-08 19:26:28'),
(5109, 18, 'first_win', 1, '2025-11-29 11:18:24', 1, '2025-11-26 14:57:56', '2025-11-30 07:06:07'),
(5110, 18, 'no_mistakes', 1, '2025-11-29 11:18:24', 1, '2025-11-26 14:57:56', '2025-11-30 07:06:07'),
(5111, 18, 'no_hints', 1, '2025-11-30 07:06:06', 1, '2025-11-26 14:57:56', '2025-11-30 07:06:07'),
(5112, 18, 'perfectionist', 1, '2025-11-30 07:06:06', 1, '2025-11-26 14:57:56', '2025-11-30 07:06:07'),
(5113, 18, 'speedster_easy', 1, '2025-11-30 07:06:06', 300, '2025-11-26 14:57:56', '2025-11-30 07:06:07'),
(5114, 18, 'speedster_medium', 0, NULL, 0, '2025-11-26 14:57:56', '2025-11-30 07:06:07'),
(5115, 18, 'speedster_hard', 0, NULL, 0, '2025-11-26 14:57:56', '2025-11-30 07:06:07'),
(5116, 18, 'veteran', 1, '2025-11-29 11:18:24', 100, '2025-11-26 14:57:56', '2025-11-30 07:06:07'),
(5117, 18, 'master', 0, NULL, 176, '2025-11-26 14:57:56', '2025-11-30 07:06:07'),
(5118, 18, 'professional', 0, NULL, 176, '2025-11-26 14:57:56', '2025-11-30 07:06:07'),
(5129, 14, 'first_win', 1, '2025-11-29 12:32:30', 1, '2025-11-26 17:21:19', '2025-11-29 18:41:58'),
(5130, 14, 'no_mistakes', 1, '2025-11-29 12:32:30', 1, '2025-11-26 17:21:19', '2025-11-29 18:41:58'),
(5131, 14, 'no_hints', 1, '2025-11-29 12:32:30', 1, '2025-11-26 17:21:19', '2025-11-29 18:41:58'),
(5132, 14, 'perfectionist', 1, '2025-11-29 11:05:18', 1, '2025-11-26 17:21:19', '2025-11-29 18:41:58'),
(5133, 14, 'speedster_easy', 1, '2025-11-29 11:05:18', 300, '2025-11-26 17:21:19', '2025-11-29 18:41:58'),
(5134, 14, 'speedster_medium', 0, NULL, 0, '2025-11-26 17:21:19', '2025-11-29 18:41:58'),
(5135, 14, 'speedster_hard', 0, NULL, 0, '2025-11-26 17:21:19', '2025-11-29 18:41:58'),
(5136, 14, 'veteran', 0, NULL, 90, '2025-11-26 17:21:19', '2025-11-29 18:41:58'),
(5137, 14, 'master', 0, NULL, 90, '2025-11-26 17:21:19', '2025-11-29 18:41:58'),
(5138, 14, 'professional', 0, NULL, 90, '2025-11-26 17:21:19', '2025-11-29 18:41:58'),
(5149, 17, 'first_win', 1, '2025-11-29 10:14:23', 1, '2025-11-26 18:39:01', '2025-11-30 20:34:27'),
(5150, 17, 'no_mistakes', 1, '2025-11-29 10:14:23', 1, '2025-11-26 18:39:01', '2025-11-30 20:34:27'),
(5151, 17, 'no_hints', 1, '2025-11-29 10:14:23', 1, '2025-11-26 18:39:01', '2025-11-30 20:34:27'),
(5152, 17, 'perfectionist', 1, '2025-11-29 11:34:21', 1, '2025-11-26 18:39:01', '2025-11-30 20:34:27'),
(5153, 17, 'speedster_easy', 1, '2025-11-29 11:34:21', 300, '2025-11-26 18:39:01', '2025-11-30 20:34:27'),
(5154, 17, 'speedster_medium', 0, NULL, 0, '2025-11-26 18:39:01', '2025-11-30 20:34:27'),
(5155, 17, 'speedster_hard', 0, NULL, 0, '2025-11-26 18:39:01', '2025-11-30 20:34:27'),
(5156, 17, 'veteran', 0, NULL, 73, '2025-11-26 18:39:01', '2025-11-30 20:34:27'),
(5157, 17, 'master', 0, NULL, 73, '2025-11-26 18:39:01', '2025-11-30 20:34:27'),
(5158, 17, 'professional', 0, NULL, 73, '2025-11-26 18:39:01', '2025-11-30 20:34:27'),
(5189, 65, 'first_win', 0, NULL, 0, '2025-11-27 08:28:35', '2025-12-02 11:01:35'),
(5190, 65, 'no_mistakes', 1, '2025-11-27 22:32:46', 1, '2025-11-27 08:28:35', '2025-12-02 11:01:35'),
(5191, 65, 'no_hints', 1, '2025-11-27 22:32:46', 1, '2025-11-27 08:28:35', '2025-12-02 11:01:35'),
(5192, 65, 'perfectionist', 1, '2025-11-29 11:14:33', 1, '2025-11-27 08:28:35', '2025-12-02 11:01:35'),
(5193, 65, 'speedster_easy', 1, '2025-11-27 22:32:46', 300, '2025-11-27 08:28:35', '2025-12-02 11:01:35'),
(5194, 65, 'speedster_medium', 0, NULL, 0, '2025-11-27 08:28:35', '2025-12-02 11:01:35'),
(5195, 65, 'speedster_hard', 0, NULL, 0, '2025-11-27 08:28:35', '2025-12-02 11:01:35'),
(5196, 65, 'veteran', 0, NULL, 65, '2025-11-27 08:28:35', '2025-12-02 11:01:35'),
(5197, 65, 'master', 0, NULL, 65, '2025-11-27 08:28:35', '2025-12-02 11:01:35'),
(5198, 65, 'professional', 0, NULL, 65, '2025-11-27 08:28:35', '2025-12-02 11:01:35');

-- --------------------------------------------------------

--
-- Структура таблицы `user_games`
--

CREATE TABLE `user_games` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `board_data` text,
  `solution_data` text,
  `fixed_cells` text,
  `difficulty` varchar(20) DEFAULT 'easy',
  `seconds` int(11) DEFAULT '0',
  `mistakes` int(11) DEFAULT '0',
  `hints_used` int(11) DEFAULT '0',
  `hints_left` int(11) DEFAULT '3',
  `was_solved` tinyint(1) DEFAULT '0',
  `game_lost` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `user_games`
--

INSERT INTO `user_games` (`id`, `user_id`, `board_data`, `solution_data`, `fixed_cells`, `difficulty`, `seconds`, `mistakes`, `hints_used`, `hints_left`, `was_solved`, `game_lost`, `created_at`, `updated_at`) VALUES
(1, 65, '[[8,5,1,4,2,6,7,9,3],[3,9,4,7,1,5,2,8,6],[7,6,2,3,9,8,4,1,5],[4,3,5,1,8,7,9,6,2],[9,7,8,5,6,2,1,3,4],[2,1,6,9,4,3,5,7,8],[5,2,3,8,7,9,6,4,1],[1,8,7,6,5,4,3,2,9],[6,4,9,2,3,1,8,5,7]]', '[[8,5,1,4,2,6,7,9,3],[3,9,4,7,1,5,2,8,6],[7,6,2,3,9,8,4,1,5],[4,3,5,1,8,7,9,6,2],[9,7,8,5,6,2,1,3,4],[2,1,6,9,4,3,5,7,8],[5,2,3,8,7,9,6,4,1],[1,8,7,6,5,4,3,2,9],[6,4,9,2,3,1,8,5,7]]', '[[true,true,true,true,true,true,true,true,true],[true,true,true,true,true,true,true,true,true],[true,true,true,true,true,true,true,true,true],[true,true,true,true,true,true,true,true,true],[true,true,true,true,true,true,true,true,true],[true,true,true,true,true,true,true,true,true],[true,true,true,true,true,true,true,true,true],[true,true,true,true,true,true,true,true,true],[true,true,true,true,true,true,true,true,true]]', 'easy', 246, 1, 0, 3, 1, 0, '2025-11-28 13:13:04', '2025-12-02 11:01:35'),
(88, 17, '[[9,4,2,8,7,5,1,3,6],[7,6,1,9,2,3,8,5,4],[3,8,5,1,6,4,2,9,7],[1,3,4,6,9,8,7,2,5],[2,5,8,4,3,7,9,6,1],[6,7,9,2,5,1,4,8,3],[8,2,7,3,4,6,5,1,9],[4,1,3,5,8,9,6,7,2],[5,9,6,7,1,2,3,4,8]]', '[[9,4,2,8,7,5,1,3,6],[7,6,1,9,2,3,8,5,4],[3,8,5,1,6,4,2,9,7],[1,3,4,6,9,8,7,2,5],[2,5,8,4,3,7,9,6,1],[6,7,9,2,5,1,4,8,3],[8,2,7,3,4,6,5,1,9],[4,1,3,5,8,9,6,7,2],[5,9,6,7,1,2,3,4,8]]', '[[true,true,true,true,true,true,true,true,true],[true,true,true,true,true,true,true,true,true],[true,true,true,true,true,true,true,true,true],[true,true,true,true,true,true,true,true,true],[true,true,true,true,true,true,true,true,true],[true,true,true,true,true,true,true,true,true],[true,true,true,true,true,true,true,true,true],[true,true,true,true,true,true,true,true,true],[true,true,true,true,true,true,true,true,true]]', 'easy', 372, 1, 0, 3, 1, 0, '2025-11-28 14:19:55', '2025-11-30 20:34:27'),
(5955, 18, '[[2,9,4,1,5,7,8,3,6],[1,3,5,6,4,8,7,9,2],[8,6,7,9,3,2,1,4,5],[9,4,1,2,6,5,3,7,8],[6,5,3,8,7,9,4,2,1],[7,2,8,3,1,4,5,6,9],[4,8,6,7,9,1,2,5,3],[3,7,2,5,8,6,9,1,4],[5,1,9,4,2,3,6,8,7]]', '[[2,9,4,1,5,7,8,3,6],[1,3,5,6,4,8,7,9,2],[8,6,7,9,3,2,1,4,5],[9,4,1,2,6,5,3,7,8],[6,5,3,8,7,9,4,2,1],[7,2,8,3,1,4,5,6,9],[4,8,6,7,9,1,2,5,3],[3,7,2,5,8,6,9,1,4],[5,1,9,4,2,3,6,8,7]]', '[[true,true,true,true,true,true,true,true,true],[true,true,true,true,true,true,true,true,true],[true,true,true,true,true,true,true,true,true],[true,true,true,true,true,true,true,true,true],[true,true,true,true,true,true,true,true,true],[true,true,true,true,true,true,true,true,true],[true,true,true,true,true,true,true,true,true],[true,true,true,true,true,true,true,true,true],[true,true,true,true,true,true,true,true,true]]', 'easy', 278, 0, 0, 3, 1, 0, '2025-11-29 07:50:58', '2025-11-30 07:06:07'),
(7297, 14, '[[3,9,6,1,8,4,7,2,5],[5,2,1,7,6,9,4,8,3],[7,4,8,2,3,5,6,1,9],[4,6,2,8,5,3,1,9,7],[1,8,5,9,4,7,3,6,2],[9,3,7,6,2,1,8,5,4],[2,7,3,5,1,8,9,4,6],[8,5,9,4,7,6,2,3,1],[6,1,4,3,9,2,5,7,8]]', '[[3,9,6,1,8,4,7,2,5],[5,2,1,7,6,9,4,8,3],[7,4,8,2,3,5,6,1,9],[4,6,2,8,5,3,1,9,7],[1,8,5,9,4,7,3,6,2],[9,3,7,6,2,1,8,5,4],[2,7,3,5,1,8,9,4,6],[8,5,9,4,7,6,2,3,1],[6,1,4,3,9,2,5,7,8]]', '[[true,true,true,true,true,true,true,true,true],[true,true,true,true,true,true,true,true,true],[true,true,true,true,true,true,true,true,true],[true,true,true,true,true,true,true,true,true],[true,true,true,true,true,true,true,true,true],[true,true,true,true,true,true,true,true,true],[true,true,true,true,true,true,true,true,true],[true,true,true,true,true,true,true,true,true],[true,true,true,true,true,true,true,true,true]]', 'easy', 367, 1, 0, 3, 1, 0, '2025-11-29 08:44:05', '2025-11-29 18:41:58');

-- --------------------------------------------------------

--
-- Структура таблицы `user_stats`
--

CREATE TABLE `user_stats` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_games` int(11) DEFAULT '0',
  `games_won` int(11) DEFAULT '0',
  `rating` int(11) NOT NULL DEFAULT '0',
  `best_time_easy` int(11) DEFAULT NULL,
  `best_time_medium` int(11) DEFAULT NULL,
  `best_time_hard` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `total_points` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `user_stats`
--

INSERT INTO `user_stats` (`id`, `user_id`, `total_games`, `games_won`, `rating`, `best_time_easy`, `best_time_medium`, `best_time_hard`, `created_at`, `updated_at`, `total_points`) VALUES
(289, 14, 211, 90, 6897, 92, 1114, NULL, '2025-09-23 09:13:06', '2025-12-02 07:00:01', 32106),
(306, 17, 127, 73, 10915, 26, 592, NULL, '2025-09-26 11:31:30', '2025-12-02 17:00:01', 22215),
(507, 18, 236, 176, 21556, 159, 530, NULL, '2025-10-24 13:56:14', '2025-12-02 05:00:01', 32656),
(516, 19, 1, 1, 12, 526, NULL, NULL, '2025-10-30 08:28:11', '2025-10-30 08:36:58', 12),
(517, 20, 1, 1, 12, 307, NULL, NULL, '2025-10-30 08:38:39', '2025-11-05 12:12:02', 12),
(518, 21, 1, 1, 12, 373, NULL, NULL, '2025-10-30 08:45:13', '2025-11-05 15:36:17', 12),
(519, 22, 1, 1, 23, 287, NULL, NULL, '2025-10-30 08:56:41', '2025-10-30 09:01:29', 23),
(520, 23, 1, 1, 12, 448, NULL, NULL, '2025-10-30 09:02:55', '2025-10-30 09:10:24', 12),
(521, 24, 1, 1, 23, 238, NULL, NULL, '2025-10-30 09:12:01', '2025-10-30 09:15:59', 23),
(522, 25, 5, 2, 45, 269, NULL, NULL, '2025-10-30 09:17:46', '2025-11-08 16:11:16', 45),
(526, 26, 7, 4, 52, 280, NULL, NULL, '2025-10-30 09:40:56', '2025-11-08 10:15:52', 52),
(527, 27, 1, 1, 30, 244, NULL, NULL, '2025-10-30 09:49:13', '2025-10-30 09:53:18', 30),
(528, 28, 2, 1, 30, 296, NULL, NULL, '2025-10-30 09:54:48', '2025-10-30 10:05:33', 30),
(530, 29, 1, 1, 12, 431, NULL, NULL, '2025-10-30 10:16:31', '2025-10-30 10:23:43', 12),
(531, 30, 2, 2, 40, 284, NULL, NULL, '2025-10-30 10:24:34', '2025-10-30 11:34:35', 40),
(532, 31, 1, 1, 19, 332, NULL, NULL, '2025-10-30 10:30:06', '2025-10-30 10:35:39', 19),
(533, 32, 1, 1, 12, 395, NULL, NULL, '2025-10-30 10:37:03', '2025-10-30 10:43:40', 12),
(534, 33, 2, 1, 12, 578, NULL, NULL, '2025-10-30 10:44:36', '2025-10-30 11:04:26', 12),
(536, 34, 4, 2, 37, 293, NULL, NULL, '2025-10-30 11:05:55', '2025-11-08 15:59:21', 37),
(537, 35, 6, 5, 64, 287, 636, NULL, '2025-10-30 11:17:23', '2025-11-07 19:35:18', 64),
(538, 36, 1, 1, 12, 558, NULL, NULL, '2025-10-30 11:37:35', '2025-10-30 12:07:37', 12),
(539, 37, 1, 1, 10, 588, NULL, NULL, '2025-10-30 12:09:33', '2025-10-30 12:19:22', 10),
(540, 38, 20, 16, 191, 237, NULL, NULL, '2025-10-30 19:21:13', '2025-11-07 14:53:46', 191),
(541, 39, 1, 1, 12, 307, NULL, NULL, '2025-10-31 06:08:07', '2025-10-31 06:13:16', 12),
(542, 40, 1, 1, 12, 380, NULL, NULL, '2025-10-31 06:22:34', '2025-10-31 06:28:55', 12),
(543, 41, 1, 1, 12, 467, NULL, NULL, '2025-10-31 06:30:44', '2025-10-31 06:38:33', 12),
(544, 42, 3, 2, 38, 293, NULL, NULL, '2025-10-31 06:48:46', '2025-11-06 08:23:39', 38),
(545, 43, 1, 1, 10, 375, NULL, NULL, '2025-10-31 07:01:03', '2025-10-31 07:07:19', 10),
(546, 44, 2, 2, 26, 459, NULL, NULL, '2025-10-31 07:12:16', '2025-11-07 07:01:14', 26),
(547, 45, 5, 4, 55, 219, NULL, NULL, '2025-10-31 07:44:37', '2025-11-07 14:38:06', 55),
(548, 46, 3, 2, 17, 381, NULL, NULL, '2025-10-31 07:54:37', '2025-11-05 15:00:43', 17),
(549, 47, 4, 4, 51, 263, NULL, NULL, '2025-10-31 08:41:57', '2025-11-08 17:04:46', 51),
(550, 48, 1, 1, 12, 490, NULL, NULL, '2025-10-31 09:05:46', '2025-10-31 09:13:57', 12),
(551, 49, 3, 2, 26, 310, NULL, NULL, '2025-10-31 09:25:32', '2025-10-31 10:03:00', 26),
(553, 50, 5, 4, 53, 240, NULL, NULL, '2025-10-31 10:06:29', '2025-11-08 16:44:56', 53),
(554, 51, 1, 1, 19, 312, NULL, NULL, '2025-10-31 10:42:10', '2025-10-31 10:47:23', 19),
(555, 52, 11, 4, 40, 369, NULL, NULL, '2025-10-31 11:07:39', '2025-11-05 10:24:51', 40),
(594, 54, 2, 1, 12, 505, NULL, NULL, '2025-11-07 10:42:54', '2025-11-08 19:26:28', 12),
(595, 55, 5, 4, 60, 237, NULL, NULL, '2025-11-07 10:51:24', '2025-11-08 19:14:02', 60),
(596, 56, 8, 7, 55, 318, NULL, NULL, '2025-11-07 11:18:56', '2025-11-07 17:11:53', 55),
(597, 57, 2, 2, 31, 278, NULL, NULL, '2025-11-07 11:26:39', '2025-11-07 15:18:03', 31),
(603, 65, 80, 65, 9670, 208, NULL, NULL, '2025-11-18 20:40:40', '2025-12-02 17:00:01', 13270);

-- --------------------------------------------------------

--
-- Структура таблицы `user_tournaments`
--

CREATE TABLE `user_tournaments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tournament_id` int(11) NOT NULL,
  `entry_fee` int(11) DEFAULT '0',
  `registered_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `results_seen` tinyint(4) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `game_sessions`
--
ALTER TABLE `game_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `tournament_id` (`tournament_id`);

--
-- Индексы таблицы `payment_history`
--
ALTER TABLE `payment_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `pending_registrations`
--
ALTER TABLE `pending_registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `confirmation_code` (`confirmation_code`),
  ADD KEY `idx_confirmation_code` (`confirmation_code`),
  ADD KEY `idx_expires_at` (`expires_at`),
  ADD KEY `idx_email` (`email`);

--
-- Индексы таблицы `saved_games`
--
ALTER TABLE `saved_games`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `tournaments`
--
ALTER TABLE `tournaments`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `tournament_games`
--
ALTER TABLE `tournament_games`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tournament_id` (`tournament_id`),
  ADD KEY `player1_id` (`player1_id`),
  ADD KEY `player2_id` (`player2_id`);

--
-- Индексы таблицы `tournament_game_stats`
--
ALTER TABLE `tournament_game_stats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_game_record` (`tournament_id`,`user_id`,`game_id`),
  ADD KEY `tournament_user` (`tournament_id`,`user_id`),
  ADD KEY `idx_played_at` (`played_at`),
  ADD KEY `fk_tournament_game_user` (`user_id`),
  ADD KEY `idx_tournament_user_time` (`tournament_id`,`user_id`,`time_seconds`);

--
-- Индексы таблицы `tournament_registrations`
--
ALTER TABLE `tournament_registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_registration` (`tournament_id`,`user_id`),
  ADD UNIQUE KEY `unique_tournament_user` (`tournament_id`,`user_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_tournament_status` (`tournament_id`,`status`);

--
-- Индексы таблицы `tournament_results`
--
ALTER TABLE `tournament_results`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_tournament_user` (`tournament_id`,`user_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_tournament_id` (`tournament_id`),
  ADD KEY `idx_position` (`position`),
  ADD KEY `idx_completed_at` (`completed_at`);

--
-- Индексы таблицы `tournament_seen`
--
ALTER TABLE `tournament_seen`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_seen` (`user_id`,`tournament_id`),
  ADD KEY `tournament_id` (`tournament_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Индексы таблицы `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_achievement` (`user_id`,`achievement_id`);

--
-- Индексы таблицы `user_games`
--
ALTER TABLE `user_games`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user` (`user_id`);

--
-- Индексы таблицы `user_stats`
--
ALTER TABLE `user_stats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user` (`user_id`);

--
-- Индексы таблицы `user_tournaments`
--
ALTER TABLE `user_tournaments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_registration` (`user_id`,`tournament_id`),
  ADD KEY `tournament_id` (`tournament_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `game_sessions`
--
ALTER TABLE `game_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `payment_history`
--
ALTER TABLE `payment_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=385;

--
-- AUTO_INCREMENT для таблицы `pending_registrations`
--
ALTER TABLE `pending_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT для таблицы `saved_games`
--
ALTER TABLE `saved_games`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `tournaments`
--
ALTER TABLE `tournaments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=214;

--
-- AUTO_INCREMENT для таблицы `tournament_games`
--
ALTER TABLE `tournament_games`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `tournament_game_stats`
--
ALTER TABLE `tournament_game_stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `tournament_registrations`
--
ALTER TABLE `tournament_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=410;

--
-- AUTO_INCREMENT для таблицы `tournament_results`
--
ALTER TABLE `tournament_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT для таблицы `tournament_seen`
--
ALTER TABLE `tournament_seen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=251;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT для таблицы `user_achievements`
--
ALTER TABLE `user_achievements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5699;

--
-- AUTO_INCREMENT для таблицы `user_games`
--
ALTER TABLE `user_games`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21281;

--
-- AUTO_INCREMENT для таблицы `user_stats`
--
ALTER TABLE `user_stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=709;

--
-- AUTO_INCREMENT для таблицы `user_tournaments`
--
ALTER TABLE `user_tournaments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `game_sessions`
--
ALTER TABLE `game_sessions`
  ADD CONSTRAINT `game_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `game_sessions_ibfk_2` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`);

--
-- Ограничения внешнего ключа таблицы `payment_history`
--
ALTER TABLE `payment_history`
  ADD CONSTRAINT `payment_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `saved_games`
--
ALTER TABLE `saved_games`
  ADD CONSTRAINT `saved_games_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `tournament_games`
--
ALTER TABLE `tournament_games`
  ADD CONSTRAINT `tournament_games_ibfk_1` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tournament_games_ibfk_2` FOREIGN KEY (`player1_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tournament_games_ibfk_3` FOREIGN KEY (`player2_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `tournament_game_stats`
--
ALTER TABLE `tournament_game_stats`
  ADD CONSTRAINT `fk_tournament_game_tournament` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tournament_game_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `tournament_registrations`
--
ALTER TABLE `tournament_registrations`
  ADD CONSTRAINT `tournament_registrations_ibfk_1` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tournament_registrations_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `tournament_results`
--
ALTER TABLE `tournament_results`
  ADD CONSTRAINT `fk_tournament_results_tournaments` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tournament_results_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `tournament_seen`
--
ALTER TABLE `tournament_seen`
  ADD CONSTRAINT `tournament_seen_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tournament_seen_ibfk_2` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD CONSTRAINT `user_achievements_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `user_games`
--
ALTER TABLE `user_games`
  ADD CONSTRAINT `user_games_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ограничения внешнего ключа таблицы `user_stats`
--
ALTER TABLE `user_stats`
  ADD CONSTRAINT `user_stats_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `user_tournaments`
--
ALTER TABLE `user_tournaments`
  ADD CONSTRAINT `user_tournaments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_tournaments_ibfk_2` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
