DROP TABLE IF EXISTS `cache`;
CREATE TABLE cache(
  `name` varchar(10) NOT NULL ,
  `value` TEXT NOT NULL,
  PRIMARY KEY (`name`)
);