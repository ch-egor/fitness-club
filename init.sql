-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.1.34-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win32
-- HeidiSQL Version:             9.5.0.5196
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Dumping database structure for fitness_club
CREATE DATABASE IF NOT EXISTS `fitness_club` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `fitness_club`;

-- Dumping structure for table fitness_club.client
CREATE TABLE IF NOT EXISTS `client` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_of_birth` date NOT NULL,
  `sex` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(24) COLLATE utf8mb4_unicode_ci NOT NULL,
  `photo` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL,
  `password` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_confirmation_code` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_C7440455E7927C74` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table fitness_club.client: ~7 rows (approximately)
/*!40000 ALTER TABLE `client` DISABLE KEYS */;
INSERT INTO `client` (`id`, `name`, `date_of_birth`, `sex`, `email`, `phone`, `photo`, `is_active`, `password`, `email_confirmation_code`) VALUES
	(1, 'admin', '2013-01-01', 'M', 'admin@example.com', '+79123456789', NULL, 1, '$2y$13$YYfRnh/02Qdo6RQIL/zg2u1J3dtHdgJ2K.MqPTCVp38i1bwyX09aq', NULL),
	(2, 'test1', '1987-05-11', 'M', 'test1@example.com', '+79001002001', NULL, 1, '$2y$13$oUduRdm1k7xkF4tTjE5da.5ikayNgjtUsLZPSwpyrBkr7awdLt.Fe', NULL),
	(5, 'test2', '1993-03-17', 'F', 'test2@example.com', '+79001002002', NULL, 1, '$2y$13$zJK3AJTvWRRBdqYVCfYFMufO.D3I4xgLbxM0J3ISS6fMHXtNXmZpu', NULL),
	(7, 'test3', '2001-09-27', 'M', 'test3@example.com', '+79001002003', NULL, 1, '$2y$13$zfQ4KGjPkaBLbyoBFqbybeoqF8kqikWmiKlQSuRHnHL1Vwpav98RK', NULL),
	(8, 'test4', '1996-05-01', 'M', 'test4@example.com', '+79001002004', NULL, 1, '$2y$13$NcEZjMOyP8Myi.sDMnw3C.i9INUhAvJEFUaZZJ9yl7CcTSKIoVtEO', NULL),
	(12, 'test5', '1985-07-08', 'F', 'test5@example.com', '+79001002005', NULL, 1, '$2y$13$PFlUGTgFkMO3mfJBe5IqNOgGUwJiKSCqrNUBBwy9tZP/U1MfGCFiS', NULL);
/*!40000 ALTER TABLE `client` ENABLE KEYS */;

-- Dumping structure for table fitness_club.group_session
CREATE TABLE IF NOT EXISTS `group_session` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `coach` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(1000) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table fitness_club.group_session: ~3 rows (approximately)
/*!40000 ALTER TABLE `group_session` DISABLE KEYS */;
INSERT INTO `group_session` (`id`, `name`, `coach`, `description`) VALUES
	(1, 'Session 1', 'Coach 1', 'Description 1'),
	(2, 'Session 2', 'Coach 2', 'Description 2'),
	(3, 'Session 3', 'Coach 3', 'Description 3');
/*!40000 ALTER TABLE `group_session` ENABLE KEYS */;

-- Dumping structure for table fitness_club.migration_versions
CREATE TABLE IF NOT EXISTS `migration_versions` (
  `version` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Dumping data for table fitness_club.migration_versions: ~4 rows (approximately)
/*!40000 ALTER TABLE `migration_versions` DISABLE KEYS */;
INSERT INTO `migration_versions` (`version`) VALUES
	('20180826084734'),
	('20180827124710'),
	('20180828120336'),
	('20180829061438'),
	('20180829124104');
/*!40000 ALTER TABLE `migration_versions` ENABLE KEYS */;

-- Dumping structure for table fitness_club.subscription
CREATE TABLE IF NOT EXISTS `subscription` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `group_session_id` int(11) NOT NULL,
  `notification_type` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_A3C664D319EB6921` (`client_id`),
  KEY `IDX_A3C664D3B6F28D6D` (`group_session_id`),
  CONSTRAINT `FK_A3C664D319EB6921` FOREIGN KEY (`client_id`) REFERENCES `client` (`id`),
  CONSTRAINT `FK_A3C664D3B6F28D6D` FOREIGN KEY (`group_session_id`) REFERENCES `group_session` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table fitness_club.subscription: ~3 rows (approximately)
/*!40000 ALTER TABLE `subscription` DISABLE KEYS */;
INSERT INTO `subscription` (`id`, `client_id`, `group_session_id`, `notification_type`) VALUES
	(5, 2, 1, 1),
	(6, 2, 2, 0),
	(7, 2, 3, 2),
	(8, 5, 1, 2),
	(9, 5, 2, 1),
	(10, 5, 3, 2),
	(11, 7, 1, 0),
	(12, 7, 2, 1),
	(13, 7, 3, 1),
	(14, 8, 1, 2),
	(15, 8, 2, 2),
	(16, 8, 3, 1),
	(17, 12, 1, 1),
	(18, 12, 2, 1),
	(19, 12, 3, 0);
/*!40000 ALTER TABLE `subscription` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
