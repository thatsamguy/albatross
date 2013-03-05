-- phpMyAdmin SQL Dump
-- version 3.4.3.1
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generation Time: Mar 05, 2013 at 09:21 PM
-- Server version: 5.5.25
-- PHP Version: 5.3.19

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `albatross`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE IF NOT EXISTS `accounts` (
  `acc_id` int(10) unsigned NOT NULL,
  `uname` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `home_dir` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `passwd_crypt` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `passwd_sha1` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `passwd_md5` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `passwd_shadow` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `system_uid` int(10) unsigned NOT NULL,
  `system_gid` int(10) unsigned NOT NULL,
  `system_uname` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `date_created` datetime NOT NULL,
  `last_modified` datetime NOT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`acc_id`),
  UNIQUE KEY `uname` (`uname`),
  UNIQUE KEY `system_uname` (`system_uname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Primary accounts database for authentication';

-- --------------------------------------------------------

--
-- Table structure for table `account_info`
--

CREATE TABLE IF NOT EXISTS `account_info` (
  `acc_id` int(10) unsigned NOT NULL,
  `type` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `attr_group` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'default',
  `attr` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`acc_id`,`type`,`attr_group`,`attr`) USING BTREE,
  KEY `accid` (`acc_id`),
  KEY `acc_id_type` (`acc_id`,`type`),
  KEY `accid_type_group` (`acc_id`,`type`,`attr_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='extended attributes for each account';

-- --------------------------------------------------------

--
-- Table structure for table `amm`
--

CREATE TABLE IF NOT EXISTS `amm` (
  `uid` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `jobtype` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `jobstatus` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `acc_id` int(10) unsigned DEFAULT NULL,
  `jobdata` text COLLATE utf8_unicode_ci,
  `created` datetime NOT NULL,
  `lastupdate` datetime NOT NULL,
  `attempt` int(2) unsigned DEFAULT '0',
  `workflow` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`uid`) USING BTREE,
  KEY `jobstatus` (`jobstatus`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Albatross Manager Monitor Job Queue and Log';

-- --------------------------------------------------------

--
-- Table structure for table `amm_sched`
--

CREATE TABLE IF NOT EXISTS `amm_sched` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `jobtype` varchar(30) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Task to run',
  `schedule` int(5) unsigned NOT NULL COMMENT 'Interval in minutes between runs',
  `created` datetime NOT NULL,
  `lastrun` datetime NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Albatross Manager Task Scheduler' AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE IF NOT EXISTS `sessions` (
  `session_id` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `acc_id` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_expires` datetime NOT NULL,
  `ipv4_address` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `useragent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`session_id`),
  KEY `select` (`session_id`,`acc_id`,`ipv4_address`,`useragent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sites`
--

CREATE TABLE IF NOT EXISTS `sites` (
  `acc_id` int(10) unsigned NOT NULL,
  `site_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`acc_id`,`site_name`) USING BTREE,
  KEY `acc_id` (`acc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sites_info`
--

CREATE TABLE IF NOT EXISTS `sites_info` (
  `acc_id` int(10) unsigned NOT NULL,
  `site_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `attr_group` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'default',
  `attr` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`acc_id`,`site_name`,`attr_group`,`attr`) USING BTREE,
  KEY `acc_id_site_name` (`acc_id`,`site_name`),
  KEY `accid_site_group` (`acc_id`,`site_name`,`attr_group`),
  KEY `accid` (`acc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='extended attributes for each site';

-- --------------------------------------------------------

--
-- Table structure for table `userdbs`
--

CREATE TABLE IF NOT EXISTS `userdbs` (
  `acc_id` int(10) unsigned NOT NULL,
  `db` varchar(100) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`db`),
  KEY `acc_id` (`acc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nodes`
--

CREATE TABLE IF NOT EXISTS `nodes` (
  `node_id` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `node_type` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  KEY `node_id` (`node_id`),
  KEY `node_type` (`node_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
