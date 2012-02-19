ALTER TABLE  `bengine_requirements` ADD `hidden` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';

INSERT INTO `bengine_requirements` (`buildingid`, `needs`, `level`, `hidden`) SELECT `buildingid`, '59', `level`, '1' FROM `bengine_requirements` WHERE `needs` = '8';

INSERT INTO `bengine_phrases` (`languageid`, `phrasegroupid`, `title`, `content`) VALUES ('1', '5', 'HIDE_IN_TECHTREE', 'Im Techtree verstecken');
