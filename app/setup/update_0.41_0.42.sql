UPDATE `bengine_achievement` SET `description` = 'Baue deinen ersten kl. Transporter.' WHERE `achievement_id` = '3';

ALTER TABLE `bengine_user` ADD `dpoints` INT(9) UNSIGNED NOT NULL DEFAULT '0' AFTER `rpoints`;

INSERT INTO `bengine_phrases` (`languageid`, `phrasegroupid`, `title`, `content`) VALUES
(1, 12, 'DEFENSE', 'Verteidigung');

ALTER TABLE `bengine_assault` CHANGE  `lostunits_attacker` `lostunits_attacker` BIGINT( 12 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `bengine_assault` CHANGE  `lostunits_defender` `lostunits_defender` BIGINT( 12 ) UNSIGNED NOT NULL DEFAULT '0';
