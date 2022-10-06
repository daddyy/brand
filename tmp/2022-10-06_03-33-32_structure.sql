SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';
SET NAMES utf8mb4;

DROP TABLE IF EXISTS `brand`;
CREATE TABLE `brand` (
`brand_id` int(11) NOT NULL AUTO_INCREMENT,
`deleted` bit(1) NOT NULL,
`data` varchar(4096) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL,
`modify_date` timestamp NOT NULL DEFAULT current_timestamp() on update current_timestamp(),
`create_date` timestamp NOT NULL DEFAULT current_timestamp()
,primary key (`brand_id`),
key `deleted` (`deleted`),
key `data` (`data`(4096))
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `route`;
CREATE TABLE `route` (
`route_id` int(11) NOT NULL AUTO_INCREMENT,
`lang_id` int(11),
`domain_id` int(11),
`object_id` int(11),
`object_type` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
`path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
`modify_date` timestamp NOT NULL DEFAULT current_timestamp() on update current_timestamp(),
`create_date` timestamp NOT NULL DEFAULT current_timestamp()
,primary key (`route_id`),
key `lang_id` (`lang_id`),
key `domain_id` (`domain_id`),
key `object_id` (`object_id`)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `content`;
CREATE TABLE `content` (
`content_id` int(11) NOT NULL AUTO_INCREMENT,
`lang_id` int(11),
`object_id` int(11),
`object_type` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
`title` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
`description` varchar(4096) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
`text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
`modify_date` timestamp NOT NULL DEFAULT current_timestamp() on update current_timestamp(),
`create_date` timestamp NOT NULL DEFAULT current_timestamp()
,primary key (`content_id`),
key `lang_id` (`lang_id`),
key `object_id` (`object_id`)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `lang`;
CREATE TABLE `lang` (
`lang_id` int(11) NOT NULL AUTO_INCREMENT,
`alpha_2` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
`modify_date` timestamp NOT NULL DEFAULT current_timestamp() on update current_timestamp(),
`create_date` timestamp NOT NULL DEFAULT current_timestamp()
,primary key (`lang_id`)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `domain`;
CREATE TABLE `domain` (
`domain_id` int(11) NOT NULL AUTO_INCREMENT,
`lang_id` int(11),
`name` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
`modify_date` timestamp NOT NULL DEFAULT current_timestamp() on update current_timestamp(),
`create_date` timestamp NOT NULL DEFAULT current_timestamp()
,primary key (`domain_id`),
key `lang_id` (`lang_id`)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;