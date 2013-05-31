UPDATE `bengine_phrases` SET `content` = 'Verlassener Planet' WHERE `title` = 'DESTROYED_PLANET' AND `languageid` = 1;
UPDATE `bengine_phrases` SET `content` = '<p>Willkommen bei {config}pagetitle{/config}.</p><p>In {config}pagetitle{/config} sind Rohstoffe ein sehr bedeutsamer Bestandteil des Spiels. Baue also als erstes deine Minen so weit wie m&ouml;glich aus, um Rohstoffe f&uuml;r weitere Stufen zu verdienen. Klicke hierf&uuml;r einfach auf die gr&uuml;ne Schrift unter dem Men&uuml;punkt <i>Geb&auml;ude</i> und baue weiter, sobald die Zeit abgelaufen ist oder wieder Rohstoffe vorhanden sind. Achte jedoch auf deine Energiereserven! Denn ohne Energie kann keine Mine Rohstoffe f&ouml;rdern. Stelle also bevor du eine Mine (aus)baust sicher, dass genug Energie vorhanden ist.<br/>Solltest du noch Fragen haben, besuche das <a href="{config}HELP_PAGE_URL{/config}">Tutorial</a> oder unser <a href="{config}FORUM_URL{/config}">Forum</a>.</p><p>Viele Gr&uuml;&szlig;e<br/>dein {config}pagetitle{/config}-Team</p>' WHERE `title` = 'START_UP_MESSAGE' AND `languageid` = 1;

INSERT INTO `bengine_phrases` (`languageid`, `phrasegroupid`, `title`, `content`) VALUES
(1, 8, 'THEME_INVALID', 'Bitte verwende als Theme eine g&uuml;ltige URL mit abschlie&szlig;endem &quot;/&quot;.'),
(1, 8, 'BAN_NOTIFICATION_MAIL_SUBJECT', 'Benutzerkonto gesperrt'),
(1, 8, 'BAN_NOTIFICATION_MAIL_1', 'dein Benutzerkonto wurde bis zum {@banDate} gesperrt. Der zuständige Moderator hat folgenden Grund für die Sperre angegeben:'),
(1, 8, 'BAN_NOTIFICATION_MAIL_2', 'Wende dich bei Problemen bitte direkt an deinen Moderator:'),
(1, 11, 'MESSAGE_FLOOD_INFO', 'Du hast du viele Nachrichten hintereinander verschickt. Warte ein wenig und versuche es anschlie&szlig;end erneut.');

INSERT INTO `bengine_config` (`var`, `value`, `type`, `description`, `options`, `groupid`, `islisted`, `sort_index`) VALUES
('MESSAGE_FLOOD_MAX', '10', 'integer', 'Maximum private message after checking for spam. Set to 0 to deactivate spam check.', NULL, '6', '1', '0'),
('MESSAGE_FLOOD_SPAN', '600', 'integer', 'Time span for checking private message spam in seconds.', NULL, '6', '1', '0');