SET FOREIGN_KEY_CHECKS=0;

-- phpMyAdmin SQL Dump 
-- version 5.2.1 
-- https://www.phpmyadmin.net/ 
-- 
-- Host: localhost:3306 
-- Waktu pembuatan: 05 Apr 2025 pada 15.14 
-- Versi server: 8.0.30 
-- Versi PHP: 8.2.22  
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
-- Struktur dari tabel `mata_pelajaran` 
--  
CREATE TABLE `mata_pelajaran` (   `id_mapel` int NOT NULL,   `nama_mapel` varchar(100) NOT NULL,   `nama_mapel_arab` varchar(255) NOT NULL,   `kategori` enum('TES TERTULIS','HAFALAN','TES BACA') NOT NULL,   `id_kelas` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;  
-- 
-- Dumping data untuk tabel `mata_pelajaran` 
--  
INSERT INTO `mata_pelajaran` (`id_mapel`, `nama_mapel`, `nama_mapel_arab`, `kategori`, `id_kelas`) VALUES (43, 'Majmuah Al-Aurod', 'مجموعه الأوراد', 'TES TERTULIS', 1), (44, 'Amtsilah 2', 'الأمثلة ٢', 'TES TERTULIS', 1), (45, 'Jurumiyah', 'الآجُرُّومِيَّة', 'TES TERTULIS', 2), (46, 'Shorof 2', 'الصرف ٢', 'TES TERTULIS', 2), (47, 'Amtsilah 1', 'الأمثلة ١', 'TES TERTULIS', 1), (48, 'Hidayatus Sibyan', 'هداية الصبيان', 'TES TERTULIS', 1), (49, 'Tahfidz Juz\'Amma', 'تحفيظ جزء عم', 'TES TERTULIS', 3), (50, 'Imrithi', 'الإمرة', 'TES TERTULIS', 2), (51, 'Lentera Santri', 'فانوس السنتري', 'TES TERTULIS', 1), (52, 'Mabadi Fiqih', 'مبادئ الفقه', 'TES TERTULIS', 2), (53, 'Shorof 1', 'الصرف ١', 'TES TERTULIS', 1), (54, 'Taqrib', 'التقريب', 'TES TERTULIS', 2), (55, 'Tuhfatul Wildan', 'تحفة الولدان', 'TES TERTULIS', 1), (56, 'Safinatun Naja', 'سفينة النجاة', 'TES TERTULIS', 2), (57, 'Wasoya', 'الوصايا', 'TES TERTULIS', 2), (58, 'Bahasa Arab', 'اللغة العربية', 'TES TERTULIS', 1), (59, 'Tuhfatul Athfal', 'تحفة الأطفال', 'TES TERTULIS', 3), (60, 'Tajwid', 'التجويد', 'TES TERTULIS', 3), (61, 'test', 'اختبار', 'HAFALAN', 5);  
-- 
-- Indexes for dumped tables 
--  
-- 
-- Indeks untuk tabel `mata_pelajaran` 
-- 
ALTER TABLE `mata_pelajaran` 
  ADD PRIMARY KEY (`id_mapel`), 
  ADD KEY `id_kelas` (`id_kelas`);  
-- 
-- AUTO_INCREMENT untuk tabel yang dibuang 
--  
-- 
-- AUTO_INCREMENT untuk tabel `mata_pelajaran` 
-- 
ALTER TABLE `mata_pelajaran` 
  MODIFY `id_mapel` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;  
-- 
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables) 
--  
-- 
-- Ketidakleluasaan untuk tabel `mata_pelajaran` 
-- 
ALTER TABLE `mata_pelajaran` 
  ADD CONSTRAINT `mata_pelajaran_ibfk_1` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`); 
COMMIT;  
/*!40101 
SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */; 
/*!40101 
SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */; 
/*!40101 
SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;