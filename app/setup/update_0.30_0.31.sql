UPDATE `bengine_phrases` SET `content` = 'Verlassener Planet' WHERE `title` = 'DESTROYED_PLANET' AND `languageid` = 1;
UPDATE `bengine_phrases` SET `content` = '<p>Willkommen bei {config}pagetitle{/config}.</p><p>In {config}pagetitle{/config} sind Rohstoffe ein sehr bedeutsamer Bestandteil des Spiels. Baue also als erstes deine Minen so weit wie m&ouml;glich aus, um Rohstoffe f&uuml;r weitere Stufen zu verdienen. Klicke hierf&uuml;r einfach auf die gr&uuml;ne Schrift unter dem Men&uuml;punkt <i>Geb&auml;ude</i> und baue weiter, sobald die Zeit abgelaufen ist oder wieder Rohstoffe vorhanden sind. Achte jedoch auf deine Energiereserven! Denn ohne Energie kann keine Mine Rohstoffe f&ouml;rdern. Stelle also bevor du eine Mine (aus)baust sicher, dass genug Energie vorhanden ist.<br/>Solltest du noch Fragen haben, besuche das <a href="{config}HELP_PAGE_URL{/config}">Tutorial</a> oder unser <a href="{config}FORUM_URL{/config}">Forum</a>.</p><p>Viele Gr&uuml;&szlig;e<br/>dein {config}pagetitle{/config}-Team</p>' WHERE `title` = 'START_UP_MESSAGE' AND `languageid` = 1;
UPDATE `bengine_phrases` SET `title` = 'UNKNOWN_PLANET' WHERE `title` = 'UNKOWN_PLANET';
UPDATE `bengine_phrases` SET `title` = 'NO_SHIPS_READY' WHERE `title` = 'NO_SHIPS_REDEADY';
UPDATE `bengine_phrases` SET `title` = 'UNKNOWN_MISSION' WHERE `title` = 'UNKOWN_MISSION';

INSERT INTO `bengine_phrases` (`languageid`, `phrasegroupid`, `title`, `content`) VALUES
(1, 8, 'THEME_INVALID', 'Bitte verwende als Theme eine g&uuml;ltige URL mit abschlie&szlig;endem &quot;/&quot;.'),
(1, 8, 'BAN_NOTIFICATION_MAIL_SUBJECT', 'Benutzerkonto gesperrt'),
(1, 8, 'BAN_NOTIFICATION_MAIL_1', 'dein Benutzerkonto wurde bis zum {@banDate} gesperrt. Der zuständige Moderator hat folgenden Grund für die Sperre angegeben:'),
(1, 8, 'BAN_NOTIFICATION_MAIL_2', 'Wende dich bei Problemen bitte direkt an deinen Moderator:'),
(1, 11, 'MESSAGE_FLOOD_INFO', 'Du hast du viele Nachrichten hintereinander verschickt. Warte ein wenig und versuche es anschlie&szlig;end erneut.'),
(1, 14, 'ALLIANCE_RELATION_APPLICATION_MESSAGE_REQUIRED', 'Bitte gib eine Nachricht an.');

INSERT INTO `bengine_config` (`var`, `value`, `type`, `description`, `options`, `groupid`, `islisted`, `sort_index`) VALUES
('MESSAGE_FLOOD_MAX', '10', 'integer', 'Maximum private message after checking for spam. Set to 0 to deactivate spam check.', NULL, '6', '1', '0'),
('MESSAGE_FLOOD_SPAN', '600', 'integer', 'Time span for checking private message spam in seconds.', NULL, '6', '1', '0');

ALTER TABLE `bengine_languages` DROP `charset`;

UPDATE `bengine_phrases` SET `content` = REPLACE(`content`, 'Ã¤', 'ä');
UPDATE `bengine_phrases` SET `content` = REPLACE(`content`, 'Ã„', 'Ä');
UPDATE `bengine_phrases` SET `content` = REPLACE(`content`, 'Ã¶', 'ö');
UPDATE `bengine_phrases` SET `content` = REPLACE(`content`, 'Ã–', 'Ö');
UPDATE `bengine_phrases` SET `content` = REPLACE(`content`, 'Ã¼', 'ü');
UPDATE `bengine_phrases` SET `content` = REPLACE(`content`, 'Ãœ', 'Ü');
UPDATE `bengine_phrases` SET `content` = REPLACE(`content`, 'ÃŸ', 'ß');

UPDATE `bengine_alliance` SET `textextern` = REPLACE(`textextern`, 'Ã¤', 'ä');
UPDATE `bengine_alliance` SET `textextern` = REPLACE(`textextern`, 'Ã„', 'Ä');
UPDATE `bengine_alliance` SET `textextern` = REPLACE(`textextern`, 'Ã¶', 'ö');
UPDATE `bengine_alliance` SET `textextern` = REPLACE(`textextern`, 'Ã–', 'Ö');
UPDATE `bengine_alliance` SET `textextern` = REPLACE(`textextern`, 'Ã¼', 'ü');
UPDATE `bengine_alliance` SET `textextern` = REPLACE(`textextern`, 'Ãœ', 'Ü');
UPDATE `bengine_alliance` SET `textextern` = REPLACE(`textextern`, 'ÃŸ', 'ß');

UPDATE `bengine_alliance` SET `textintern` = REPLACE(`textintern`, 'Ã¤', 'ä');
UPDATE `bengine_alliance` SET `textintern` = REPLACE(`textintern`, 'Ã„', 'Ä');
UPDATE `bengine_alliance` SET `textintern` = REPLACE(`textintern`, 'Ã¶', 'ö');
UPDATE `bengine_alliance` SET `textintern` = REPLACE(`textintern`, 'Ã–', 'Ö');
UPDATE `bengine_alliance` SET `textintern` = REPLACE(`textintern`, 'Ã¼', 'ü');
UPDATE `bengine_alliance` SET `textintern` = REPLACE(`textintern`, 'Ãœ', 'Ü');
UPDATE `bengine_alliance` SET `textintern` = REPLACE(`textintern`, 'ÃŸ', 'ß');

