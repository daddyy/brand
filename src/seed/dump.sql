-- Adminer 4.7.6 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `brand`;
CREATE TABLE `brand` (
  `brand_id` int(11) NOT NULL AUTO_INCREMENT,
  `deleted` bit(1) NOT NULL,
  `data` varchar(4096) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `modify_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `create_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`brand_id`),
  KEY `deleted` (`deleted`),
  KEY `data` (`data`(768))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `brand` (`brand_id`, `deleted`, `data`, `modify_date`, `create_date`) VALUES
(1,	CONV('0', 2, 10) + 0,	NULL,	'2022-10-06 11:54:32',	'2022-10-06 11:54:32'),
(2,	CONV('0', 2, 10) + 0,	NULL,	'2022-10-06 11:55:02',	'2022-10-06 11:55:02'),
(3,	CONV('0', 2, 10) + 0,	NULL,	'2022-10-06 11:59:39',	'2022-10-06 11:59:39'),
(4,	CONV('0', 2, 10) + 0,	NULL,	'2022-10-06 11:59:40',	'2022-10-06 11:59:40'),
(5,	CONV('0', 2, 10) + 0,	NULL,	'2022-10-06 11:59:40',	'2022-10-06 11:59:40'),
(6,	CONV('0', 2, 10) + 0,	NULL,	'2022-10-06 11:59:41',	'2022-10-06 11:59:41'),
(7,	CONV('0', 2, 10) + 0,	NULL,	'2022-10-06 11:59:41',	'2022-10-06 11:59:41'),
(8,	CONV('0', 2, 10) + 0,	NULL,	'2022-10-06 11:59:42',	'2022-10-06 11:59:42'),
(9,	CONV('0', 2, 10) + 0,	NULL,	'2022-10-06 11:59:42',	'2022-10-06 11:59:42');

DROP TABLE IF EXISTS `content`;
CREATE TABLE `content` (
  `content_id` int(11) NOT NULL AUTO_INCREMENT,
  `lang_id` int(11) DEFAULT NULL,
  `object_id` int(11) DEFAULT NULL,
  `object_type` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `title` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `description` varchar(4096) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `modify_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `create_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`content_id`),
  KEY `lang_id` (`lang_id`),
  KEY `object_id` (`object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `content` (`content_id`, `lang_id`, `object_id`, `object_type`, `title`, `description`, `text`, `modify_date`, `create_date`) VALUES
(1,	1,	1,	'brand',	'ahoj',	'ahoj',	'ahoj',	'2022-10-06 11:59:20',	'2022-10-06 11:54:42'),
(2,	1,	3,	'brand',	'ahoj',	'aaa',	'aaaaaaa',	'2022-10-06 12:18:50',	'2022-10-06 12:18:50');

DROP TABLE IF EXISTS `domain`;
CREATE TABLE `domain` (
  `domain_id` int(11) NOT NULL AUTO_INCREMENT,
  `lang_id` int(11) DEFAULT NULL,
  `name` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `modify_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `create_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`domain_id`),
  KEY `lang_id` (`lang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `domain` (`domain_id`, `lang_id`, `name`, `modify_date`, `create_date`) VALUES
(1,	1,	'brand.daddyy.local',	'2022-10-06 11:46:24',	'2022-10-06 11:46:24');

DROP TABLE IF EXISTS `lang`;
CREATE TABLE `lang` (
  `lang_id` int(11) NOT NULL AUTO_INCREMENT,
  `alpha_2` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `modify_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `create_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`lang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `lang` (`lang_id`, `alpha_2`, `modify_date`, `create_date`) VALUES
(1,	'cs',	'2022-10-06 11:46:46',	'2022-10-06 11:46:46');

DROP TABLE IF EXISTS `route`;
CREATE TABLE `route` (
  `route_id` int(11) NOT NULL AUTO_INCREMENT,
  `lang_id` int(11) DEFAULT NULL,
  `domain_id` int(11) DEFAULT NULL,
  `object_id` int(11) DEFAULT NULL,
  `object_type` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `modify_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `create_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`route_id`),
  KEY `lang_id` (`lang_id`),
  KEY `domain_id` (`domain_id`),
  KEY `object_id` (`object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `route` (`route_id`, `lang_id`, `domain_id`, `object_id`, `object_type`, `path`, `modify_date`, `create_date`) VALUES
(1,	NULL,	NULL,	1,	'brand',	'brand/1',	'2022-10-06 11:54:42',	'2022-10-06 11:54:42'),
(2,	NULL,	NULL,	3,	'brand',	'brand/3',	'2022-10-06 12:18:50',	'2022-10-06 12:18:50'),
(3,	NULL,	NULL,	NULL,	'brand',	'/',	'2022-10-06 12:20:07',	'2022-10-06 12:20:07');

-- 2022-10-06 12:28:16
