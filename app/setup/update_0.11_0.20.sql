INSERT INTO `bengine_phrases` (`languageid`, `phrasegroupid`, `title`, `content`) VALUES
(1, 5, 'ON', 'auf'),
(1, 9, 'GALAXY_END_REACHED', 'Du hast bereits das Ende der Galaxie erreicht. Weiter geht es nicht.'),
(1, 19, 'ALLOW_ON_MOON', 'Auf Mond erlauben');

INSERT INTO `bengine_config` (`var`, `value`, `type`, `description`, `options`, `groupid`, `islisted`, `sort_index`) VALUES
('EXCLUDE_TEMPLATE_PACKAGE', NULL, 'text', 'Exclude template package from drop-down in user preferences (comma separated).', NULL, '8', '1', '0'),
('TERRAFORMER_ADDITIONAL_FIELDS', 5, 'text', 'Number of fields that will be added to a planet per terraformer level.', NULL, '2', '1', '0');

ALTER TABLE `bengine_construction` ADD `allow_on_moon` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '1' AFTER `mode`;

UPDATE `bengine_construction` SET `allow_on_moon` = '0' WHERE `buildingid` = 1;
UPDATE `bengine_construction` SET `allow_on_moon` = '0' WHERE `buildingid` = 2;
UPDATE `bengine_construction` SET `allow_on_moon` = '0' WHERE `buildingid` = 3;
UPDATE `bengine_construction` SET `allow_on_moon` = '0' WHERE `buildingid` = 4;
UPDATE `bengine_construction` SET `allow_on_moon` = '0' WHERE `buildingid` = 5;
UPDATE `bengine_construction` SET `allow_on_moon` = '0' WHERE `buildingid` = 7;
UPDATE `bengine_construction` SET `allow_on_moon` = '0' WHERE `buildingid` = 9;
UPDATE `bengine_construction` SET `allow_on_moon` = '0' WHERE `buildingid` = 10;
UPDATE `bengine_construction` SET `allow_on_moon` = '0' WHERE `buildingid` = 11;
UPDATE `bengine_construction` SET `allow_on_moon` = '0' WHERE `buildingid` = 12;
UPDATE `bengine_construction` SET `allow_on_moon` = '0' WHERE `buildingid` = 51;
UPDATE `bengine_construction` SET `allow_on_moon` = '0' WHERE `buildingid` = 52;
UPDATE `bengine_construction` SET `allow_on_moon` = '0' WHERE `buildingid` = 53;
UPDATE `bengine_construction` SET `allow_on_moon` = '0' WHERE `buildingid` = 58;

UPDATE `bengine_building2planet` SET `buildingid` = 6 WHERE `buildingid` = 57;
UPDATE `bengine_building2planet` SET `buildingid` = 8 WHERE `buildingid` = 59;

DELETE FROM `bengine_construction` WHERE `buildingid` = 57;
DELETE FROM `bengine_construction` WHERE `buildingid` = 59;

ALTER TABLE  `bengine_planet` ADD `sort_index` INT( 11 ) UNSIGNED NULL DEFAULT NULL;

INSERT INTO `bengine_config` (`var`, `value`, `type`, `description`, `options`, `groupid`, `islisted`, `sort_index`) VALUES
('INACTIVE_USER_TIME_1', '604800', 'integer', 'Time to display a small i for inactive users in galaxy and ranking. Time in seconds.', NULL, '5', '1', '0'),
('INACTIVE_USER_TIME_2', '1814400', 'integer', 'Time to display a large I for inactive users in galaxy and ranking. Time in seconds.', NULL, '5', '1', '0'),
('MIN_VACATION_MODE', '172800', 'integer', 'Minimum time to stay in vacation mode. Time in seconds.', NULL, '8', '1', '0');
