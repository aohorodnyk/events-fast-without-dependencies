DROP TABLE IF EXISTS `events`;
CREATE TABLE events(
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_date` DATE NOT NULL,
  `country_code` CHAR(2) NOT NULL,
  `event_name` varchar(6) NOT NULL,
  `count` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`event_date`, `country_code`, `event_name`)
);
