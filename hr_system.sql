-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 26, 2026 at 05:10 AM
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
-- Database: `hr_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(4, '2026_05_26_000000_add_role_to_users_table', 1);

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
('e90yqJdSBcKYhqt3f8SdDbR9zupyB0k9d4Z4LMOX', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.121.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoianFIVGhuVU9mZDJGQWJYRkxLUlhxN0huc2JLMnVYWURoMG10NUlMZiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1779763329),
('LJs79dKAXOIMGuEHtvaklWNEYSxxz5RKpYnofFrU', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVVljdVozYXUxRGx0YWg4NW5YaEU3bXJZOGJ2bEM0cFJjZ25vY0NKbiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fX0=', 1779764974);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` enum('admin','hr','employee') NOT NULL DEFAULT 'employee',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `role`, `is_active`, `last_login_at`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', 'admin@company.com', 'admin', 1, '2026-05-25 18:44:07', '2026-05-25 18:39:14', '$2y$12$ZlbLqOkMqp8f0JvHkrP6VORGY.Ws7FyyW6/I4CNgRka/PfXL/vWeW', 'vgkcOksQU9Qg5j0prBFX9HCi2IyAaw8ngJeNmyWzDkSGqmrQPtXmRngT1KjZ', '2026-05-25 18:39:14', '2026-05-25 18:44:07'),
(2, 'HR Manager', 'hr@company.com', 'hr', 1, '2026-05-25 18:45:29', '2026-05-25 18:39:14', '$2y$12$ZlbLqOkMqp8f0JvHkrP6VORGY.Ws7FyyW6/I4CNgRka/PfXL/vWeW', 'mBd6pyeWBnMrN0fhlkkvhEFnt86LPCwjeyLQCXESIRySGIxYuB3kIzJI5wxv', '2026-05-25 18:39:14', '2026-05-25 18:45:29'),
(3, 'HR Specialist', 'hrspecialist@company.com', 'hr', 1, NULL, '2026-05-25 18:39:14', '$2y$12$ZlbLqOkMqp8f0JvHkrP6VORGY.Ws7FyyW6/I4CNgRka/PfXL/vWeW', 'd279BObJpp', '2026-05-25 18:39:14', '2026-05-25 18:39:14'),
(4, 'John Doe', 'john@company.com', 'employee', 1, NULL, '2026-05-25 18:39:14', '$2y$12$ZlbLqOkMqp8f0JvHkrP6VORGY.Ws7FyyW6/I4CNgRka/PfXL/vWeW', 'qN122otl6I', '2026-05-25 18:39:14', '2026-05-25 18:39:14'),
(5, 'Jane Smith', 'jane@company.com', 'employee', 1, NULL, '2026-05-25 18:39:14', '$2y$12$ZlbLqOkMqp8f0JvHkrP6VORGY.Ws7FyyW6/I4CNgRka/PfXL/vWeW', 'IdiOLzNCKC', '2026-05-25 18:39:14', '2026-05-25 18:39:14'),
(6, 'Mr. Jedidiah Fadel', 'taya60@example.org', 'employee', 1, NULL, '2026-05-25 18:39:14', '$2y$12$ZlbLqOkMqp8f0JvHkrP6VORGY.Ws7FyyW6/I4CNgRka/PfXL/vWeW', 'rerHgvTaDB', '2026-05-25 18:39:14', '2026-05-25 18:39:14'),
(7, 'Dr. Macey Morar', 'graham.nicklaus@example.com', 'employee', 1, NULL, '2026-05-25 18:39:14', '$2y$12$ZlbLqOkMqp8f0JvHkrP6VORGY.Ws7FyyW6/I4CNgRka/PfXL/vWeW', 'BnUQDevcQh', '2026-05-25 18:39:14', '2026-05-25 18:39:14'),
(8, 'Miss Alice Schimmel', 'pollich.sigrid@example.com', 'employee', 1, NULL, '2026-05-25 18:39:14', '$2y$12$ZlbLqOkMqp8f0JvHkrP6VORGY.Ws7FyyW6/I4CNgRka/PfXL/vWeW', 'Wn7EnNTbrR', '2026-05-25 18:39:14', '2026-05-25 18:39:14'),
(9, 'Deontae Baumbach', 'keeling.cordia@example.org', 'employee', 1, NULL, '2026-05-25 18:39:14', '$2y$12$ZlbLqOkMqp8f0JvHkrP6VORGY.Ws7FyyW6/I4CNgRka/PfXL/vWeW', '3VoR8B2Ily', '2026-05-25 18:39:14', '2026-05-25 18:39:14'),
(10, 'Prof. Emmalee Yost PhD', 'brook07@example.com', 'employee', 1, NULL, '2026-05-25 18:39:14', '$2y$12$ZlbLqOkMqp8f0JvHkrP6VORGY.Ws7FyyW6/I4CNgRka/PfXL/vWeW', '1H5gaDkZd1', '2026-05-25 18:39:14', '2026-05-25 18:39:14'),
(11, 'Shana Waters', 'golda.abshire@example.net', 'employee', 1, NULL, '2026-05-25 18:39:14', '$2y$12$ZlbLqOkMqp8f0JvHkrP6VORGY.Ws7FyyW6/I4CNgRka/PfXL/vWeW', 'UTmL8f2PzH', '2026-05-25 18:39:14', '2026-05-25 18:39:14'),
(12, 'Miss Nellie Mayer', 'jschamberger@example.com', 'employee', 1, NULL, '2026-05-25 18:39:14', '$2y$12$ZlbLqOkMqp8f0JvHkrP6VORGY.Ws7FyyW6/I4CNgRka/PfXL/vWeW', '7c7sq4VedZ', '2026-05-25 18:39:14', '2026-05-25 18:39:14'),
(13, 'Dr. Terry Schuster', 'rfranecki@example.net', 'employee', 1, NULL, '2026-05-25 18:39:14', '$2y$12$ZlbLqOkMqp8f0JvHkrP6VORGY.Ws7FyyW6/I4CNgRka/PfXL/vWeW', 'J5c7eCcwzX', '2026-05-25 18:39:14', '2026-05-25 18:39:14'),
(14, 'Valentin Mante', 'pokon@example.net', 'employee', 1, NULL, '2026-05-25 18:39:14', '$2y$12$ZlbLqOkMqp8f0JvHkrP6VORGY.Ws7FyyW6/I4CNgRka/PfXL/vWeW', 'cF5mqlJceK', '2026-05-25 18:39:14', '2026-05-25 18:39:14'),
(15, 'Georgianna Boyer', 'fboehm@example.org', 'employee', 1, NULL, '2026-05-25 18:39:14', '$2y$12$ZlbLqOkMqp8f0JvHkrP6VORGY.Ws7FyyW6/I4CNgRka/PfXL/vWeW', 'P9GYChNWc6', '2026-05-25 18:39:14', '2026-05-25 18:39:14'),
(16, 'Stevie Jast', 'jack.kshlerin@example.net', 'employee', 1, NULL, '2026-05-25 18:39:14', '$2y$12$ZlbLqOkMqp8f0JvHkrP6VORGY.Ws7FyyW6/I4CNgRka/PfXL/vWeW', 'p5ROsXBqBg', '2026-05-25 18:39:14', '2026-05-25 18:39:14'),
(17, 'Gus Mayer', 'emmy57@example.net', 'employee', 1, NULL, '2026-05-25 18:39:14', '$2y$12$ZlbLqOkMqp8f0JvHkrP6VORGY.Ws7FyyW6/I4CNgRka/PfXL/vWeW', 'CVUUidLSYp', '2026-05-25 18:39:14', '2026-05-25 18:39:14'),
(18, 'Theresa Lesch', 'lmonahan@example.org', 'employee', 1, NULL, '2026-05-25 18:39:14', '$2y$12$ZlbLqOkMqp8f0JvHkrP6VORGY.Ws7FyyW6/I4CNgRka/PfXL/vWeW', '6f4zSOrQAQ', '2026-05-25 18:39:14', '2026-05-25 18:39:14'),
(19, 'Helen McKenzie', 'reid.bernhard@example.net', 'employee', 1, NULL, '2026-05-25 18:39:14', '$2y$12$ZlbLqOkMqp8f0JvHkrP6VORGY.Ws7FyyW6/I4CNgRka/PfXL/vWeW', '3aaofu5uKt', '2026-05-25 18:39:14', '2026-05-25 18:39:14'),
(20, 'Oma Kunze', 'amurray@example.com', 'employee', 1, NULL, '2026-05-25 18:39:14', '$2y$12$ZlbLqOkMqp8f0JvHkrP6VORGY.Ws7FyyW6/I4CNgRka/PfXL/vWeW', 'p5QjTmxjai', '2026-05-25 18:39:14', '2026-05-25 18:39:14'),
(21, 'Raymond Hintz', 'elian.moore@example.org', 'employee', 1, NULL, '2026-05-25 18:39:14', '$2y$12$ZlbLqOkMqp8f0JvHkrP6VORGY.Ws7FyyW6/I4CNgRka/PfXL/vWeW', '2TOQpni4th', '2026-05-25 18:39:14', '2026-05-25 18:39:14'),
(22, 'Edgar Schaefer', 'ikoss@example.net', 'employee', 1, NULL, '2026-05-25 18:39:14', '$2y$12$ZlbLqOkMqp8f0JvHkrP6VORGY.Ws7FyyW6/I4CNgRka/PfXL/vWeW', 'zrvRjGEzcb', '2026-05-25 18:39:14', '2026-05-25 18:39:14'),
(23, 'Maryse Walter', 'delaney50@example.org', 'employee', 1, NULL, '2026-05-25 18:39:14', '$2y$12$ZlbLqOkMqp8f0JvHkrP6VORGY.Ws7FyyW6/I4CNgRka/PfXL/vWeW', '6RUbHsQzVj', '2026-05-25 18:39:14', '2026-05-25 18:39:14'),
(24, 'Sedrick Hintz', 'damore.darrin@example.net', 'employee', 1, NULL, '2026-05-25 18:39:14', '$2y$12$ZlbLqOkMqp8f0JvHkrP6VORGY.Ws7FyyW6/I4CNgRka/PfXL/vWeW', 'dDg8ycUTTD', '2026-05-25 18:39:14', '2026-05-25 18:39:14'),
(25, 'Seth Streich', 'gladys.okuneva@example.com', 'employee', 1, NULL, '2026-05-25 18:39:14', '$2y$12$ZlbLqOkMqp8f0JvHkrP6VORGY.Ws7FyyW6/I4CNgRka/PfXL/vWeW', 'WkH28ekQcY', '2026-05-25 18:39:14', '2026-05-25 18:39:14'),
(26, 'Inactive User', 'inactive@company.com', 'employee', 0, NULL, '2026-05-25 18:39:14', '$2y$12$ZlbLqOkMqp8f0JvHkrP6VORGY.Ws7FyyW6/I4CNgRka/PfXL/vWeW', 'VLWtmf1YsD', '2026-05-25 18:39:14', '2026-05-25 18:39:14'),
(27, 'vincent', 'vincent@gmail.com', 'employee', 1, NULL, NULL, '$2y$12$bVLb.1LuNVAo16Szh3YISOYGV.WuM8UADRngtt8K2oqazO7PIy9QW', NULL, '2026-05-25 18:54:17', '2026-05-25 18:54:17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

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
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
