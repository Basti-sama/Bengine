UPDATE `bengine_achievement` SET `description` = 'Baue deinen ersten kl. Transporter.' WHERE `achievement_id` = '3';

ALTER TABLE `bengine_user` ADD `dpoints` INT(9) UNSIGNED NOT NULL DEFAULT '0' AFTER `rpoints`;

INSERT INTO `bengine_phrases` (`languageid`, `phrasegroupid`, `title`, `content`) VALUES
(1, 12, 'DEFENSE', 'Verteidigung');