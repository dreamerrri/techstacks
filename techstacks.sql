-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 02, 2026 at 04:43 AM
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
-- Database: `techstacks`
--

-- --------------------------------------------------------

--
-- Table structure for table `allowances`
--

CREATE TABLE `allowances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'monthly',
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `effective_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `allowances`
--

INSERT INTO `allowances` (`id`, `employee_id`, `name`, `amount`, `type`, `description`, `is_active`, `effective_date`, `end_date`, `created_at`, `updated_at`) VALUES
(1, 22, 'manuela', 1500.00, 'one-time', 'advance', 1, NULL, NULL, '2026-05-28 22:18:29', '2026-05-28 22:18:29'),
(3, 22, 'manuela', 1500.00, 'one-time', NULL, 1, NULL, NULL, '2026-05-28 22:23:45', '2026-05-28 22:23:45'),
(4, 5, 'pang internet', 3000.00, 'one-time', NULL, 1, NULL, NULL, '2026-06-01 00:41:46', '2026-06-01 00:41:46');

-- --------------------------------------------------------

--
-- Table structure for table `attendances`
--

CREATE TABLE `attendances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `month` varchar(255) NOT NULL,
  `year` varchar(255) NOT NULL,
  `days_worked` int(11) NOT NULL DEFAULT 0,
  `regular_hours` int(11) NOT NULL DEFAULT 0,
  `overtime_hours` int(11) NOT NULL DEFAULT 0,
  `late_hours` int(11) NOT NULL DEFAULT 0,
  `night_differential_hours` int(11) NOT NULL DEFAULT 0,
  `regular_holiday_worked` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `benefits`
--

CREATE TABLE `benefits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'monthly',
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `effective_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `sss_rate` decimal(5,4) NOT NULL DEFAULT 0.0450,
  `sss_cap` decimal(10,2) NOT NULL DEFAULT 900.00,
  `philhealth_rate` decimal(5,4) NOT NULL DEFAULT 0.0225,
  `philhealth_cap` decimal(10,2) NOT NULL DEFAULT 1500.00,
  `pagibig_rate` decimal(5,4) NOT NULL DEFAULT 0.0200,
  `pagibig_cap` decimal(10,2) NOT NULL DEFAULT 100.00,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `user_id`, `employee_id`, `first_name`, `middle_name`, `last_name`, `birthdate`, `gender`, `civil_status`, `address`, `contact_number`, `email`, `department`, `position`, `employment_status`, `date_hired`, `salary_type`, `basic_salary`, `sss_number`, `philhealth_number`, `pagibig_number`, `tin_number`, `sss_rate`, `sss_cap`, `philhealth_rate`, `philhealth_cap`, `pagibig_rate`, `pagibig_cap`, `is_archived`, `created_at`, `updated_at`) VALUES
(1, 1, 'EMP-4873', 'Admin', 'Carleton', 'User', '1979-02-20', 'Other', 'Married', '71493 Kunze Mills\nPort Morganstad, ND 84716', '+1-606-420-2072', 'admin@company.com', 'Sales', 'Postsecondary Teacher', 'Part-time', '2016-07-24', 'Monthly', 83328.00, '32-3132616-3', '61-825703476-0', '0286-0155-5965', '221-341-179-174', 0.0450, 900.00, 0.0225, 1500.00, 0.0200, 100.00, 0, '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(2, 2, 'EMP-9515', 'HR', 'Marquis', 'Manager', '2023-04-13', 'Female', 'Separated', '38438 Hyman Isle\nWest Nonatown, TX 87900-7573', '(425) 441-0783', 'hr@company.com', 'HR', 'HR Manager', 'Regular', '1994-02-06', 'Daily', 74458.00, '97-3073922-6', '88-051787322-7', '1414-7874-6088', '777-165-518-796', 0.0450, 900.00, 0.0225, 1500.00, 0.0200, 100.00, 0, '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(3, 3, 'EMP-9689', 'HR', 'Caitlyn', 'Specialist', '1994-03-07', 'Other', 'Married', '1469 Paula Points\nKossbury, MA 29507', '279-563-4672', 'hrspecialist@company.com', 'HR', 'HR Specialist', 'Regular', '2003-11-17', 'Daily', 34100.00, '41-1330398-5', '54-160227035-7', '7952-2475-4127', '794-516-893-151', 0.0450, 900.00, 0.0225, 1500.00, 0.0200, 100.00, 0, '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(4, 4, 'EMP-9548', 'John', 'Eveline', 'Doe', '2021-09-25', 'Female', 'Separated', '8001 Orin Bypass Suite 746\r\nNikolausburgh, DE 27721', '641-678-1902', 'john@company.com', 'Information Technology', 'Employee', 'Regular', '2004-03-24', 'Monthly', 30000.00, '73-2662784-8', '25-932282902-1', '5160-5097-0668', '461-309-968-361', 0.0900, 15000.00, 0.0225, 1500.00, 0.0200, 100.00, 0, '2026-05-27 23:57:33', '2026-06-01 18:23:51'),
(5, 5, 'EMP-8311', 'Jane', NULL, 'Smith', '1996-11-23', 'Female', 'Married', '7586 Kaleb Terrace\r\nEast Glennie, DE 57399', '(646) 767-1389', 'jane@company.com', 'Information Technology', 'Supervisor', 'Regular', '1981-11-30', 'Monthly', 25000.00, '39-2737819-3', '56-466058920-7', '5403-0332-7421', '268-519-280-041', 0.0450, 560.00, 0.0225, 300.00, 0.0200, 100.00, 0, '2026-05-27 23:57:33', '2026-06-01 01:37:16'),
(6, 6, 'EMP-3372', 'Cooper', 'Ronny', 'Bogan', '1995-02-16', 'Other', 'Widowed', '921 Keebler Forges Suite 327\r\nKesslerborough, NV 26963', '(470) 456-6579', 'darien54@example.org', 'Marketing', 'Material Movers', 'Part-time', '1997-02-02', 'Hourly', 74528.00, '99-0347997-5', '17-970158606-2', '6001-4701-5494', '256-597-384-994', 0.0450, 900.00, 0.0225, 1500.00, 0.0200, 100.00, 0, '2026-05-27 23:57:33', '2026-05-28 21:55:08'),
(7, 7, 'EMP-3452', 'Clinton', 'Alize', 'Gottlieb', '1985-12-10', 'Male', 'Married', '90304 Marquardt Run Apt. 598\nPort Hardy, NV 75673', '+1-539-438-7927', 'aparker@example.com', 'IT', 'Video Editor', 'Probationary', '2001-08-15', 'Monthly', 57995.00, '49-5049335-3', '74-692607564-7', '3819-9322-3323', '001-662-604-453', 0.0450, 900.00, 0.0225, 1500.00, 0.0200, 100.00, 0, '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(8, 8, 'EMP-3292', 'Paxton', 'Leonardo', 'Spencer', '1996-03-12', 'Other', 'Married', '79729 Wyman Plains\nSouth Sister, FL 03910-1642', '+1 (703) 442-9431', 'gabriella44@example.net', 'Marketing', 'System Administrator', 'Probationary', '2004-11-22', 'Daily', 31405.00, '82-7668564-7', '24-994042234-7', '1788-8619-1289', '452-343-047-815', 0.0450, 900.00, 0.0225, 1500.00, 0.0200, 100.00, 0, '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(9, 9, 'EMP-1281', 'Jane', 'Verla', 'Gleichner', '1989-01-17', 'Other', 'Separated', '26269 Cole Plaza Suite 868\nNew Camron, IL 88552', '959.755.2745', 'gislason.theron@example.com', 'HR', 'Office Machine Operator', 'Contractual', '1986-09-23', 'Hourly', 26875.00, '10-8925477-4', '67-136420262-6', '4103-1237-4470', '168-398-003-511', 0.0450, 900.00, 0.0225, 1500.00, 0.0200, 100.00, 0, '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(10, 10, 'EMP-7656', 'Marcos', NULL, 'Hegmann', '1994-03-02', 'Male', 'Separated', '767 Smitham Prairie Suite 818\nShieldstown, IL 28666-4972', '(267) 528-2610', 'fgutkowski@example.net', 'Sales', 'Production Control Manager', 'Contractual', '2024-11-07', 'Monthly', 20518.00, '22-5151911-5', '21-189927563-5', '3710-3898-1774', '507-140-634-319', 0.0450, 900.00, 0.0225, 1500.00, 0.0200, 100.00, 0, '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(11, 11, 'EMP-1590', 'Fredrick', NULL, 'Pfeffer', '2004-03-29', 'Male', 'Single', '596 Klein Crossroad Suite 006\nFaybury, WV 49748-5028', '1-732-301-7102', 'meta.dietrich@example.com', 'Marketing', 'Nursing Instructor', 'Part-time', '1996-02-29', 'Daily', 87339.00, '30-7303634-8', '47-367446345-8', '1207-1989-1299', '038-905-768-753', 0.0450, 900.00, 0.0225, 1500.00, 0.0200, 100.00, 0, '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(12, 12, 'EMP-9270', 'Kaitlin', NULL, 'Pfeffer', '1973-08-22', 'Other', 'Separated', '42179 Morar Falls\nWest Dorthyside, IN 86264', '1-914-569-0125', 'zcarroll@example.org', 'Operations', 'Precision Etcher and Engraver', 'Probationary', '1990-09-26', 'Daily', 42465.00, '43-9716169-7', '24-379385412-6', '9950-9105-0282', '249-354-012-320', 0.0450, 900.00, 0.0225, 1500.00, 0.0200, 100.00, 0, '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(13, 13, 'EMP-7184', 'Magdalena', 'Sabina', 'Medhurst', '2005-04-28', 'Other', 'Separated', '3277 Camryn Lodge Apt. 708\nSouth Daphneehaven, UT 89266', '(321) 419-6651', 'hagenes.aliya@example.net', 'Sales', 'Product Specialist', 'Regular', '1977-06-17', 'Daily', 64342.00, '82-3370250-4', '78-619071406-2', '3952-5501-5057', '740-553-057-458', 0.0450, 900.00, 0.0225, 1500.00, 0.0200, 100.00, 0, '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(14, 14, 'EMP-8755', 'Kailee', 'Johnson', 'Prosacco', '2024-05-26', 'Female', 'Widowed', '574 Spencer Mill\nStantonburgh, VT 25976-3128', '+17046365945', 'brian.adams@example.org', 'Finance', 'Vocational Education Teacher', 'Part-time', '1983-09-12', 'Hourly', 85154.00, '93-6993278-8', '07-225127737-5', '7828-9749-0152', '479-978-704-856', 0.0450, 900.00, 0.0225, 1500.00, 0.0200, 100.00, 0, '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(15, 15, 'EMP-7290', 'Molly', 'Lily', 'Bernhard', '1998-11-27', 'Male', 'Single', '328 Trantow Freeway Suite 698\r\nDereckstad, WY 58467-5895', '(360) 490-7625', 'simonis.buford@example.org', 'Finance', 'Tax Examiner', 'Regular', '1984-08-21', 'Monthly', 64479.00, '26-1233594-5', '42-583119816-6', '2725-1692-2227', '770-684-750-255', 1.0000, 5000.00, 0.3000, 2300.00, 0.2000, 100.00, 0, '2026-05-27 23:57:33', '2026-05-29 01:13:43'),
(16, 16, 'EMP-3107', 'Brice', 'Manuela', 'Flatley', '1999-08-09', 'Male', 'Separated', '3279 Kristopher Greens Apt. 692\nDillanberg, SD 60128-8896', '629-627-2147', 'tara.hermiston@example.org', 'Sales', 'Engineering Manager', 'Probationary', '2008-09-23', 'Daily', 58975.00, '77-9822771-9', '78-767802987-0', '8224-0338-1248', '598-379-715-057', 0.0450, 900.00, 0.0225, 1500.00, 0.0200, 100.00, 0, '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(17, 17, 'EMP-6732', 'Fiona', NULL, 'Huels', '1981-01-30', 'Female', 'Single', '5552 Daugherty Via\nLake Zeldamouth, DC 09559-7047', '541.822.7533', 'kali22@example.org', 'Finance', 'Director Religious Activities', 'Contractual', '2013-10-03', 'Hourly', 50658.00, '36-7810020-5', '57-541358614-7', '3023-9849-8359', '129-968-404-256', 0.0450, 900.00, 0.0225, 1500.00, 0.0200, 100.00, 0, '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(18, 18, 'EMP-4557', 'Creola', NULL, 'Hand', '2012-05-19', 'Other', 'Separated', '8831 Torp Port Apt. 999\nEast Gianni, WV 20629', '(386) 924-7054', 'kmurphy@example.org', 'Marketing', 'Social Worker', 'Part-time', '1980-10-31', 'Daily', 35846.00, '24-8786902-1', '57-472477592-9', '3063-8854-0510', '613-347-759-326', 0.0450, 900.00, 0.0225, 1500.00, 0.0200, 100.00, 0, '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(19, 19, 'EMP-9451', 'Janelle', 'Lulu', 'Kuhlman', '1996-05-24', 'Other', 'Separated', '1888 Waters Greens\nSouth Dexterchester, ND 48155', '980.692.3318', 'mckenzie.afton@example.net', 'IT', 'Automotive Glass Installers', 'Regular', '2013-07-25', 'Daily', 58357.00, '96-5540551-1', '65-613761921-6', '6648-9661-1134', '512-468-360-681', 0.0450, 900.00, 0.0225, 1500.00, 0.0200, 100.00, 0, '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(20, 20, 'EMP-9782', 'Pedro', NULL, 'Lowe', '2014-12-28', 'Male', 'Single', '751 Purdy Spring Suite 187\nNew Artbury, VA 86929-1940', '+1-980-712-3421', 'hand.holden@example.org', 'Sales', 'Train Crew', 'Part-time', '2005-09-28', 'Hourly', 88092.00, '16-7725298-2', '24-479914382-2', '1793-1955-3686', '272-084-109-558', 0.0450, 900.00, 0.0225, 1500.00, 0.0200, 100.00, 0, '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(21, 21, 'EMP-8833', 'Angus', 'Estel', 'Osinski', '2024-12-06', 'Male', 'Single', '9021 Cesar Ville Suite 634\nPort Ricofort, GA 43467', '+1.806.613.1614', 'lilian.fahey@example.com', 'HR', 'Plumber OR Pipefitter OR Steamfitter', 'Contractual', '1973-06-28', 'Hourly', 81511.00, '77-9954061-7', '89-760562777-7', '0175-3109-5169', '926-416-088-030', 0.0450, 900.00, 0.0225, 1500.00, 0.0200, 100.00, 0, '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(22, 22, 'EMP-7161', 'Manuela', NULL, 'Bradtke', '1988-06-16', 'Male', 'Separated', '51809 Gutmann Roads Suite 292\r\nDurganhaven, VT 01221', '1-678-425-2972', 'sporer.theron@example.com', 'IT', 'Aircraft Launch Specialist', 'Regular', '2015-07-04', 'Monthly', 25000.00, '87-1608127-3', '00-338742370-0', '5631-8398-5079', '811-583-896-033', 0.0450, 900.00, 0.0225, 1500.00, 0.0200, 100.00, 0, '2026-05-27 23:57:33', '2026-05-28 18:29:41'),
(23, 23, 'EMP-8238', 'Annetta', NULL, 'Prosacco', '2007-04-13', 'Other', 'Separated', '7154 Stoltenberg Corner Apt. 731\nSouth Verla, WY 80642-0733', '1-620-673-9889', 'demario.parker@example.org', 'Marketing', 'Dietetic Technician', 'Regular', '1996-01-03', 'Daily', 58700.00, '91-1955541-5', '52-372605491-7', '2508-6818-3704', '083-889-954-658', 0.0450, 900.00, 0.0225, 1500.00, 0.0200, 100.00, 0, '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(24, 24, 'EMP-4996', 'Alberto', NULL, 'Legros', '1980-01-24', 'Male', 'Married', '922 Gaylord Coves\nLake Vicenta, OK 46946-8608', '+14242523706', 'cole.gerson@example.org', 'Finance', 'Order Filler', 'Contractual', '1991-04-28', 'Monthly', 35532.00, '69-9424707-7', '16-996868481-5', '3612-6108-9399', '800-390-677-616', 0.0450, 900.00, 0.0225, 1500.00, 0.0200, 100.00, 0, '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(25, 25, 'EMP-1632', 'Ariel', 'Muhammad', 'Collins', '2002-06-18', 'Other', 'Married', '59155 Clyde Ferry\nCesarberg, MI 66403', '845.619.0729', 'xryan@example.net', 'IT', 'Jeweler', 'Regular', '1981-09-12', 'Daily', 25308.00, '68-8951800-7', '98-015717157-2', '7093-4058-0963', '160-743-817-416', 0.0450, 900.00, 0.0225, 1500.00, 0.0200, 100.00, 0, '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(26, 26, 'EMP-8819', 'Inactive', 'Christian', 'User', '2004-07-25', 'Female', 'Widowed', '9180 Towne Parkway Suite 629\nSouth Velmaport, KS 09285-1395', '989-489-7013', 'inactive@company.com', 'IT', 'Trainer', 'Probationary', '2024-02-25', 'Hourly', 47910.00, '54-9856048-8', '16-813165035-3', '9605-9231-3090', '482-971-101-364', 0.0450, 900.00, 0.0225, 1500.00, 0.0200, 100.00, 0, '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(27, 27, 'EMP-0027', 'Vincent', NULL, 'Vincent', '1990-01-01', 'Other', 'Single', 'Blk 23 Lot 3 Cordova Street Sta. Arcadia', '09165461438', 'vincent@company.com', 'Unassigned', 'Unassigned', 'Probationary', '2026-05-29', 'Monthly', 29999.99, NULL, NULL, NULL, NULL, 0.0450, 900.00, 0.0225, 1500.00, 0.0200, 100.00, 0, '2026-05-28 23:41:51', '2026-05-29 00:03:54');

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
(5, '2026_05_26_024404_create_employees_table', 1),
(6, '2026_05_28_013505_add_user_id_to_employees_table', 1),
(7, '2026_05_29_000000_create_attendances_table', 2),
(8, '2026_05_29_060241_create_allowances_table', 3),
(9, '2026_05_29_060249_create_benefits_table', 3),
(10, '2026_05_29_083906_add_government_contributions_to_employees_table', 4),
(11, '2026_05_29_022254_create_payroll_periods_table', 5),
(12, '2026_05_29_022303_create_payroll_inputs_table', 6),
(13, '2026_05_29_022309_create_payroll_adjustments_table', 7),
(14, '2026_06_01_052848_add_rate_type_to_payroll_inputs_table', 8),
(15, '2026_06_01_053820_add_rate_type_column_to_payroll_inputs_table_again', 9),
(16, '2026_06_01_081655_add_holiday_days_to_payroll_inputs_table', 10),
(17, '2026_06_01_083043_add_night_differential_hours_to_payroll_inputs_table', 11),
(18, '2026_06_01_084805_add_regular_hours_to_payroll_inputs_table', 12);

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
-- Table structure for table `payroll_adjustments`
--

CREATE TABLE `payroll_adjustments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `payroll_input_id` bigint(20) UNSIGNED NOT NULL,
  `adjustment_type` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll_inputs`
--

CREATE TABLE `payroll_inputs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `payroll_period_id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `daily_rate` decimal(10,2) NOT NULL,
  `rate_type` varchar(255) NOT NULL DEFAULT 'daily',
  `days_worked` decimal(5,2) NOT NULL DEFAULT 0.00,
  `regular_hours` decimal(8,2) NOT NULL DEFAULT 0.00,
  `overtime_hours` decimal(5,2) NOT NULL DEFAULT 0.00,
  `late_hours` decimal(5,2) NOT NULL DEFAULT 0.00,
  `holiday_days` decimal(5,2) NOT NULL DEFAULT 0.00,
  `night_differential_hours` decimal(5,2) NOT NULL DEFAULT 0.00,
  `allowances` decimal(10,2) NOT NULL DEFAULT 0.00,
  `deductions` decimal(10,2) NOT NULL DEFAULT 0.00,
  `gross_pay` decimal(10,2) NOT NULL DEFAULT 0.00,
  `net_pay` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payroll_inputs`
