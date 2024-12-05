-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 03, 2024 at 02:45 PM
-- Server version: 10.6.18-MariaDB-0ubuntu0.22.04.1
-- PHP Version: 8.2.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `siakad`
--

-- --------------------------------------------------------

--
-- Table structure for table `level_wilayahs`
--

CREATE TABLE `level_wilayahs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_level_wilayah` int(11) NOT NULL,
  `nama_level_wilayah` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `level_wilayahs`
--

INSERT INTO `level_wilayahs` (`id`, `id_level_wilayah`, `nama_level_wilayah`, `created_at`, `updated_at`) VALUES
(1, 2, 'Kab / Kota', '2024-01-16 09:35:42', '2024-07-30 13:04:31'),
(2, 0, 'Negara', '2024-01-16 09:35:42', '2024-07-30 13:04:31'),
(3, 1, 'Propinsi', '2024-01-16 09:35:42', '2024-07-30 13:04:31'),
(4, 3, 'Kecamatan', '2024-01-16 09:35:42', '2024-07-30 13:04:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `level_wilayahs`
--
ALTER TABLE `level_wilayahs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `level_wilayahs_id_level_wilayah_unique` (`id_level_wilayah`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `level_wilayahs`
--
ALTER TABLE `level_wilayahs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
