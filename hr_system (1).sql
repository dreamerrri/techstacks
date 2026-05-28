-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 28, 2026 at 04:50 AM
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
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `employee_id` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) NOT NULL,
  `birthdate` date NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `civil_status` enum('Single','Married','Widowed','Separated') NOT NULL,
  `address` text NOT NULL,
  `contact_number` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `position` varchar(255) NOT NULL,
  `employment_status` enum('Regular','Probationary','Contractual','Part-time') NOT NULL,
  `date_hired` date NOT NULL,
  `salary_type` enum('Monthly','Daily','Hourly') NOT NULL,
  `basic_salary` decimal(10,2) NOT NULL,
  `sss_number` varchar(255) DEFAULT NULL,
  `philhealth_number` varchar(255) DEFAULT NULL,
  `pagibig_number` varchar(255) DEFAULT NULL,
  `tin_number` varchar(255) DEFAULT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `user_id`, `employee_id`, `first_name`, `middle_name`, `last_name`, `birthdate`, `gender`, `civil_status`, `address`, `contact_number`, `email`, `department`, `position`, `employment_status`, `date_hired`, `salary_type`, `basic_salary`, `sss_number`, `philhealth_number`, `pagibig_number`, `tin_number`, `is_archived`, `created_at`, `updated_at`) VALUES
