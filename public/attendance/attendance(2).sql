-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 03, 2025 at 06:32 AM
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
-- Database: `attendance`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendances`
--

CREATE TABLE `attendances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `enroll_id` varchar(255) NOT NULL,
  `punch_time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `check_in_time` timestamp NULL DEFAULT NULL,
  `check_out_time` timestamp NULL DEFAULT NULL,
  `attendance_date` date DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `verify_mode` varchar(255) DEFAULT NULL,
  `device_ip` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendances`
--

INSERT INTO `attendances` (`id`, `user_id`, `enroll_id`, `punch_time`, `check_in_time`, `check_out_time`, `attendance_date`, `status`, `verify_mode`, `device_ip`, `created_at`, `updated_at`) VALUES
(47, 92, '1', '2025-12-01 23:32:16', '2025-12-01 23:31:49', '2025-12-01 23:32:16', '2025-12-01', '0', 'Fingerprint', '192.168.100.108', '2025-12-01 20:31:49', '2025-12-01 20:32:15'),
(48, 95, '7890', '2025-12-01 23:44:14', '2025-12-01 23:43:55', '2025-12-01 23:44:14', '2025-12-01', '0', 'Fingerprint', '192.168.100.108', '2025-12-01 20:43:54', '2025-12-01 20:44:11'),
(49, 94, '35', '2025-12-01 23:59:31', '2025-12-01 23:51:04', '2025-12-01 23:59:31', '2025-12-01', '0', 'Fingerprint', '192.168.100.108', '2025-12-02 14:00:59', '2025-12-02 14:01:00'),
(50, 92, '1', '2025-12-02 16:56:12', '2025-12-02 16:52:17', '2025-12-02 16:56:12', '2025-12-02', '0', 'Fingerprint', '192.168.100.108', '2025-12-02 14:01:00', '2025-12-02 14:01:00'),
(51, 95, '7890', '2025-12-02 16:57:56', '2025-12-02 16:52:25', '2025-12-02 16:57:56', '2025-12-02', '0', 'Fingerprint', '192.168.100.108', '2025-12-02 14:01:00', '2025-12-02 14:01:00'),
(52, 98, '40', '2025-12-02 16:57:43', '2025-12-02 16:54:48', '2025-12-02 16:57:43', '2025-12-02', '0', 'Fingerprint', '192.168.100.108', '2025-12-02 14:01:00', '2025-12-02 14:01:00'),
(53, 100, '5572', '2025-12-02 17:02:56', '2025-12-02 17:01:29', '2025-12-02 17:02:56', '2025-12-02', '0', 'Fingerprint', '192.168.100.108', '2025-12-02 14:01:24', '2025-12-02 14:02:58'),
(54, 102, '36', '2025-12-02 17:23:02', '2025-12-02 17:21:51', '2025-12-02 17:23:02', '2025-12-02', '0', 'Fingerprint', '192.168.100.108', '2025-12-02 14:22:26', '2025-12-02 14:46:59'),
(55, 104, '8837', '2025-12-02 18:41:46', '2025-12-02 17:46:42', '2025-12-02 18:41:46', '2025-12-02', '0', 'Fingerprint', '192.168.100.108', '2025-12-02 14:46:59', '2025-12-02 15:41:45'),
(56, 105, '2851', '2025-12-02 18:43:04', '2025-12-02 17:50:07', '2025-12-02 18:43:04', '2025-12-02', '0', 'Fingerprint', '192.168.100.108', '2025-12-02 14:50:26', '2025-12-02 15:43:00'),
(57, 106, '6826', '2025-12-02 18:40:49', '2025-12-02 18:39:17', '2025-12-02 18:40:49', '2025-12-02', '0', 'Fingerprint', '192.168.100.108', '2025-12-02 15:40:16', '2025-12-02 15:41:13'),
(58, 101, '5773', '2025-12-02 19:09:01', '2025-12-02 18:41:54', '2025-12-02 19:09:01', '2025-12-02', '0', 'Fingerprint', '192.168.100.108', '2025-12-02 15:41:57', '2025-12-02 16:09:03'),
(59, 107, '14', '2025-12-02 21:25:21', '2025-12-02 21:06:23', '2025-12-02 21:25:21', '2025-12-02', '0', 'Fingerprint', '192.168.100.108', '2025-12-02 18:06:59', '2025-12-02 19:22:50'),
(60, 108, '15', '2025-12-02 11:30:08', '2025-12-02 22:29:59', NULL, '2025-12-02', '1', 'Fingerprint', '192.168.100.108', '2025-12-02 19:30:08', '2025-12-02 19:30:08'),
(61, 109, '3383', '2025-12-02 11:37:20', '2025-12-02 22:36:39', NULL, '2025-12-02', '1', 'Fingerprint', '192.168.100.108', '2025-12-02 19:37:20', '2025-12-02 19:37:20'),
(62, 110, '3514', '2025-12-02 11:42:20', '2025-12-02 22:42:08', NULL, '2025-12-02', '1', 'Fingerprint', '192.168.100.108', '2025-12-02 19:42:20', '2025-12-02 19:42:20'),
(63, 111, '16', '2025-12-02 11:53:41', '2025-12-02 22:53:41', NULL, '2025-12-02', '1', 'Fingerprint', '192.168.100.108', '2025-12-02 19:53:41', '2025-12-02 19:53:41'),
(64, 112, '8571', '2025-12-02 23:17:00', '2025-12-02 23:13:06', '2025-12-02 23:17:00', '2025-12-02', '0', 'Fingerprint', '192.168.100.108', '2025-12-02 20:13:02', '2025-12-02 20:17:07'),
(65, 113, '17', '2025-12-02 23:26:41', '2025-12-02 23:25:20', '2025-12-02 23:26:41', '2025-12-02', '0', 'Fingerprint', '192.168.100.108', '2025-12-02 20:25:15', '2025-12-02 20:27:20'),
(66, 114, '9151', '2025-12-03 00:08:45', '2025-12-03 00:06:56', '2025-12-03 00:08:45', '2025-12-02', '0', 'Fingerprint', '192.168.100.108', '2025-12-02 21:07:54', '2025-12-02 21:08:48');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-external_webhook_url', 's:25:\"https://webhook.site/test\";', 2079938462),
('laravel-cache-webhook_minimal_payload', 'b:1;', 2079938462);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_11_21_145238_create_attendances_table', 1),
(5, '2025_11_21_145257_add_device_fields_to_users_table', 1),
(6, '2025_11_28_073807_add_check_in_out_times_to_attendances_table', 2);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('WBbLJVSlWD9dswsQ9ZedHbg46qYAuBKLWrslov9R', NULL, '192.168.100.105', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiU0FJREduUmYxMjJKTndCdGFZajQwdmxsVHo4bk1lbEJWdThWTk5YYyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzk6Imh0dHA6Ly8xOTIuMTY4LjEwMC4xMDA6ODAwMC9hdHRlbmRhbmNlcyI7czo1OiJyb3V0ZSI7czoxNzoiYXR0ZW5kYW5jZXMuaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1764691721),
('wyu9VVxeDphjnCcLWh6yVNkwaAOgSvpIqLHkjTy4', NULL, '192.168.100.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiOGhkOFpkdm5wYW12bE1iZEY3RmdyNTJGVWdXajY2S3hDRUNzU3hLQSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1764699233);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `enroll_id` varchar(255) DEFAULT NULL,
  `registered_on_device` tinyint(1) NOT NULL DEFAULT 0,
  `device_registered_at` timestamp NULL DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `enroll_id`, `registered_on_device`, `device_registered_at`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(92, '1', 1, '2025-12-01 20:25:54', 'Often', 'user1@device.local', NULL, '$2y$12$CrL/DUrEL1bC.SG.dmHV6eInp1x8PFWhxBRDXVG.kRt9Fd1ar0ifi', NULL, '2025-12-01 20:25:54', '2025-12-01 20:25:54'),
(93, '4546', 0, NULL, 'SHOKO', 'user_4546@attendance.local', NULL, '$2y$12$icybz09ug31Z1sIwSse3vuV5y5NCN2slSn33sAdFNEwxNhL1aOxiS', NULL, '2025-12-01 20:28:19', '2025-12-01 20:28:19'),
(94, '35', 1, '2025-12-01 20:38:38', 'Neema', 'user_35@attendance.local', NULL, '$2y$12$tMgl09s52IHmE0UTKCg4N.LTiXG5IbQw6.cBB1YD5WZkSOF28E9pm', NULL, '2025-12-01 20:30:15', '2025-12-01 20:38:38'),
(95, '7890', 1, '2025-12-01 20:42:48', 'MAJUTO', 'user_7890@attendance.local', NULL, '$2y$12$gfsgnDyFRoNJzFtWmYjY0eYHrnssR/2I7CLPjN85xM6QwcZzt4xI2', NULL, '2025-12-01 20:39:51', '2025-12-01 20:42:48'),
(96, '43', 0, NULL, 'Ofeni', 'user_43@attendance.local', NULL, '$2y$12$.rhqa5kG/L1oa3pCihBIUO.rsxeF6fZrB7F47Jha3lWPZ2im3cUKu', NULL, '2025-12-01 20:51:35', '2025-12-01 20:51:35'),
(97, '37', 1, '2025-12-01 22:50:25', 'Emmanuel', 'user_37@attendance.local', NULL, '$2y$12$SueC1zQi9GfhqX.9L3I8euADwBUBt3uuYLuEOeMvMMlWAOhIWLQ7S', NULL, '2025-12-01 22:47:06', '2025-12-01 22:50:25'),
(98, '40', 1, '2025-12-02 13:53:33', 'David', 'user_40@attendance.local', NULL, '$2y$12$8ucZNKMBmaYmCpQ39i2Z7O6qrVjl5Hge6XwPpLBvoDbj2GbgOO0zC', NULL, '2025-12-02 13:53:29', '2025-12-02 13:53:33'),
(99, '38', 1, '2025-12-02 13:58:49', 'Paul', 'user_38@attendance.local', NULL, '$2y$12$ZCnYr/rNFwd2KTqNkNk68.5OrpQrUlKE.L7zdC.hbF5Mj1QveDZ4K', NULL, '2025-12-02 13:58:46', '2025-12-02 13:58:49'),
(100, '5572', 1, '2025-12-02 13:59:54', 'KIDIWA', 'user_5572@attendance.local', NULL, '$2y$12$vgXLz2dqA1AQqAi1Oofy1O5OdDxxPFuHIYvrLizSZAkBmpxixDAvW', NULL, '2025-12-02 13:59:51', '2025-12-02 13:59:54'),
(101, '5773', 1, '2025-12-02 14:18:25', 'KAMARA', 'user_5773@attendance.local', NULL, '$2y$12$qIavAIE4jv0MdvyzMeP5DOF419aeGi/8C5gqQc0oOjgYxwArnxf6m', NULL, '2025-12-02 14:18:22', '2025-12-02 14:18:25'),
(102, '36', 1, '2025-12-02 14:21:07', 'Caroline', 'user_36@attendance.local', NULL, '$2y$12$G1MDLoypGyXv2EsK2PMeR.LYHtbSXxkEJOy.teLH10EeStxzllCrW', NULL, '2025-12-02 14:21:03', '2025-12-02 14:21:07'),
(103, '5967', 1, '2025-12-02 14:29:05', 'CHIEF', 'user_5967@attendance.local', NULL, '$2y$12$EiwXLU4wkcL0ceXqGa/PLu4vHhzmnPwavIBreBbpo0wLUPSfG11tu', NULL, '2025-12-02 14:29:02', '2025-12-02 14:29:05'),
(104, '8837', 1, '2025-12-02 14:31:41', 'JUMAA', 'user_8837@attendance.local', NULL, '$2y$12$Y1V5Vc67EnKG7iw.SgH8Yu.TASuRMUbwYRQep4sTeFFC4cTa.ke1S', NULL, '2025-12-02 14:31:38', '2025-12-02 14:31:41'),
(105, '2851', 1, '2025-12-02 14:35:03', 'HARUNA', 'user_2851@attendance.local', NULL, '$2y$12$/avH8a08rFaFLuWo9ZlN8e1t9cJMvUd82I4kpAMur4Ffqi8JGAh9u', NULL, '2025-12-02 14:34:59', '2025-12-02 14:35:03'),
(106, '6826', 1, '2025-12-02 15:37:29', 'HASSANI', 'user_6826@attendance.local', NULL, '$2y$12$sO3L7NJfEglHCCQ2VfvFVODoyJtVET/Gox0lcvQiMjVsNz92z2JyG', NULL, '2025-12-02 15:37:26', '2025-12-02 15:37:29'),
(107, '14', 1, '2025-12-02 17:55:52', 'Maria Juma', 'user_14@attendance.local', NULL, '$2y$12$oJUs9DsQPLeOKus7hPMnGe8zBHkyq4JxdU1bIJVrSdEoGYSoXsNoa', NULL, '2025-12-02 17:55:49', '2025-12-02 17:55:52'),
(108, '15', 1, '2025-12-02 19:27:33', 'Elias John', 'user_15@attendance.local', NULL, '$2y$12$G/Ea9X3oQc9BpZ60AKVTLuSUsD2EDdD/..IziIeje2X71T0S53y2O', NULL, '2025-12-02 19:27:30', '2025-12-02 19:27:33'),
(109, '3383', 1, '2025-12-02 19:34:46', 'ABDUL', 'user_3383@attendance.local', NULL, '$2y$12$OE16G8iVzw9qijqPeb/hzOfoGaLOlscTOuqfNvyG9tzqGItFe.266', NULL, '2025-12-02 19:34:42', '2025-12-02 19:34:46'),
(110, '3514', 1, '2025-12-02 19:40:29', 'ZUBERI', 'user_3514@attendance.local', NULL, '$2y$12$PeoCwIAN0rfKZYxJjkkSLuGEauvWBtTHWNkSyzExsTthwfPAFK3lq', NULL, '2025-12-02 19:40:26', '2025-12-02 19:40:29'),
(111, '16', 1, '2025-12-02 19:51:28', 'Faru John', 'user_16@attendance.local', NULL, '$2y$12$53UTy5xNDImY8NyPj.IA3eMEA8JhGksjbUq0wKyd41IM8FavYlA0u', NULL, '2025-12-02 19:51:25', '2025-12-02 19:51:28'),
(112, '8571', 1, '2025-12-02 20:11:05', 'CAROLINE', 'user_8571@attendance.local', NULL, '$2y$12$g7XNmcZI1YBYOXRyiX7ZmuuY/qMu8Skcb6Y0jB2jBFSLtLJXyLJyi', NULL, '2025-12-02 20:11:02', '2025-12-02 20:11:05'),
(113, '17', 1, '2025-12-02 20:22:20', 'Caroline Shija', 'user_17@attendance.local', NULL, '$2y$12$7Uef0Rv29idT9Rhomfljx.RX8ni6QTupLw1V/cKi1mND2TPtlLQfS', NULL, '2025-12-02 20:22:17', '2025-12-02 20:22:20'),
(114, '9151', 1, '2025-12-02 20:59:46', 'KASIM', 'user_9151@attendance.local', NULL, '$2y$12$m1rDbq/lQy.JXUfQ5.y0deAdwf.238vswja2aKy.pwEmARInMVgNC', NULL, '2025-12-02 20:59:43', '2025-12-02 20:59:46'),
(115, '2462', 1, '2025-12-02 22:00:07', 'ABDUL', 'user_2462@attendance.local', NULL, '$2y$12$N.lDs35ECHYwuCLWJHiWV.4UWW6Aevr/573WzUh2Ly5IaUpzEuqGG', NULL, '2025-12-02 22:00:04', '2025-12-02 22:00:07'),
(116, '5178', 1, '2025-12-02 22:16:21', 'HUSAIN', 'user_5178@attendance.local', NULL, '$2y$12$031yTruQh8AxQgzpfWmo8OeZQcARN6CGuvR/3u10Oy37LS4q4Jfxy', NULL, '2025-12-02 22:16:18', '2025-12-02 22:16:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attendances_enroll_id_punch_time_index` (`enroll_id`,`punch_time`),
  ADD KEY `attendances_user_id_punch_time_index` (`user_id`,`punch_time`),
  ADD KEY `attendances_user_id_attendance_date_index` (`user_id`,`attendance_date`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_enroll_id_unique` (`enroll_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=117;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendances`
--
ALTER TABLE `attendances`
  ADD CONSTRAINT `attendances_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
