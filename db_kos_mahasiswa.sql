-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 16 Okt 2026 pada 17.59
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_kos_mahasiswa`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id_log` bigint(20) UNSIGNED NOT NULL,
  `id_user` int(10) UNSIGNED DEFAULT NULL,
  `aktivitas` varchar(100) NOT NULL,
  `modul` varchar(50) NOT NULL,
  `data_diproses` varchar(255) DEFAULT NULL,
  `waktu_aktivitas` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `activity_logs`
--

INSERT INTO `activity_logs` (`id_log`, `id_user`, `aktivitas`, `modul`, `data_diproses`, `waktu_aktivitas`) VALUES
(1, 2, 'Login', 'Autentikasi', 'pengelola', '2026-10-04 18:37:10'),
(2, 2, 'Logout', 'Autentikasi', 'pengelola', '2026-10-04 18:37:53'),
(3, NULL, 'Login', 'Autentikasi', 'penghuni', '2026-10-04 18:38:01'),
(4, NULL, 'Logout', 'Autentikasi', 'penghuni', '2026-10-04 18:45:37'),
(5, NULL, 'Login', 'Autentikasi', 'penghuni', '2026-10-04 18:46:32'),
(6, NULL, 'Logout', 'Autentikasi', 'penghuni', '2026-10-04 18:49:15'),
(7, NULL, 'Login', 'Autentikasi', 'penghuni', '2026-10-04 18:49:29'),
(8, NULL, 'Logout', 'Autentikasi', 'penghuni', '2026-10-04 19:06:17'),
(9, NULL, 'Login', 'Autentikasi', 'penghuni', '2026-10-04 19:06:33'),
(10, NULL, 'Logout', 'Autentikasi', 'penghuni', '2026-10-04 19:06:50'),
(11, 2, 'Login', 'Autentikasi', 'pengelola', '2026-10-04 19:10:28'),
(12, 2, 'Logout', 'Autentikasi', 'pengelola', '2026-10-05 08:05:55'),
(13, 2, 'Login', 'Autentikasi', 'pengelola', '2026-10-05 08:06:44'),
(14, 2, 'Logout', 'Autentikasi', 'pengelola', '2026-10-05 08:37:27'),
(15, 2, 'Login', 'Autentikasi', 'pengelola', '2026-10-05 08:37:50'),
(16, 2, 'Logout', 'Autentikasi', 'pengelola', '2026-10-05 08:38:53'),
(17, NULL, 'Login', 'Autentikasi', 'penghuni', '2026-10-05 08:39:04'),
(18, NULL, 'Logout', 'Autentikasi', 'penghuni', '2026-10-05 08:40:24'),
(19, 2, 'Login', 'Autentikasi', 'pengelola', '2026-10-05 08:40:36'),
(20, 2, 'Logout', 'Autentikasi', 'pengelola', '2026-10-05 08:51:46'),
(21, NULL, 'Login', 'Autentikasi', 'penghuni', '2026-10-05 08:51:59'),
(22, NULL, 'Logout', 'Autentikasi', 'penghuni', '2026-10-05 08:52:04'),
(23, NULL, 'Login', 'Autentikasi', 'amir', '2026-10-05 08:52:10'),
(24, NULL, 'Mengubah password', 'Pengaturan Akun', 'Password akun diperbarui', '2026-10-05 08:52:55'),
(25, NULL, 'Logout', 'Autentikasi', 'amir', '2026-10-05 08:53:33'),
(26, 2, 'Login', 'Autentikasi', 'pengelola', '2026-10-05 08:53:45'),
(27, 2, 'Logout', 'Autentikasi', 'pengelola', '2026-10-05 09:00:28'),
(28, NULL, 'Login', 'Autentikasi', 'amir', '2026-10-05 09:00:40'),
(29, NULL, 'Logout', 'Autentikasi', 'amir', '2026-10-05 09:14:32'),
(30, 2, 'Login', 'Autentikasi', 'pengelola', '2026-10-05 09:14:55'),
(31, 2, 'Logout', 'Autentikasi', 'pengelola', '2026-10-05 09:24:45'),
(32, 2, 'Login', 'Autentikasi', 'pengelola', '2026-10-05 09:25:12'),
(33, 2, 'Menambahkan akun admin', 'Manajemen Pengguna', 'okta', '2026-10-05 09:26:04'),
(34, 2, 'Logout', 'Autentikasi', 'pengelola', '2026-10-05 09:39:51'),
(35, 2, 'Login', 'Autentikasi', 'pengelola', '2026-10-05 09:42:23'),
(36, 2, 'Menambahkan akun admin', 'Manajemen Pengguna', 'okta', '2026-10-05 09:43:35'),
(37, 2, 'Mengubah password sendiri', 'Pengaturan Akun', 'Password akun diperbarui', '2026-10-05 10:07:42'),
(38, 2, 'Logout', 'Autentikasi', 'pengelola', '2026-10-05 10:07:58'),
(39, NULL, 'Login', 'Autentikasi', 'amir', '2026-10-05 10:08:05'),
(40, NULL, 'Logout', 'Autentikasi', 'amir', '2026-10-05 10:08:26'),
(41, NULL, 'Login', 'Autentikasi', 'okta', '2026-10-05 10:08:37'),
(42, NULL, 'Logout', 'Autentikasi', 'okta', '2026-10-05 10:10:40'),
(43, NULL, 'Login', 'Autentikasi', 'windah', '2026-10-05 10:10:47'),
(44, NULL, 'Mengubah password', 'Pengaturan Akun', 'Password akun diperbarui', '2026-10-05 10:14:07'),
(45, NULL, 'Logout', 'Autentikasi', 'windah', '2026-10-05 10:14:28'),
(46, NULL, 'Login', 'Autentikasi', 'okta', '2026-10-05 10:14:35'),
(47, NULL, 'Mengubah password sendiri', 'Pengaturan Akun', 'Password akun diperbarui', '2026-10-05 10:34:03'),
(48, NULL, 'Logout', 'Autentikasi', 'okta', '2026-10-05 10:48:02'),
(49, NULL, 'Login', 'Autentikasi', 'okta', '2026-10-05 10:48:15'),
(50, NULL, 'Mengubah password sendiri', 'Pengaturan Akun', 'Password akun diperbarui', '2026-10-05 10:48:34'),
(51, NULL, 'Logout', 'Autentikasi', 'okta', '2026-10-05 10:50:29'),
(52, 2, 'Login', 'Autentikasi', 'pengelola', '2026-10-05 10:50:55'),
(53, 2, 'Logout', 'Autentikasi', 'pengelola', '2026-10-05 11:44:37'),
(54, NULL, 'Login', 'Autentikasi', 'wahyu', '2026-10-05 11:44:43'),
(55, NULL, 'Logout', 'Autentikasi', 'wahyu', '2026-10-05 11:46:02'),
(56, NULL, 'Login', 'Autentikasi', 'okta', '2026-10-05 11:46:13'),
(57, NULL, 'Logout', 'Autentikasi', 'okta', '2026-10-05 11:48:36'),
(58, NULL, 'Login', 'Autentikasi', 'sultan', '2026-10-05 11:48:45'),
(59, NULL, 'Logout', 'Autentikasi', 'sultan', '2026-10-05 12:06:44'),
(60, NULL, 'Login', 'Autentikasi', 'windah', '2026-10-05 12:07:14'),
(61, NULL, 'Logout', 'Autentikasi', 'windah', '2026-10-05 12:08:02'),
(62, NULL, 'Login', 'Autentikasi', 'windah', '2026-10-05 12:08:21'),
(63, NULL, 'Logout', 'Autentikasi', 'windah', '2026-10-05 12:08:33'),
(64, NULL, 'Login', 'Autentikasi', 'okta', '2026-10-05 12:08:42'),
(65, NULL, 'Logout', 'Autentikasi', 'okta', '2026-10-05 12:09:44'),
(66, NULL, 'Login', 'Autentikasi', 'sultan', '2026-10-05 12:09:55'),
(67, NULL, 'Logout', 'Autentikasi', 'sultan', '2026-10-05 12:10:50'),
(68, NULL, 'Login', 'Autentikasi', 'okta', '2026-10-05 12:11:12'),
(69, NULL, 'Logout', 'Autentikasi', 'okta', '2026-10-05 12:12:24'),
(70, NULL, 'Login', 'Autentikasi', 'sheva', '2026-10-05 12:12:41'),
(71, NULL, 'Logout', 'Autentikasi', 'sheva', '2026-10-05 12:13:01'),
(72, NULL, 'Login', 'Autentikasi', 'okta', '2026-10-05 12:13:13'),
(73, NULL, 'Logout', 'Autentikasi', 'okta', '2026-10-05 12:40:00'),
(74, NULL, 'Login', 'Autentikasi', 'okta', '2026-10-05 12:40:24'),
(75, NULL, 'Logout', 'Autentikasi', 'okta', '2026-10-05 12:43:28'),
(76, NULL, 'Login', 'Autentikasi', 'haza', '2026-10-05 12:43:37'),
(77, NULL, 'Logout', 'Autentikasi', 'haza', '2026-10-05 13:26:50'),
(78, 2, 'Login', 'Autentikasi', 'pengelola', '2026-10-05 13:27:02'),
(79, 2, 'Logout', 'Autentikasi', 'pengelola', '2026-10-06 01:32:53'),
(80, NULL, 'Login', 'Autentikasi', 'okta', '2026-10-06 01:33:10'),
(81, NULL, 'Logout', 'Autentikasi', 'okta', '2026-10-06 01:47:48'),
(82, 2, 'Login', 'Autentikasi', 'pengelola', '2026-10-06 01:48:12'),
(83, 2, 'Logout', 'Autentikasi', 'pengelola', '2026-10-06 01:49:43'),
(84, NULL, 'Login', 'Autentikasi', 'amir', '2026-10-06 01:49:55'),
(85, NULL, 'Logout', 'Autentikasi', 'amir', '2026-10-06 01:49:59'),
(86, NULL, 'Login', 'Autentikasi', 'okta', '2026-10-06 01:50:09'),
(87, NULL, 'Logout', 'Autentikasi', 'okta', '2026-10-06 01:51:16'),
(88, 2, 'Login', 'Autentikasi', 'pengelola', '2026-10-06 01:51:23'),
(89, 2, 'Logout', 'Autentikasi', 'pengelola', '2026-10-06 01:51:45'),
(90, NULL, 'Login', 'Autentikasi', 'aji', '2026-10-06 01:51:50'),
(91, NULL, 'Logout', 'Autentikasi', 'aji', '2026-10-06 01:52:26'),
(92, NULL, 'Login', 'Autentikasi', 'okta', '2026-10-06 01:52:39'),
(93, NULL, 'Logout', 'Autentikasi', 'okta', '2026-10-06 01:56:12'),
(94, NULL, 'Login', 'Autentikasi', 'aji', '2026-10-06 01:56:18'),
(95, NULL, 'Logout', 'Autentikasi', 'aji', '2026-10-06 01:56:41'),
(96, NULL, 'Login', 'Autentikasi', 'okta', '2026-10-06 01:56:49'),
(97, NULL, 'Logout', 'Autentikasi', 'okta', '2026-10-06 02:07:08'),
(98, NULL, 'Login', 'Autentikasi', 'aji', '2026-10-06 02:07:14'),
(99, NULL, 'Logout', 'Autentikasi', 'aji', '2026-10-06 02:15:12'),
(100, NULL, 'Login', 'Autentikasi', 'okta', '2026-10-06 02:15:22'),
(101, NULL, 'Logout', 'Autentikasi', 'okta', '2026-10-06 02:16:21'),
(102, NULL, 'Login', 'Autentikasi', 'aji', '2026-10-06 02:16:25'),
(103, NULL, 'Logout', 'Autentikasi', 'aji', '2026-10-06 02:17:53'),
(104, NULL, 'Login', 'Autentikasi', 'okta', '2026-10-06 02:17:58'),
(105, NULL, 'Logout', 'Autentikasi', 'okta', '2026-10-06 02:24:37'),
(106, 2, 'Login', 'Autentikasi', 'pengelola', '2026-10-06 02:24:46'),
(107, 2, 'Logout', 'Autentikasi', 'pengelola', '2026-10-06 02:30:34'),
(108, NULL, 'Login', 'Autentikasi', 'okta', '2026-10-06 02:30:42'),
(109, NULL, 'Logout', 'Autentikasi', 'okta', '2026-10-06 02:30:52'),
(110, NULL, 'Login', 'Autentikasi', 'aji', '2026-10-06 02:30:57'),
(111, NULL, 'Logout', 'Autentikasi', 'aji', '2026-10-06 02:31:46'),
(112, NULL, 'Login', 'Autentikasi', 'okta', '2026-10-06 02:31:53'),
(113, NULL, 'Logout', 'Autentikasi', 'okta', '2026-10-06 02:34:11'),
(114, 2, 'Login', 'Autentikasi', 'pengelola', '2026-10-06 02:34:20'),
(115, NULL, 'Login', 'Autentikasi', 'okta', '2026-10-12 07:14:37'),
(116, NULL, 'Logout', 'Autentikasi', 'okta', '2026-10-12 13:23:58'),
(117, 2, 'Login', 'Autentikasi', 'pengelola', '2026-10-12 13:24:08'),
(118, 2, 'Logout', 'Autentikasi', 'pengelola', '2026-10-12 14:13:16'),
(119, 2, 'Login', 'Autentikasi', 'pengelola', '2026-10-12 14:13:28'),
(120, 2, 'Logout', 'Autentikasi', 'pengelola', '2026-10-12 14:17:20'),
(121, NULL, 'Login', 'Autentikasi', 'okta', '2026-10-12 14:27:15'),
(122, NULL, 'Logout', 'Autentikasi', 'okta', '2026-10-12 14:36:56'),
(123, 2, 'Login', 'Autentikasi', 'pengelola', '2026-10-12 14:37:03'),
(124, 2, 'Logout', 'Autentikasi', 'pengelola', '2026-10-12 15:09:35'),
(125, 2, 'Login', 'Autentikasi', 'pengelola', '2026-10-12 15:09:45'),
(126, 2, 'Logout', 'Autentikasi', 'pengelola', '2026-10-12 15:22:00'),
(127, 17, 'Login', 'Autentikasi', 'aji', '2026-10-12 15:22:06'),
(128, 17, 'Logout', 'Autentikasi', 'aji', '2026-10-12 15:28:04'),
(129, 2, 'Login', 'Autentikasi', 'pengelola', '2026-10-12 15:28:11'),
(130, 2, 'Logout', 'Autentikasi', 'pengelola', '2026-10-12 15:29:33'),
(131, 18, 'Login', 'Autentikasi', 'nayla', '2026-10-12 15:29:43'),
(132, 18, 'Mengubah username sendiri', 'Pengaturan Akun', 'Username: nayla -> naylay', '2026-10-12 15:30:58'),
(133, 18, 'Logout', 'Autentikasi', 'naylay', '2026-10-12 15:33:24'),
(134, 2, 'Login', 'Autentikasi', 'pengelola', '2026-10-12 15:33:35'),
(135, 2, 'Logout', 'Autentikasi', 'pengelola', '2026-10-12 15:34:41'),
(136, 19, 'Login', 'Autentikasi', 'amir', '2026-10-12 15:34:46'),
(137, 19, 'Mengubah username sendiri', 'Pengaturan Akun', 'Username: amir -> amirz', '2026-10-12 15:35:11'),
(138, 19, 'Mengubah password', 'Pengaturan Akun', 'Password akun diperbarui', '2026-10-12 15:35:28'),
(139, 19, 'Logout', 'Autentikasi', 'amirz', '2026-10-12 15:35:45'),
(140, 2, 'Login', 'Autentikasi', 'pengelola', '2026-10-12 15:35:54'),
(141, 2, 'Menambahkan akun admin', 'Manajemen Pengguna', 'okta', '2026-10-12 15:44:06'),
(142, 2, 'Logout', 'Autentikasi', 'pengelola', '2026-10-12 15:47:39'),
(143, 20, 'Login', 'Autentikasi', 'okta', '2026-10-12 15:47:45'),
(144, 20, 'Logout', 'Autentikasi', 'okta', '2026-10-12 15:56:47'),
(145, 20, 'Login', 'Autentikasi', 'okta', '2026-10-12 15:57:36'),
(146, 20, 'Logout', 'Autentikasi', 'okta', '2026-10-12 15:57:47'),
(147, 17, 'Login', 'Autentikasi', 'aji', '2026-10-12 15:57:58'),
(148, 17, 'Logout', 'Autentikasi', 'aji', '2026-10-12 16:49:55'),
(149, 20, 'Login', 'Autentikasi', 'okta', '2026-10-12 16:50:03'),
(150, 20, 'Logout', 'Autentikasi', 'okta', '2026-10-12 16:52:25'),
(151, 2, 'Login', 'Autentikasi', 'pengelola', '2026-10-12 16:53:00'),
(152, 2, 'Logout', 'Autentikasi', 'pengelola', '2026-10-12 16:55:22'),
(153, 2, 'Login', 'Autentikasi', 'pengelola', '2026-10-12 16:55:32'),
(154, 2, 'Logout', 'Autentikasi', 'pengelola', '2026-10-12 16:56:39'),
(155, 19, 'Login', 'Autentikasi', 'amirz', '2026-10-12 16:56:50'),
(156, 19, 'Logout', 'Autentikasi', 'amirz', '2026-10-12 16:57:36'),
(157, 20, 'Login', 'Autentikasi', 'okta', '2026-10-12 16:57:43'),
(158, 20, 'Logout', 'Autentikasi', 'okta', '2026-10-12 16:58:06'),
(159, 19, 'Login', 'Autentikasi', 'amirz', '2026-10-12 16:58:13'),
(160, 19, 'Mengubah username sendiri', 'Pengaturan Akun', 'Username: amirz -> amir', '2026-10-12 16:59:07'),
(161, 19, 'Logout', 'Autentikasi', 'amir', '2026-10-12 16:59:11'),
(162, 2, 'Login', 'Autentikasi', 'pengelola', '2026-10-12 16:59:19'),
(163, 20, 'Login', 'Autentikasi', 'okta', '2026-10-13 01:32:21'),
(164, 20, 'Logout', 'Autentikasi', 'okta', '2026-10-13 01:32:41'),
(165, 2, 'Login', 'Autentikasi', 'pengelola', '2026-10-13 01:33:00'),
(166, 2, 'Logout', 'Autentikasi', 'pengelola', '2026-10-13 01:40:01'),
(167, 2, 'Login', 'Autentikasi', 'pengelola', '2026-10-13 01:42:28'),
(168, 2, 'Logout', 'Autentikasi', 'pengelola', '2026-10-13 01:46:45'),
(169, 20, 'Login', 'Autentikasi', 'okta', '2026-10-13 01:46:52'),
(170, 20, 'Logout', 'Autentikasi', 'okta', '2026-10-13 01:47:24'),
(171, 19, 'Login', 'Autentikasi', 'amir', '2026-10-13 01:47:30'),
(172, 19, 'Logout', 'Autentikasi', 'amir', '2026-10-13 01:48:34'),
(173, 2, 'Login', 'Autentikasi', 'pengelola', '2026-10-13 01:48:43'),
(174, 2, 'Logout', 'Autentikasi', 'pengelola', '2026-10-13 01:49:50'),
(175, 21, 'Login', 'Autentikasi', 'ari', '2026-10-13 01:49:56'),
(176, 21, 'Logout', 'Autentikasi', 'ari', '2026-10-13 01:54:49'),
(177, 2, 'Login', 'Autentikasi', 'pengelola', '2026-10-13 01:54:55'),
(178, 2, 'Logout', 'Autentikasi', 'pengelola', '2026-10-13 02:06:47'),
(179, 23, 'Login', 'Autentikasi', 'sultan', '2026-10-13 02:06:53'),
(180, 23, 'Logout', 'Autentikasi', 'sultan', '2026-10-13 02:07:07'),
(181, 2, 'Login', 'Autentikasi', 'pengelola', '2026-10-13 02:07:13'),
(182, 2, 'Login', 'Autentikasi', 'pengelola', '2026-10-15 12:18:21'),
(183, 2, 'Menambahkan akun admin', 'Manajemen Pengguna', 'jasmine', '2026-10-15 12:24:49'),
(184, 2, 'Mengubah password sendiri', 'Pengaturan Akun', 'Password akun diperbarui', '2026-10-15 12:25:55'),
(185, 2, 'Logout', 'Autentikasi', 'pengelola', '2026-10-15 12:26:06'),
(186, 25, 'Login', 'Autentikasi', 'luis', '2026-10-15 12:27:50'),
(187, 25, 'Mengubah username sendiri', 'Pengaturan Akun', 'Username: luis -> luisz', '2026-10-15 12:28:33'),
(188, 25, 'Mengubah password', 'Pengaturan Akun', 'Password akun diperbarui', '2026-10-15 12:28:49'),
(189, 25, 'Logout', 'Autentikasi', 'luisz', '2026-10-15 12:28:57'),
(190, 26, 'Login', 'Autentikasi', 'jasmine', '2026-10-15 12:29:06');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kamar`
--

CREATE TABLE `kamar` (
  `id_kamar` int(10) UNSIGNED NOT NULL,
  `nomor_kamar` varchar(20) NOT NULL,
  `harga_sewa` decimal(12,2) NOT NULL,
  `fasilitas` text DEFAULT NULL,
  `status_kamar` enum('tersedia','terisi') NOT NULL DEFAULT 'tersedia',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `kamar`
--

INSERT INTO `kamar` (`id_kamar`, `nomor_kamar`, `harga_sewa`, `fasilitas`, `status_kamar`, `created_at`, `updated_at`) VALUES
(1, 'A01', 500000.00, 'Kasur, lemari, meja', 'terisi', '2026-10-04 18:40:01', '2026-10-13 02:07:30'),
(5, 'A02', 1100000.00, 'Kasur, lemari, ac, meja, kursi', 'terisi', '2026-10-05 09:45:44', '2026-10-12 15:34:17'),
(6, 'B02', 500000.00, 'kipas', 'tersedia', '2026-10-05 10:09:41', '2026-10-12 15:03:53'),
(7, 'B01', 1000000.00, 'kasur, lemari, kamar mandi dalam', 'tersedia', '2026-10-05 11:46:42', '2026-10-12 15:03:51'),
(8, 'A03', 500000.00, 'Kasur, lemari', 'terisi', '2026-10-05 11:47:15', '2026-10-12 15:29:08'),
(9, 'B03', 600000.00, 'Kasur', 'tersedia', '2026-10-05 12:14:01', '2026-10-12 15:02:09'),
(10, 'A04', 900000.00, 'Kasur, lemari, kipas, ac, meja', 'tersedia', '2026-10-05 12:41:20', '2026-10-13 02:02:52'),
(11, 'A05', 600000.00, 'Kasur, Meja, Kursi, KIpas', 'terisi', '2026-10-15 12:19:00', '2026-10-15 12:29:57');

-- --------------------------------------------------------

--
-- Struktur dari tabel `keluhan`
--

CREATE TABLE `keluhan` (
  `id_keluhan` int(10) UNSIGNED NOT NULL,
  `id_penghuni` int(10) UNSIGNED NOT NULL,
  `tanggal_keluhan` date NOT NULL,
  `isi_keluhan` text NOT NULL,
  `kategori_keluhan` varchar(50) DEFAULT NULL,
  `status_penanganan` enum('menunggu','diproses','selesai') NOT NULL DEFAULT 'menunggu',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `keluhan`
--

INSERT INTO `keluhan` (`id_keluhan`, `id_penghuni`, `tanggal_keluhan`, `isi_keluhan`, `kategori_keluhan`, `status_penanganan`, `created_at`) VALUES
(3, 15, '2026-10-12', 'Bangku di ruang tamu rusak', 'fasilitas', 'selesai', '2026-10-12 16:57:23'),
(4, 20, '2026-10-15', 'kaki mejanya patah', 'fasilitas', 'selesai', '2026-10-15 12:28:21');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id_pembayaran` int(10) UNSIGNED NOT NULL,
  `id_penghuni` int(10) UNSIGNED NOT NULL,
  `tanggal_pembayaran` date DEFAULT NULL,
  `periode_sewa` date NOT NULL,
  `jumlah_pembayaran` decimal(12,2) NOT NULL,
  `status_pembayaran` enum('belum_bayar','lunas','terlambat') NOT NULL DEFAULT 'belum_bayar',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `pembayaran`
