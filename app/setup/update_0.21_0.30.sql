UPDATE bengine_event_type SET code = 'game/build' WHERE event_type_id = 1;
UPDATE bengine_event_type SET code = 'game/demolish' WHERE event_type_id = 2;
UPDATE bengine_event_type SET code = 'game/research' WHERE event_type_id = 3;
UPDATE bengine_event_type SET code = 'game/shipyard' WHERE event_type_id = 4;
UPDATE bengine_event_type SET code = 'game/shipyard' WHERE event_type_id = 5;
UPDATE bengine_event_type SET code = 'game/position' WHERE event_type_id = 6;
UPDATE bengine_event_type SET code = 'game/transport' WHERE event_type_id = 7;
UPDATE bengine_event_type SET code = 'game/colonize' WHERE event_type_id = 8;
UPDATE bengine_event_type SET code = 'game/recycling' WHERE event_type_id = 9;
UPDATE bengine_event_type SET code = 'game/attack' WHERE event_type_id = 10;
UPDATE bengine_event_type SET code = 'game/espionage' WHERE event_type_id = 11;
UPDATE bengine_event_type SET code = 'game/allianceAttack' WHERE event_type_id = 12;
UPDATE bengine_event_type SET code = 'game/halt' WHERE event_type_id = 13;
UPDATE bengine_event_type SET code = 'game/moonDestruction' WHERE event_type_id = 14;
UPDATE bengine_event_type SET code = 'game/placeholder' WHERE event_type_id = 15;
UPDATE bengine_event_type SET code = 'game/missileAttack' WHERE event_type_id = 16;
UPDATE bengine_event_type SET code = 'game/holding' WHERE event_type_id = 17;
UPDATE bengine_event_type SET code = 'game/alliedFleet' WHERE event_type_id = 18;
UPDATE bengine_event_type SET code = 'game/return' WHERE event_type_id = 20;

UPDATE bengine_folder SET class = 'game/user' WHERE folder_id = 1;
UPDATE bengine_folder SET class = 'game/user' WHERE folder_id = 2;
UPDATE bengine_folder SET class = 'game/system' WHERE folder_id = 3;
UPDATE bengine_folder SET class = 'game/system' WHERE folder_id = 4;
UPDATE bengine_folder SET class = 'game/combat' WHERE folder_id = 5;
UPDATE bengine_folder SET class = 'game/alliance' WHERE folder_id = 6;

INSERT INTO `bengine_phrases` (`languageid`, `phrasegroupid`, `title`, `content`) VALUES
('1', '4', 'RESEARCH_LAB_UPGRADING', 'Das Forschungslabor wird gerade ausgebaut. Forschung ist derzeit nicht m&ouml;glich.'),
('1', '4', 'SHIPYARD_UPGRADING', 'Die Schiffswerft oder Nanitenfabrik wird gerade ausgebaut. Der Bau von Schiffen ist derzeit nicht m&ouml;glich.'),
('1', '18', 'ALIENS', 'Alienvolk'),
('1', '10', 'COLONIZE_RESOURCE_WARNING', 'Die Intergalaktische Aufsichtsbeh&ouml;rde f&uuml;r Wirtschaft verbietet das Mitf&uuml;hren von G&uuml;tern bei der Kolonisierung von fremden Planeten aufgrund von Wettbewerbsverzerrung. Deine Flotte l&auml;sst die Rohstoffe daher zur&uuml;ck.');

UPDATE bengine_cronjob SET `class` = 'game/pointClean' WHERE `cronid` = 1;
UPDATE bengine_cronjob SET `class` = 'game/removeInactiveUser' WHERE `cronid` = 2;
UPDATE bengine_cronjob SET `class` = 'game/removeGalaxyGarbage' WHERE `cronid` = 3;
UPDATE bengine_cronjob SET `class` = 'game/cleanSessions' WHERE `cronid` = 4;
UPDATE bengine_cronjob SET `class` = 'game/reminder' WHERE `cronid` = 5;
UPDATE bengine_cronjob SET `class` = 'game/cleanCombats' WHERE `cronid` = 6;

UPDATE bengine_user SET `templatepackage` = '' WHERE `templatepackage` = 'standard';

INSERT INTO `bengine_config` (`var`, `value`, `type`, `description`, `options`, `groupid`, `islisted`, `sort_index`) VALUES
('RAPIDFIRE_DISABLED', '0', 'boolean', 'Disables the rapid fire in combat system.', 9, 1, 0);

ALTER TABLE  `bengine_assault` CHANGE  `accomplished` `accomplished` TINYINT( 1 ) NOT NULL DEFAULT '0';
