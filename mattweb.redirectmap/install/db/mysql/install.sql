CREATE TABLE IF NOT EXISTS `mw_redirectmap` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `OLD_URL` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `NEW_URL` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `URL_NOTE` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `OLD_URL` (`OLD_URL`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;