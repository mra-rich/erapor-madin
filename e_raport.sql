SET FOREIGN_KEY_CHECKS=0;

-- phpMyAdmin SQL Dump 
-- version 5.2.1 
-- https://www.phpmyadmin.net/ 
-- 
-- Host: localhost:3306 
-- Generation Time: Mar 31, 2025 at 01:40 PM 
-- Server version: 8.0.30 
-- PHP Version: 8.2.22  
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO"; 
START TRANSACTION; 
SET time_zone = "+00:00";   
/*!40101 
SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */; 
/*!40101 
SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */; 
/*!40101 
SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */; 
/*!40101 
SET NAMES utf8mb4 */;  
-- 
-- Database: `e_raport` 
--  
-- ------------------------------------------------------
--  
-- 
-- Table structure for table `absensi` 
--  
CREATE TABLE `absensi` (   `id_absensi` int NOT NULL,   `id_transaksi` int NOT NULL,   `izin` int DEFAULT '0',   `sakit` int DEFAULT '0',   `tanpa_keterangan` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;  
-- 
-- Dumping data for table `absensi` 
--  
INSERT INTO `absensi` (`id_absensi`, `id_transaksi`, `izin`, `sakit`, `tanpa_keterangan`) VALUES (5, 6, 1, 1, 1), (6, 7, 8, 8, 8), (7, 8, 9, 9, 9), (8, 9, 9, 10, 11);  
-- ------------------------------------------------------
--  
-- 
-- Table structure for table `administrasi` 
--  
CREATE TABLE `administrasi` (   `id_admin` int NOT NULL,   `id_siswa` int DEFAULT NULL,   `id_pengguna` int DEFAULT NULL,   `tanggal_dikeluarkan` date DEFAULT NULL,   `wali_kelas` varchar(100) DEFAULT NULL,   `kepala_madrasah` varchar(100) DEFAULT NULL,   `tempat_dikeluarkan` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;  
-- ------------------------------------------------------
--  
-- 
-- Table structure for table `catatan_wali_kelas` 
--  
CREATE TABLE `catatan_wali_kelas` (   `id_catatan` int NOT NULL,   `id_transaksi` int NOT NULL,   `catatan` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;  
-- 
-- Dumping data for table `catatan_wali_kelas` 
--  
INSERT INTO `catatan_wali_kelas` (`id_catatan`, `id_transaksi`, `catatan`) VALUES (2, 6, '1'), (3, 7, '8'), (4, 8, '9'), (5, 9, 'Kurang pinter');  
-- ------------------------------------------------------
--  
-- 
-- Table structure for table `kelas` 
--  
CREATE TABLE `kelas` (   `id_kelas` int NOT NULL,   `nama_kelas` varchar(50) NOT NULL,   `tingkat` enum('VII','VIII','IX') NOT NULL,   `id_wali_kelas` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;  
-- 
-- Dumping data for table `kelas` 
--  
INSERT INTO `kelas` (`id_kelas`, `nama_kelas`, `tingkat`, `id_wali_kelas`) VALUES (1, 'VII-A', 'VII', 2), (2, 'VII-B', 'VII', 3), (3, 'VIII-A', 'VIII', 3), (4, 'VIII-B', 'VIII', 3), (5, 'IX-A', 'IX', 2), (6, 'IX-B', 'IX', 3);  
-- ------------------------------------------------------
--  
-- 
-- Table structure for table `kepribadian` 
--  
CREATE TABLE `kepribadian` (   `id_kepribadian` int NOT NULL,   `id_transaksi` int NOT NULL,   `kelakuan` varchar(20) NOT NULL,   `kerajinan` varchar(20) NOT NULL,   `kerapian` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;  
-- 
-- Dumping data for table `kepribadian` 
--  
INSERT INTO `kepribadian` (`id_kepribadian`, `id_transaksi`, `kelakuan`, `kerajinan`, `kerapian`) VALUES (5, 6, 'A', 'A', 'B'), (6, 7, 'A', 'A', 'A'), (7, 8, 'B', 'B', 'B'), (8, 9, 'B', 'B', 'A');  
-- ------------------------------------------------------
--  
-- 
-- Table structure for table `mata_pelajaran` 
--  
CREATE TABLE `mata_pelajaran` (   `id_mapel` int NOT NULL,   `nama_mapel` varchar(100) NOT NULL,   `nama_mapel_arab` varchar(255) NOT NULL,   `kategori` enum('TES TERTULIS','HAFALAN','TES BACA') NOT NULL,   `id_kelas` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;  
-- ------------------------------------------------------
--  
-- 
-- Table structure for table `nilai` 
--  
CREATE TABLE `nilai` (   `id_nilai` int NOT NULL,   `id_transaksi` int NOT NULL,   `id_mapel` int NOT NULL,   `nilai_angka` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;  
-- 
-- Dumping data for table `nilai` 
--  
INSERT INTO `nilai` (`id_nilai`, `id_transaksi`, `id_mapel`, `nilai_angka`) VALUES (7, 6, 37, 94), (8, 6, 31, 99), (9, 6, 2, 88), (10, 6, 1, 87), (11, 6, 5, 76), (12, 6, 3, 67), (13, 6, 4, 56), (14, 7, 38, 89), (15, 7, 32, 98), (16, 7, 7, 88), (17, 7, 6, 88), (18, 7, 10, 88), (19, 7, 8, 88), (20, 7, 9, 88), (21, 8, 39, 90), (22, 8, 33, 89), (23, 8, 12, 77), (24, 8, 11, 89), (25, 8, 15, 56), (26, 8, 13, 89), (27, 8, 14, 90), (28, 9, 38, 90), (29, 9, 32, 89), (30, 9, 7, 78), (31, 9, 6, 88), (32, 9, 10, 90), (33, 9, 8, 76), (34, 9, 9, 80);  
-- ------------------------------------------------------
--  
-- 
-- Table structure for table `pengguna` 
--  
CREATE TABLE `pengguna` (   `id_pengguna` int NOT NULL,   `nama` varchar(100) NOT NULL,   `username` varchar(50) NOT NULL,   `password` varchar(255) NOT NULL,   `peran` enum('Admin','Wali Kelas') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;  
-- 
-- Dumping data for table `pengguna` 
--  
INSERT INTO `pengguna` (`id_pengguna`, `nama`, `username`, `password`, `peran`) VALUES (1, 'Administrator', 'admin', '123', 'Admin'), (2, 'Wali Kelas 1', 'wali', '123', 'Wali Kelas'), (3, 'RDF', 'rdf', '123', 'Wali Kelas');  
-- ------------------------------------------------------
--  
-- 
-- Table structure for table `siswa` 
--  
CREATE TABLE `siswa` (   `id_siswa` int NOT NULL,   `nisn` int NOT NULL,   `nama` varchar(100) NOT NULL,   `nomor_santri` varchar(20) CHARACTER 
SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,   `id_kelas` int DEFAULT NULL,   `tahun_ajaran` varchar(20) NOT NULL,   `alamat` text,   `nama_wali` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;  
-- 
-- Dumping data for table `siswa` 
--  
INSERT INTO `siswa` (`id_siswa`, `nisn`, `nama`, `nomor_santri`, `id_kelas`, `tahun_ajaran`, `alamat`, `nama_wali`) VALUES (1, 1234567890, 'Ahmad Fauzan', 'S001', 1, '2024/2025', 'Jl. Merdeka No. 10', 'Budi Santoso'), (2, 1234567891, 'Siti Aisyah', 'S002', 2, '2024/2025', 'Jl. Kenangan No. 5', 'Nurhayati'), (3, 1234567892, 'Rizky Maulana', 'S003', 3, '2024/2025', 'Jl. Mawar No. 12', 'Ahmad Ridwan'), (4, 1234567893, 'Nabila Zahra', 'S004', 4, '2024/2025', 'Jl. Melati No. 8', 'Siti Rohmah'), (5, 1234567894, 'Fadli Pratama', 'S005', 5, '2024/2025', 'Jl. Anggrek No. 7', 'Hendrianto'), (6, 1234567895, 'Aulia Putri', 'S006', 6, '2024/2025', 'Jl. Cemara No. 20', 'Dewi Kartika');  
-- ------------------------------------------------------
--  
-- 
-- Table structure for table `transaksi_raport` 
--  
CREATE TABLE `transaksi_raport` (   `id_transaksi` int NOT NULL,   `id_siswa` int NOT NULL,   `id_pengguna` int NOT NULL,   `tahun_ajaran` varchar(20) NOT NULL,   `semester` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;  
-- 
-- Dumping data for table `transaksi_raport` 
--  
INSERT INTO `transaksi_raport` (`id_transaksi`, `id_siswa`, `id_pengguna`, `tahun_ajaran`, `semester`) VALUES (6, 1, 3, '2024/2025', 1), (7, 2, 3, '2024/2025', 1), (8, 3, 3, '2024/2025', 1), (9, 2, 3, '2024/2025', 2);  
-- 
-- Indexes for dumped tables 
--  
-- 
-- Indexes for table `absensi` 
-- 
ALTER TABLE `absensi` 
  ADD PRIMARY KEY (`id_absensi`), 
  ADD KEY `id_transaksi` (`id_transaksi`);  
-- 
-- Indexes for table `administrasi` 
-- 
ALTER TABLE `administrasi` 
  ADD PRIMARY KEY (`id_admin`), 
  ADD KEY `id_siswa` (`id_siswa`), 
  ADD KEY `id_pengguna` (`id_pengguna`);  
-- 
-- Indexes for table `catatan_wali_kelas` 
-- 
ALTER TABLE `catatan_wali_kelas` 
  ADD PRIMARY KEY (`id_catatan`), 
  ADD KEY `id_transaksi` (`id_transaksi`);  
-- 
-- Indexes for table `kelas` 
-- 
ALTER TABLE `kelas` 
  ADD PRIMARY KEY (`id_kelas`), 
  ADD KEY `fk_wali_kelas` (`id_wali_kelas`);  
-- 
-- Indexes for table `kepribadian` 
-- 
ALTER TABLE `kepribadian` 
  ADD PRIMARY KEY (`id_kepribadian`), 
  ADD KEY `id_transaksi` (`id_transaksi`);  
-- 
-- Indexes for table `mata_pelajaran` 
-- 
ALTER TABLE `mata_pelajaran` 
  ADD PRIMARY KEY (`id_mapel`), 
  ADD KEY `id_kelas` (`id_kelas`);  
-- 
-- Indexes for table `nilai` 
-- 
ALTER TABLE `nilai` 
  ADD PRIMARY KEY (`id_nilai`), 
  ADD KEY `id_transaksi` (`id_transaksi`), 
  ADD KEY `id_mapel` (`id_mapel`);  
-- 
-- Indexes for table `pengguna` 
-- 
ALTER TABLE `pengguna` 
  ADD PRIMARY KEY (`id_pengguna`), 
  ADD UNIQUE KEY `username` (`username`);  
-- 
-- Indexes for table `siswa` 
-- 
ALTER TABLE `siswa` 
  ADD PRIMARY KEY (`id_siswa`), 
  ADD UNIQUE KEY `nisn` (`nomor_santri`), 
  ADD UNIQUE KEY `id_kelas` (`id_kelas`), 
  ADD KEY `nomor_santri` (`nomor_santri`), 
  ADD KEY `nisn_2` (`nisn`), 
  ADD KEY `id_kelas_2` (`id_kelas`);  
-- 
-- Indexes for table `transaksi_raport` 
-- 
ALTER TABLE `transaksi_raport` 
  ADD PRIMARY KEY (`id_transaksi`), 
  ADD KEY `id_siswa` (`id_siswa`), 
  ADD KEY `id_pengguna` (`id_pengguna`);  
-- 
-- AUTO_INCREMENT for dumped tables 
--  
-- 
-- AUTO_INCREMENT for table `absensi` 
-- 
ALTER TABLE `absensi` 
  MODIFY `id_absensi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;  
-- 
-- AUTO_INCREMENT for table `administrasi` 
-- 
ALTER TABLE `administrasi` 
  MODIFY `id_admin` int NOT NULL AUTO_INCREMENT;  
-- 
-- AUTO_INCREMENT for table `catatan_wali_kelas` 
-- 
ALTER TABLE `catatan_wali_kelas` 
  MODIFY `id_catatan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;  
-- 
-- AUTO_INCREMENT for table `kelas` 
-- 
ALTER TABLE `kelas` 
  MODIFY `id_kelas` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;  
-- 
-- AUTO_INCREMENT for table `kepribadian` 
-- 
ALTER TABLE `kepribadian` 
  MODIFY `id_kepribadian` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;  
-- 
-- AUTO_INCREMENT for table `mata_pelajaran` 
-- 
ALTER TABLE `mata_pelajaran` 
  MODIFY `id_mapel` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;  
-- 
-- AUTO_INCREMENT for table `nilai` 
-- 
ALTER TABLE `nilai` 
  MODIFY `id_nilai` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;  
-- 
-- AUTO_INCREMENT for table `pengguna` 
-- 
ALTER TABLE `pengguna` 
  MODIFY `id_pengguna` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;  
-- 
-- AUTO_INCREMENT for table `siswa` 
-- 
ALTER TABLE `siswa` 
  MODIFY `id_siswa` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;  
-- 
-- AUTO_INCREMENT for table `transaksi_raport` 
-- 
ALTER TABLE `transaksi_raport` 
  MODIFY `id_transaksi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;  
-- 
-- Constraints for dumped tables 
--  
-- 
-- Constraints for table `absensi` 
-- 
ALTER TABLE `absensi` 
  ADD CONSTRAINT `absensi_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi_raport` (`id_transaksi`) ON DELETE CASCADE;  
-- 
-- Constraints for table `administrasi` 
-- 
ALTER TABLE `administrasi` 
  ADD CONSTRAINT `administrasi_ibfk_1` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE, 
  ADD CONSTRAINT `administrasi_ibfk_2` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id_pengguna`) ON DELETE 
SET NULL;  
-- 
-- Constraints for table `catatan_wali_kelas` 
-- 
ALTER TABLE `catatan_wali_kelas` 
  ADD CONSTRAINT `catatan_wali_kelas_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi_raport` (`id_transaksi`) ON DELETE CASCADE;  
-- 
-- Constraints for table `kelas` 
-- 
ALTER TABLE `kelas` 
  ADD CONSTRAINT `fk_wali_kelas` FOREIGN KEY (`id_wali_kelas`) REFERENCES `pengguna` (`id_pengguna`) ON DELETE RESTRICT;  
-- 
-- Constraints for table `kepribadian` 
-- 
ALTER TABLE `kepribadian` 
  ADD CONSTRAINT `kepribadian_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi_raport` (`id_transaksi`) ON DELETE CASCADE;  
-- 
-- Constraints for table `mata_pelajaran` 
-- 
ALTER TABLE `mata_pelajaran` 
  ADD CONSTRAINT `mata_pelajaran_ibfk_1` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`);  
-- 
-- Constraints for table `nilai` 
-- 
ALTER TABLE `nilai` 
  ADD CONSTRAINT `nilai_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi_raport` (`id_transaksi`) ON DELETE CASCADE, 
  ADD CONSTRAINT `nilai_ibfk_2` FOREIGN KEY (`id_mapel`) REFERENCES `mata_pelajaran` (`id_mapel`);  
-- 
-- Constraints for table `siswa` 
-- 
ALTER TABLE `siswa` 
  ADD CONSTRAINT `fk_siswa_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`) ON DELETE 
SET NULL;  
-- 
-- Constraints for table `transaksi_raport` 
-- 
ALTER TABLE `transaksi_raport` 
  ADD CONSTRAINT `transaksi_raport_ibfk_1` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE, 
  ADD CONSTRAINT `transaksi_raport_ibfk_2` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id_pengguna`) ON DELETE CASCADE; 
COMMIT;  
/*!40101 
SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */; 
/*!40101 
SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */; 
/*!40101 
SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;