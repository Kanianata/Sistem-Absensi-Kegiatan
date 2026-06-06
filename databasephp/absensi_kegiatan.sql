-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 06, 2026 at 04:45 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `absensi_kegiatan`
--

-- --------------------------------------------------------

--
-- Table structure for table `absensi`
--

CREATE TABLE `absensi` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `kegiatan_id` int NOT NULL,
  `waktu_absen` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('hadir','izin','sakit','alfa') NOT NULL DEFAULT 'hadir',
  `keterangan` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `absensi`
--

INSERT INTO `absensi` (`id`, `user_id`, `kegiatan_id`, `waktu_absen`, `status`, `keterangan`) VALUES
(1, 2, 1, '2025-06-10 08:05:00', 'hadir', NULL),
(2, 3, 1, '2025-06-10 08:12:00', 'hadir', NULL),
(3, 4, 1, '2025-06-10 08:00:00', 'hadir', NULL),
(4, 5, 1, '2025-06-10 09:30:00', 'izin', 'Ada keperluan keluarga'),
(5, 2, 2, '2025-06-15 08:50:00', 'hadir', NULL),
(6, 3, 2, '2025-06-15 08:55:00', 'sakit', 'Demam'),
(7, 4, 2, '2025-06-15 09:00:00', 'hadir', NULL),
(8, 2, 3, '2025-06-20 07:45:00', 'hadir', NULL),
(9, 5, 3, '2025-06-20 08:00:00', 'hadir', NULL),
(10, 1, 8, '2026-06-06 11:31:24', 'hadir', ''),
(11, 6, 8, '2026-06-06 11:34:20', 'sakit', 'demam'),
(12, 2, 8, '2026-06-06 11:36:44', 'hadir', '');

-- --------------------------------------------------------

--
-- Table structure for table `kegiatan`
--

CREATE TABLE `kegiatan` (
  `id` int NOT NULL,
  `nama_kegiatan` varchar(150) NOT NULL,
  `tanggal` date NOT NULL,
  `lokasi` varchar(200) DEFAULT NULL,
  `deskripsi` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kegiatan`
--

INSERT INTO `kegiatan` (`id`, `nama_kegiatan`, `tanggal`, `lokasi`, `deskripsi`, `created_at`) VALUES
(1, 'Rapat Koordinasi Bulanan', '2025-06-10', 'Aula Utama Lt. 2', 'Rapat koordinasi rutin seluruh anggota.', '2026-06-05 18:28:15'),
(2, 'Seminar Teknologi Informasi', '2025-06-15', 'Gedung Serbaguna', 'Seminar perkembangan teknologi informasi.', '2026-06-05 18:28:15'),
(3, 'Pelatihan K3', '2025-06-20', 'Ruang Pelatihan A', 'Pelatihan K3 wajib karyawan baru.', '2026-06-05 18:28:15'),
(4, 'Workshop Desain UI/UX', '2025-07-05', 'Lab Komputer Lt. 3', 'Workshop intensif desain UI/UX.', '2026-06-05 18:28:15'),
(5, 'Outbound Team Building', '2025-07-12', 'Taman Wisata Batu', 'Team building dan outbound.', '2026-06-05 18:28:15'),
(7, 'Binda Desa 2025', '2025-06-03', 'Wilayut, Sidoarjo', '', '2026-06-06 04:23:29'),
(8, 'UAS KPP', '2026-06-12', 'GKB', '', '2026-06-06 04:29:07');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `nama`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'Administrator', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2026-06-05 18:28:14'),
(2, 'Iqbal Febryan Santoso', 'Iqbal', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '2026-06-05 18:28:14'),
(3, 'Adinda Bethantya', 'Bethan', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '2026-06-05 18:28:14'),
(4, 'Alviando Desta', 'nando', '$2y$10$xK9qDAsYkAHFb1tpMwq0Bu3axzx5y8uvUhCVtl52ZKmHpdbsa2vA2', 'user', '2026-06-05 18:28:14'),
(5, 'Ariel Eka R', 'Ariel', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '2026-06-05 18:28:14'),
(6, 'Naufa Evania', 'naufa', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '2026-06-06 04:17:34'),
(7, 'Kanianata Fahlevy', 'may', '$2y$10$Z7tZp8SjpqfbxKkJ81/2veHRv73bL6aIqUg9U49iKtmFf67xgRBc2', 'user', '2026-06-06 04:17:58'),
(8, 'felziero', 'fels', '$2y$10$MTB0BtrGwx6uQHDHqtOgou1UvwBc/clLnQfYxEpy37OKAEVZktVhy', 'admin', '2026-06-06 04:18:43'),
(9, 'Tirhany', 'hany', '$2y$10$kFPXMEJXkQP8UicVzotPguzJf9QpRinY1IofgDGWmrF3xQm1oHUju', 'user', '2026-06-06 04:21:09');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_absensi` (`user_id`,`kegiatan_id`),
  ADD KEY `kegiatan_id` (`kegiatan_id`);

--
-- Indexes for table `kegiatan`
--
ALTER TABLE `kegiatan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `absensi`
--
ALTER TABLE `absensi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `kegiatan`
--
ALTER TABLE `kegiatan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `absensi`
--
ALTER TABLE `absensi`
  ADD CONSTRAINT `absensi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `absensi_ibfk_2` FOREIGN KEY (`kegiatan_id`) REFERENCES `kegiatan` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
