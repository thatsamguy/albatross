-- MySQL dump 10.14  Distrib 5.5.25-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: albatross
-- ------------------------------------------------------
-- Server version	5.5.25-MariaDB-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `account_info`
--

DROP TABLE IF EXISTS `account_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account_info` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `accounts`
--

DROP TABLE IF EXISTS `accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accounts` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `amm`
--

DROP TABLE IF EXISTS `amm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amm` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `amm_sched`
--

DROP TABLE IF EXISTS `amm_sched`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amm_sched` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `jobtype` varchar(30) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Task to run',
  `schedule` int(5) unsigned NOT NULL COMMENT 'Interval in minutes between runs',
  `created` datetime NOT NULL,
  `lastrun` datetime NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Albatross Manager Task Scheduler';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `session_id` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `acc_id` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_expires` datetime NOT NULL,
  `ipv4_address` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `useragent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`session_id`),
  KEY `select` (`session_id`,`acc_id`,`ipv4_address`,`useragent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sites`
--

DROP TABLE IF EXISTS `sites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sites` (
  `acc_id` int(10) unsigned NOT NULL,
  `site_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`acc_id`,`site_name`) USING BTREE,
  KEY `acc_id` (`acc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sites_info`
--

DROP TABLE IF EXISTS `sites_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sites_info` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `userdbs`
--

DROP TABLE IF EXISTS `userdbs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `userdbs` (
  `acc_id` int(10) unsigned NOT NULL,
  `db` varchar(100) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`db`),
  KEY `acc_id` (`acc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-09-07 18:33:52
