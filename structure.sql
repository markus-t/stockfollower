-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Värd: 127.0.0.1
-- Skapad: 14 jan 2013 kl 19:07
-- Serverversion: 5.5.27
-- PHP-version: 5.4.7

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Databas: `stock`
--


-- --------------------------------------------------------

--
-- Tabellstruktur `cdividendsum`
--

CREATE TABLE IF NOT EXISTS `cdividendsum` (
  `ID` int(11) NOT NULL DEFAULT '0',
  `date` date NOT NULL DEFAULT '0000-00-00',
  `sum` decimal(11,2) DEFAULT NULL,
  PRIMARY KEY (`ID`,`date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `choldingsum`
--

CREATE TABLE IF NOT EXISTS `choldingsum` (
  `stockID` int(11) NOT NULL DEFAULT '0',
  `date` date NOT NULL DEFAULT '0000-00-00',
  `aprice` decimal(11,5) DEFAULT NULL,
  `mValue` decimal(11,5) DEFAULT NULL,
  `tmValue` decimal(11,5) DEFAULT NULL,
  `taValue` decimal(11,5) DEFAULT NULL,
  `utv` decimal(10,5) DEFAULT NULL,
  `rea` decimal(10,5) NOT NULL,
  `diravk` decimal(7,2) NOT NULL,
  PRIMARY KEY (`stockID`,`date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `indexname`
--

CREATE TABLE IF NOT EXISTS `indexname` (
  `ISIN` varchar(15) COLLATE utf8_swedish_ci NOT NULL,
  `name` text CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`ISIN`),
  KEY `isin` (`ISIN`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

-- --------------------------------------------------------

--
-- Tabellstruktur `indexprice`
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
-- Tabellstruktur `rss`
--

CREATE TABLE IF NOT EXISTS `rss` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `stockID` int(11) NOT NULL,
  `pubDate` datetime NOT NULL,
  `title` varchar(100) COLLATE utf8_swedish_ci NOT NULL,
  `link` varchar(150) COLLATE utf8_swedish_ci NOT NULL,
  `new` tinyint(1) NOT NULL DEFAULT '0',
  `pdf` mediumblob NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `stockID` (`stockID`,`pubDate`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci AUTO_INCREMENT=272 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `stockbought`
--

CREATE TABLE IF NOT EXISTS `stockbought` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `stockID` int(11) unsigned NOT NULL,
  `quantity` decimal(12,6) NOT NULL,
  `price` decimal(10,5) NOT NULL,
  `courtage` decimal(5,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`ID`),
  KEY `fk_stockID` (`stockID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=95 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `stockdividend`
--

CREATE TABLE IF NOT EXISTS `stockdividend` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `stockID` int(11) unsigned NOT NULL,
  `dividend` decimal(12,9) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `fk_stockID4` (`stockID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2054 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `stockname`
--

CREATE TABLE IF NOT EXISTS `stockname` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `shortName` text CHARACTER SET latin1 NOT NULL,
  `Name` text CHARACTER SET latin1 NOT NULL,
  `type` int(3) NOT NULL,
  `rss` varchar(150) COLLATE utf8_swedish_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `ID` (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci AUTO_INCREMENT=2001 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `stockprice`
--

CREATE TABLE IF NOT EXISTS `stockprice` (
  `date` date NOT NULL,
  `price` decimal(11,5) NOT NULL,
  `stockID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`stockID`,`date`),
  KEY `fk_stockID3` (`stockID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `stocksold`
--

CREATE TABLE IF NOT EXISTS `stocksold` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `stockID` int(11) unsigned NOT NULL,
  `quantity` decimal(12,6) NOT NULL,
  `price` decimal(10,5) NOT NULL,
  `courtage` decimal(5,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`ID`),
  KEY `fk_stockID2` (`stockID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=40 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `updateavanza`
--

CREATE TABLE IF NOT EXISTS `updateavanza` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `stockID` int(11) unsigned NOT NULL,
  `link` varchar(256) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `fk.stockID4` (`stockID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

--
-- Restriktioner för dumpade tabeller
--

--
-- Restriktioner för tabell `indexprice`
--
ALTER TABLE `indexprice`
  ADD CONSTRAINT `fk_isin` FOREIGN KEY (`ISIN`) REFERENCES `indexname` (`ISIN`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restriktioner för tabell `stockbought`
--
ALTER TABLE `stockbought`
  ADD CONSTRAINT `fk_stockID` FOREIGN KEY (`stockID`) REFERENCES `stockname` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restriktioner för tabell `stockdividend`
--
ALTER TABLE `stockdividend`
  ADD CONSTRAINT `fk_stockID4` FOREIGN KEY (`stockID`) REFERENCES `stockname` (`ID`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Restriktioner för tabell `stockprice`
--
ALTER TABLE `stockprice`
  ADD CONSTRAINT `fk_stockID3` FOREIGN KEY (`stockID`) REFERENCES `stockname` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restriktioner för tabell `stocksold`
--
ALTER TABLE `stocksold`
  ADD CONSTRAINT `fk_stockID2` FOREIGN KEY (`stockID`) REFERENCES `stockname` (`ID`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Restriktioner för tabell `updateavanza`
--
ALTER TABLE `updateavanza`
  ADD CONSTRAINT `fk.stockID4` FOREIGN KEY (`stockID`) REFERENCES `stockname` (`ID`) ON DELETE NO ACTION ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
