-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 19, 2026 at 08:26 AM
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
-- Database: `db_parkir`
--
CREATE DATABASE IF NOT EXISTS `db_parkir` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `db_parkir`;

-- --------------------------------------------------------

--
-- Table structure for table `tb_area_parkir`
--

DROP TABLE IF EXISTS `tb_area_parkir`;
CREATE TABLE `tb_area_parkir` (
  `id_area` int(11) NOT NULL,
  `nama_area` varchar(50) NOT NULL,
  `kapasitas` int(5) NOT NULL,
  `terisi` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_area_parkir`
--

INSERT INTO `tb_area_parkir` (`id_area`, `nama_area`, `kapasitas`, `terisi`) VALUES
(1, 'Area A - Motor', 50, 2),
(2, 'Area B - Mobil', 30, 2),
(3, 'Area C - Kendaraan berat', 30, 1);

-- --------------------------------------------------------

--
-- Table structure for table `tb_kendaraan`
--

DROP TABLE IF EXISTS `tb_kendaraan`;
CREATE TABLE `tb_kendaraan` (
  `id_kendaraan` int(11) NOT NULL,
  `plat_nomor` varchar(15) NOT NULL,
  `jenis_kendaraan` varchar(20) NOT NULL,
  `warna` varchar(20) DEFAULT NULL,
  `pemilik` varchar(100) DEFAULT NULL,
  `id_user` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_kendaraan`
--

INSERT INTO `tb_kendaraan` (`id_kendaraan`, `plat_nomor`, `jenis_kendaraan`, `warna`, `pemilik`, `id_user`) VALUES
(1, 'M 1234 AB', 'motor', 'Hitam', 'Andi', 2),
(2, 'M 5678 CD', 'mobil', 'Putih', 'Rina', 2),
(3, 'BH 7869 N', 'motor', 'Kuning', 'Andara', 2),
(4, 'A 5690 JK', 'mobil', 'MERAHHHHH', 'MARAAAAAAAAAAAAAAA', 2),
(5, 'B 8972 NI', 'motor', 'Biruuu', 'Amanda Manopo', 2),
(6, 'KL 6765 HJ', 'lainnya', 'Kuning', 'Haji Ahmaddahlan', 2),
(7, 'L 9874 UI', 'mobil', 'Cyan muda', 'Putri jalan pepaya', 2);

-- --------------------------------------------------------

--
-- Table structure for table `tb_laporan`
--

DROP TABLE IF EXISTS `tb_laporan`;
CREATE TABLE `tb_laporan` (
  `id_laporan` int(11) NOT NULL,
  `id_kendaraan` int(11) NOT NULL,
  `id_petugas` int(11) NOT NULL,
  `data_lama` text NOT NULL,
  `data_baru` text NOT NULL,
  `pesan_tambahan` text DEFAULT NULL,
  `status` enum('pending','selesai') DEFAULT 'pending',
  `waktu_lapor` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_laporan`
--

INSERT INTO `tb_laporan` (`id_laporan`, `id_kendaraan`, `id_petugas`, `data_lama`, `data_baru`, `pesan_tambahan`, `status`, `waktu_lapor`) VALUES
(1, 7, 2, 'Plat: L 9874 UI, Jenis: mobil, Warna: Cyan, Pemilik: Putri jalan pepaya', 'Plat: L 9874 UI, Jenis: mobil, Warna: Cyan muda, Pemilik: Putri jalan pepaya', 'warna yang sebelumnya cyan tolong di benarkan menjadi cyan muda\r\n', 'selesai', '2026-10-19 13:21:47');

-- --------------------------------------------------------

--
-- Table structure for table `tb_log_aktivitas`
--

DROP TABLE IF EXISTS `tb_log_aktivitas`;
CREATE TABLE `tb_log_aktivitas` (
  `id_log` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `aktivitas` varchar(100) NOT NULL,
  `waktu_aktivitas` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_log_aktivitas`
--

INSERT INTO `tb_log_aktivitas` (`id_log`, `id_user`, `aktivitas`, `waktu_aktivitas`) VALUES
(1, 1, 'Login ke sistem', '2026-10-19 09:18:32'),
(2, 2, 'Login ke sistem', '2026-10-19 09:20:20'),
(3, 1, 'Login ke sistem', '2026-10-19 09:26:28'),
(4, 2, 'Login ke sistem', '2026-10-19 09:27:58'),
(5, 1, 'Login ke sistem', '2026-10-19 09:31:54'),
(6, 1, 'Login ke sistem', '2026-10-19 09:37:48'),
(7, 1, 'Login ke sistem', '2026-10-19 09:42:12'),
(8, 2, 'Login ke sistem', '2026-10-19 09:49:37'),
(9, 1, 'Login ke sistem', '2026-10-19 09:50:07'),
(10, 1, 'Login ke sistem', '2026-10-19 09:54:23'),
(11, 2, 'Login ke sistem', '2026-10-19 10:09:22'),
(12, 1, 'Login ke sistem', '2026-10-19 10:38:32'),
(13, 2, 'Login ke sistem', '2026-10-19 10:40:58'),
(14, 2, 'Login ke sistem', '2026-10-19 10:44:43'),
(15, 2, 'Kendaraan masuk (BH 7869 N) di Area ID: 2', '2026-10-19 10:48:49'),
(16, 2, 'Kendaraan masuk (A 5690 JK) di Area ID: 1', '2026-10-19 10:55:44'),
(17, 1, 'Login ke sistem', '2026-10-19 10:56:09'),
(18, 1, 'Proses keluar ID Parkir: 2, Biaya: Rp 2000', '2026-10-19 10:57:24'),
(19, 1, 'Login ke sistem', '2026-10-19 11:01:21'),
(20, 2, 'Login ke sistem', '2026-10-19 11:13:29'),
(21, 2, 'Proses keluar A 5690 JK, Biaya: Rp 5000', '2026-10-19 11:13:41'),
(22, 2, 'Kendaraan masuk (A 5690 JK) di Area ID: 1', '2026-10-19 11:14:01'),
(23, 2, 'Kendaraan masuk (B 8972 NI) di Area ID: 2', '2026-10-19 11:14:56'),
(24, 1, 'Login ke sistem', '2026-10-19 11:15:10'),
(25, 1, 'Login ke sistem', '2026-10-19 11:29:53'),
(26, 1, 'Login ke sistem', '2026-10-19 11:31:11'),
(27, 1, 'Login ke sistem', '2026-10-19 11:33:55'),
(28, 1, 'Login ke sistem', '2026-10-19 11:50:16'),
(29, 2, 'Login ke sistem', '2026-10-19 12:44:20'),
(30, 1, 'Login ke sistem', '2026-10-19 12:55:30'),
(31, 1, 'Login ke sistem', '2026-10-19 12:59:27'),
(32, 2, 'Login ke sistem', '2026-10-19 12:59:42'),
(33, 2, 'Kendaraan masuk (KL 6765 HJ) di Area ID: 3', '2026-10-19 13:00:44'),
(34, 2, 'Kendaraan masuk (L 9874 UI) di Area ID: 2', '2026-10-19 13:04:52'),
(35, 1, 'Login ke sistem', '2026-10-19 13:05:25'),
(36, 1, 'Proses keluar L 9874 UI, Biaya: Rp 5000', '2026-10-19 13:08:17'),
(37, 1, 'Kendaraan masuk (L 9874 UI) di Area ID: 2', '2026-10-19 13:08:35'),
(38, 2, 'Login ke sistem', '2026-10-19 13:13:21'),
(39, 1, 'Login ke sistem', '2026-10-19 13:22:00');

-- --------------------------------------------------------

--
-- Table structure for table `tb_tarif`
--

DROP TABLE IF EXISTS `tb_tarif`;
CREATE TABLE `tb_tarif` (
  `id_tarif` int(11) NOT NULL,
  `jenis_kendaraan` enum('motor','mobil','lainnya') NOT NULL,
  `tarif_per_jam` decimal(10,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_tarif`
--

INSERT INTO `tb_tarif` (`id_tarif`, `jenis_kendaraan`, `tarif_per_jam`) VALUES
(1, 'motor', 2000),
(2, 'mobil', 5000),
(3, 'lainnya', 8000);

-- --------------------------------------------------------

--
-- Table structure for table `tb_transaksi`
--

DROP TABLE IF EXISTS `tb_transaksi`;
CREATE TABLE `tb_transaksi` (
  `id_parkir` int(11) NOT NULL,
  `id_kendaraan` int(11) NOT NULL,
  `waktu_masuk` datetime NOT NULL,
  `waktu_keluar` datetime DEFAULT NULL,
  `id_tarif` int(11) NOT NULL,
  `durasi_jam` int(5) DEFAULT NULL,
  `biaya_total` decimal(10,0) DEFAULT NULL,
  `status` enum('masuk','keluar') NOT NULL DEFAULT 'masuk',
  `id_user` int(11) NOT NULL,
  `id_area` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_transaksi`
--

INSERT INTO `tb_transaksi` (`id_parkir`, `id_kendaraan`, `waktu_masuk`, `waktu_keluar`, `id_tarif`, `durasi_jam`, `biaya_total`, `status`, `id_user`, `id_area`) VALUES
(1, 1, '2026-10-19 08:38:47', NULL, 1, NULL, NULL, 'masuk', 2, 1),
(2, 3, '2026-10-19 05:48:49', '2026-10-19 05:57:24', 1, 1, 2000, 'keluar', 2, 2),
(3, 4, '2026-10-19 05:55:44', '2026-10-19 06:13:41', 2, 1, 5000, 'keluar', 2, 1),
(4, 4, '2026-10-19 06:14:01', NULL, 1, NULL, NULL, 'masuk', 2, 1),
(5, 5, '2026-10-19 06:14:56', NULL, 2, NULL, NULL, 'masuk', 2, 2),
(6, 6, '2026-10-19 08:00:44', NULL, 3, NULL, NULL, 'masuk', 2, 3),
(7, 7, '2026-10-19 13:04:52', '2026-10-19 13:08:17', 2, 1, 5000, 'keluar', 2, 2),
(8, 7, '2026-10-19 13:08:35', NULL, 1, NULL, NULL, 'masuk', 1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `tb_user`
--

DROP TABLE IF EXISTS `tb_user`;
CREATE TABLE `tb_user` (
  `id_user` int(11) NOT NULL,
  `nama_lengkap` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `role` enum('admin','petugas','owner') NOT NULL,
  `status_aktif` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_user`
--

INSERT INTO `tb_user` (`id_user`, `nama_lengkap`, `username`, `password`, `role`, `status_aktif`) VALUES
(1, 'Budi Santoso', 'admin1', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', 'admin', 1),
(2, 'Siti Aminah', 'petugas1', '2dad904f71aa0dcf6ea1addaa084a5865ffe448e4d3f900668e1cc7e7b6153d7', 'petugas', 1),
(3, 'Pak Hendra', 'owner1', '43a0d17178a9d26c9e0fe9a74b0b45e38d32f27aed887a008a54bf6e033bf7b9', 'owner', 1);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_rekap_transaksi`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `v_rekap_transaksi`;
CREATE TABLE `v_rekap_transaksi` (
`id_parkir` int(11)
,`plat_nomor` varchar(15)
,`jenis_kendaraan` varchar(20)
,`nama_area` varchar(50)
,`waktu_masuk` datetime
,`waktu_keluar` datetime
,`durasi_jam` int(5)
,`biaya_total` decimal(10,0)
,`status` enum('masuk','keluar')
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_status_area`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `v_status_area`;
CREATE TABLE `v_status_area` (
`id_area` int(11)
,`nama_area` varchar(50)
,`kapasitas` int(5)
,`terisi` int(5)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_total_kendaraan`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `v_total_kendaraan`;
CREATE TABLE `v_total_kendaraan` (
`total` bigint(21)
);

-- --------------------------------------------------------

--
-- Structure for view `v_rekap_transaksi`
--
DROP TABLE IF EXISTS `v_rekap_transaksi`;

DROP VIEW IF EXISTS `v_rekap_transaksi`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_rekap_transaksi`  AS SELECT `t`.`id_parkir` AS `id_parkir`, `k`.`plat_nomor` AS `plat_nomor`, `k`.`jenis_kendaraan` AS `jenis_kendaraan`, `a`.`nama_area` AS `nama_area`, `t`.`waktu_masuk` AS `waktu_masuk`, `t`.`waktu_keluar` AS `waktu_keluar`, `t`.`durasi_jam` AS `durasi_jam`, `t`.`biaya_total` AS `biaya_total`, `t`.`status` AS `status` FROM ((`tb_transaksi` `t` join `tb_kendaraan` `k` on(`k`.`id_kendaraan` = `t`.`id_kendaraan`)) join `tb_area_parkir` `a` on(`a`.`id_area` = `t`.`id_area`)) ;

-- --------------------------------------------------------

--
-- Structure for view `v_status_area`
--
DROP TABLE IF EXISTS `v_status_area`;

DROP VIEW IF EXISTS `v_status_area`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_status_area`  AS SELECT `tb_area_parkir`.`id_area` AS `id_area`, `tb_area_parkir`.`nama_area` AS `nama_area`, `tb_area_parkir`.`kapasitas` AS `kapasitas`, `tb_area_parkir`.`terisi` AS `terisi` FROM `tb_area_parkir` ;

-- --------------------------------------------------------

--
-- Structure for view `v_total_kendaraan`
--
DROP TABLE IF EXISTS `v_total_kendaraan`;

DROP VIEW IF EXISTS `v_total_kendaraan`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_total_kendaraan`  AS SELECT count(0) AS `total` FROM `tb_kendaraan` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tb_area_parkir`
--
ALTER TABLE `tb_area_parkir`
  ADD PRIMARY KEY (`id_area`);

--
-- Indexes for table `tb_kendaraan`
--
ALTER TABLE `tb_kendaraan`
  ADD PRIMARY KEY (`id_kendaraan`),
  ADD KEY `tb_kendaraan_id_user_foreign` (`id_user`);

--
-- Indexes for table `tb_laporan`
--
ALTER TABLE `tb_laporan`
  ADD PRIMARY KEY (`id_laporan`),
  ADD KEY `fk_lapor_kendaraan` (`id_kendaraan`),
  ADD KEY `fk_lapor_user` (`id_petugas`);

--
-- Indexes for table `tb_log_aktivitas`
--
ALTER TABLE `tb_log_aktivitas`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `tb_log_aktivitas_id_user_foreign` (`id_user`);

--
-- Indexes for table `tb_tarif`
--
ALTER TABLE `tb_tarif`
  ADD PRIMARY KEY (`id_tarif`);

--
-- Indexes for table `tb_transaksi`
--
ALTER TABLE `tb_transaksi`
  ADD PRIMARY KEY (`id_parkir`),
  ADD KEY `tb_transaksi_id_tarif_foreign` (`id_tarif`),
  ADD KEY `tb_transaksi_id_area_foreign` (`id_area`),
  ADD KEY `tb_transaksi_id_kendaraan_foreign` (`id_kendaraan`),
  ADD KEY `tb_transaksi_id_user_foreign` (`id_user`);

--
-- Indexes for table `tb_user`
--
ALTER TABLE `tb_user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `tb_user_username_unique` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tb_area_parkir`
--
ALTER TABLE `tb_area_parkir`
  MODIFY `id_area` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tb_kendaraan`
--
ALTER TABLE `tb_kendaraan`
  MODIFY `id_kendaraan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tb_laporan`
--
ALTER TABLE `tb_laporan`
  MODIFY `id_laporan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tb_log_aktivitas`
--
ALTER TABLE `tb_log_aktivitas`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `tb_tarif`
--
ALTER TABLE `tb_tarif`
  MODIFY `id_tarif` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tb_transaksi`
--
ALTER TABLE `tb_transaksi`
  MODIFY `id_parkir` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tb_user`
--
ALTER TABLE `tb_user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tb_kendaraan`
--
ALTER TABLE `tb_kendaraan`
  ADD CONSTRAINT `tb_kendaraan_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `tb_user` (`id_user`);

--
-- Constraints for table `tb_laporan`
--
ALTER TABLE `tb_laporan`
  ADD CONSTRAINT `fk_lapor_kendaraan` FOREIGN KEY (`id_kendaraan`) REFERENCES `tb_kendaraan` (`id_kendaraan`),
  ADD CONSTRAINT `fk_lapor_user` FOREIGN KEY (`id_petugas`) REFERENCES `tb_user` (`id_user`);

--
-- Constraints for table `tb_log_aktivitas`
--
ALTER TABLE `tb_log_aktivitas`
  ADD CONSTRAINT `tb_log_aktivitas_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `tb_user` (`id_user`);

--
-- Constraints for table `tb_transaksi`
--
ALTER TABLE `tb_transaksi`
  ADD CONSTRAINT `tb_transaksi_id_area_foreign` FOREIGN KEY (`id_area`) REFERENCES `tb_area_parkir` (`id_area`),
  ADD CONSTRAINT `tb_transaksi_id_kendaraan_foreign` FOREIGN KEY (`id_kendaraan`) REFERENCES `tb_kendaraan` (`id_kendaraan`),
  ADD CONSTRAINT `tb_transaksi_id_tarif_foreign` FOREIGN KEY (`id_tarif`) REFERENCES `tb_tarif` (`id_tarif`),
  ADD CONSTRAINT `tb_transaksi_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `tb_user` (`id_user`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
