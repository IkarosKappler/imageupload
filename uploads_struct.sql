-- phpMyAdmin SQL Dump
-- version 4.2.12deb2+deb8u2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 16, 2017 at 12:50 PM
-- Server version: 5.7.17
-- PHP Version: 5.6.29-0+deb8u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `hundredforty`
--

-- --------------------------------------------------------

--
-- Table structure for table `uploads`
--

CREATE TABLE IF NOT EXISTS `uploads` (
`id` int(11) unsigned NOT NULL,
  `filename` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `original_filename` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `remote_address` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `referrer` varchar(256) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'The referring website.'
) ENGINE=InnoDB AUTO_INCREMENT=203 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Tracks all uploaded files.';

--
-- Dumping data for table `uploads`
--
