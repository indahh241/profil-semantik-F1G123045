-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 04, 2026 at 01:01 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `semantic_profile`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT 'bcrypt hash',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uJmjkW4Di', '2026-06-24 03:52:30');

-- --------------------------------------------------------

--
-- Table structure for table `komentar`
--

CREATE TABLE `komentar` (
  `id` int UNSIGNED NOT NULL,
  `nama` varchar(100) NOT NULL,
  `pesan` text NOT NULL,
  `approved` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `id` int UNSIGNED NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `likes`
--

INSERT INTO `likes` (`id`, `ip_address`, `created_at`) VALUES
(2, '::1', '2026-06-25 10:53:33');

-- --------------------------------------------------------

--
-- Table structure for table `organisasi`
--

CREATE TABLE `organisasi` (
  `id` int NOT NULL,
  `nama` varchar(150) NOT NULL,
  `jabatan` varchar(100) NOT NULL,
  `tahun_masuk` year NOT NULL,
  `tahun_keluar` year DEFAULT NULL COMMENT 'NULL = masih aktif',
  `deskripsi` text,
  `ikon` varchar(10) DEFAULT 0xF09F91A5,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `organisasi`
--

INSERT INTO `organisasi` (`id`, `nama`, `jabatan`, `tahun_masuk`, `tahun_keluar`, `deskripsi`, `ikon`, `created_at`) VALUES
(1, 'HIMAKOM UHO', 'Anggota', '2023', NULL, 'Himpunan Mahasiswa Ilmu Komputer Universitas Halu Oleo', '💻', '2026-06-24 03:52:30'),
(2, 'Kepanitiaan PKKMB', 'Panitia', '2023', '2023', 'Kepanitiaan Pengenalan Kehidupan Kampus bagi Mahasiswa Baru', '🎓', '2026-06-24 03:52:30'),
(3, 'BAPPEDA Provinsi Sulawesi Tenggara', 'Peserta Magang', '2024', '2024', 'Melaksanakan kegiatan magang di Badan Perencanaan Pembangunan Daerah (BAPPEDA) Provinsi Sulawesi Tenggara pada bidang teknologi informasi dan administrasi pemerintahan.', '🏢', '2026-06-24 03:52:30'),
(4, 'KKN UHO', 'Peserta', '2025', '2025', 'Kuliah Kerja Nyata Universitas Halu Oleo', '🌍', '2026-06-24 03:52:30');

-- --------------------------------------------------------

--
-- Table structure for table `pendidikan`
--

CREATE TABLE `pendidikan` (
  `id` int NOT NULL,
  `jenjang` varchar(10) NOT NULL COMMENT 'SD, SMP, SMA, D3, S1, S2',
  `institusi` varchar(150) NOT NULL,
  `jurusan` varchar(100) DEFAULT NULL,
  `tahun_masuk` year NOT NULL,
  `tahun_lulus` year DEFAULT NULL COMMENT 'NULL = masih aktif',
  `keterangan` text,
  `urutan` int DEFAULT '0' COMMENT 'untuk sorting tampilan',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pendidikan`
--

INSERT INTO `pendidikan` (`id`, `jenjang`, `institusi`, `jurusan`, `tahun_masuk`, `tahun_lulus`, `keterangan`, `urutan`, `created_at`) VALUES
(1, 'SD', 'SD Negeri 54 Kendari', NULL, '2011', '2017', NULL, 1, '2026-06-24 03:52:30'),
(2, 'SMP', 'SMP Negeri 2 Kendari', NULL, '2017', '2020', NULL, 2, '2026-06-24 03:52:30'),
(3, 'SMA', 'SMA Negeri 3 Kendari', 'IPA', '2020', '2023', NULL, 3, '2026-06-24 03:52:30'),
(4, 'S1', 'Universitas Halu Oleo', 'Ilmu Komputer', '2023', NULL, NULL, 4, '2026-06-24 03:52:30');

-- --------------------------------------------------------

--
-- Table structure for table `profil`
--

CREATE TABLE `profil` (
  `id` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `nim` varchar(20) NOT NULL,
  `prodi` varchar(100) NOT NULL,
  `fakultas` varchar(100) NOT NULL,
  `universitas` varchar(150) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `alamat` text,
  `bio` text,
  `foto` varchar(255) DEFAULT 'assets/foto.jpg',
  `linkedin` varchar(255) DEFAULT NULL,
  `github` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `profil`
--

INSERT INTO `profil` (`id`, `nama`, `nim`, `prodi`, `fakultas`, `universitas`, `email`, `telepon`, `alamat`, `bio`, `foto`, `linkedin`, `github`, `website`, `updated_at`) VALUES
(1, 'Indah Haerunnisa', 'F1G123045', 'Ilmu Komputer', 'Fakultas Matematika dan Ilmu Pengetahuan Alam', 'Universitas Halu Oleo', 'indonggds@gmail.com', '082283137642', 'Kendari, Sulawesi Tenggara', 'Saya adalah mahasiswa yang memiliki ketertarikan di bidang pengembangan web, semantic web, dan teknologi informasi. Website ini dibangun untuk merepresentasikan data diri saya menggunakan pendekatan Semantic Web.', 'assets/foto.jpeg', '', 'https://github.com/indahh241', '', '2026-06-25 04:33:00');

-- --------------------------------------------------------

--
-- Table structure for table `proyek`
--

CREATE TABLE `proyek` (
  `id` int NOT NULL,
  `judul` varchar(200) NOT NULL,
  `deskripsi` text,
  `teknologi` varchar(255) DEFAULT NULL COMMENT 'pisahkan dengan koma: PHP, MySQL, Laravel',
  `link_demo` varchar(255) DEFAULT NULL,
  `link_github` varchar(255) DEFAULT NULL,
  `tahun` year NOT NULL,
  `ikon` varchar(10) DEFAULT 0xF09F9782EFB88F,
  `featured` tinyint(1) DEFAULT '0' COMMENT '1 = tampil di dashboard',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `proyek`
--

INSERT INTO `proyek` (`id`, `judul`, `deskripsi`, `teknologi`, `link_demo`, `link_github`, `tahun`, `ikon`, `featured`, `created_at`) VALUES
(1, 'Website Profil Semantik', 'Website profil mahasiswa berbasis Semantic Web dengan Schema.org, RDF, dan SPARQL.', 'PHP, MySQL, HTML, CSS, JSON-LD', NULL, NULL, '2025', '🌐', 1, '2026-06-24 03:52:30'),
(2, 'Sistem Prediksi Kelulusan', 'Sistem prediksi kelulusan mahasiswa menggunakan teknologi semantic web dan PHP.', 'PHP, MySQL, RDF, OWL, SPARQL', NULL, NULL, '2024', '🎓', 1, '2026-06-24 03:52:30'),
(3, 'E-Arsip BAPPEDA', 'Sistem manajemen arsip digital untuk BAPPEDA dengan fitur RBAC dan tanda tangan digital.', 'Laravel, Filament, MySQL', NULL, NULL, '2024', '📁', 1, '2026-06-24 03:52:30'),
(4, 'Smart Room Booking', 'Aplikasi pemesanan ruangan kampus berbasis web dengan deployment Docker di VPS.', 'PHP, MySQL, Docker, Nginx', NULL, NULL, '2025', '🏢', 1, '2026-06-24 03:52:30'),
(5, 'Sistem Pengaduan Mahasiswa FMIPA', 'Sistem pengaduan mahasiswa berbasis NLP dengan metode Text Classification. Frontend dan backend menggunakan PHP Native, sedangkan pemrosesan NLP menggunakan Python.', 'PHP, Python, NLP, Text Classification, MySQL', NULL, NULL, '2025', '📢', 1, '2026-06-24 03:52:30');

-- --------------------------------------------------------

--
-- Table structure for table `skill`
--

CREATE TABLE `skill` (
  `id` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `kategori` varchar(50) DEFAULT 'Umum' COMMENT 'Frontend, Backend, Database, Tools, Lainnya',
  `level` int DEFAULT '70' COMMENT 'persentase 0-100',
  `ikon` varchar(10) DEFAULT 0xF09F92A1,
  `urutan` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `skill`
--

INSERT INTO `skill` (`id`, `nama`, `kategori`, `level`, `ikon`, `urutan`, `created_at`) VALUES
(1, 'HTML', 'Frontend', 90, '🌐', 1, '2026-06-24 03:52:30'),
(2, 'CSS', 'Frontend', 85, '🎨', 2, '2026-06-24 03:52:30'),
(3, 'PHP', 'Backend', 80, '🐘', 3, '2026-06-24 03:52:30'),
(4, 'MySQL', 'Database', 75, '🗄️', 4, '2026-06-24 03:52:30'),
(5, 'JavaScript', 'Frontend', 70, '⚡', 5, '2026-06-24 03:52:30'),
(6, 'Laravel', 'Backend', 65, '🔴', 6, '2026-06-24 03:52:30'),
(7, 'Python', 'Backend', 60, '🐍', 7, '2026-06-24 03:52:30'),
(8, 'Git', 'Tools', 75, '🔧', 8, '2026-06-24 03:52:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `komentar`
--
ALTER TABLE `komentar`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_ip` (`ip_address`);

--
-- Indexes for table `organisasi`
--
ALTER TABLE `organisasi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pendidikan`
--
ALTER TABLE `pendidikan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `profil`
--
ALTER TABLE `profil`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `proyek`
--
ALTER TABLE `proyek`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `skill`
--
ALTER TABLE `skill`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `komentar`
--
ALTER TABLE `komentar`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `organisasi`
--
ALTER TABLE `organisasi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `pendidikan`
--
ALTER TABLE `pendidikan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `profil`
--
ALTER TABLE `profil`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `proyek`
--
ALTER TABLE `proyek`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `skill`
--
ALTER TABLE `skill`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
