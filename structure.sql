-- phpMyAdmin SQL Dump
-- version 3.3.9
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 28, 2013 at 10:49 
-- Server version: 5.5.8
-- PHP Version: 5.3.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `stock`
--

-- --------------------------------------------------------

--
-- Table structure for table `cdividendsum`
--

CREATE TABLE IF NOT EXISTS `cdividendsum` (
  `ID` int(11) unsigned NOT NULL DEFAULT '0',
  `date` date NOT NULL DEFAULT '0000-00-00',
  `userID` int(11) NOT NULL,
  `sum` decimal(11,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`ID`,`date`,`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `choldingsum`
--

CREATE TABLE IF NOT EXISTS `choldingsum` (
  `stockID` int(11) unsigned NOT NULL DEFAULT '0',
  `date` date NOT NULL DEFAULT '0000-00-00',
  `userID` int(11) NOT NULL,
  `aprice` decimal(11,5) DEFAULT '0.00000',
  `mValue` decimal(11,5) DEFAULT '0.00000',
  `tmValue` decimal(11,5) DEFAULT '0.00000',
  `taValue` decimal(11,5) DEFAULT '0.00000',
  `utv` decimal(10,5) DEFAULT '0.00000',
  `rea` decimal(10,5) NOT NULL DEFAULT '0.00000',
  `diravk` decimal(7,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`stockID`,`date`,`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `indexname`
--

CREATE TABLE IF NOT EXISTS `indexname` (
  `ISIN` varchar(15) COLLATE utf8_swedish_ci NOT NULL,
  `name` text CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`ISIN`),
  KEY `isin` (`ISIN`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `indexprice`
--

CREATE TABLE IF NOT EXISTS `indexprice` (
  `ISIN` varchar(15) COLLATE utf8_swedish_ci NOT NULL,
  `date` date NOT NULL,
  `price` decimal(30,13) NOT NULL,
  PRIMARY KEY (`date`,`ISIN`),
  KEY `fk_isin` (`ISIN`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rss`
--

CREATE TABLE IF NOT EXISTS `rss` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `stockID` int(11) NOT NULL,
  `pubDate` datetime NOT NULL,
  `title` varchar(100) COLLATE utf8_swedish_ci NOT NULL,
  `link` varchar(150) COLLATE utf8_swedish_ci NOT NULL,
  `new` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `stockID` (`stockID`,`pubDate`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci AUTO_INCREMENT=360 ;

-- --------------------------------------------------------

--
-- Table structure for table `stockdividend`
--

CREATE TABLE IF NOT EXISTS `stockdividend` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `stockID` int(11) unsigned NOT NULL,
  `dividend` decimal(12,9) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `fk_stockID4` (`stockID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2066 ;

-- --------------------------------------------------------

--
-- Table structure for table `stockname`
--

CREATE TABLE IF NOT EXISTS `stockname` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `shortName` text CHARACTER SET latin1 NOT NULL,
  `Name` text CHARACTER SET latin1 NOT NULL,
  `type` int(3) NOT NULL,
  `rss` varchar(150) COLLATE utf8_swedish_ci NOT NULL DEFAULT '',
  `avanza` varchar(256) CHARACTER SET latin1 NOT NULL,
  `nordnet` varchar(256) COLLATE utf8_swedish_ci NOT NULL,
  `nordnetcurrent` varchar(5) COLLATE utf8_swedish_ci NOT NULL,
  `morningstar` varchar(256) COLLATE utf8_swedish_ci NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci AUTO_INCREMENT=41 ;

-- --------------------------------------------------------

--
-- Table structure for table `stockprice`
--

CREATE TABLE IF NOT EXISTS `stockprice` (
  `date` date NOT NULL,
  `price` decimal(11,5) NOT NULL,
  `stockID` int(11) unsigned NOT NULL,
  `time` time NOT NULL,
  `close` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`stockID`,`date`),
  KEY `fk_stockID3` (`stockID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `stocktransactions`
--

CREATE TABLE IF NOT EXISTS `stocktransactions` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL,
  `date` date NOT NULL,
  `stockID` int(11) unsigned NOT NULL,
  `quantity` decimal(12,6) NOT NULL,
  `price` decimal(10,5) NOT NULL,
  `courtage` decimal(5,2) NOT NULL DEFAULT '0.00',
  `action` varchar(12) NOT NULL DEFAULT 'bought',
  `account` varchar(3) NOT NULL DEFAULT 'VP',
  PRIMARY KEY (`ID`),
  KEY `fk_stockID` (`stockID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=190 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cdividendsum`
--
ALTER TABLE `cdividendsum`
  ADD CONSTRAINT `cdividendsum_ibfk_1` FOREIGN KEY (`ID`) REFERENCES `stockname` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `choldingsum`
--
ALTER TABLE `choldingsum`
  ADD CONSTRAINT `choldingsum_ibfk_1` FOREIGN KEY (`stockID`) REFERENCES `stockname` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `indexprice`
--
ALTER TABLE `indexprice`
  ADD CONSTRAINT `fk_isin` FOREIGN KEY (`ISIN`) REFERENCES `indexname` (`ISIN`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `stockdividend`
--
ALTER TABLE `stockdividend`
  ADD CONSTRAINT `stockdividend_ibfk_1` FOREIGN KEY (`stockID`) REFERENCES `stockname` (`ID`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `stockprice`
--
ALTER TABLE `stockprice`
  ADD CONSTRAINT `stockprice_ibfk_1` FOREIGN KEY (`stockID`) REFERENCES `stockname` (`ID`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `stocktransactions`
--
ALTER TABLE `stocktransactions`
  ADD CONSTRAINT `fk_stockID` FOREIGN KEY (`stockID`) REFERENCES `stockname` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;
