SET @achievements_phrasegroup_id = (SELECT `phrasegroupid` FROM `bengine_phrasesgroups` WHERE `title` = 'Achievements');

INSERT INTO `bengine_phrases` (`languageid`, `phrasegroupid`, `title`, `content`) VALUES
(1, @achievements_phrasegroup_id, 'CURRENT_LEVEL_FOR_USER', 'Aktuelles Level von {@achievementUser}: {@level} ({@xp} XP)');