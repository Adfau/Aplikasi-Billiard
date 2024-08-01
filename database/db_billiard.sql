-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 21, 2024 at 12:24 AM
-- Server version: 8.0.36
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET GLOBAL event_scheduler = ON;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_billiard`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id_log` int UNSIGNED NOT NULL,
  `id_user` int UNSIGNED NOT NULL,
  `id_billing` int DEFAULT NULL,
  `deskripsi` text NOT NULL,
  `type` enum('CREATE','READ','UPDATE','DELETE','LOGIN','LOGOUT','MISC') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `billing`
--

CREATE TABLE `billing` (
  `id` int UNSIGNED NOT NULL,
  `harga` int NOT NULL,
  `waktu_mulai` datetime DEFAULT NULL,
  `waktu_selesai` datetime DEFAULT NULL,
  `durasi` time NOT NULL,
  `nama_penyewa` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `no_meja` tinyint(2) UNSIGNED NOT NULL,
  `status` enum('TIMER','OPEN','RESERVED','PAUSE','CHECKOUT','') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `harga_perjam` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `billing`
--
DELIMITER $$
CREATE TRIGGER `billing_delete_trigger` AFTER DELETE ON `billing` FOR EACH ROW BEGIN
    INSERT INTO billing_history (billing_id, harga, waktu_mulai, waktu_selesai, durasi, nama_penyewa, no_meja)
    VALUES (OLD.id, OLD.harga, OLD.waktu_mulai, OLD.waktu_selesai, OLD.durasi, OLD.nama_penyewa, OLD.no_meja);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `billing_history`
--

CREATE TABLE `billing_history` (
  `billing_id` int UNSIGNED NOT NULL,
  `harga` int NOT NULL,
  `waktu_mulai` datetime DEFAULT NULL,
  `waktu_selesai` datetime DEFAULT NULL,
  `durasi` time NOT NULL,
  `nama_penyewa` varchar(50) NOT NULL,
  `no_meja` tinyint(2) UNSIGNED NOT NULL,
  `is_paid` tinyint(1) DEFAULT '0',
  `keterangan` text,
  `deleted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `billing_history`
--


-- --------------------------------------------------------

--
-- Table structure for table `meja`
--

CREATE TABLE `meja` (
  `no_meja` tinyint(2) UNSIGNED NOT NULL,
  `status` tinyint(1) UNSIGNED NOT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meja`
--

INSERT INTO `meja` (`no_meja`, `status`, `deskripsi`) VALUES
(1, 0, ''),
(2, 0, ''),
(3, 0, ''),
(4, 0, ''),
(5, 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id_user` int UNSIGNED NOT NULL,
  `nama_user` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(50) NOT NULL,
  `dgt000x2_pin` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `level` enum('USER','ADMIN') NOT NULL,
  `catatan` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id_user`, `nama_user`, `username`, `dgt000x2_pin`, `level`, `catatan`) VALUES
(1, 'ADMIN', 'admin', '$2y$10$y712IlOCRMVkQJ1gPTup.eaMsNCj7BseRjlyzwt3Rx3hCcJKan0fS', 'ADMIN', NULL),
(2, 'Asatu', 'satu', '8vKGdXijJA7RIBJt2AnoqLEyKi6I3vKrE6cV1xBv0Kc=', 'USER', NULL),
(3, 'Adua', 'dua', 'rTPQUEd0V93CtxEfbccK1cSUV8ubyDe3ejpzu18ieTA=', 'USER', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `config`
--

CREATE TABLE `config` (
  `id_config` int UNSIGNED NOT NULL,
  `timezone` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `harga_weekdays` int NOT NULL,
  `harga_weekends` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `config`
--

INSERT INTO `config` (`id_config`, `timezone`, `harga_weekdays`, `harga_weekends`) VALUES
(1, 'WIB', 50000, 50000);

-- --------------------------------------------------------

--
-- Table structure for table `event_log`
--

CREATE TABLE `event_log` (
  `event_name` varchar(255) NOT NULL,
  `billing_id` int DEFAULT NULL,
  `scheduled_time` timestamp NULL DEFAULT NULL,
  `executed` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fnb_menu`
--

CREATE TABLE `fnb_menu` (
  `id_menu` int UNSIGNED NOT NULL,
  `nama_menu` varchar(255) NOT NULL,
  `harga_menu` int NOT NULL,
  `deskripsi` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fnb_menu`
--

INSERT INTO `fnb_menu` (`id_menu`, `nama_menu`, `harga_menu`, `deskripsi`) VALUES
(1, 'Coca Cola', 10000, 'Minuman bersoda segar'),
(2, 'Sprite', 10000, 'Minuman bersoda lemon-lime'),
(3, 'Beng Beng', 5000, 'Cokelat wafer renyah'),
(4, 'Aqua', 3000, 'Air mineral'),
(5, 'Teh Botol Sosro', 6000, 'Teh manis dalam botol'),
(6, 'Nescafe', 7000, 'Kopi instan'),
(7, 'Chitato', 8000, 'Keripik kentang'),
(8, 'Pop Mie', 12000, 'Mie instan dalam cup'),
(9, 'Red Bull', 15000, 'Minuman Berenergi'),
(10, 'Indomilk', 6000, 'Susu segar dalam kemasan');

-- --------------------------------------------------------

--
-- Table structure for table `fnb_orders`
--

CREATE TABLE `fnb_orders` (
  `id_order` int UNSIGNED NOT NULL,
  `id_billing` int UNSIGNED NOT NULL,
  `nama_fnb` varchar(50) NOT NULL,
  `harga_fnb` int NOT NULL,
  `jumlah_fnb` int NOT NULL,
  `total_fnb` int NOT NULL,
  `total` int DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `fnb_orders`
--

INSERT INTO `fnb_orders` (`id_order`, `id_billing`, `nama_fnb`, `harga_fnb`, `jumlah_fnb`, `total_fnb`, `total`, `timestamp`) VALUES
(1, 321, 'Coca Cola', 5000, 2, 10000, NULL, '2024-07-09 06:25:24'),
(2, 321, 'Pepsi', 4500, 3, 13500, NULL, '2024-07-09 07:30:24'),
(3, 322, 'Sprite', 4800, 1, 4800, NULL, '2024-07-09 08:15:30'),
(4, 322, 'Fanta', 4700, 4, 18800, NULL, '2024-07-09 09:05:50'),
(5, 322, 'Mountain Dew', 5500, 2, 11000, NULL, '2024-07-09 09:45:12'),
(6, 326, 'Nasi Goreng', 5300, 1, 5300, NULL, '2024-07-09 10:20:40'),
(7, 327, 'Lemonade', 6000, 2, 12000, NULL, '2024-07-09 11:10:24'),
(8, 328, 'Iced Tea', 4000, 3, 12000, NULL, '2024-07-09 11:50:35'),
(9, 329, 'Water', 2000, 5, 10000, NULL, '2024-07-09 12:30:50'),
(10, 330, 'Orange Juice', 6500, 1, 6500, NULL, '2024-07-09 13:05:20'),
(11, 331, 'Apple Juice', 6800, 2, 13600, NULL, '2024-07-09 13:50:45'),
(12, 332, 'Soymilk', 7000, 1, 7000, NULL, '2024-07-09 14:25:10'),
(13, 333, 'Milkshake', 7500, 1, 7500, NULL, '2024-07-09 15:00:30'),
(14, 334, 'Smoothie', 8000, 2, 16000, NULL, '2024-07-09 15:45:55'),
(15, 334, 'Burger', 15000, 1, 15000, NULL, '2024-07-09 16:20:15'),
(16, 334, 'Tempura', 25000, 1, 25000, NULL, '2024-07-09 17:05:45'),
(17, 334, 'Nugget', 20000, 1, 20000, NULL, '2024-07-09 17:50:30'),
(18, 330, 'Sandwich', 12000, 2, 24000, NULL, '2024-07-09 18:35:10'),
(19, 330, 'Salad', 10000, 1, 10000, NULL, '2024-07-09 19:20:25'),
(20, 330, 'French Fries', 8000, 3, 24000, NULL, '2024-07-09 20:05:50');

-- --------------------------------------------------------

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `billing`
--
ALTER TABLE `billing`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_meja_2` (`no_meja`),
  ADD KEY `no_meja` (`no_meja`);

--
-- Indexes for table `billing_history`
--
ALTER TABLE `billing_history`
  ADD PRIMARY KEY (`billing_id`);

  --
-- Indexes for table `config`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`id_config`);

  --
-- Indexes for table `event_log`
--
ALTER TABLE `event_log`
  ADD PRIMARY KEY (`event_name`);

--
-- Indexes for table `fnb_menu`
--
ALTER TABLE `fnb_menu`
  ADD PRIMARY KEY (`id_menu`);

--
-- Indexes for table `fnb_orders`
--
ALTER TABLE `fnb_orders`
  ADD PRIMARY KEY (`id_order`),
  ADD KEY `id_billing` (`id_billing`);

--
-- Indexes for table `meja`
--
ALTER TABLE `meja`
  ADD PRIMARY KEY (`no_meja`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id_log` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `billing`
--
ALTER TABLE `billing`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `billing_history`
--
ALTER TABLE `billing_history`
  MODIFY `billing_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `config`
--
ALTER TABLE `config`
  MODIFY `id_config` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `fnb_menu`
--
ALTER TABLE `fnb_menu`
  MODIFY `id_menu` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `fnb_orders`
--
ALTER TABLE `fnb_orders`
  MODIFY `id_order` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `meja`
--
ALTER TABLE `meja`
  MODIFY `no_meja` tinyint(2) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `config`
--
ALTER TABLE `config`
  MODIFY `id_config` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`);

--
-- Constraints for table `billing`
--
ALTER TABLE `billing`
  ADD CONSTRAINT `billing_ibfk_1` FOREIGN KEY (`no_meja`) REFERENCES `meja` (`no_meja`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
