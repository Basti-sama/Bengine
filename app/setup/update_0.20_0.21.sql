DELETE FROM `bengine_phrases` WHERE `title` = 'ALLIANCE_ATTACK' AND `languageid` = 1 AND `phrasegroupid` = 1 LIMIT 1;
DELETE FROM `bengine_phrases` WHERE `title` = 'MENU_EXCHANGE';

UPDATE `bengine_phrases` SET `content` = 'Eine schwache Verteidigungsm&ouml;glichkeit, die einfache Lasersch&uuml;sse auf feindliche Raumschiffe feuert.' WHERE `title` = 'LIGHT_LASER_DESC' AND `languageid` = 1;
