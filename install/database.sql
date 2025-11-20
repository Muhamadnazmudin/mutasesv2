-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 20, 2025 at 09:30 AM
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
-- Table structure for table `absensi`
--

CREATE TABLE `absensi` (
  `id_absensi` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `id_kelas` int(11) NOT NULL,
  `tahun_pelajaran` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dibuat_oleh` int(11) DEFAULT NULL,
  `dibuat_pada` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `absensi`
--


-- --------------------------------------------------------

--
-- Table structure for table `absensi_detail`
--

CREATE TABLE `absensi_detail` (
  `id_detail` int(11) NOT NULL,
  `id_absensi` int(11) NOT NULL,
  `id_siswa` int(11) NOT NULL,
  `status` enum('sakit','izin','alpa') COLLATE utf8mb4_unicode_ci NOT NULL,
  `keterangan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `absensi_detail`
--


-- --------------------------------------------------------

--
-- Table structure for table `absensi_jadwal`
--

CREATE TABLE `absensi_jadwal` (
  `id` int(11) NOT NULL,
  `hari` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jam_masuk` time NOT NULL,
  `jam_pulang` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `absensi_jadwal`
--

INSERT INTO `absensi_jadwal` (`id`, `hari`, `jam_masuk`, `jam_pulang`) VALUES
(1, 'Senin', '06:40:00', '15:00:00'),
(2, 'Selasa', '06:30:00', '14:30:00'),
(3, 'Rabu', '22:00:00', '23:00:00'),
(4, 'Kamis', '06:30:00', '14:30:00'),
(5, 'Jumat', '06:30:00', '14:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `absensi_qr`
--

CREATE TABLE `absensi_qr` (
  `id` int(11) NOT NULL,
  `nis` varchar(50) NOT NULL,
  `tanggal` date NOT NULL,
  `jam_masuk` time DEFAULT NULL,
  `jam_pulang` time DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Hadir',
  `kehadiran` enum('H','I','S','A') DEFAULT 'H',
  `sumber` varchar(50) DEFAULT 'scan_qr'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `absensi_qr`
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


-- --------------------------------------------------------

--
-- Table structure for table `hari_libur`
--

CREATE TABLE `hari_libur` (
  `id` int(11) NOT NULL,
  `nama` varchar(200) NOT NULL,
  `start` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `hari_libur`
--

INSERT INTO `hari_libur` (`id`, `nama`, `start`) VALUES
(1, 'Tahun Baru 2025', '2025-01-01'),
(2, 'Libur Sekolah', '2025-06-05'),
(3, 'liburrrrr', '2025-11-06');

-- --------------------------------------------------------

--
-- Table structure for table `izin_keluar`
--

CREATE TABLE `izin_keluar` (
  `id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `nis` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kelas_id` int(11) DEFAULT NULL,
  `kelas_nama` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keperluan` text COLLATE utf8mb4_unicode_ci,
  `estimasi_menit` int(11) DEFAULT NULL,
  `jam_keluar` datetime DEFAULT NULL,
  `jam_masuk` datetime DEFAULT NULL,
  `token_keluar` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token_kembali` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('keluar','kembali') COLLATE utf8mb4_unicode_ci DEFAULT 'keluar',
  `jenis_izin` enum('keluar','pulang') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'keluar',
  `id_guru_mapel` int(11) DEFAULT NULL,
  `id_piket` int(11) DEFAULT NULL,
  `id_walikelas` int(11) DEFAULT NULL,
  `ditujukan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `izin_keluar`
--

-- --------------------------------------------------------

--
-- Table structure for table `kelas`
--

CREATE TABLE `kelas` (
  `id` int(11) NOT NULL,
  `nama` varchar(50) NOT NULL,
  `wali_kelas_id` int(11) DEFAULT NULL,
  `kapasitas` int(11) DEFAULT '0',
  `download_count` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `kelas`
--


-- --------------------------------------------------------

--
-- Table structure for table `mutasi`
--

CREATE TABLE `mutasi` (
  `id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `kelas_asal_id` int(11) DEFAULT NULL,
  `jenis` enum('keluar','masuk') NOT NULL,
  `jenis_keluar` varchar(50) DEFAULT NULL,
  `tanggal` date NOT NULL,
  `alasan` text,
  `nohp_ortu` varchar(20) DEFAULT NULL,
  `tujuan_kelas_id` int(11) DEFAULT NULL,
  `tujuan_sekolah` varchar(255) DEFAULT NULL,
  `tahun_id` int(11) NOT NULL,
  `berkas` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status_mutasi` enum('aktif','dibatalkan') DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
  `nisn` varchar(50) DEFAULT NULL,
  `nik` varchar(50) DEFAULT NULL,
  `nama` varchar(150) NOT NULL,
  `jk` enum('L','P') DEFAULT 'L',
  `agama` varchar(50) DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tgl_lahir` date DEFAULT NULL,
  `alamat` text,
  `jalan` varchar(200) DEFAULT NULL,
  `rt` varchar(5) DEFAULT NULL,
  `rw` varchar(5) DEFAULT NULL,
  `dusun` varchar(100) DEFAULT NULL,
  `kecamatan` varchar(100) DEFAULT NULL,
  `kode_pos` varchar(10) DEFAULT NULL,
  `jenis_tinggal` varchar(50) DEFAULT NULL,
  `alat_transportasi` varchar(50) DEFAULT NULL,
  `telp` varchar(20) DEFAULT NULL,
  `hp` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `skhun` varchar(50) DEFAULT NULL,
  `penerima_kps` tinyint(1) DEFAULT '0',
  `no_kps` varchar(50) DEFAULT NULL,
  `nama_ayah` varchar(150) DEFAULT NULL,
  `tahun_lahir_ayah` varchar(4) DEFAULT NULL,
  `pendidikan_ayah` varchar(50) DEFAULT NULL,
  `pekerjaan_ayah` varchar(50) DEFAULT NULL,
  `penghasilan_ayah` varchar(50) DEFAULT NULL,
  `nik_ayah` varchar(50) DEFAULT NULL,
  `nama_ibu` varchar(150) DEFAULT NULL,
  `tahun_lahir_ibu` varchar(4) DEFAULT NULL,
  `pendidikan_ibu` varchar(50) DEFAULT NULL,
  `pekerjaan_ibu` varchar(50) DEFAULT NULL,
  `penghasilan_ibu` varchar(50) DEFAULT NULL,
  `nik_ibu` varchar(50) DEFAULT NULL,
  `nama_wali` varchar(150) DEFAULT NULL,
  `tahun_lahir_wali` varchar(4) DEFAULT NULL,
  `pendidikan_wali` varchar(50) DEFAULT NULL,
  `pekerjaan_wali` varchar(50) DEFAULT NULL,
  `penghasilan_wali` varchar(50) DEFAULT NULL,
  `nik_wali` varchar(50) DEFAULT NULL,
  `sekolah_asal` varchar(150) DEFAULT NULL,
  `hobi` varchar(50) DEFAULT NULL,
  `cita_cita` varchar(50) DEFAULT NULL,
  `anak_keberapa` int(11) DEFAULT NULL,
  `nomor_kk` varchar(50) DEFAULT NULL,
  `berat_badan` int(11) DEFAULT NULL,
  `tinggi_badan` int(11) DEFAULT NULL,
  `jumlah_saudara_kandung` int(11) DEFAULT NULL,
  `id_kelas` int(11) DEFAULT NULL,
  `tahun_id` int(11) NOT NULL,
  `status` enum('aktif','mutasi_keluar','mutasi_masuk','lulus','keluar') DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `password` varchar(255) DEFAULT NULL,
  `token_qr` varchar(200) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `siswa`
--

-- --------------------------------------------------------

--
-- Table structure for table `siswa_history`
--

CREATE TABLE `siswa_history` (
  `id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `kelas_id` int(11) NOT NULL,
  `tahun_id` int(11) NOT NULL,
  `status` enum('aktif','mutasi_masuk','mutasi_keluar','lulus') COLLATE utf8mb4_unicode_ci DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `siswa_tahun`
--

CREATE TABLE `siswa_tahun` (
  `id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `kelas_id` int(11) NOT NULL,
  `tahun_id` int(11) NOT NULL,
  `status` enum('aktif','lulus','mutasi_keluar','mutasi_masuk') DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `siswa_tahun`
--



-- --------------------------------------------------------

--
-- Table structure for table `system_config`
--

CREATE TABLE `system_config` (
  `id` int(11) NOT NULL,
  `config_key` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `config_value` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_config`
--



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
(2, 'kesiswaan', '$2y$10$wF3mOG0C9p7.SFZuh4TSOOYdAbdRrfqKteA1E2ShQJSul8s1VmO8C', 'Staf Kesiswaan', 'kesiswaan@mutases.local', 2, '2025-11-04 08:14:57');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_mutasi_detail`
-- (See below for the actual view)
--
CREATE TABLE `v_mutasi_detail` (
`id` int(11)
,`siswa_id` int(11)
,`nis` varchar(50)
,`nisn` varchar(50)
,`nama_siswa` varchar(150)
,`kelas_asal_id` int(11)
,`kelas_asal` varchar(50)
,`tujuan_kelas_id` int(11)
,`kelas_tujuan` varchar(50)
,`jenis` enum('keluar','masuk')
,`jenis_keluar` varchar(50)
,`alasan` text
,`nohp_ortu` varchar(20)
,`tujuan_sekolah` varchar(255)
,`tanggal` date
,`tahun_ajaran` varchar(20)
,`berkas` varchar(255)
,`status_mutasi` enum('aktif','dibatalkan')
,`dibuat_oleh` varchar(150)
);

-- --------------------------------------------------------

--
-- Structure for view `v_mutasi_detail`
--
DROP TABLE IF EXISTS `v_mutasi_detail`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_mutasi_detail`  AS  select `m`.`id` AS `id`,`m`.`siswa_id` AS `siswa_id`,`s`.`nis` AS `nis`,`s`.`nisn` AS `nisn`,`s`.`nama` AS `nama_siswa`,`s`.`id_kelas` AS `kelas_asal_id`,`k1`.`nama` AS `kelas_asal`,`m`.`tujuan_kelas_id` AS `tujuan_kelas_id`,`k2`.`nama` AS `kelas_tujuan`,`m`.`jenis` AS `jenis`,`m`.`jenis_keluar` AS `jenis_keluar`,`m`.`alasan` AS `alasan`,`m`.`nohp_ortu` AS `nohp_ortu`,`m`.`tujuan_sekolah` AS `tujuan_sekolah`,`m`.`tanggal` AS `tanggal`,`t`.`tahun` AS `tahun_ajaran`,`m`.`berkas` AS `berkas`,`m`.`status_mutasi` AS `status_mutasi`,`u`.`nama` AS `dibuat_oleh` from (((((`mutasi` `m` join `siswa` `s` on((`s`.`id` = `m`.`siswa_id`))) left join `kelas` `k1` on((`k1`.`id` = `s`.`id_kelas`))) left join `kelas` `k2` on((`k2`.`id` = `m`.`tujuan_kelas_id`))) left join `tahun_ajaran` `t` on((`t`.`id` = `m`.`tahun_id`))) left join `users` `u` on((`u`.`id` = `m`.`created_by`))) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`id_absensi`);

--
-- Indexes for table `absensi_detail`
--
ALTER TABLE `absensi_detail`
  ADD PRIMARY KEY (`id_detail`);

--
-- Indexes for table `absensi_jadwal`
--
ALTER TABLE `absensi_jadwal`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `absensi_qr`
--
ALTER TABLE `absensi_qr`
  ADD PRIMARY KEY (`id`),
  ADD KEY `nis` (`nis`);

--
-- Indexes for table `guru`
--
ALTER TABLE `guru`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hari_libur`
--
ALTER TABLE `hari_libur`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `izin_keluar`
--
ALTER TABLE `izin_keluar`
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
  ADD UNIQUE KEY `nisn` (`nisn`),
  ADD KEY `id_kelas` (`id_kelas`),
  ADD KEY `tahun_id` (`tahun_id`);

--
-- Indexes for table `siswa_history`
--
ALTER TABLE `siswa_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `siswa_tahun`
--
ALTER TABLE `siswa_tahun`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_config`
--
ALTER TABLE `system_config`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `absensi`
--
ALTER TABLE `absensi`
  MODIFY `id_absensi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `absensi_detail`
--
ALTER TABLE `absensi_detail`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `absensi_jadwal`
--
ALTER TABLE `absensi_jadwal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `absensi_qr`
--
ALTER TABLE `absensi_qr`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `guru`
--
ALTER TABLE `guru`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `hari_libur`
--
ALTER TABLE `hari_libur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `izin_keluar`
--
ALTER TABLE `izin_keluar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `kelas`
--
ALTER TABLE `kelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `mutasi`
--
ALTER TABLE `mutasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1540;

--
-- AUTO_INCREMENT for table `siswa_history`
--
ALTER TABLE `siswa_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `siswa_tahun`
--
ALTER TABLE `siswa_tahun`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1540;

--
-- AUTO_INCREMENT for table `system_config`
--
ALTER TABLE `system_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
