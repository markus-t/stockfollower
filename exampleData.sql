-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Värd: 127.0.0.1
-- Skapad: 14 jan 2013 kl 19:10
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

--
-- Dumpning av Data i tabell `indexname`
--

INSERT INTO `indexname` (`ISIN`, `name`) VALUES
('SE0000744195', 'OMXSPI'),
('SE0001775669', 'OMXNSCSEKPI'),
('SE0001809476', 'OMXN40'),
('SE0002905992', 'OMXS60PI');

--
-- Dumpning av Data i tabell `stockname`
--

INSERT INTO `stockname` (`ID`, `shortName`, `Name`, `type`, `rss`) VALUES
(2, 'Axfood', 'Axfood', 1, 'http://www.cisionwire.se/axfood/rss/all-information/'),
(5, 'Avanza Zero', 'Avanza Zero', 1, ''),
(10, 'RB STENA N', 'RB STENA N', 1, ''),
(1000, 'Hoist Spar', 'Hoist Spar', 3, '');

--
-- Dumpning av Data i tabell `stockbought`
--

INSERT INTO `stockbought` (`ID`, `date`, `stockID`, `quantity`, `price`, `courtage`) VALUES
(1, '2011-12-19', 2, 150.000000, 248.9000, 0.00),
(2, '2012-02-28', 5, 100.000000, 117.68000, 0.00),
(39, '2011-05-26', 1000, 65046.000000, 1.00000, 0.00),
(40, '2011-06-17', 1000, 9954.000000, 1.00000, 0.00),
(41, '2011-06-28', 1000, 9000.000000, 1.00000, 0.00),
(42, '2011-07-25', 1000, 11000.000000, 1.00000, 0.00),
(43, '2011-08-29', 1000, 13000.000000, 1.00000, 0.00),
(44, '2011-09-27', 1000, 10000.000000, 1.00000, 0.00),
(45, '2011-10-26', 1000, 10000.000000, 1.00000, 0.00),
(47, '2011-11-28', 1000, 10000.000000, 1.00000, 0.00),
(48, '2011-12-27', 1000, 12500.000000, 1.00000, 0.00),
(50, '2012-02-02', 1000, 8000.000000, 1.00000, 0.00),
(51, '2012-01-26', 1000, 5840.000000, 1.00000, 0.00),
(54, '2011-05-23', 1000, 5000.000000, 1.00000, 0.00),
(67, '2012-05-25', 10, 200.000000, 95.95000, 153.00),
(69, '2012-05-30', 10, 100.000000, 95.10000, 164.00),
(71, '2012-06-04', 1000, 10000.000000, 1.00000, 0.00);

--
-- Dumpning av Data i tabell `stockdividend`
--

INSERT INTO `stockdividend` (`ID`, `date`, `stockID`, `dividend`) VALUES
(402, '2011-05-01', 1000, 3.150000000),
(1754, '2012-06-12', 10, 1.937500000),
(2044, '2012-09-12', 10, 1.940166666),
(2050, '2012-12-14', 10, 1.775000000);


--
-- Dumpning av Data i tabell `stocksold`
--

INSERT INTO `stocksold` (`ID`, `date`, `stockID`, `quantity`, `price`, `courtage`) VALUES
(2, '2011-08-04', 1000, 3000.000000, 1.00000, 0.00),
(3, '2011-11-30', 1000, 2500.000000, 1.00000, 0.00),
(4, '2012-01-23', 1000, 30840.000000, 1.00000, 0.00),
(5, '2012-03-09', 1000, 8000.000000, 1.00000, 0.00),
(6, '2012-03-26', 1000, 14000.000000, 1.00000, 0.00),
(7, '2012-04-01', 1000, 11000.000000, 1.00000, 0.00),
(8, '2012-04-03', 1000, 15000.000000, 1.00000, 0.00),
(11, '2012-04-23', 1000, 10000.000000, 1.00000, 0.00),
(12, '2012-05-08', 1000, 1000.000000, 1.00000, 0.00),
(13, '2012-05-14', 1000, 10000.000000, 1.00000, 0.00),
(16, '2012-05-28', 1000, 30000.000000, 1.00000, 0.00),
(17, '2012-05-29', 1000, 20000.000000, 1.00000, 0.00),
(18, '2012-06-14', 1000, 24000.000000, 1.00000, 0.00),
(24, '2012-09-13', 10, 100.000000, 104.00000, 0.00),
(38, '2012-12-21', 10, 100.000000, 107.10000, 0.00),
(39, '2013-01-03', 10, 100.000000, 108.00000, 0.00);

--
-- Dumpning av Data i tabell `updateavanza`
--

INSERT INTO `updateavanza` (`ID`, `stockID`, `link`) VALUES
(5, 10, 'https://www.avanza.se/aza/aktieroptioner/kurslistor/aktie.jsp?orderbookId=339777');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
