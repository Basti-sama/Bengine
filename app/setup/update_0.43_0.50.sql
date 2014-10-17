UPDATE `bengine_phrases` SET `content` = 'Login' WHERE `title` = 'SIGN_IN' AND `languageid` = 1;
UPDATE `bengine_phrases` SET `title` = 'WITHDRAW' WHERE `title` = 'CANCEL' AND `languageid` = 1;

INSERT INTO `bengine_phrases` (`languageid`, `phrasegroupid`, `title`, `content`) VALUES
(1, 1, 'CANCEL', 'Abbrechen'),
(1, 7, 'SIGN_IN_BUTTON', 'Los!'),
(1, 7, 'OTHER_LANGUAGES', 'Andere Sprachen:');