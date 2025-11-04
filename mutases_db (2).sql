-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 04, 2025 at 09:08 PM
-- Server version: 5.7.33
-- PHP Version: 5.6.40

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mutases_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `guru`
--

CREATE TABLE `guru` (
  `id` int(11) NOT NULL,
  `nip` varchar(50) DEFAULT NULL,
  `nama` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `telp` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `guru`
--

INSERT INTO `guru` (`id`, `nip`, `nama`, `email`, `telp`, `created_at`) VALUES
(1, '19800101', 'Budi Santoso', 'budi@sekolah.sch.id', '081234567890', '2025-11-04 09:17:24'),
(2, '19820202', 'Siti Aminah', 'siti@sekolah.sch.id', '082345678901', '2025-11-04 09:17:24'),
(3, '12121', 'Muhamad Nazmudin', 'nazmudin@gmail.com', '0865165526', '2025-11-04 09:21:41');

-- --------------------------------------------------------

--
-- Table structure for table `kelas`
--

CREATE TABLE `kelas` (
  `id` int(11) NOT NULL,
  `nama` varchar(50) NOT NULL,
  `wali_kelas_id` int(11) DEFAULT NULL,
  `kapasitas` int(11) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `kelas`
--

INSERT INTO `kelas` (`id`, `nama`, `wali_kelas_id`, `kapasitas`, `created_at`) VALUES
(1, 'VII A', 1, 30, '2025-11-04 08:14:57'),
(2, 'VII B', 2, 30, '2025-11-04 08:14:57'),
(3, 'X KL1', 3, 36, '2025-11-04 09:31:18');

-- --------------------------------------------------------

--
-- Table structure for table `mutasi`
--

CREATE TABLE `mutasi` (
  `id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `jenis` enum('keluar','masuk') NOT NULL,
  `tanggal` date NOT NULL,
  `alasan` text,
  `tujuan_kelas_id` int(11) DEFAULT NULL,
  `tujuan_sekolah` varchar(255) DEFAULT NULL,
  `tahun_id` int(11) NOT NULL,
  `berkas` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `mutasi`
--

INSERT INTO `mutasi` (`id`, `siswa_id`, `jenis`, `tanggal`, `alasan`, `tujuan_kelas_id`, `tujuan_sekolah`, `tahun_id`, `berkas`, `created_by`, `created_at`) VALUES
(3, 3, 'keluar', '2025-11-04', 'kerja merantau', NULL, NULL, 2, 'abe0348811f8a67dd62c1488fcdf4d57.pdf', 1, '2025-11-04 12:01:14'),
(4, 1, 'keluar', '2025-11-04', 'gatauuuuuu', NULL, NULL, 2, '2d9d506699d6216c14f8781414cf8321.pdf', 1, '2025-11-04 12:21:18');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'admin', 'Administrator Sekolah', '2025-11-04 08:14:57'),
(2, 'kesiswaan', 'Guru / Staf Kesiswaan', '2025-11-04 08:14:57');

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `id` int(11) NOT NULL,
  `nis` varchar(50) DEFAULT NULL,
  `nama` varchar(150) NOT NULL,
  `jk` enum('L','P') DEFAULT 'L',
  `agama` varchar(50) DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tgl_lahir` date DEFAULT NULL,
  `alamat` text,
  `id_kelas` int(11) DEFAULT NULL,
  `tahun_id` int(11) NOT NULL,
  `status` enum('aktif','mutasi_keluar','mutasi_masuk','lulus','keluar') DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `siswa`
--

INSERT INTO `siswa` (`id`, `nis`, `nama`, `jk`, `agama`, `tempat_lahir`, `tgl_lahir`, `alamat`, `id_kelas`, `tahun_id`, `status`, `created_at`) VALUES
(1, '1001', 'Andi Pratama', 'L', NULL, 'Bandung', '2012-01-01', 'Jl. Melati No.1', 1, 2, 'keluar', '2025-11-04 08:14:58'),
(2, '1002', 'Dewi Lestari', 'P', NULL, 'Cimahi', '2011-03-10', 'Jl. Mawar No.2', 2, 2, 'aktif', '2025-11-04 08:14:58'),
(3, '11990', 'Muhammad Zaini Nazra', 'L', 'Islam', 'Kuningan', '2008-01-01', 'Danalampah', 3, 2, 'keluar', '2025-11-04 09:47:46');

-- --------------------------------------------------------

--
-- Table structure for table `tahun_ajaran`
--

CREATE TABLE `tahun_ajaran` (
  `id` int(11) NOT NULL,
  `tahun` varchar(20) NOT NULL,
  `aktif` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tahun_ajaran`
--

INSERT INTO `tahun_ajaran` (`id`, `tahun`, `aktif`, `created_at`) VALUES
(1, '2024/2025', 0, '2025-11-04 08:14:57'),
(2, '2025/2026', 1, '2025-11-04 08:14:57');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(150) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `role_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama`, `email`, `role_id`, `created_at`) VALUES
(1, 'admin', '$2y$10$1XDEFn8/aM7A2bx.0NYJj./FhdERVwcu8RQi8pQ2xF8CthdauZofS', 'Administrator', 'admin@mutases.local', 1, '2025-11-04 08:14:57'),
(2, 'kesiswaan', '$2y$10$wF3mOG0C9p7.SFZuh4TSOOYdAbdRrfqKteA1E2ShQJSul8s1VmO8C', 'Staf Kesiswaan', 'kesiswaan@mutases.local', 2, '2025-11-04 08:14:57'),
(3, 'nazmudin', '$2y$10$Nvx0DYHEr6WPpPTSHqIaduiyBVZpGXmhKtBonXFhU/FvZSG6m5opm', 'Muhamad Nazmudin', 'apaja@gmail.com', 1, '2025-11-04 14:04:04');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_mutasi_detail`
-- (See below for the actual view)
--
CREATE TABLE `v_mutasi_detail` (
`id` int(11)
,`nis` varchar(50)
,`nama_siswa` varchar(150)
,`id_kelas` int(11)
,`kelas_asal` varchar(50)
,`jenis` enum('keluar','masuk')
,`tanggal` date
,`alasan` text
,`tujuan_kelas_id` int(11)
,`kelas_tujuan` varchar(50)
,`tujuan_sekolah` varchar(255)
,`tahun_ajaran` varchar(20)
,`dibuat_oleh` varchar(150)
);

-- --------------------------------------------------------

--
-- Structure for view `v_mutasi_detail`
--
DROP TABLE IF EXISTS `v_mutasi_detail`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_mutasi_detail`  AS  select `m`.`id` AS `id`,`s`.`nis` AS `nis`,`s`.`nama` AS `nama_siswa`,`s`.`id_kelas` AS `id_kelas`,`k_asal`.`nama` AS `kelas_asal`,`m`.`jenis` AS `jenis`,`m`.`tanggal` AS `tanggal`,`m`.`alasan` AS `alasan`,`m`.`tujuan_kelas_id` AS `tujuan_kelas_id`,`k_tujuan`.`nama` AS `kelas_tujuan`,`m`.`tujuan_sekolah` AS `tujuan_sekolah`,`t`.`tahun` AS `tahun_ajaran`,`u`.`nama` AS `dibuat_oleh` from (((((`mutasi` `m` left join `siswa` `s` on((`s`.`id` = `m`.`siswa_id`))) left join `kelas` `k_asal` on((`k_asal`.`id` = `s`.`id_kelas`))) left join `kelas` `k_tujuan` on((`k_tujuan`.`id` = `m`.`tujuan_kelas_id`))) left join `tahun_ajaran` `t` on((`t`.`id` = `m`.`tahun_id`))) left join `users` `u` on((`u`.`id` = `m`.`created_by`))) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `guru`
--
ALTER TABLE `guru`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kelas`
--
ALTER TABLE `kelas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wali_kelas_id` (`wali_kelas_id`);

--
-- Indexes for table `mutasi`
--
ALTER TABLE `mutasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `siswa_id` (`siswa_id`),
  ADD KEY `tujuan_kelas_id` (`tujuan_kelas_id`),
  ADD KEY `tahun_id` (`tahun_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nis` (`nis`),
  ADD KEY `id_kelas` (`id_kelas`),
  ADD KEY `tahun_id` (`tahun_id`);

--
-- Indexes for table `tahun_ajaran`
--
ALTER TABLE `tahun_ajaran`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `guru`
--
ALTER TABLE `guru`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `kelas`
--
ALTER TABLE `kelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `mutasi`
--
ALTER TABLE `mutasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tahun_ajaran`
--
ALTER TABLE `tahun_ajaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `kelas`
--
ALTER TABLE `kelas`
  ADD CONSTRAINT `kelas_ibfk_1` FOREIGN KEY (`wali_kelas_id`) REFERENCES `guru` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `mutasi`
--
ALTER TABLE `mutasi`
  ADD CONSTRAINT `mutasi_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mutasi_ibfk_2` FOREIGN KEY (`tujuan_kelas_id`) REFERENCES `kelas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `mutasi_ibfk_3` FOREIGN KEY (`tahun_id`) REFERENCES `tahun_ajaran` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mutasi_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `siswa`
--
ALTER TABLE `siswa`
  ADD CONSTRAINT `siswa_ibfk_1` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `siswa_ibfk_2` FOREIGN KEY (`tahun_id`) REFERENCES `tahun_ajaran` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
