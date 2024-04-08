-- phpMyAdmin SQL Dump
-- version 4.6.6
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 21, 2023 at 01:38 PM
-- Server version: 5.7.17-log
-- PHP Version: 5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `doss`
--

-- --------------------------------------------------------

--
-- Table structure for table `collection`
--

CREATE TABLE `collection` (
  `perroundID` varchar(5) NOT NULL COMMENT 'รหัสรอบการชำระหนี้',
  `perroundName` varchar(50) NOT NULL COMMENT 'ชื่อรอบ'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `collection`
--

INSERT INTO `collection` (`perroundID`, `perroundName`) VALUES
('por1', 'ชำระทุก 3 วัน'),
('por2', 'ชำระทุก 5 วัน'),
('por3', 'ชำระทุก 7 วัน'),
('por4', 'ชำระทุก 14 วัน'),
('por5', 'ชำระทุก 30 วัน');

-- --------------------------------------------------------

--
-- Table structure for table `debtor`
--

CREATE TABLE `debtor` (
  `debtorID` int(3) NOT NULL COMMENT 'รหัสลูกหนี้',
  `FirstName` varchar(100) NOT NULL COMMENT 'ชื่อ',
  `LastName` varchar(100) NOT NULL COMMENT 'นามสกุล',
  `Addr` text NOT NULL COMMENT 'ที่อยู่',
  `tell` varchar(10) NOT NULL COMMENT 'เบอร์โทร'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `debtor`
--

INSERT INTO `debtor` (`debtorID`, `FirstName`, `LastName`, `Addr`, `tell`) VALUES
(1, 'กามาตาล', 'โสพลลา', '154 หมู่ 5 ต.มั่นคง อ.มั่นคง จ.ร้อยเอ็ด', '0999567999'),
(2, 'เป็นหนึ่ง', 'อินทิรา', '56 หมู่ 9 ต.มั่นคง อ.มั่นคง จ.ร้อยเอ็ด', '0936274758'),
(3, 'ประยุทธ์', 'พุทธโอวาท', '25 หมู่ 8 ต.มั่นคง อ.มั่นคง จ.ร้อยเอ็ด', '0846264850'),
(4, 'ไข่เจียว', 'วุ้นเส้น', '11 หมู่ 9 ต.มั่น อ.มั่นคง จ.ร้อยเอ็ด', '0989632188'),
(5, 'กากเจียว', 'กากไก่กา', '31 หมู่ 6 ต.มั่นคง อ.ไม่มั่นคง จ.ร้อยเอ็ด', '0998674206'),
(6, 'สุกี้', 'ปากดาด', '142 หมู่ 6 ต.มั่นคง อ.มั่นคง จ.ร้อยเอ็ด', '0979526121'),
(7, 'ยำ', 'ไข่ดอง', '12/1 หมู่ 2 ต.มั่น อ.มั่นคง จ.ร้อยเอ็ด', '0943227954'),
(8, 'ไข่พะโล้', 'ยางมะตูม', '97 หมู่ 5 ต.มั่น อ.มั่นคง จ.ร้อยเอ็ด', '0987890865'),
(9, 'ไข่', 'หวาน', '56 หมู่ 1 ต.มั่นใจ อ.มั่นคง จ.ร้อยเอ็ด', '0987532114'),
(10, 'ยำไข่', 'ชั่งฝัน', '51/6 หมู่ 9 ต.มั่นใจ อ.มั่นคง จ.ร้อยเอ็ด', '0812345432'),
(11, 'สมศรี', 'รุ่งเรือง', '123 456 ถ.บบบ ต.ในเมือง อ.เมือง จ.ขอนแก่น', '0933262110'),
(12, 'มาลี', 'กาบมาลา', ' hs0.5 ', '0999999999'),
(13, 'ยายสมหมาย', 'สมหวัง', 'หลังวัดต้นมะม่วง', '0959999999');

-- --------------------------------------------------------

--
-- Table structure for table `paydebt`
--

CREATE TABLE `paydebt` (
  `payDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'วันเดือนปีที่มาชำระหนี้',
  `payID` int(3) NOT NULL COMMENT 'รหัสการชำระ',
  `receiptID` int(5) NOT NULL COMMENT 'รหัสการค้างชำระ',
  `ordinalPay` int(4) NOT NULL COMMENT 'ครั้งที่ชำระ',
  `amount` float(9,2) NOT NULL COMMENT 'ยอดการชำระ',
  `unpaiddebt` float(8,2) NOT NULL COMMENT 'ยอดหนี้คงเหลือ',
  `debt` float(8,2) NOT NULL COMMENT 'ยอดหนี้ทั้งหมด',
  `note` text COMMENT 'หมายเหตุ'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `paydebt`
--

INSERT INTO `paydebt` (`payDate`, `payID`, `receiptID`, `ordinalPay`, `amount`, `unpaiddebt`, `debt`, `note`) VALUES
('2022-12-24 13:01:54', 1, 3, 1, 60.00, 60.00, 120.00, 'มัดจำ 80 ค่าอาหารแมว'),
('2022-12-27 09:28:43', 2, 3, 2, 60.00, 0.00, 120.00, NULL),
('2023-08-18 23:27:22', 3, 2, 1, 1000.00, 2000.00, 5000.00, 'มัดจำ 2000'),
('2023-07-20 23:27:56', 4, 1, 1, 420.00, 1680.00, 2100.00, NULL),
('2023-09-11 23:36:27', 5, 4, 1, 200.00, 800.00, 1000.00, NULL),
('2023-09-11 23:36:41', 6, 4, 2, 200.00, 600.00, 1000.00, NULL),
('2023-09-11 23:36:50', 7, 4, 3, 200.00, 400.00, 1000.00, NULL),
('2023-07-23 04:00:26', 8, 1, 2, 420.00, 1260.00, 2100.00, NULL),
('2023-09-12 14:39:32', 9, 5, 1, 100.00, 100.00, 200.00, NULL),
('2023-09-13 12:12:04', 10, 4, 4, 200.00, 600.00, 1000.00, NULL),
('2023-09-13 12:14:22', 11, 4, 5, 200.00, 400.00, 1000.00, NULL),
('2023-09-13 12:25:55', 12, 4, 6, 400.00, 0.00, 1000.00, NULL),
('2023-09-19 12:51:22', 13, 7, 1, 150.00, 300.00, 500.00, NULL),
('2023-09-20 05:18:50', 14, 8, 1, 50.00, 900.00, 1000.00, NULL),
('2023-09-21 02:10:55', 15, 1, 3, 420.00, 840.00, 2100.00, NULL),
('2023-09-21 11:48:49', 16, 13, 1, 30.00, 170.00, 200.00, NULL),
('2023-09-21 12:02:52', 17, 13, 2, 25.00, 145.00, 200.00, NULL),
('2023-09-21 12:12:06', 18, 14, 1, 5000.00, 0.00, 5200.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `receipt`
--

CREATE TABLE `receipt` (
  `receiptID` int(5) NOT NULL COMMENT 'รหัสการค้างชำระ',
  `receiptDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'วันที่ค้างชำระ',
  `debtorID` int(3) NOT NULL COMMENT 'รหัสลูกหนี้',
  `perroundID` varchar(5) NOT NULL COMMENT 'รหัสรอบการชำระหนี้',
  `receiptPay` float(8,2) DEFAULT NULL COMMENT 'จำนวนเงินที่จ่าย',
  `receiptOwe` float(8,2) NOT NULL COMMENT 'ยอดหนี้ทั้งหมด',
  `receiptLeft` float(8,2) NOT NULL COMMENT 'หนี้คงเหลือปัจจุบัน',
  `mustpay` float(8,2) NOT NULL COMMENT 'จำนวนเงินที่ต้องชำระเงิน'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `receipt`
--

INSERT INTO `receipt` (`receiptID`, `receiptDate`, `debtorID`, `perroundID`, `receiptPay`, `receiptOwe`, `receiptLeft`, `mustpay`) VALUES
(1, '2023-07-16 12:17:44', 1, 'por1', 0.00, 2100.00, 840.00, 420.00),
(2, '2023-08-21 09:21:00', 1, 'por2', 2000.00, 5000.00, 2000.00, 1000.00),
(3, '2022-12-21 15:54:33', 5, 'por3', 80.00, 200.00, 0.00, 60.00),
(4, '2023-09-11 23:35:51', 10, 'por1', 0.00, 1000.00, 0.00, 200.00),
(5, '2023-09-12 02:24:35', 7, 'por2', 0.00, 200.00, 100.00, 100.00),
(6, '2023-09-12 14:50:19', 6, 'por4', 0.00, 200.00, 200.00, 66.67),
(7, '2023-09-19 12:49:58', 11, 'por3', 50.00, 500.00, 300.00, 112.50),
(8, '2023-09-19 13:07:30', 3, 'por2', 50.00, 1000.00, 900.00, 47.50),
(9, '2023-09-19 13:10:36', 11, 'por3', 0.00, 20.00, 20.00, 1.00),
(10, '2023-09-20 05:48:53', 4, 'por1', 0.00, 300.00, 300.00, 150.00),
(11, '2023-09-20 05:49:20', 4, 'por3', 0.00, 600.00, 600.00, 120.00),
(12, '2023-09-20 17:14:37', 4, 'por1', 0.00, 2000.00, 2000.00, 100.00),
(13, '2023-09-21 11:47:10', 12, 'por2', 0.00, 200.00, 145.00, 25.00),
(14, '2023-09-21 12:11:08', 13, 'por3', 200.00, 5200.00, 0.00, 100.00);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `username` varchar(12) NOT NULL,
  `password` varchar(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`username`, `password`) VALUES
('owner', '12345678');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `collection`
--
ALTER TABLE `collection`
  ADD PRIMARY KEY (`perroundID`);

--
-- Indexes for table `debtor`
--
ALTER TABLE `debtor`
  ADD PRIMARY KEY (`debtorID`);

--
-- Indexes for table `paydebt`
--
ALTER TABLE `paydebt`
  ADD PRIMARY KEY (`payID`),
  ADD KEY `receiptID_fk` (`receiptID`);

--
-- Indexes for table `receipt`
--
ALTER TABLE `receipt`
  ADD PRIMARY KEY (`receiptID`),
  ADD KEY `debtorID_fk` (`debtorID`),
  ADD KEY `perroundID_fk` (`perroundID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `debtor`
--
ALTER TABLE `debtor`
  MODIFY `debtorID` int(3) NOT NULL AUTO_INCREMENT COMMENT 'รหัสลูกหนี้', AUTO_INCREMENT=14;
--
-- AUTO_INCREMENT for table `paydebt`
--
ALTER TABLE `paydebt`
  MODIFY `payID` int(3) NOT NULL AUTO_INCREMENT COMMENT 'รหัสการชำระ', AUTO_INCREMENT=19;
--
-- AUTO_INCREMENT for table `receipt`
--
ALTER TABLE `receipt`
  MODIFY `receiptID` int(5) NOT NULL AUTO_INCREMENT COMMENT 'รหัสการค้างชำระ', AUTO_INCREMENT=15;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `paydebt`
--
ALTER TABLE `paydebt`
  ADD CONSTRAINT `receiptID_fk` FOREIGN KEY (`receiptID`) REFERENCES `receipt` (`receiptID`) ON UPDATE CASCADE;

--
-- Constraints for table `receipt`
--
ALTER TABLE `receipt`
  ADD CONSTRAINT `debtorID_fk` FOREIGN KEY (`debtorID`) REFERENCES `debtor` (`debtorID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `perroundID_fk` FOREIGN KEY (`perroundID`) REFERENCES `collection` (`perroundID`) ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
