ALTER TABLE `bengine_user` ADD `level` TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT  '1', ADD `xp` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0';

CREATE TABLE IF NOT EXISTS `bengine_achievement` (
  `achievement_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
  `description` text NOT NULL,
  `icon` varchar(250) NOT NULL,
  `xp` int(4) unsigned NOT NULL DEFAULT '0',
  `sort_index` int(11) unsigned DEFAULT NULL,
  `parent` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`achievement_id`),
  KEY `parent` (`parent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

INSERT INTO `bengine_achievement` (`achievement_id`, `name`, `description`, `icon`, `xp`, `sort_index`, `parent`) VALUES
(1, 'Grundversorgung', 'Baue Metallmine, Siliziumlabor, Wasserstofflabor und Solarkraftwerk auf mindestens auf Stufe 1 aus.', 'achievement.png', 10, 10, NULL),
(2, 'Angehender Forscher', 'Erforsche deine erste Technologie.', 'achievement.png', 10, 20, NULL),
(3, 'Handelsbereit', 'Baue deinen ersten Transporter.', 'achievement.png', 10, 30, NULL),
(4, 'Feindselig', 'Baue einen Leichten Jäger.', 'achievement.png', 10, 40, NULL),
(5, 'Teamfähig', 'Trete einer Allianz mit mindestens 4 weiteren Mitgliedern bei.', 'achievement.png', 20, 50, NULL),
(6, 'Siegessicher', 'Greife jemanden erfolgreich an.', 'achievement.png', 50, 60, NULL),
(7, 'Kolonist', 'Kolonisiere einen Planeten.', 'achievement.png', 50, 70, NULL),
(8, 'Hinterhalt', 'Verteidige dich erfolgreich gegen einen feindlichen Angriff.', 'achievement.png', 80, 80, NULL),
(9, 'Imperium', 'Kolonisiere 8 Planeten.', 'achievement.png', 80, 90, NULL),
(10, 'Bunker', 'Errichte 2000 Verteidigungsanlagen auf einem Planeten.', 'achievement.png', 80, 100, NULL),
(11, 'Kleiner Schritt', 'Errichte eine Mondbasis.', 'achievement.png', 100, 120, NULL),
(12, 'Angesehner Forscher', 'Erreiche 130 Forschungspunkte.', 'achievement.png', 100, 130, NULL),
(13, 'Imperator', 'Baue einen Todesstern.', 'achievement.png', 200, 140, NULL),
(14, 'Urbanisierung', 'Baue einen Planeten vollständig aus.', 'achievement.png', 100, 110, NULL),
(16, 'Namensgeber', 'Benenne deinen Hauptplaneten um.', 'achievement.png', 10, 11, NULL),
(17, 'Ein Gesicht', 'Gebe einen Avatar an und schreibe einen eigenen Text für dein Profil.', 'achievement.png', 20, 41, NULL),
(18, 'Bester Freund', 'Füge jemanden zu deiner Freundeliste hinzu.', 'achievement.png', 40, 55, NULL),
(19, 'Sammler', 'Sammle 10.000.000 Rohstoffe auf einem Planeten.', 'achievement.png', 100, 121, NULL);

CREATE TABLE IF NOT EXISTS `bengine_achievement_l10n` (
  `achievement_l10n_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `language_id` int(4) unsigned NOT NULL,
  `achievement_id` int(10) unsigned NOT NULL,
  `name` varchar(250) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`achievement_l10n_id`),
  KEY `language_id` (`language_id`),
  KEY `achievement_id` (`achievement_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `bengine_achievement2user` (
  `achievement2user_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `achievement_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `date` int(11) unsigned NOT NULL,
  PRIMARY KEY (`achievement2user_id`),
  KEY `achievement_id` (`achievement_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `bengine_achievement_requirement` (
  `achievement_requirement_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `achievement_id` int(11) unsigned NOT NULL,
  `class` varchar(255) NOT NULL,
  `id` varchar(250) NOT NULL,
  `value` varchar(250) NOT NULL,
  `config` text,
  PRIMARY KEY (`achievement_requirement_id`),
  KEY `achievement_id` (`achievement_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

INSERT INTO `bengine_achievement_requirement` (`achievement_requirement_id`, `achievement_id`, `class`, `id`, `value`, `config`) VALUES
(1, 1, 'game/achievement_requirement_building', 'METALMINE', '1', NULL),
(2, 1, 'game/achievement_requirement_building', 'SILICON_LAB', '1', NULL),
(3, 1, 'game/achievement_requirement_building', 'HYDROGEN_LAB', '1', NULL),
(4, 1, 'game/achievement_requirement_building', 'SOLAR_PLANT', '1', NULL),
(5, 2, 'game/achievement_requirement_researchPoints', '', '1', NULL),
(6, 3, 'game/achievement_requirement_unit', 'SMALL_TRANSPORTER', '1', NULL),
(7, 4, 'game/achievement_requirement_unit', 'LIGHT_FIGHTER', '1', NULL),
(8, 5, 'game/achievement_requirement_allianceMember', '', '4', NULL),
(9, 6, 'game/achievement_requirement_assaultCount', 'ATTACKER_WON', '1', NULL),
(10, 7, 'game/achievement_requirement_planets', '', '2', NULL),
(11, 8, 'game/achievement_requirement_assaultCount', 'DEFENDER_WON', '1', NULL),
(12, 9, 'game/achievement_requirement_planets', '', '9', NULL),
(13, 10, 'game/achievement_requirement_unit', '_DEFENSE', '2000', NULL),
(14, 11, 'game/achievement_requirement_building', 'MOON_BASE', '1', NULL),
(15, 12, 'game/achievement_requirement_researchPoints', '', '130', NULL),
(16, 13, 'game/achievement_requirement_unit', 'DEATH_STAR', '1', NULL),
(17, 14, 'game/achievement_requirement_planetFields', '', '0', NULL),
(19, 16, 'game/achievement_requirement_planetName', '', '', NULL),
(20, 17, 'game/achievement_requirement_profile', '', '', NULL),
(21, 18, 'game/achievement_requirement_friends', '', '1', NULL),
(22, 19, 'game/achievement_requirement_resources', 'any', '10000000', NULL);

ALTER TABLE `bengine_achievement` ADD FOREIGN KEY ( `parent` ) REFERENCES `bengine_achievement` (`achievement_id`) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE `bengine_achievement_requirement` ADD FOREIGN KEY (`achievement_id`) REFERENCES `bengine_achievement` (`achievement_id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `bengine_achievement2user` ADD FOREIGN KEY ( `achievement_id` ) REFERENCES `bengine_achievement` (`achievement_id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `bengine_achievement2user` ADD FOREIGN KEY ( `user_id` ) REFERENCES `bengine_user` (`userid`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `bengine_achievement_l10n` ADD FOREIGN KEY (`achievement_id`) REFERENCES `bengine_achievement` (`achievement_id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `bengine_achievement_l10n` ADD FOREIGN KEY ( `language_id` ) REFERENCES `bengine_languages` (`languageid`) ON DELETE CASCADE ON UPDATE CASCADE;

INSERT INTO `bengine_phrasesgroups` (`title`) VALUES ('Achievements');
SET @achievements_phrasegroup_id = LAST_INSERT_ID();

INSERT INTO `bengine_phrases` (`languageid`, `phrasegroupid`, `title`, `content`) VALUES
('1', '1', 'MENU_ACHIEVEMENTS', 'Abzeichen'),
('1', @achievements_phrasegroup_id, 'CURRENT_LEVEL', 'Aktuelles Level: {@level} ({@xp} XP)'),
('1', @achievements_phrasegroup_id, 'XP_TO_NEXT_LEVEL', '{@leftXP} XP bis Level {@nextLevel}'),
('1', @achievements_phrasegroup_id, 'UNLOCKED', 'abgeschlossen'),
('1', @achievements_phrasegroup_id, 'ACHIEVEMENT_UNLOCKED', 'Abzeichen freigeschaltet'),
('1', @achievements_phrasegroup_id, 'COLLECTED_XP', 'Gesammelte XP');