--

INSERT INTO `pembayaran` (`id_pembayaran`, `id_penghuni`, `tanggal_pembayaran`, `periode_sewa`, `jumlah_pembayaran`, `status_pembayaran`, `created_at`) VALUES
(10, 13, NULL, '0000-00-00', 500000.00, '', '2026-10-12 15:22:06'),
(11, 13, NULL, '2026-06-01', 500000.00, 'belum_bayar', '2026-10-12 15:27:52'),
(12, 13, NULL, '2026-07-01', 500000.00, 'belum_bayar', '2026-10-12 15:27:52'),
(13, 13, NULL, '2026-08-01', 500000.00, 'belum_bayar', '2026-10-12 15:27:52'),
(14, 13, NULL, '2026-09-01', 500000.00, 'belum_bayar', '2026-10-12 15:27:52'),
(15, 13, NULL, '2026-10-01', 500000.00, 'belum_bayar', '2026-10-12 15:27:52'),
(16, 14, '2026-10-12', '2026-08-01', 500000.00, 'lunas', '2026-10-12 15:29:16'),
(17, 14, '2026-10-12', '2026-09-01', 500000.00, 'lunas', '2026-10-12 15:29:16'),
(18, 14, '2026-10-12', '2026-10-01', 500000.00, 'lunas', '2026-10-12 15:29:16'),
(19, 15, '2026-10-12', '2026-10-01', 1100000.00, 'lunas', '2026-10-12 15:34:46'),
(20, 16, NULL, '2026-05-01', 900000.00, 'belum_bayar', '2026-10-13 01:49:41'),
(21, 16, NULL, '2026-06-01', 900000.00, 'belum_bayar', '2026-10-13 01:49:41'),
(22, 16, NULL, '2026-07-01', 900000.00, 'belum_bayar', '2026-10-13 01:49:41'),
(23, 16, NULL, '2026-08-01', 900000.00, 'belum_bayar', '2026-10-13 01:49:41'),
(24, 16, NULL, '2026-09-01', 900000.00, 'belum_bayar', '2026-10-13 01:49:41'),
(25, 16, NULL, '2026-10-01', 900000.00, 'belum_bayar', '2026-10-13 01:49:41'),
(26, 17, NULL, '2026-10-01', 900000.00, 'belum_bayar', '2026-10-13 02:03:44'),
(27, 18, NULL, '2026-01-01', 500000.00, 'belum_bayar', '2026-10-13 02:06:09'),
(28, 18, NULL, '2026-02-01', 500000.00, 'belum_bayar', '2026-10-13 02:06:09'),
(29, 18, NULL, '2026-03-01', 500000.00, 'belum_bayar', '2026-10-13 02:06:09'),
(30, 18, NULL, '2026-04-01', 500000.00, 'belum_bayar', '2026-10-13 02:06:09'),
(31, 18, NULL, '2026-05-01', 500000.00, 'belum_bayar', '2026-10-13 02:06:09'),
(32, 18, NULL, '2026-06-01', 500000.00, 'belum_bayar', '2026-10-13 02:06:09'),
(33, 18, NULL, '2026-07-01', 500000.00, 'belum_bayar', '2026-10-13 02:06:09'),
(34, 18, NULL, '2026-08-01', 500000.00, 'belum_bayar', '2026-10-13 02:06:09'),
(35, 18, NULL, '2026-09-01', 500000.00, 'belum_bayar', '2026-10-13 02:06:09'),
(36, 18, '2026-10-13', '2026-10-01', 500000.00, 'lunas', '2026-10-13 02:06:09'),
(46, 19, '2026-01-06', '2026-01-01', 900000.00, 'lunas', '2026-10-13 02:24:34'),
(47, 19, '2026-02-06', '2026-02-01', 900000.00, 'lunas', '2026-10-13 02:24:34'),
(48, 19, '2026-03-06', '2026-03-01', 900000.00, 'lunas', '2026-10-13 02:24:34'),
(49, 19, '2026-04-06', '2026-04-01', 900000.00, 'lunas', '2026-10-13 02:24:34'),
(50, 19, '2026-05-06', '2026-05-01', 900000.00, 'lunas', '2026-10-13 02:24:34'),
(51, 19, '2026-06-06', '2026-06-01', 900000.00, 'lunas', '2026-10-13 02:24:34'),
(52, 19, '2026-07-06', '2026-07-01', 900000.00, 'lunas', '2026-10-13 02:24:34'),
(53, 19, '2026-08-06', '2026-08-01', 900000.00, 'lunas', '2026-10-13 02:24:34'),
(54, 19, '2026-09-06', '2026-09-01', 900000.00, 'lunas', '2026-10-13 02:24:34'),
(55, 19, NULL, '2026-10-01', 900000.00, 'belum_bayar', '2026-10-13 02:24:34'),
(56, 20, '2026-10-15', '2026-10-01', 600000.00, 'lunas', '2026-10-15 12:20:11');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengeluaran`
--

CREATE TABLE `pengeluaran` (
  `id_pengeluaran` int(10) UNSIGNED NOT NULL,
  `tanggal_pengeluaran` date NOT NULL,
  `jenis_pengeluaran` varchar(100) NOT NULL,
  `jumlah` decimal(12,2) NOT NULL,
  `catatan` text DEFAULT NULL,
  `id_user` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `pengeluaran`
--

INSERT INTO `pengeluaran` (`id_pengeluaran`, `tanggal_pengeluaran`, `jenis_pengeluaran`, `jumlah`, `catatan`, `id_user`, `created_at`, `updated_at`) VALUES
(1, '2026-10-06', 'Membeli gas', 20000.00, 'Membeli gas untuk lantai 2', NULL, '2026-10-06 02:19:43', '2026-10-06 02:19:43'),
(2, '2026-10-06', 'Perbaiki Kipas', 200000.00, 'Perbaiki Kipas kamar B02 lantai 2', NULL, '2026-10-06 02:21:40', '2026-10-06 02:21:40'),
(3, '2026-10-06', 'LIstrik', 600000.00, NULL, NULL, '2026-10-06 02:32:12', '2026-10-06 02:32:12'),
(4, '2026-07-06', 'Air', 300000.00, NULL, NULL, '2026-10-06 02:33:17', '2026-10-06 02:33:17'),
(13, '2026-01-15', 'Simulasi presentasi', 250000.00, 'DATA SIMULASI PRESENTASI - bukan transaksi asli', NULL, '2026-10-13 02:36:19', '2026-10-13 02:36:19'),
(14, '2026-02-15', 'Simulasi presentasi', 350000.00, 'DATA SIMULASI PRESENTASI - bukan transaksi asli', NULL, '2026-10-13 02:36:19', '2026-10-13 02:36:19'),
(15, '2026-03-15', 'Simulasi presentasi', 200000.00, 'DATA SIMULASI PRESENTASI - bukan transaksi asli', NULL, '2026-10-13 02:36:19', '2026-10-13 02:36:19'),
(16, '2026-04-15', 'Simulasi presentasi', 450000.00, 'DATA SIMULASI PRESENTASI - bukan transaksi asli', NULL, '2026-10-13 02:36:19', '2026-10-13 02:36:19'),
(17, '2026-05-15', 'Simulasi presentasi', 300000.00, 'DATA SIMULASI PRESENTASI - bukan transaksi asli', NULL, '2026-10-13 02:36:19', '2026-10-13 02:36:19'),
(18, '2026-06-15', 'Simulasi presentasi', 400000.00, 'DATA SIMULASI PRESENTASI - bukan transaksi asli', NULL, '2026-10-13 02:36:19', '2026-10-13 02:36:19'),
(19, '2026-08-15', 'Simulasi presentasi', 275000.00, 'DATA SIMULASI PRESENTASI - bukan transaksi asli', NULL, '2026-10-13 02:36:19', '2026-10-13 02:36:19'),
(20, '2026-09-15', 'Simulasi presentasi', 325000.00, 'DATA SIMULASI PRESENTASI - bukan transaksi asli', NULL, '2026-10-13 02:36:19', '2026-10-13 02:36:19');

-- --------------------------------------------------------

--
-- Struktur dari tabel `penghuni`
--

CREATE TABLE `penghuni` (
  `id_penghuni` int(10) UNSIGNED NOT NULL,
  `id_user` int(10) UNSIGNED DEFAULT NULL,
  `id_kamar` int(10) UNSIGNED DEFAULT NULL,
  `nama` varchar(100) NOT NULL,
  `nomor_identitas_mahasiswa` varchar(30) NOT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  `tanggal_mulai_tinggal` date NOT NULL,
  `status_penghuni` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `penghuni`
--

INSERT INTO `penghuni` (`id_penghuni`, `id_user`, `id_kamar`, `nama`, `nomor_identitas_mahasiswa`, `no_telepon`, `tanggal_mulai_tinggal`, `status_penghuni`, `created_at`) VALUES
(13, 17, 1, 'Aji Pamungkas', '250605110188', '081290902020', '2026-06-11', 'nonaktif', '2026-10-12 15:18:39'),
(14, 18, 8, 'Salsabila Nayy', '250605110200', '081280805050', '2026-08-01', 'aktif', '2026-10-12 15:29:08'),
(15, 19, 5, 'Amir Muzaki', '250605110300', '081280817272', '2026-10-12', 'aktif', '2026-10-12 15:34:17'),
(16, 21, 10, 'ari', '250605220292', '08122084402', '2026-05-06', 'nonaktif', '2026-10-13 01:49:33'),
(17, 22, 10, 'Ridho Atalla', '25060410193', '08122084402', '2026-10-13', 'nonaktif', '2026-10-13 02:03:44'),
(18, 23, 1, 'Sultan Mahawira', '2506983082', '0812875609', '2026-01-01', 'aktif', '2026-10-13 02:06:09'),
(19, 24, 10, 'Zafa Maliki', '250692032', '081220886721', '2026-01-01', 'nonaktif', '2026-10-13 02:24:34'),
(20, 25, 11, 'Luis Lufangton', '24060530292', '081290908013', '2026-10-15', 'aktif', '2026-10-15 12:20:11');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id_user` int(10) UNSIGNED NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `password_terbuka` text DEFAULT NULL,
  `role` enum('pengelola','admin','penghuni') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id_user`, `nama`, `username`, `password`, `password_terbuka`, `role`, `created_at`) VALUES
(2, 'Pengelola Kos', 'pengelola', '$2y$10$u60d/lT58nYiEi1oxQlvEujpaig7.napE.GAwmHXoW8L0aye15S6a', 'EBKL4BEjHeeRbqt9CwSoTVfDyz7jYbcdsckxwv72aPZ4B+d4/A==', 'pengelola', '2026-10-04 18:33:36'),
(17, 'Aji Pamungkas', 'aji', '$2y$10$376uXzy1BszdfoX1aos5Vuq/8I5.7LRHxqCFEdAJgrqgtiW7KDFX6', 'txWzuPxLFQ3xP5F2V3KTSsiNn2Ty2O3nUqCySYA4moQSvUfR', 'penghuni', '2026-10-12 15:18:39'),
(18, 'Salsabila Nayy', 'naylay', '$2y$10$3TDs9NqhidFGc9atVU6.PuHmw6Q.eLNgEZRCyUUMLIoWyLI6x2Ghi', 'QXb7VRkNAKik3thKgfhqZapSwTSLvrNZB2Q5rQwSdmYbJ3ijrDY=', 'penghuni', '2026-10-12 15:29:08'),
(19, 'Amir Muzaki', 'amir', '$2y$10$c0r.f0AVIh/v9bkTpunJgOBQLS8DLAO4TeUMiOQ1g9PiNqqo6.sS.', 'g32vZwFeK3Nr/ilkAs5qlo9nmjLMXP6kkDJ09QSDdyxxMXy2', 'penghuni', '2026-10-12 15:34:17'),
(20, 'Rizky Okta', 'okta', '$2y$10$y2tiPIQqDKZJmPDJdk//QelP8c4O0t.C9Fxj/XG.uAfD/1koBPwky', 'sgvZ8dLojUPMZL4eUEHUghwMTCGbtT7PRzBA4UjZY6XHwzTMQg==', 'admin', '2026-10-12 15:44:06'),
(21, 'ari', 'ari', '$2y$10$TiGQKGhuLN0isOPqQ4ECy.9YRnCHAPz6RI9YhvpcNPdAQ4p93lm4.', 'kjyHw58U2walVFhgp9/AwCC/jryBhGnfi3uKA9OMsTdldQqK', 'penghuni', '2026-10-13 01:49:33'),
(22, 'Ridho Atalla', 'ridho', '$2y$10$c.mznuqPbXIQezCgUlmGgOif.zqZpkUqv7btZxdRvLVIv7265H/q.', '5ueXmW0OSd5OsIsdR6DfvQhI/0lDUzkwybH4XT/lTJPbbKhkawI=', 'penghuni', '2026-10-13 02:03:44'),
(23, 'Sultan Mahawira', 'sultan', '$2y$10$IWcwb0PkfmjoBjEgp6N3l.LUWFKsMdxdJttG9uLETVkVdCbxkk1l.', 'Dt3V89XzeJP3p1HbfrK8XbYsMPFFcvQXSH6dedNvTuLQ+dCWKcQy', 'penghuni', '2026-10-13 02:06:09'),
(24, 'Zafa Maliki', 'zafa', '$2y$10$0qeTkwf16DKP39wz2XC4keLJl7usQKrVUZEHpH0NQSu.B7tXkYjYW', '/E0z21q3j1yrVgHg1O9ZgkslXSeCVz+KyANnCCj0igITL+7Yxg==', 'penghuni', '2026-10-13 02:24:34'),
(25, 'Luis Lufangton', 'luisz', '$2y$10$3peVccW3PMUnHalopmtCB.3WbJvUmS4eU9lB2mUr6KG1Rj8Z5u9BO', 'Z3E90AH9SdjC5otJgm6m/qmZ9OTQ0/wDOMBCLA5oG4kum++w', 'penghuni', '2026-10-15 12:20:11'),
(26, 'Jasmine Aulia', 'jasmine', '$2y$10$7X5cn8c7KSgB9ElxnBFW9ezC4AONqlnQTbw.F2UnRhSsobsy385Um', '4NR6g+APtHORdZeFwLEFhHGGGK3R9RQ3+Jfd6xhB5/1wNSDxW+0nOw==', 'admin', '2026-10-15 12:24:49');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `fk_logs_user` (`id_user`);

--
-- Indeks untuk tabel `kamar`
--
ALTER TABLE `kamar`
  ADD PRIMARY KEY (`id_kamar`),
  ADD UNIQUE KEY `nomor_kamar` (`nomor_kamar`);

--
-- Indeks untuk tabel `keluhan`
--
ALTER TABLE `keluhan`
  ADD PRIMARY KEY (`id_keluhan`),
  ADD KEY `fk_keluhan_penghuni` (`id_penghuni`);

--
-- Indeks untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD UNIQUE KEY `uq_pembayaran_periode` (`id_penghuni`,`periode_sewa`);

--
-- Indeks untuk tabel `pengeluaran`
--
ALTER TABLE `pengeluaran`
  ADD PRIMARY KEY (`id_pengeluaran`),
  ADD KEY `idx_pengeluaran_tanggal` (`tanggal_pengeluaran`),
  ADD KEY `idx_pengeluaran_jenis` (`jenis_pengeluaran`),
  ADD KEY `idx_pengeluaran_user` (`id_user`);

--
-- Indeks untuk tabel `penghuni`
--
ALTER TABLE `penghuni`
  ADD PRIMARY KEY (`id_penghuni`),
  ADD UNIQUE KEY `nomor_identitas_mahasiswa` (`nomor_identitas_mahasiswa`),
  ADD UNIQUE KEY `id_user` (`id_user`),
  ADD KEY `fk_penghuni_kamar` (`id_kamar`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id_log` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=191;

--
-- AUTO_INCREMENT untuk tabel `kamar`
--
ALTER TABLE `kamar`
  MODIFY `id_kamar` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `keluhan`
--
ALTER TABLE `keluhan`
  MODIFY `id_keluhan` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id_pembayaran` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT untuk tabel `pengeluaran`
--
ALTER TABLE `pengeluaran`
  MODIFY `id_pengeluaran` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT untuk tabel `penghuni`
--
ALTER TABLE `penghuni`
  MODIFY `id_penghuni` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `fk_logs_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `keluhan`
--
ALTER TABLE `keluhan`
  ADD CONSTRAINT `fk_keluhan_penghuni` FOREIGN KEY (`id_penghuni`) REFERENCES `penghuni` (`id_penghuni`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `fk_pembayaran_penghuni` FOREIGN KEY (`id_penghuni`) REFERENCES `penghuni` (`id_penghuni`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pengeluaran`
--
ALTER TABLE `pengeluaran`
  ADD CONSTRAINT `fk_pengeluaran_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `penghuni`
--
ALTER TABLE `penghuni`
  ADD CONSTRAINT `fk_penghuni_kamar` FOREIGN KEY (`id_kamar`) REFERENCES `kamar` (`id_kamar`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_penghuni_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
