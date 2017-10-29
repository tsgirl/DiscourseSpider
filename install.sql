-- Adminer 4.2.2 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `config`;
CREATE TABLE `config` (
  `id` int(11) NOT NULL,
  `name` text,
  `value` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `posts`;
CREATE TABLE `posts` (
  `id` int(11) NOT NULL DEFAULT '0',
  `fr` int(11) NOT NULL,
  `content` text NOT NULL,
  `creatime` int(11) NOT NULL,
  `updatime` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `replyto` text,
  PRIMARY KEY (`id`),
  KEY `creatime` (`creatime`),
  KEY `updatime` (`updatime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `status`;
CREATE TABLE `status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` int(11) NOT NULL DEFAULT '0',
  `comment` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

INSERT INTO `status` (`id`, `value`, `comment`) VALUES (1,	0,	'page of latest.json');
INSERT INTO `status` (`id`, `value`, `comment`) VALUES (2,	0,	'topic(item) of latest.json');
INSERT INTO `status` (`id`, `value`, `comment`) VALUES (3,	0,	'is_page_completed');

DROP TABLE IF EXISTS `topics`;
CREATE TABLE `topics` (
  `id` int(11) NOT NULL DEFAULT '0',
  `stream` text,
  `offset` int(11) NOT NULL DEFAULT '0',
  `title` text NOT NULL,
  `creatime` int(11) NOT NULL,
  `updatime` int(11) NOT NULL,
  `count` int(11) NOT NULL DEFAULT '0',
  `username` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `creatime` (`creatime`),
  KEY `updatime` (`updatime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL DEFAULT '0',
  `name` text,
  `username` text NOT NULL,
  `avatar` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- 2017-10-28 13:20:16