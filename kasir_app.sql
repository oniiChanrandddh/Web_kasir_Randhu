-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 11, 2025 at 05:07 PM
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
-- Database: `kasir_app`
--

-- --------------------------------------------------------

--
-- Table structure for table `barang`
--

CREATE TABLE `barang` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `harga` int(11) NOT NULL,
  `stok` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barang`
--

INSERT INTO `barang` (`id`, `nama`, `harga`, `stok`) VALUES
(26, 'Air Mineral - 330ml', 3000, 60),
(27, 'Air Mineral - 500ml', 5000, 90),
(28, 'Beras - 5kg', 75000, 20),
(29, 'Biskuit - 100g', 8000, 40),
(30, 'Cokelat Batang - 50g', 7000, 70),
(31, 'Gula Pasir - 2kg', 28000, 40),
(32, 'Kopi Instan - 50g', 15000, 30),
(33, 'Mentega - 250g', 25000, 25),
(34, 'Mie Instan - 70g', 3500, 80),
(35, 'Mie Kering - 500g', 12000, 40),
(36, 'Minyak Goreng - 2L', 45000, 20),
(37, 'Nasi Goreng - 250g', 12500, 50),
(38, 'Roti Manis - 100g', 12000, 35),
(39, 'Roti Tawar - 500g', 13000, 35),
(40, 'Sari Kacang - 250g', 25000, 40),
(41, 'Saus Sambal - 200ml', 12000, 50),
(42, 'Saus Tomat - 200ml', 10000, 40),
(43, 'Snack Kentang - 150g', 12000, 60),
(44, 'Snack Singkong - 150g', 11000, 60),
(45, 'Susu Kental - 370g', 12000, 50),
(46, 'Susu UHT - 200ml', 8500, 40),
(47, 'Teh Celup - 25pcs', 13000, 60),
(48, 'Telur Ayam - 12pcs', 24000, 60),
(49, 'Tepung Maizena - 500g', 12000, 50),
(50, 'Keju Cheddar - 250g', 27000, 30);

-- --------------------------------------------------------

--
-- Table structure for table `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id` int(11) NOT NULL,
  `transaksi_id` int(11) DEFAULT NULL,
  `barang_id` int(11) DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL,
  `subtotal` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id`, `transaksi_id`, `barang_id`, `jumlah`, `subtotal`) VALUES
(13, 12, 3, 30, 360000),
(14, 12, 22, 5, 375000),
(15, 13, 1, 12, 36000),
(16, 14, 2, 10, 50000),
(17, 15, 3, 15, 162000),
(18, 16, 12, 5, 225000),
(19, 17, 18, 40, 320000),
(20, 17, 10, 20, 70000),
(21, 18, 16, 25, 375000),
(22, 19, 3, 5, 60000),
(23, 20, 19, 20, 500000),
(24, 21, 24, 10, 100000),
(25, 22, 13, 2, 50000),
(26, 23, 3, 10, 120000),
(27, 24, 1, 10, 27000),
(28, 25, 1, 2, 5400),
(29, 26, 26, 20, 51000),
(30, 27, 26, 20, 51000),
(31, 28, 36, 5, 191250);

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id` int(11) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `no_hp` varchar(25) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `diskon_member` decimal(5,2) NOT NULL DEFAULT 10.00,
  `level` enum('Bronze','Silver','Gold','Platinum') DEFAULT NULL,
  `total_transaksi` int(11) NOT NULL DEFAULT 0,
  `total_spent` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tanggal_daftar` datetime NOT NULL DEFAULT current_timestamp(),
  `last_transaction` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `nama`, `no_hp`, `alamat`, `diskon_member`, `level`, `total_transaksi`, `total_spent`, `tanggal_daftar`, `last_transaction`) VALUES
(10, 'Randhu', '08123456789', NULL, 15.00, 'Platinum', 3, 293250.00, '2025-10-29 18:57:39', '2025-10-30 21:28:51');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int(11) NOT NULL,
  `tanggal` datetime DEFAULT current_timestamp(),
  `kasir_id` int(11) DEFAULT NULL,
  `member_id` int(11) DEFAULT NULL,
  `is_member` tinyint(1) DEFAULT 0,
  `total` int(11) DEFAULT NULL,
  `diskon` int(11) DEFAULT 0,
  `total_akhir` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id`, `tanggal`, `kasir_id`, `member_id`, `is_member`, `total`, `diskon`, `total_akhir`) VALUES
(1, '2025-10-01 08:49:18', 2, NULL, 0, 15000, 0, 0),
(2, '2025-10-01 08:50:15', 2, NULL, 0, 15000, 0, 0),
(3, '2025-10-01 08:50:49', 2, NULL, 0, 15000, 0, 0),
(4, '2025-10-01 08:52:30', 2, NULL, 0, 15000, 0, 0),
(5, '2025-10-01 08:54:21', 2, NULL, 0, 30000, 0, 0),
(6, '2025-10-01 09:00:57', 2, NULL, 0, 30000, 0, 0),
(7, '2025-10-01 09:05:01', 2, NULL, 0, 220000, 0, 0),
(8, '2025-10-01 09:07:02', 2, NULL, 0, 10000, 0, 0),
(9, '2025-10-01 09:43:08', 2, NULL, 0, 100000, 0, 0),
(10, '2025-10-01 12:40:20', 2, NULL, 0, 2418000, 0, 0),
(11, '2025-10-16 15:40:20', 2, NULL, 0, 150000, 0, 0),
(12, '2025-10-23 22:14:53', 2, NULL, 0, 735000, 0, 0),
(13, '2025-10-23 22:47:04', 2, 8, 1, 36000, 5400, 30600),
(14, '2025-10-23 22:47:28', 2, 6, 1, 50000, 5000, 45000),
(15, '2025-10-23 22:58:06', 2, 6, 1, 180000, 18000, 162000),
(16, '2025-10-23 22:59:09', 2, 6, 1, 225000, 22500, 202500),
(17, '2025-10-23 23:03:39', 2, 6, 1, 390000, 39000, 351000),
(18, '2025-10-23 23:04:13', 2, 6, 1, 375000, 37500, 337500),
(19, '2025-10-23 23:04:40', 2, 6, 1, 60000, 6000, 54000),
(20, '2025-10-23 23:05:10', 2, 6, 1, 500000, 50000, 450000),
(21, '2025-10-23 23:05:46', 2, 6, 1, 100000, 10000, 90000),
(22, '2025-10-23 23:06:28', 2, 6, 1, 50000, 5000, 45000),
(23, '2025-10-26 19:58:37', 2, 9, 1, 120000, 12000, 108000),
(24, '2025-10-28 20:21:08', 2, 9, 1, 30000, 3000, 27000),
(25, '2025-10-28 20:22:40', 2, 9, 1, 6000, 600, 5400),
(26, '2025-10-29 21:37:10', 2, 10, 1, 60000, 9000, 51000),
(27, '2025-10-29 21:38:44', 2, 10, 1, 60000, 9000, 51000),
(28, '2025-10-30 21:28:51', 2, 10, 1, 225000, 33750, 191250);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','kasir') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'Admin', '$2y$10$OqaME.Yqc0pe70U3pIox0ursgeMOS06jMrgUsKQnql94ltG7Du5Oa', 'admin'),
(2, 'Kasir', '$2y$10$0dFcSpgra98vOY7OMoeF3e/KDnwPVNnxEynWlH2T0Rc1FzFEYmYqG', 'kasir');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_members_no_hp` (`no_hp`),
  ADD KEY `idx_members_nama` (`nama`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `barang`
--
ALTER TABLE `barang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