UPDATE `bengine_page` SET `content` = REPLACE(`content`, 'Ã¤', 'ä');
UPDATE `bengine_page` SET `content` = REPLACE(`content`, 'Ã„', 'Ä');
UPDATE `bengine_page` SET `content` = REPLACE(`content`, 'Ã¶', 'ö');
UPDATE `bengine_page` SET `content` = REPLACE(`content`, 'Ã–', 'Ö');
UPDATE `bengine_page` SET `content` = REPLACE(`content`, 'Ã¼', 'ü');
UPDATE `bengine_page` SET `content` = REPLACE(`content`, 'Ãœ', 'Ü');
UPDATE `bengine_page` SET `content` = REPLACE(`content`, 'ÃŸ', 'ß');

UPDATE `bengine_page` SET `title` = REPLACE(`title`, 'Ã¤', 'ä');
UPDATE `bengine_page` SET `title` = REPLACE(`title`, 'Ã„', 'Ä');
UPDATE `bengine_page` SET `title` = REPLACE(`title`, 'Ã¶', 'ö');
UPDATE `bengine_page` SET `title` = REPLACE(`title`, 'Ã–', 'Ö');
UPDATE `bengine_page` SET `title` = REPLACE(`title`, 'Ã¼', 'ü');
UPDATE `bengine_page` SET `title` = REPLACE(`title`, 'Ãœ', 'Ü');
UPDATE `bengine_page` SET `title` = REPLACE(`title`, 'ÃŸ', 'ß');

UPDATE `bengine_message` SET `message` = REPLACE(`message`, 'Ã¤', 'ä');
UPDATE `bengine_message` SET `message` = REPLACE(`message`, 'Ã„', 'Ä');
UPDATE `bengine_message` SET `message` = REPLACE(`message`, 'Ã¶', 'ö');
UPDATE `bengine_message` SET `message` = REPLACE(`message`, 'Ã–', 'Ö');
UPDATE `bengine_message` SET `message` = REPLACE(`message`, 'Ã¼', 'ü');
UPDATE `bengine_message` SET `message` = REPLACE(`message`, 'Ãœ', 'Ü');
UPDATE `bengine_message` SET `message` = REPLACE(`message`, 'ÃŸ', 'ß');

UPDATE `bengine_message` SET `subject` = REPLACE(`subject`, 'Ã¤', 'ä');
UPDATE `bengine_message` SET `subject` = REPLACE(`subject`, 'Ã„', 'Ä');
UPDATE `bengine_message` SET `subject` = REPLACE(`subject`, 'Ã¶', 'ö');
UPDATE `bengine_message` SET `subject` = REPLACE(`subject`, 'Ã–', 'Ö');
UPDATE `bengine_message` SET `subject` = REPLACE(`subject`, 'Ã¼', 'ü');
UPDATE `bengine_message` SET `subject` = REPLACE(`subject`, 'Ãœ', 'Ü');
UPDATE `bengine_message` SET `subject` = REPLACE(`subject`, 'ÃŸ', 'ß');

UPDATE `bengine_profile2user` SET `data` = REPLACE(`data`, 'Ã¤', 'ä');
UPDATE `bengine_profile2user` SET `data` = REPLACE(`data`, 'Ã„', 'Ä');
UPDATE `bengine_profile2user` SET `data` = REPLACE(`data`, 'Ã¶', 'ö');
UPDATE `bengine_profile2user` SET `data` = REPLACE(`data`, 'Ã–', 'Ö');
UPDATE `bengine_profile2user` SET `data` = REPLACE(`data`, 'Ã¼', 'ü');
UPDATE `bengine_profile2user` SET `data` = REPLACE(`data`, 'Ãœ', 'Ü');
UPDATE `bengine_profile2user` SET `data` = REPLACE(`data`, 'ÃŸ', 'ß');

UPDATE `bengine_news` SET `title` = REPLACE(`title`, 'Ã¤', 'ä');
UPDATE `bengine_news` SET `title` = REPLACE(`title`, 'Ã„', 'Ä');
UPDATE `bengine_news` SET `title` = REPLACE(`title`, 'Ã¶', 'ö');
UPDATE `bengine_news` SET `title` = REPLACE(`title`, 'Ã–', 'Ö');
UPDATE `bengine_news` SET `title` = REPLACE(`title`, 'Ã¼', 'ü');
UPDATE `bengine_news` SET `title` = REPLACE(`title`, 'Ãœ', 'Ü');
UPDATE `bengine_news` SET `title` = REPLACE(`title`, 'ÃŸ', 'ß');

UPDATE `bengine_news` SET `text` = REPLACE(`text`, 'Ã¤', 'ä');
UPDATE `bengine_news` SET `text` = REPLACE(`text`, 'Ã„', 'Ä');
UPDATE `bengine_news` SET `text` = REPLACE(`text`, 'Ã¶', 'ö');
UPDATE `bengine_news` SET `text` = REPLACE(`text`, 'Ã–', 'Ö');
UPDATE `bengine_news` SET `text` = REPLACE(`text`, 'Ã¼', 'ü');
UPDATE `bengine_news` SET `text` = REPLACE(`text`, 'Ãœ', 'Ü');
UPDATE `bengine_news` SET `text` = REPLACE(`text`, 'ÃŸ', 'ß');
