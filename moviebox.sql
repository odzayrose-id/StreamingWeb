-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 02 Okt 2026 pada 11.46
-- Versi server: 8.0.46
-- Versi PHP: 8.5.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Basis data: `moviebox`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin`
--

CREATE TABLE `admin` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `episodes`
--

CREATE TABLE `episodes` (
  `id` int NOT NULL,
  `film_id` int DEFAULT NULL,
  `season` int NOT NULL DEFAULT '1',
  `eps_ke` int DEFAULT NULL,
  `judul_eps` varchar(255) DEFAULT NULL,
  `deskripsi` text,
  `video_url_360` varchar(255) DEFAULT NULL,
  `video_url_480` varchar(255) DEFAULT NULL,
  `video_url_720` varchar(255) DEFAULT NULL,
  `video_url_1080` varchar(255) DEFAULT NULL,
  `telegram_url` varchar(255) DEFAULT NULL,
  `download_url_360` varchar(255) DEFAULT NULL,
  `download_url_480` varchar(255) DEFAULT NULL,
  `download_url_720` varchar(255) DEFAULT NULL,
  `download_url_1080` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `films`
--

CREATE TABLE `films` (
  `id` int NOT NULL,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text,
  `pemeran` text,
  `tahun` int DEFAULT NULL,
  `kategori` varchar(100) DEFAULT NULL,
  `asal_negara` varchar(100) DEFAULT NULL,
  `tipe` enum('Movie','Series') DEFAULT 'Movie',
  `video_url_360` varchar(255) DEFAULT NULL,
  `video_url_480` varchar(255) DEFAULT NULL,
  `video_url_720` varchar(255) DEFAULT NULL,
  `video_url_1080` varchar(255) DEFAULT NULL,
  `telegram_url` varchar(255) DEFAULT NULL,
  `thumbnail_url` varchar(255) DEFAULT NULL,
  `download_url_360` varchar(255) DEFAULT NULL,
  `download_url_480` varchar(255) DEFAULT NULL,
  `download_url_720` varchar(255) DEFAULT NULL,
  `download_url_1080` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indeks untuk tabel yang dibuang
--

--
-- Indeks untuk tabel `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `episodes`
--
ALTER TABLE `episodes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `film_id` (`film_id`);

--
-- Indeks untuk tabel `films`
--
ALTER TABLE `films`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `episodes`
--
ALTER TABLE `episodes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `films`
--
ALTER TABLE `films`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `episodes`
--
ALTER TABLE `episodes`
  ADD CONSTRAINT `episodes_ibfk_1` FOREIGN KEY (`film_id`) REFERENCES `films` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
