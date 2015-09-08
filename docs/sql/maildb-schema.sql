-- MySQL dump 10.14  Distrib 5.5.25-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: maildb
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
-- Table structure for table `alias`
--

DROP TABLE IF EXISTS `alias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alias` (
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Alias address ie. alias@domain.tld',
  `destination` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Email address for mail to be forwarded to ie. user@domain.tld',
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Set to 1 for active, 0 for inactive.',
  `domain` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `antispam`
--

DROP TABLE IF EXISTS `antispam`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `antispam` (
  `address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `action` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `domain`
--

DROP TABLE IF EXISTS `domain`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `domain` (
  `domain` varchar(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Hosted domain name ie. domain.tld',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `spamprefs`
--

DROP TABLE IF EXISTS `spamprefs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `spamprefs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `preference` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Spamassassin Preferences';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Full email address, ie. user@domain.tld',
  `passwdClear` varchar(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Cleartext or CRAM-MD5 password',
  `passwdCrypt` varchar(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Crypt password, using mysql encrypt()',
  `name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Real name of User',
  `uid` int(10) unsigned NOT NULL DEFAULT '5000' COMMENT 'System User ID for maildir files ie. 5000',
  `gid` int(10) unsigned NOT NULL DEFAULT '5000' COMMENT 'System Group ID for maildir files ie. 5000',
  `home` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '/' COMMENT 'Home directory, Set to / for best use',
  `maildir` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Maildir directory for mail, use as /var/vhosts/domain.tld/user/ make sure the trailing / is on for maildir support.',
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Set to 1 for active, 0 for inactive.',
  `domain` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`email`)
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

-- Dump completed on 2015-09-08 20:09:09
