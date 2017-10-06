-- phpMyAdmin SQL Dump
-- version 4.7.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 06, 2017 at 10:38 AM
-- Server version: 10.1.25-MariaDB
-- PHP Version: 5.6.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `apimynetpay`
--

-- --------------------------------------------------------

--
-- Table structure for table `jenis_trx`
--

CREATE TABLE `jenis_trx` (
  `id` int(11) NOT NULL,
  `jenis` varchar(25) NOT NULL,
  `keterangan` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `jenis_trx`
--

INSERT INTO `jenis_trx` (`id`, `jenis`, `keterangan`) VALUES
(1, 'transfer', 'Transfer ke sesama MyNetPay');

-- --------------------------------------------------------

--
-- Table structure for table `konfirmasi_topup`
--

CREATE TABLE `konfirmasi_topup` (
  `id` int(11) NOT NULL,
  `invoice` varchar(15) NOT NULL,
  `hp` varchar(25) NOT NULL,
  `bukti` text NOT NULL,
  `waktu` datetime NOT NULL,
  `validasi` int(1) NOT NULL DEFAULT '0',
  `validator` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `log`
--

CREATE TABLE `log` (
  `id` int(11) NOT NULL,
  `api` varchar(255) NOT NULL,
  `ip_address` varchar(25) NOT NULL,
  `apikey` varchar(255) NOT NULL,
  `session` varchar(255) NOT NULL,
  `variable` varchar(255) NOT NULL,
  `waktu_akses` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `log`
--

INSERT INTO `log` (`id`, `api`, `ip_address`, `apikey`, `session`, `variable`, `waktu_akses`) VALUES
(1, 'user/ceksaldo', '::1', '6b5cc4c659b85ae0e92fb777a51ba0ee20577f32', '421aa90e079fa326b6494f812ad13e79', 'hp:085220150587', '2017-09-27 11:35:37'),
(2, 'user/ceksaldo', '::1', '6b5cc4c659b85ae0e92fb777a51ba0ee20577f32', '421aa90e079fa326b6494f812ad13e79', 'hp:085220150587', '2017-09-27 11:49:18');

-- --------------------------------------------------------

--
-- Table structure for table `mynetpaykey`
--

CREATE TABLE `mynetpaykey` (
  `id` int(11) NOT NULL,
  `identitas` varchar(255) NOT NULL,
  `apikey` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `mynetpaykey`
--

INSERT INTO `mynetpaykey` (`id`, `identitas`, `apikey`, `created_at`) VALUES
(1, 'Fauzan Beta Dev', '6b5cc4c659b85ae0e92fb777a51ba0ee20577f32', '2017-09-14 10:02:00'),
(2, 'Heri Setiawan', '0870f0c9e6299e1055be06d8d7d7ac63', '2017-09-29 10:20:00');

-- --------------------------------------------------------

--
-- Table structure for table `otp`
--

CREATE TABLE `otp` (
  `id` int(11) NOT NULL,
  `kodeotp` varchar(255) NOT NULL,
  `hp` varchar(25) NOT NULL,
  `created_at` datetime NOT NULL,
  `used` int(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `otp`
--

INSERT INTO `otp` (`id`, `kodeotp`, `hp`, `created_at`, `used`) VALUES
(1, '580267', '085221050587', '2017-09-14 05:56:06', 1),
(2, '145593', '085221050587', '2017-09-14 05:56:19', 1),
(3, '714190', '085221050587', '2017-09-14 05:58:35', 1),
(4, '201293', '085221050587', '2017-09-14 06:01:01', 1),
(5, '726303', '085221050587', '2017-09-14 06:26:57', 1),
(6, '826416', '085221050587', '2017-09-14 06:27:31', 1),
(7, '193411', '085221050587', '2017-09-14 06:28:05', 1),
(8, '256829', '085221050587', '2017-09-14 06:28:43', 1),
(9, '715536', '085221050587', '2017-09-14 06:29:35', 1),
(10, '665356', '085221050587', '2017-09-14 06:29:43', 1),
(11, '194345', '085221050587', '2017-09-14 06:33:13', 0);

-- --------------------------------------------------------

--
-- Table structure for table `produk_mitra`
--

CREATE TABLE `produk_mitra` (
  `id` int(11) NOT NULL,
  `nama_produk` varchar(150) NOT NULL,
  `deskripsi_produk` text NOT NULL,
  `gambar_produk` varchar(255) NOT NULL,
  `kategori_produk` varchar(50) NOT NULL,
  `harga` varchar(15) NOT NULL,
  `no_hp_mitra` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `rekening_topup`
--

CREATE TABLE `rekening_topup` (
  `id` int(11) NOT NULL,
  `bank` varchar(50) NOT NULL,
  `no_rekening` varchar(50) NOT NULL,
  `nama_rekening` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `rekening_topup`
--

INSERT INTO `rekening_topup` (`id`, `bank`, `no_rekening`, `nama_rekening`) VALUES
(1, 'Bank Mandiri', '1234567890', 'MYNETPAY'),
(2, 'Bank BCA', '0987654321', 'MYNETPAY');

-- --------------------------------------------------------

--
-- Table structure for table `session`
--

CREATE TABLE `session` (
  `id` int(11) NOT NULL,
  `access` varchar(255) NOT NULL,
  `generate_at` datetime NOT NULL,
  `destroy_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `session`
--

INSERT INTO `session` (`id`, `access`, `generate_at`, `destroy_at`) VALUES
(1, '421aa90e079fa326b6494f812ad13e79', '2017-09-27 15:46:01', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `topup`
--

CREATE TABLE `topup` (
  `id` int(11) NOT NULL,
  `invoice` varchar(10) NOT NULL,
  `hp` varchar(25) NOT NULL,
  `jumlah` varchar(15) NOT NULL,
  `waktu` datetime NOT NULL,
  `konfirmasi` int(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `trx`
--

CREATE TABLE `trx` (
  `id` int(11) NOT NULL,
  `hp_pengirim` varchar(20) NOT NULL,
  `hp_penerima` varchar(20) NOT NULL,
  `waktu_transaksi` datetime NOT NULL,
  `jenis_transaksi` int(2) NOT NULL,
  `debetkredit` enum('debet','kredit') NOT NULL,
  `jumlah` varchar(20) NOT NULL,
  `keterangan` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `trx`
--

INSERT INTO `trx` (`id`, `hp_pengirim`, `hp_penerima`, `waktu_transaksi`, `jenis_transaksi`, `debetkredit`, `jumlah`, `keterangan`) VALUES
(1, '085220150587', '08123456789', '2017-09-17 03:09:13', 1, 'kredit', '50000', ''),
(2, '085220150587', '08123456789', '2017-09-18 02:06:19', 1, 'debet', '120000', '');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `hp` varchar(25) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `pin` varchar(255) NOT NULL,
  `tgl_daftar` datetime NOT NULL,
  `saldo` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `hp`, `nama`, `email`, `password`, `pin`, `tgl_daftar`, `saldo`) VALUES
(1, '085220150587', 'Fauzan', 'azmifauzan@gmail.com', '2f6c80a6b5d8464b77e67c356c579ced', 'd93a5def7511da3d0f2d171d9c344e91', '2017-09-27 15:35:00', '0'),
(2, '08123456789', 'Saya', 'anu@gmail.com', '912ec803b2ce49e4a541068d495ab570', 'e10adc3949ba59abbe56e057f20f883e', '2017-10-05 05:12:19', '5000');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `jenis_trx`
--
ALTER TABLE `jenis_trx`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `konfirmasi_topup`
--
ALTER TABLE `konfirmasi_topup`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `log`
--
ALTER TABLE `log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mynetpaykey`
--
ALTER TABLE `mynetpaykey`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `otp`
--
ALTER TABLE `otp`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `produk_mitra`
--
ALTER TABLE `produk_mitra`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rekening_topup`
--
ALTER TABLE `rekening_topup`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `session`
--
ALTER TABLE `session`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `topup`
--
ALTER TABLE `topup`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trx`
--
ALTER TABLE `trx`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `jenis_trx`
--
ALTER TABLE `jenis_trx`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `konfirmasi_topup`
--
ALTER TABLE `konfirmasi_topup`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `log`
--
ALTER TABLE `log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `mynetpaykey`
--
ALTER TABLE `mynetpaykey`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `otp`
--
ALTER TABLE `otp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
--
-- AUTO_INCREMENT for table `produk_mitra`
--
ALTER TABLE `produk_mitra`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `rekening_topup`
--
ALTER TABLE `rekening_topup`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `session`
--
ALTER TABLE `session`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `topup`
--
ALTER TABLE `topup`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `trx`
--
ALTER TABLE `trx`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