(1, 28, '01-2223-000193', 'Renz', 'Santos', 'Ebreo', '2002-01-15', 'Male', 'Single', 'Brgy. Palomaria, Bongabon, Nueva Ecija', '09362671655', 'andrewrennn@gmail.com', 'IT', 'Intern', 'Contractual', '2026-05-18', 'Monthly', 1232322.99, '01-2223-000193', '01-2223-000193', '01-2223-000193', '01-2223-000193', 0, '2026-05-25 21:38:48', '2026-05-27 17:42:15'),
(2, 30, '483589345', 'erwerewrprlew', 'ewrwer', 'Official', '2026-05-20', 'Male', 'Single', 'fg', '74543534534', 'andrew.ae215@gmail.com', 'gfg', 'fg', 'Regular', '2026-05-07', 'Daily', 44443434.00, NULL, NULL, NULL, NULL, 1, '2026-05-25 22:51:21', '2026-05-27 17:42:15'),
(3, 29, 'test', 'tjaiofewj', 'powmfewlf', 'ebreo', '0002-03-12', 'Female', 'Single', 'rizal', '09242343432', 'mydonglonglmoa@gmail.com', 'IT', 'ULO', 'Regular', '0002-03-21', 'Monthly', 23232323.00, NULL, NULL, NULL, NULL, 0, '2026-05-25 23:14:41', '2026-05-27 17:42:15');

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
(4, '2026_05_26_000000_add_role_to_users_table', 1),
(5, '2026_05_26_024404_create_employees_table', 2),
(6, '2026_05_28_013505_add_user_id_to_employees_table', 3);

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
('2Afac5U3KajAQ4VOgJybNN69GHQfJqxG4UkNxbHn', 30, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.121.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiUk5qTW5MMUk4WXU5VVRHZXVOdDNHQnFQTFJQUVBIQ1g2aFpVTHlWVSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2VtcGxveWVlcyI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjMxOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvZGFzaGJvYXJkIjtzOjU6InJvdXRlIjtzOjk6ImRhc2hib2FyZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjMwO30=', 1779788483),
('ALFRRs9yFPN5hViZRB00zxK7MqcdWMvPTkQb7PX5', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiaG1OU2E5dTFnWXplYVZldHUwS1FGY3hLWERGZFlFdnowcjhza1ZTcyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fX0=', 1779788483),
('fusglNrWSndFF1V1OdnmZeQJuojmaa3MtSmfZBXe', 31, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiRzhFSW90YnoyeUNCcEJvVjEwa01pSFNVSEpOcFRwd0ZWMVNWRXhqSSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzA6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9yZWdpc3RlciI7czo1OiJyb3V0ZSI7czo4OiJyZWdpc3RlciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjE6e2k6MDtzOjc6InN1Y2Nlc3MiO31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjMxO3M6Nzoic3VjY2VzcyI7czo1NzoiUmVnaXN0cmF0aW9uIHN1Y2Nlc3NmdWwhIFdlbGNvbWUgdG8gSFIgTWFuYWdlbWVudCBTeXN0ZW0uIjt9', 1779930978),
('lLOdbJzC3bH06tkj1RyANmVNW7n6BeOgIbaLXUZO', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoicW02MldHUW1KZEc4aTVTNWIyanJIRkhlcDZCaEZCcVpwcDl2N3I5SyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1779844797),
('off4eBGfZVFJ8ognJRciNvrLhw63S4Xt5mAdR3st', 29, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoibWFyaU9zaFd5SlcyeXViNUZKemhzOUpYVHFiQUlhUEZJbTJJb3ZxTCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9lbXBsb3llZXMiO3M6NToicm91dGUiO3M6MTU6ImVtcGxveWVlcy5pbmRleCI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjI5O30=', 1779935945),
('ory12DX2o1EfUGnqX1lVYPpXj0gujXzNDHTMTeqE', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiQVVJZFdkMm1sS25RcG13aEZQT0dpcDhXdWNaNUxXVnFMUXhwU2hlMCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fX0=', 1779931160),
('ZXkd1ggeAT6BFOYe6nUPMh21iDmRTNeDPgZB38M0', 28, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiR1BtSzdDWG5YRDRjV0pmMnNTZVZPTjZIYXRla0RjNUE0NG9pT1VubiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9lbXBsb3llZXMiO3M6NToicm91dGUiO3M6MTU6ImVtcGxveWVlcy5pbmRleCI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjI4O30=', 1779936272);

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
(1, 'Admin User', 'admin@company.com', 'admin', 1, '2026-05-25 19:34:58', '2026-05-25 18:39:14', '$2y$12$ZlbLqOkMqp8f0JvHkrP6VORGY.Ws7FyyW6/I4CNgRka/PfXL/vWeW', 'jDD5L0nl9W7fKfW5wDVTiq8UO5ARnmSAH305tuRJUaP3UFKNWGV9OGCmfiEJ', '2026-05-25 18:39:14', '2026-05-25 19:34:58'),
(2, 'HR Manager', 'hr@company.com', 'hr', 1, '2026-05-25 19:47:12', '2026-05-25 18:39:14', '$2y$12$ZlbLqOkMqp8f0JvHkrP6VORGY.Ws7FyyW6/I4CNgRka/PfXL/vWeW', 'e8OKvbEiTXLKf834CCN8YKXJ49O46wshZVxeWlwvvfAtHMM8PfBEAOxkYHMf', '2026-05-25 18:39:14', '2026-05-25 19:47:12'),
(3, 'HR Specialist', 'hrspecialist@company.com', 'hr', 1, NULL, '2026-05-25 18:39:14', '$2y$12$ZlbLqOkMqp8f0JvHkrP6VORGY.Ws7FyyW6/I4CNgRka/PfXL/vWeW', 'd279BObJpp', '2026-05-25 18:39:14', '2026-05-25 18:39:14'),
(4, 'John Doe', 'john@company.com', 'employee', 1, NULL, '2026-05-25 18:39:14', '$2y$12$ZlbLqOkMqp8f0JvHkrP6VORGY.Ws7FyyW6/I4CNgRka/PfXL/vWeW', 'qN122otl6I', '2026-05-25 18:39:14', '2026-05-25 18:39:14'),
(5, 'Jane Smith', 'jane@company.com', 'employee', 1, NULL, '2026-05-25 18:39:14', '$2y$12$ZlbLqOkMqp8f0JvHkrP6VORGY.Ws7FyyW6/I4CNgRka/PfXL/vWeW', 'IdiOLzNCKC', '2026-05-25 18:39:14', '2026-05-25 18:39:14'),
(6, 'Mr. Jedidiah Fadel', 'taya60@example.org', 'employee', 1, NULL, '2026-05-25 18:39:14', '$2y$12$ZlbLqOkMqp8f0JvHkrP6VORGY.Ws7FyyW6/I4CNgRka/PfXL/vWeW', 'rerHgvTaDB', '2026-05-25 18:39:14', '2026-05-25 18:39:14'),
(7, 'Dr. Macey Morar', 'graham.nicklaus@example.com', 'employee', 1, NULL, '2026-05-25 18:39:14', '$2y$12$ZlbLqOkMqp8f0JvHkrP6VORGY.Ws7FyyW6/I4CNgRka/PfXL/vWeW', 'BnUQDevcQh', '2026-05-25 18:39:14', '2026-05-25 18:39:14'),
(8, 'Miss Alice Schimmel', 'pollich.sigrid@example.com', 'employee', 1, NULL, '2026-05-25 18:39:14', '$2y$12$ZlbLqOkMqp8f0JvHkrP6VORGY.Ws7FyyW6/I4CNgRka/PfXL/vWeW', 'Wn7EnNTbrR', '2026-05-25 18:39:14', '2026-05-25 18:39:14'),
(9, 'Deontae Baumbach', 'keeling.cordia@example.org', 'employee', 0, NULL, '2026-05-25 18:39:14', '$2y$12$ZlbLqOkMqp8f0JvHkrP6VORGY.Ws7FyyW6/I4CNgRka/PfXL/vWeW', '3VoR8B2Ily', '2026-05-25 18:39:14', '2026-05-27 18:08:09'),
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
(27, 'vincent', 'vincent@gmail.com', 'employee', 1, NULL, NULL, '$2y$12$bVLb.1LuNVAo16Szh3YISOYGV.WuM8UADRngtt8K2oqazO7PIy9QW', NULL, '2026-05-25 18:54:17', '2026-05-25 18:54:17'),
(28, 'Renz Andrew', 'andrewrennn@gmail.com', 'admin', 1, '2026-05-27 17:29:43', NULL, '$2y$12$1GFjoWQZ0If5XuCfmKzvmOToF8ueeykDyCv7vaeWYTgdC3BJ6GJiG', '0e7ghcoNKkiI6Wr7OL6AJkLt8HFvttOr1p1q0fs0ugTVXjvNzCjo3PVT7ERw', '2026-05-25 19:48:06', '2026-05-27 17:29:43'),
(29, 'yema', 'mydonglonglmoa@gmail.com', 'hr', 1, '2026-05-27 18:22:57', NULL, '$2y$12$gq6jpXlpAPpPhN1SaT6NKuSbD2M1wIvt6GUyv/fsEZRrXNrSCkccy', 'XUMQmCLZcSDS5OFEmVI3XQCsZ9RsnhtZqqGrJPcK8jRITqRzMZ8bHFCEi7Hb', '2026-05-25 20:25:02', '2026-05-27 18:22:57'),
(30, 'what', 'andrew.ae215@gmail.com', 'employee', 1, NULL, NULL, '$2y$12$dQ6rFxP4YaqBJL5e3MrbkuQV1VDxNXY00E926PDMREyZpxfDTonf2', NULL, '2026-05-25 22:27:20', '2026-05-25 22:27:20'),
(31, 'John Doe', 'johndoe@gmail.com', 'employee', 1, '2026-05-27 17:19:04', NULL, '$2y$12$pjPYR7uNgZ9JN/HkxM9PuObj9G0l6JTll6dL.l5zfJ4r4g3IOcVr2', NULL, '2026-05-27 17:16:17', '2026-05-27 17:19:04'),
(32, 'Postman', 'postman@gmail.com', 'employee', 1, NULL, NULL, '$2y$12$LsiV3.N0MtUzk87mCeb1teB1sL7JeTQXXPrRSmuaFfF6hcFR1LRMq', NULL, '2026-05-27 17:22:49', '2026-05-27 17:22:49');

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
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employees_employee_id_unique` (`employee_id`),
  ADD UNIQUE KEY `employees_email_unique` (`email`),
  ADD KEY `employees_user_id_foreign` (`user_id`);

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
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
