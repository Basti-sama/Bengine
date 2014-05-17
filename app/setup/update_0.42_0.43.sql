INSERT INTO `bengine_config` (`var`, `value`, `type`, `description`, `options`, `groupid`, `islisted`, `sort_index`) VALUES
('CRONJOB_MAX_EVENT_EXECUTION', '1000', 'integer', 'Number of events that will be executed by the cron job at once.', '', 3, 1, 0);

INSERT INTO `bengine_cronjob` (`class`, `month`, `day`, `weekday`, `hour`, `minute`, `xtime`, `last`, `active`) VALUES
('game/eventExecution', '1,2,3,4,5,6,7,8,9,10,11,12', '1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31', '1,2,3,4,5,6,7', '0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23', '0', NULL, NULL, 0);

INSERT INTO `bengine_phrases` (`languageid`, `phrasegroupid`, `title`, `content`) VALUES
('1', '14', 'CONFIRM_ABANDON_ALLIANCE', 'Willst du die Allianz wirklich aufl&ouml;sen?'),
('1', '5', 'CONFIRM_SHIPYARD_ABORT', 'Willst du die ausgew&auml;hlten Auftr&auml;ge wirklich abbrechen? Du erh&auml;ltst nur {config=SHIPYARD_ORDER_ABORT_PERCENT}% der Rohstoffe zur&uuml;ck.'),
('1', '5', 'CHOOSE_SHIPYARD_ORDER', 'Bitte w&auml;hle einen Auftrag aus den du abbrechen m&ouml;chtest.');
