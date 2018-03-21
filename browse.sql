-- phpMyAdmin SQL Dump
-- version 4.7.8
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 21, 2018 at 03:36 PM
-- Server version: 5.7.21
-- PHP Version: 7.2.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `browse`
--

-- --------------------------------------------------------

--
-- Table structure for table `duplicates`
--

DROP TABLE IF EXISTS `duplicates`;
CREATE TABLE `duplicates` (
  `ID` int(11) NOT NULL,
  `Share` int(11) NOT NULL,
  `Path` varchar(4096) NOT NULL,
  `Filename` varchar(255) NOT NULL,
  `MD5` char(32) NOT NULL,
  `Size` int(11) NOT NULL,
  `Modtime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

DROP TABLE IF EXISTS `files`;
CREATE TABLE `files` (
  `ID` int(11) NOT NULL,
  `Share` int(11) NOT NULL,
  `Path` varchar(4096) NOT NULL,
  `Filename` varchar(255) NOT NULL,
  `MD5` char(32) NOT NULL,
  `Size` int(11) NOT NULL,
  `Modtime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `playlist`
--

DROP TABLE IF EXISTS `playlist`;
CREATE TABLE `playlist` (
  `ID` int(11) NOT NULL,
  `Name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `playlistentries`
--

DROP TABLE IF EXISTS `playlistentries`;
CREATE TABLE `playlistentries` (
  `ID` int(11) NOT NULL,
  `Playlist` int(11) NOT NULL,
  `File` int(11) NOT NULL,
  `Weight` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `shares`
--

DROP TABLE IF EXISTS `shares`;
CREATE TABLE `shares` (
  `ID` int(11) NOT NULL,
  `Path` varchar(1024) NOT NULL,
  `Searchable` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `duplicates`
--
ALTER TABLE `duplicates`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `ID` (`ID`);

--
-- Indexes for table `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `unique_index` (`MD5`,`Size`),
  ADD KEY `ID` (`ID`),
  ADD KEY `Share` (`Share`);

--
-- Indexes for table `playlist`
--
ALTER TABLE `playlist`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Name` (`Name`);

--
-- Indexes for table `playlistentries`
--
ALTER TABLE `playlistentries`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `Playlist` (`Playlist`),
  ADD KEY `File` (`File`);

--
-- Indexes for table `shares`
--
ALTER TABLE `shares`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Path` (`Path`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `duplicates`
--
ALTER TABLE `duplicates`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `files`
--
ALTER TABLE `files`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91837;

--
-- AUTO_INCREMENT for table `playlist`
--
ALTER TABLE `playlist`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `playlistentries`
--
ALTER TABLE `playlistentries`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `shares`
--
ALTER TABLE `shares`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `files`
--
ALTER TABLE `files`
  ADD CONSTRAINT `files_ibfk_1` FOREIGN KEY (`Share`) REFERENCES `shares` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `playlistentries`
--
ALTER TABLE `playlistentries`
  ADD CONSTRAINT `playlistentries_ibfk_1` FOREIGN KEY (`Playlist`) REFERENCES `playlist` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `playlistentries_ibfk_2` FOREIGN KEY (`File`) REFERENCES `files` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