--

INSERT INTO `payroll_inputs` (`id`, `payroll_period_id`, `employee_id`, `daily_rate`, `rate_type`, `days_worked`, `regular_hours`, `overtime_hours`, `late_hours`, `holiday_days`, `night_differential_hours`, `allowances`, `deductions`, `gross_pay`, `net_pay`, `created_at`, `updated_at`) VALUES
(1, 3, 5, 25000.00, 'monthly', 11.00, 88.00, 6.00, 1.00, 1.00, 4.00, 3000.00, 1000.00, 18752.83, 16387.01, '2026-05-31 18:44:48', '2026-06-01 01:03:52'),
(2, 3, 6, 200.00, 'daily', 25.00, 0.00, 0.00, 2.00, 0.00, 0.00, 0.00, 0.00, 4950.00, 4950.00, '2026-05-31 18:55:33', '2026-05-31 22:51:22'),
(3, 3, 4, 30000.00, 'monthly', 11.00, 88.00, 6.00, 1.00, 1.00, 0.00, 0.00, 0.00, 18835.31, 17463.93, '2026-05-31 19:21:49', '2026-06-01 17:46:51'),
(4, 2, 5, 25000.00, 'monthly', 24.00, 106.00, 2.00, 0.00, 0.00, 6.00, 3000.00, 0.00, 30713.00, 27968.80, '2026-06-01 18:11:58', '2026-06-01 18:13:48'),
(5, 4, 4, 30000.00, 'monthly', 23.00, 106.00, 2.00, 3.00, 0.00, 0.00, 0.00, 0.00, 31278.49, 27826.18, '2026-06-01 18:15:31', '2026-06-01 18:15:31');

