-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 11 Feb 2026 pada 01.50
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dbukk_rifaya`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin`
--

CREATE TABLE `admin` (
  `username` varchar(30) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admin`
--

INSERT INTO `admin` (`username`, `password`) VALUES
('admin', 'admin123');

-- --------------------------------------------------------

--
-- Struktur dari tabel `aspirasi`
--

CREATE TABLE `aspirasi` (
  `id_aspirasi` int(5) NOT NULL,
  `id_pelaporan` int(11) DEFAULT NULL,
  `nis` int(10) NOT NULL,
  `id_kategori` int(5) NOT NULL,
  `status` enum('Menunggu','Proses','Selesai') DEFAULT 'Menunggu',
  `feedback` varchar(255) DEFAULT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `aspirasi`
--

INSERT INTO `aspirasi` (`id_aspirasi`, `id_pelaporan`, `nis`, `id_kategori`, `status`, `feedback`, `tanggal`) VALUES
(6, 1, 0, 1, 'Selesai', 'swdefe', '2026-01-14 00:57:11'),
(7, 4, 0, 6, 'Proses', 'blbalana albsjhwd whjgqiy7owrdvhgfwqop', '2026-01-14 00:57:35'),
(8, 5, 0, 1, 'Selesai', 'sudah diperbaiki', '2026-01-14 04:28:47'),
(9, 3, 0, 2, 'Selesai', 'dah', '2026-01-15 07:28:21'),
(10, 6, 0, 1, 'Selesai', 'dsss', '2026-01-20 00:42:40'),
(11, 7, 0, 2, 'Proses', 'selesai dibersihkan', '2026-01-21 01:17:06'),
(12, 8, 0, 1, 'Selesai', 'efrgr', '2026-01-21 02:31:56'),
(13, 9, 0, 2, 'Proses', '', '2026-01-21 06:02:31'),
(14, 10, 0, 3, 'Selesai', '', '2026-01-28 01:14:29'),
(17, 22, 0, 2, 'Selesai', 'sudah dibersihkan.', '2026-02-09 07:15:39');

-- --------------------------------------------------------

--
-- Struktur dari tabel `histori_aspirasi`
--

CREATE TABLE `histori_aspirasi` (
  `id_histori` int(11) NOT NULL,
  `id_pelaporan` varchar(50) NOT NULL,
  `jenis_perubahan` enum('masuk','status','feedback') NOT NULL,
  `nilai_lama` text DEFAULT NULL,
  `nilai_baru` text DEFAULT NULL,
  `waktu_perubahan` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `histori_aspirasi`
--

INSERT INTO `histori_aspirasi` (`id_histori`, `id_pelaporan`, `jenis_perubahan`, `nilai_lama`, `nilai_baru`, `waktu_perubahan`) VALUES
(1, '10', 'status', 'Proses', 'Selesai', '2026-02-09 13:49:14'),
(2, '9', 'status', 'Selesai', 'Proses', '2026-02-09 14:08:53'),
(3, '9', 'feedback', 'dibersihkan', NULL, '2026-02-09 14:08:53'),
(4, '22', 'masuk', NULL, 'Aspirasi masuk ke sistem', '2026-02-09 14:15:26'),
(5, '22', 'status', NULL, 'Proses', '2026-02-09 14:15:39'),
(6, '22', 'feedback', NULL, 'dibersihkan', '2026-02-09 14:15:39'),
(7, '22', 'feedback', 'dibersihkan', 'sudah dibersihkan', '2026-02-09 14:15:52'),
(8, '22', 'status', 'Proses', 'Selesai', '2026-02-09 14:15:59'),
(9, '22', 'feedback', 'sudah dibersihkan', 'sudah dibersihkan.', '2026-02-09 14:15:59');

-- --------------------------------------------------------

--
-- Struktur dari tabel `input_aspirasi`
--

CREATE TABLE `input_aspirasi` (
  `id_pelaporan` int(5) NOT NULL,
  `nis` int(10) NOT NULL,
  `id_kategori` int(5) NOT NULL,
  `lokasi` varchar(50) NOT NULL,
  `ket` varchar(50) NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Menunggu','Proses','Selesai') DEFAULT 'Menunggu'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `input_aspirasi`
--

INSERT INTO `input_aspirasi` (`id_pelaporan`, `nis`, `id_kategori`, `lokasi`, `ket`, `tanggal`, `status`) VALUES
(1, 12345678, 1, 'lab a rpl', 'ac rusak', '2026-01-12 07:43:59', 'Proses'),
(3, 12345678, 2, 'wc laki laki', 'bau pesing', '2026-01-12 07:56:03', 'Selesai'),
(4, 12345678, 6, 'lap indoor', 'kotor', '2026-01-12 17:58:18', 'Menunggu'),
(5, 12345678, 1, 'lab a rpl', 'ac rusak, tidak dingin, panas', '2026-01-12 18:34:46', 'Selesai'),
(6, 12345678, 1, 'kelas c22', 'panas', '2026-01-13 01:39:11', 'Menunggu'),
(7, 67891011, 2, 'kelas c22', 'piket tidak bersih', '2026-01-21 01:16:35', 'Menunggu'),
(8, 67891011, 1, 'lab f', 'panas kurang kipas', '2026-01-21 02:31:39', 'Menunggu'),
(9, 67891011, 2, 'kelas c22', 'kotor', '2026-01-21 06:01:39', 'Menunggu'),
(10, 67891011, 3, 'kelas c22', 'uang hilang di kelas', '2026-01-28 01:13:43', 'Menunggu'),
(22, 12345678, 2, 'c22', 'kotor', '2026-02-09 07:15:26', 'Menunggu');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int(5) NOT NULL,
  `ket_kategori` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `ket_kategori`) VALUES
(1, 'Fasilitas Sekolah'),
(2, 'Kebersihan'),
(3, 'Keamanan'),
(4, 'Pembelajaran'),
(5, 'Administrasi'),
(6, 'Lainnya');

-- --------------------------------------------------------

--
-- Struktur dari tabel `siswa`
--

CREATE TABLE `siswa` (
  `nis` int(10) NOT NULL,
  `kelas` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `siswa`
--

INSERT INTO `siswa` (`nis`, `kelas`) VALUES
(12345678, '12 PPLG 2'),
(67891011, '12 PPLG 2');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`username`);

--
-- Indeks untuk tabel `aspirasi`
--
ALTER TABLE `aspirasi`
  ADD PRIMARY KEY (`id_aspirasi`),
  ADD KEY `nis` (`nis`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indeks untuk tabel `histori_aspirasi`
--
ALTER TABLE `histori_aspirasi`
  ADD PRIMARY KEY (`id_histori`),
  ADD KEY `idx_id_pelaporan` (`id_pelaporan`);

--
-- Indeks untuk tabel `input_aspirasi`
--
ALTER TABLE `input_aspirasi`
  ADD PRIMARY KEY (`id_pelaporan`),
  ADD KEY `fk_input_aspirasi_siswa` (`nis`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indeks untuk tabel `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`nis`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `aspirasi`
--
ALTER TABLE `aspirasi`
  MODIFY `id_aspirasi` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `histori_aspirasi`
--
ALTER TABLE `histori_aspirasi`
  MODIFY `id_histori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `input_aspirasi`
--
ALTER TABLE `input_aspirasi`
  MODIFY `id_pelaporan` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `aspirasi`
--
ALTER TABLE `aspirasi`
  ADD CONSTRAINT `aspirasi_ibfk_2` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`);

--
-- Ketidakleluasaan untuk tabel `input_aspirasi`
--
ALTER TABLE `input_aspirasi`
  ADD CONSTRAINT `fk_input_aspirasi_siswa` FOREIGN KEY (`nis`) REFERENCES `siswa` (`nis`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
