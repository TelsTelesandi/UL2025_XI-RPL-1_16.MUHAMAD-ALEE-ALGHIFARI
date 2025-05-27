-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 23, 2025 at 04:08 AM
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
-- Database: `pjb_muhamad_alee_alghifari`
--

-- --------------------------------------------------------

--
-- Table structure for table `peminjaman`
--

CREATE TABLE `peminjaman` (
  `peminjaman_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `sarana_id` int(11) DEFAULT NULL,
  `tanggal_pinjam` varchar(50) DEFAULT NULL,
  `tanggal_kembali` varchar(50) DEFAULT NULL,
  `jumlah_pinjam` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `catatan_admin` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `peminjaman`
--

INSERT INTO `peminjaman` (`peminjaman_id`, `user_id`, `sarana_id`, `tanggal_pinjam`, `tanggal_kembali`, `jumlah_pinjam`, `status`, `catatan_admin`) VALUES
(111, 232410022, 232444, '27 April', '28 April', 1, 'selesai', 'pengembalian tepat waktu'),
(112, 232410033, 232411, '2 Mei', '2 Mei', 1, 'selesai', 'pengembalian tepat waktu'),
(113, 232410011, 232433, '6 Mei', '10 Mei', 3, 'belum selesai', 'ditunggu...'),
(114, 232410044, 232455, '9 Mei', '9 Mei', 1, 'selesai', 'pengembalian tepat waktu'),
(115, 232410055, 232422, '10 Mei', '10 Mei', 1, 'selesai', 'Pengembalian tepat waktu'),
(116, 232410022, 232422, '23 May', '2025-05-23', 2, 'belum selesai', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sarana`
--

CREATE TABLE `sarana` (
  `sarana_id` int(11) NOT NULL,
  `nama_sarana` varchar(50) DEFAULT NULL,
  `jumlah_tersedia` int(11) DEFAULT NULL,
  `lokasi` varchar(50) DEFAULT NULL,
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sarana`
--

INSERT INTO `sarana` (`sarana_id`, `nama_sarana`, `jumlah_tersedia`, `lokasi`, `keterangan`) VALUES
(232411, 'HDMI', 4, 'Ruang Sarpras', 'Good'),
(232422, 'Speaker', 1, 'Ruang Sarpras', 'Baru!'),
(232433, 'Saklar', 4, 'Ruang Sarpras', 'Bagus'),
(232444, 'Mic', 3, 'Ruang Sarpras', 'Baru!'),
(232455, 'Pengki', 1, 'Ruang Sarpras', 'Rusak');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(15) NOT NULL,
  `id_card` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `role` varchar(50) NOT NULL,
  `nama_lengkap` varchar(50) NOT NULL,
  `jenis_pengguna` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `id_card`, `username`, `password`, `role`, `nama_lengkap`, `jenis_pengguna`) VALUES
(232410011, '1001', 'Jea', 'jeaxkiko123', 'user', 'Jea Lipa', 'User lama'),
(232410015, '270408', 'Alee', 'muhamadaleealghifari', 'Admin', 'Muhamad Alee Alghifari', 'Admin baru'),
(232410022, '1002', 'Looki', 'lookikiloo', 'user', 'Looki Ndihome', 'User lama'),
(232410033, '1003', 'Loyd', 'loydanakrajin', 'user', 'Loyd Forger', 'User lama'),
(232410044, '1004', 'xxkaenjete', 'betulbetulges123', 'user', 'Keenan Kaen', 'User baru'),
(232410055, '1005', 'Zee', 'ZeexZea', 'User', 'Zee De\' Noir', 'User baru');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD PRIMARY KEY (`peminjaman_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `sarana_id` (`sarana_id`);

--
-- Indexes for table `sarana`
--
ALTER TABLE `sarana`
  ADD PRIMARY KEY (`sarana_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `peminjaman_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=117;

--
-- AUTO_INCREMENT for table `sarana`
--
ALTER TABLE `sarana`
  MODIFY `sarana_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=232456;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(15) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=232410057;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD CONSTRAINT `fk_sarana_id` FOREIGN KEY (`sarana_id`) REFERENCES `sarana` (`sarana_id`),
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
