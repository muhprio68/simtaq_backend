-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 29 Sep 2022 pada 08.28
-- Versi server: 10.4.22-MariaDB
-- Versi PHP: 7.4.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `restful_db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `kegiatan`
--

CREATE TABLE `kegiatan` (
  `id_kegiatan` int(11) NOT NULL,
  `no_kegiatan` varchar(14) NOT NULL,
  `nama_kegiatan` varchar(65) NOT NULL,
  `tipe_kegiatan` varchar(12) NOT NULL,
  `tgl_kegiatan` date NOT NULL,
  `waktu_kegiatan` time NOT NULL,
  `tempat_kegiatan` varchar(65) NOT NULL,
  `pembicara_kegiatan` varchar(65) NOT NULL,
  `deskripsi_kegiatan` varchar(200) NOT NULL,
  `create_at` date NOT NULL,
  `update_at` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `keuangan`
--

CREATE TABLE `keuangan` (
  `id_keuangan` int(11) NOT NULL,
  `no_keuangan` varchar(15) NOT NULL,
  `tipe_keuangan` varchar(12) NOT NULL,
  `tgl_keuangan` date NOT NULL,
  `keterangan_keuangan` varchar(65) NOT NULL,
  `status_keuangan` varchar(12) NOT NULL,
  `nominal_keuangan` bigint(20) NOT NULL,
  `jml_kas_awal` bigint(20) NOT NULL,
  `jml_kas_akhir` bigint(20) NOT NULL,
  `deskripsi_keuangan` varchar(200) NOT NULL,
  `create_at` date NOT NULL,
  `update_at` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `nomorkegiatan`
--

CREATE TABLE `nomorkegiatan` (
  `id_nomor` int(11) NOT NULL,
  `tgl_kegiatan` date NOT NULL,
  `no_terakhir` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `nomorkeuangan`
--

CREATE TABLE `nomorkeuangan` (
  `id_nomor` int(11) NOT NULL,
  `tgl_keuangan` date NOT NULL,
  `no_terakhir` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `saldo`
--

CREATE TABLE `saldo` (
  `id_saldo` int(11) NOT NULL,
  `jml_saldo` bigint(20) NOT NULL,
  `update_at` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `saldo`
--

INSERT INTO `saldo` (`id_saldo`, `jml_saldo`, `update_at`) VALUES
(1, 30910000, '2022-09-29');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `kegiatan`
--
ALTER TABLE `kegiatan`
  ADD PRIMARY KEY (`id_kegiatan`);

--
-- Indeks untuk tabel `keuangan`
--
ALTER TABLE `keuangan`
  ADD PRIMARY KEY (`id_keuangan`);

--
-- Indeks untuk tabel `nomorkegiatan`
--
ALTER TABLE `nomorkegiatan`
  ADD PRIMARY KEY (`id_nomor`),
  ADD UNIQUE KEY `tgl_kegiatan` (`tgl_kegiatan`);

--
-- Indeks untuk tabel `nomorkeuangan`
--
ALTER TABLE `nomorkeuangan`
  ADD PRIMARY KEY (`id_nomor`),
  ADD UNIQUE KEY `tgl_keuangan` (`tgl_keuangan`);

--
-- Indeks untuk tabel `saldo`
--
ALTER TABLE `saldo`
  ADD PRIMARY KEY (`id_saldo`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `kegiatan`
--
ALTER TABLE `kegiatan`
  MODIFY `id_kegiatan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `keuangan`
--
ALTER TABLE `keuangan`
  MODIFY `id_keuangan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `nomorkegiatan`
--
ALTER TABLE `nomorkegiatan`
  MODIFY `id_nomor` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `nomorkeuangan`
--
ALTER TABLE `nomorkeuangan`
  MODIFY `id_nomor` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `saldo`
--
ALTER TABLE `saldo`
  MODIFY `id_saldo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