-- --------------------------------------------------------

--
-- Table structure for table `payroll_periods`
--

CREATE TABLE `payroll_periods` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cutoff_start` date NOT NULL,
  `cutoff_end` date NOT NULL,
  `payroll_date` date NOT NULL,
  `status` enum('draft','finalized') NOT NULL DEFAULT 'draft',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payroll_periods`
--

INSERT INTO `payroll_periods` (`id`, `cutoff_start`, `cutoff_end`, `payroll_date`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, '2026-06-01', '2026-06-22', '2026-06-30', 'draft', 2, '2026-05-31 17:42:43', '2026-05-31 17:42:43'),
(2, '2026-06-01', '2026-06-22', '2026-06-22', 'draft', 2, '2026-05-31 17:43:09', '2026-05-31 17:43:09'),
(3, '2026-05-01', '2026-05-31', '2026-06-01', 'finalized', 1, '2026-05-31 18:33:40', '2026-06-01 18:04:28'),
(4, '2026-06-02', '2026-06-30', '2026-07-01', 'draft', 2, '2026-06-01 18:14:40', '2026-06-01 18:14:40');

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
('Aw0sTn4AD1bJfLg7Hjb3mm8RjzJQJU8AARwObb1a', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiY0dhVU9mM09xYmQ2cjQ2eFQyUEVNSFRpb2FUWllHcGVyN00xd2loRCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1779955107);

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
(1, 'Admin User', 'admin@company.com', 'admin', 1, '2026-05-31 22:54:32', '2026-05-27 23:57:33', '$2y$12$Rep4sY0z235RbU1Bt3lkEOjKcqMweVVrcToRTezgHQcDeTNc2I9yG', 'ri9GWpiSnnNJovpIY15aeZ7ATwI7aHzsf1EQMWkWXw40OX7trB50r8BlqwmV', '2026-05-27 23:57:33', '2026-05-31 22:54:32'),
(2, 'HR Manager', 'hr@company.com', 'hr', 1, '2026-06-01 17:46:20', '2026-05-27 23:57:33', '$2y$12$Rep4sY0z235RbU1Bt3lkEOjKcqMweVVrcToRTezgHQcDeTNc2I9yG', 'sNvY7JH7nb3CauhJ8bVepACnG3IUqi4TRRvSJ3PwS94pPtaxeQp1q0Thk165', '2026-05-27 23:57:33', '2026-06-01 17:46:20'),
(3, 'HR Specialist', 'hrspecialist@company.com', 'hr', 1, NULL, '2026-05-27 23:57:33', '$2y$12$Rep4sY0z235RbU1Bt3lkEOjKcqMweVVrcToRTezgHQcDeTNc2I9yG', 'I99RCbTyoN', '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(4, 'John Doe', 'john@company.com', 'employee', 1, NULL, '2026-05-27 23:57:33', '$2y$12$Rep4sY0z235RbU1Bt3lkEOjKcqMweVVrcToRTezgHQcDeTNc2I9yG', 'Cnp963ORXA', '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(5, 'Jane Smith', 'jane@company.com', 'employee', 1, NULL, '2026-05-27 23:57:33', '$2y$12$Rep4sY0z235RbU1Bt3lkEOjKcqMweVVrcToRTezgHQcDeTNc2I9yG', 'OTlWTklm69', '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(6, 'Rolando O\'Connell', 'darien54@example.org', 'employee', 1, NULL, '2026-05-27 23:57:33', '$2y$12$Rep4sY0z235RbU1Bt3lkEOjKcqMweVVrcToRTezgHQcDeTNc2I9yG', 'y9udMHryOL', '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(7, 'Mr. Hudson McLaughlin', 'aparker@example.com', 'employee', 1, NULL, '2026-05-27 23:57:33', '$2y$12$Rep4sY0z235RbU1Bt3lkEOjKcqMweVVrcToRTezgHQcDeTNc2I9yG', 'MVqjlPh6Od', '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(8, 'Lora Bailey', 'gabriella44@example.net', 'employee', 1, NULL, '2026-05-27 23:57:33', '$2y$12$Rep4sY0z235RbU1Bt3lkEOjKcqMweVVrcToRTezgHQcDeTNc2I9yG', 'BLe2DoLRzJ', '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(9, 'Bartholome Price', 'gislason.theron@example.com', 'employee', 1, NULL, '2026-05-27 23:57:33', '$2y$12$Rep4sY0z235RbU1Bt3lkEOjKcqMweVVrcToRTezgHQcDeTNc2I9yG', 'Vxc2syfdlY', '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(10, 'Dr. Osborne Jerde DDS', 'fgutkowski@example.net', 'employee', 1, NULL, '2026-05-27 23:57:33', '$2y$12$Rep4sY0z235RbU1Bt3lkEOjKcqMweVVrcToRTezgHQcDeTNc2I9yG', 'u79V4fT7n8', '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(11, 'Efren Bruen Sr.', 'meta.dietrich@example.com', 'employee', 1, NULL, '2026-05-27 23:57:33', '$2y$12$Rep4sY0z235RbU1Bt3lkEOjKcqMweVVrcToRTezgHQcDeTNc2I9yG', 'JCnNWIdIE8', '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(12, 'Rodrigo Maggio', 'zcarroll@example.org', 'employee', 1, NULL, '2026-05-27 23:57:33', '$2y$12$Rep4sY0z235RbU1Bt3lkEOjKcqMweVVrcToRTezgHQcDeTNc2I9yG', 'C35X8aDgw1', '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(13, 'Jalyn White', 'hagenes.aliya@example.net', 'employee', 1, NULL, '2026-05-27 23:57:33', '$2y$12$Rep4sY0z235RbU1Bt3lkEOjKcqMweVVrcToRTezgHQcDeTNc2I9yG', '5aTSicn0Jg', '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(14, 'Katlyn Smith', 'brian.adams@example.org', 'employee', 1, NULL, '2026-05-27 23:57:33', '$2y$12$Rep4sY0z235RbU1Bt3lkEOjKcqMweVVrcToRTezgHQcDeTNc2I9yG', 'JRyJk1980f', '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(15, 'Molly Bernhard', 'simonis.buford@example.org', 'employee', 1, NULL, '2026-05-27 23:57:33', '$2y$12$Rep4sY0z235RbU1Bt3lkEOjKcqMweVVrcToRTezgHQcDeTNc2I9yG', '4VGcaCx0Xe', '2026-05-27 23:57:33', '2026-05-28 23:56:14'),
(16, 'Larissa Ullrich', 'tara.hermiston@example.org', 'employee', 1, NULL, '2026-05-27 23:57:33', '$2y$12$Rep4sY0z235RbU1Bt3lkEOjKcqMweVVrcToRTezgHQcDeTNc2I9yG', 'AomsUSCGnz', '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(17, 'Otha Dickinson', 'kali22@example.org', 'employee', 1, NULL, '2026-05-27 23:57:33', '$2y$12$Rep4sY0z235RbU1Bt3lkEOjKcqMweVVrcToRTezgHQcDeTNc2I9yG', '3viY4ANMBy', '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(18, 'Forest Parisian', 'kmurphy@example.org', 'employee', 1, NULL, '2026-05-27 23:57:33', '$2y$12$Rep4sY0z235RbU1Bt3lkEOjKcqMweVVrcToRTezgHQcDeTNc2I9yG', 'vvgNL0iHmA', '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(19, 'Lindsay Denesik', 'mckenzie.afton@example.net', 'employee', 1, NULL, '2026-05-27 23:57:33', '$2y$12$Rep4sY0z235RbU1Bt3lkEOjKcqMweVVrcToRTezgHQcDeTNc2I9yG', 'OohnFF9VK4', '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(20, 'Olin Aufderhar Jr.', 'hand.holden@example.org', 'employee', 1, NULL, '2026-05-27 23:57:33', '$2y$12$Rep4sY0z235RbU1Bt3lkEOjKcqMweVVrcToRTezgHQcDeTNc2I9yG', 'BYK9dxnMun', '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(21, 'Mr. Ervin Hills DDS', 'lilian.fahey@example.com', 'employee', 1, NULL, '2026-05-27 23:57:33', '$2y$12$Rep4sY0z235RbU1Bt3lkEOjKcqMweVVrcToRTezgHQcDeTNc2I9yG', 'xNseUkYJxo', '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(22, 'Marge Greenholt', 'sporer.theron@example.com', 'employee', 1, NULL, '2026-05-27 23:57:33', '$2y$12$Rep4sY0z235RbU1Bt3lkEOjKcqMweVVrcToRTezgHQcDeTNc2I9yG', 'oZ5ENdSCUa', '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(23, 'Ms. Alanna Schimmel', 'demario.parker@example.org', 'employee', 1, NULL, '2026-05-27 23:57:33', '$2y$12$Rep4sY0z235RbU1Bt3lkEOjKcqMweVVrcToRTezgHQcDeTNc2I9yG', 'CGkPl3wAas', '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(24, 'Dr. Eleanore Abernathy', 'cole.gerson@example.org', 'employee', 1, NULL, '2026-05-27 23:57:33', '$2y$12$Rep4sY0z235RbU1Bt3lkEOjKcqMweVVrcToRTezgHQcDeTNc2I9yG', 'NSAeUNs3y6', '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(25, 'Dr. Andre Luettgen', 'xryan@example.net', 'employee', 1, NULL, '2026-05-27 23:57:33', '$2y$12$Rep4sY0z235RbU1Bt3lkEOjKcqMweVVrcToRTezgHQcDeTNc2I9yG', 'JQCKO3bb8m', '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(26, 'Inactive User', 'inactive@company.com', 'employee', 0, NULL, '2026-05-27 23:57:33', '$2y$12$Rep4sY0z235RbU1Bt3lkEOjKcqMweVVrcToRTezgHQcDeTNc2I9yG', '5uLTTL9Gfa', '2026-05-27 23:57:33', '2026-05-27 23:57:33'),
(27, 'Vincent Vincent', 'vincent@company.com', 'employee', 1, NULL, NULL, '$2y$12$9xUuQP88Wk.C2oq.20K6/uTW6fZa3XYLVtaVNrqsyBka91GErq59G', NULL, '2026-05-28 23:41:51', '2026-05-29 00:03:54');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `allowances`
--
ALTER TABLE `allowances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `allowances_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `attendances_employee_id_month_year_unique` (`employee_id`,`month`,`year`);

--
-- Indexes for table `benefits`
--
ALTER TABLE `benefits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `benefits_employee_id_foreign` (`employee_id`);

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
-- Indexes for table `payroll_adjustments`
--
ALTER TABLE `payroll_adjustments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payroll_adjustments_payroll_input_id_foreign` (`payroll_input_id`);

--
-- Indexes for table `payroll_inputs`
--
ALTER TABLE `payroll_inputs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payroll_inputs_payroll_period_id_employee_id_unique` (`payroll_period_id`,`employee_id`),
  ADD KEY `payroll_inputs_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `payroll_periods`
--
ALTER TABLE `payroll_periods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payroll_periods_created_by_foreign` (`created_by`);

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
-- AUTO_INCREMENT for table `allowances`
--
ALTER TABLE `allowances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `benefits`
--
ALTER TABLE `benefits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `payroll_adjustments`
--
ALTER TABLE `payroll_adjustments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll_inputs`
--
ALTER TABLE `payroll_inputs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payroll_periods`
--
ALTER TABLE `payroll_periods`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `allowances`
--
ALTER TABLE `allowances`
  ADD CONSTRAINT `allowances_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attendances`
--
ALTER TABLE `attendances`
  ADD CONSTRAINT `attendances_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `benefits`
--
ALTER TABLE `benefits`
  ADD CONSTRAINT `benefits_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payroll_adjustments`
--
ALTER TABLE `payroll_adjustments`
  ADD CONSTRAINT `payroll_adjustments_payroll_input_id_foreign` FOREIGN KEY (`payroll_input_id`) REFERENCES `payroll_inputs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payroll_inputs`
--
ALTER TABLE `payroll_inputs`
  ADD CONSTRAINT `payroll_inputs_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `payroll_inputs_payroll_period_id_foreign` FOREIGN KEY (`payroll_period_id`) REFERENCES `payroll_periods` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payroll_periods`
--
ALTER TABLE `payroll_periods`
  ADD CONSTRAINT `payroll_periods_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
