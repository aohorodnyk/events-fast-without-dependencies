DROP TABLE IF EXISTS `events_raw`;
CREATE TABLE events_raw(
  `event_date` DATE NOT NULL,
  `country_code` CHAR(2) NOT NULL,
  `event_name` varchar(6) NOT NULL,
);