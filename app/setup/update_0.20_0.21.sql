DELETE FROM `bengine_phrases` WHERE `title` = 'ALLIANCE_ATTACK' AND `languageid` = 1 AND `phrasegroupid` = 1 LIMIT 1;
DELETE FROM `bengine_phrases` WHERE `title` = 'MENU_EXCHANGE';

UPDATE `bengine_phrases` SET `content` = 'Eine schwache Verteidigungsm&ouml;glichkeit, die einfache Lasersch&uuml;sse auf feindliche Raumschiffe feuert.' WHERE `title` = 'LIGHT_LASER_DESC' AND `languageid` = 1;
UPDATE `bengine_phrases` SET `content` = 'Dieses Konto wurde gesperrt. Der Moderator hat folgenden Grund angegebenen: {@banReason}<br/>Weitere Informationen findest du im {@pilloryLink}.' WHERE `title` = 'ACCOUNT_BANNED' AND `languageid` = 1;

INSERT INTO `bengine_phrases` (`languageid`, `phrasegroupid`, `title`, `content`) VALUES
(1, 8, 'NO_BAN_REASON', 'Kein Grund angegeben.');
