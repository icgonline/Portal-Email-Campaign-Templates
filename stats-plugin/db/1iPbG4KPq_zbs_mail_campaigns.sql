-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 05, 2026 at 04:04 PM
-- Server version: 8.0.42-33
-- PHP Version: 8.4.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `wp_6dael`
--

-- --------------------------------------------------------

--
-- Table structure for table `1iPbG4KPq_zbs_mail_campaigns`
--

CREATE TABLE `1iPbG4KPq_zbs_mail_campaigns` (
  `ID` int NOT NULL,
  `zbs_site` int DEFAULT NULL,
  `zbs_team` int DEFAULT NULL,
  `zbs_owner` int NOT NULL,
  `zbsmailcamp_status` int NOT NULL,
  `zbsmailcamp_leaveopen` int NOT NULL DEFAULT '-1',
  `zbsmailcamp_title` varchar(200) NOT NULL,
  `zbsmailcamp_audiencesstr` varchar(500) DEFAULT NULL,
  `zbsmailcamp_mailid` int NOT NULL,
  `zbsmailcamp_splittestid` int DEFAULT NULL,
  `zbsmailcamp_scheduled` int NOT NULL,
  `zbsmailcamp_created` int NOT NULL,
  `zbsmailcamp_lastupdated` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `1iPbG4KPq_zbs_mail_campaigns`
--

INSERT INTO `1iPbG4KPq_zbs_mail_campaigns` (`ID`, `zbs_site`, `zbs_team`, `zbs_owner`, `zbsmailcamp_status`, `zbsmailcamp_leaveopen`, `zbsmailcamp_title`, `zbsmailcamp_audiencesstr`, `zbsmailcamp_mailid`, `zbsmailcamp_splittestid`, `zbsmailcamp_scheduled`, `zbsmailcamp_created`, `zbsmailcamp_lastupdated`) VALUES
(1, 1, 1, 1, 2, -1, 'Test Email', 'segment:3', 1, -1, -1, 1766397855, 1766397982),
(2, 1, 1, 3, -1, -1, 'test2', '', 2, -1, -1, 1766406369, 1766406369),
(3, 1, 1, 1, -1, -1, 'Test Email', 'segment:3', 3, -1, -1, 1777991697, 1778225004),
(4, 1, 1, 1, -1, -1, 'Test Email', 'segment:1', 4, -1, -1, 1778080263, 1778080344),
(7, 1, 1, 1, -1, -1, 'Test Email', 'tag:2', 7, -1, -1, 1778080621, 1778142624),
(10, 1, 1, 2, -1, -1, 'Dave Test', 'segment:2', 10, -1, -1, 1778082267, 1778082302),
(16, 1, 1, 6, -1, -1, 'Fridaytest', 'segment:3', 17, -1, -1, 1778230213, 1778231494),
(17, 1, 1, 6, 2, -1, 'TestGroupsend', 'segment:3', 20, -1, -1, 1778233781, 1778234014),
(18, 1, 1, 6, 2, -1, '[Internal] - Say hello to ICG Belfast', 'segment:3', 21, -1, -1, 1778234778, 1778235203),
(19, 1, 1, 6, 2, -1, '[Internal', 'segment:3', 22, -1, -1, 1778235455, 1778235502),
(20, 1, 1, 6, -1, -1, 'ICG TEMPLATE - Don&#039;t Delete', 'segment:3', 23, -1, -1, 1778236731, 1784115722),
(21, 1, 1, 6, 2, -1, 'Say hello to ICG Belfast', 'segment:3', 24, -1, -1, 1778236994, 1778237344),
(22, 1, 1, 6, 2, -1, 'Say hello to ICG Belfast', 'segment:2', 25, -1, -1, 1778237447, 1778237934),
(23, 1, 1, 6, 2, -1, 'Say hello to ICG Belfast', 'segment:1', 26, -1, -1, 1778238128, 1778241524),
(24, 1, 1, 6, 2, -1, 'Say hello to ICG Belfast', 'segment:4', 27, -1, 1778241000, 1778238302, 1778241525),
(25, 1, 1, 6, -1, -1, 'test-outofoffice', 'segment:3', 28, -1, -1, 1778248217, 1778248222),
(26, 1, 1, 2, 2, -1, 'Celebrating another long-standing client partnership', 'segment:3', 29, -1, -1, 1784115345, 1784126265),
(27, 1, 1, 2, 2, -1, 'Celebrating another long-standing client partnership', 'segment:2', 30, -1, -1, 1784126725, 1784127094),
(28, 1, 1, 2, 2, -1, 'Celebrating another long-standing client partnership', 'segment:1', 31, -1, 1784190600, 1784126948, 1784191023),
(29, 1, 1, 1, 2, -1, 'An exciting September ahead', 'segment:5', 32, -1, 1787836500, 1787733434, 1787840487),
(30, 1, 1, 1, 2, -1, 'ICG at Belfast&rsquo;s iconic St George&rsquo;s market', 'segment:5', 33, -1, -1, 1790327879, 1790346129),
(31, 1, 1, 6, 2, -1, 'A rebrand for BUMAX', 'segment:5', 34, -1, -1, 1793115058, 1793199737);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `1iPbG4KPq_zbs_mail_campaigns`
--
ALTER TABLE `1iPbG4KPq_zbs_mail_campaigns`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `1iPbG4KPq_zbs_mail_campaigns`
--
ALTER TABLE `1iPbG4KPq_zbs_mail_campaigns`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
