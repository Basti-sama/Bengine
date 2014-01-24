SET @achievements_phrasegroup_id = (SELECT `phrasegroupid` FROM `bengine_phrasesgroups` WHERE `title` = 'Achievements');

INSERT INTO `bengine_phrases` (`languageid`, `phrasegroupid`, `title`, `content`) VALUES
(1, @achievements_phrasegroup_id, 'CURRENT_LEVEL_FOR_USER', 'Aktuelles Level von {@achievementUser}: {@level} ({@xp} XP)'),
(1, 10, 'ATTACKING_STOPPAGE_ENABLED_UNTIL', 'Angriffssperre bis');

INSERT INTO `bengine_config` (`var`, `value`, `type`, `description`, `options`, `groupid`, `islisted`, `sort_index`) VALUES
('ATTACKING_STOPPAGE_END_DATE', '0', 'datetime', 'Disables the attacking stoppage at the given date.', '', 3, 1, 0);
