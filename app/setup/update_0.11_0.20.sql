INSERT INTO `bengine_phrases` (`languageid`, `phrasegroupid`, `title`, `content`) VALUES
(1, 5, 'ON', 'auf'),
(1, 9, 'GALAXY_END_REACHED', 'Du hast bereits das Ende der Galaxie erreicht. Weiter geht es nicht.'),
(1, 19, 'ALLOW_ON_MOON', 'Auf Mond erlauben');

INSERT INTO `bengine_config` (`var`, `value`, `type`, `description`, `options`, `groupid`, `islisted`, `sort_index`) VALUES
('EXCLUDE_TEMPLATE_PACKAGE', NULL, 'text', 'Exclude template package from drop-down in user preferences (comma separated).', NULL, '8', '1', '0'),
('TERRAFORMER_ADDITIONAL_FIELDS', 5, 'text', 'Number of fields that will be added to a planet per terraformer level.', NULL, '2', '1', '0');

ALTER TABLE `bengine_construction` ADD `allow_on_moon` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '1' AFTER `mode`;

UPDATE `bengine_construction` SET `allow_on_moon` = '0' WHERE `buildingid` = 1;
UPDATE `bengine_construction` SET `allow_on_moon` = '0' WHERE `buildingid` = 2;
UPDATE `bengine_construction` SET `allow_on_moon` = '0' WHERE `buildingid` = 3;
UPDATE `bengine_construction` SET `allow_on_moon` = '0' WHERE `buildingid` = 4;
UPDATE `bengine_construction` SET `allow_on_moon` = '0' WHERE `buildingid` = 5;
UPDATE `bengine_construction` SET `allow_on_moon` = '0' WHERE `buildingid` = 7;
UPDATE `bengine_construction` SET `allow_on_moon` = '0' WHERE `buildingid` = 9;
UPDATE `bengine_construction` SET `allow_on_moon` = '0' WHERE `buildingid` = 10;
UPDATE `bengine_construction` SET `allow_on_moon` = '0' WHERE `buildingid` = 11;
UPDATE `bengine_construction` SET `allow_on_moon` = '0' WHERE `buildingid` = 12;
UPDATE `bengine_construction` SET `allow_on_moon` = '0' WHERE `buildingid` = 51;
UPDATE `bengine_construction` SET `allow_on_moon` = '0' WHERE `buildingid` = 52;
UPDATE `bengine_construction` SET `allow_on_moon` = '0' WHERE `buildingid` = 53;
UPDATE `bengine_construction` SET `allow_on_moon` = '0' WHERE `buildingid` = 58;

UPDATE `bengine_building2planet` SET `buildingid` = 6 WHERE `buildingid` = 57;
UPDATE `bengine_building2planet` SET `buildingid` = 8 WHERE `buildingid` = 59;

DELETE FROM `bengine_construction` WHERE `buildingid` = 57;
DELETE FROM `bengine_construction` WHERE `buildingid` = 59;

ALTER TABLE  `bengine_planet` ADD `sort_index` INT( 11 ) UNSIGNED NULL DEFAULT NULL;

INSERT INTO `bengine_config` (`var`, `value`, `type`, `description`, `options`, `groupid`, `islisted`, `sort_index`) VALUES
('INACTIVE_USER_TIME_1', '604800', 'integer', 'Time to display a small i for inactive users in galaxy and ranking. Time in seconds.', NULL, '5', '1', '0'),
('INACTIVE_USER_TIME_2', '1814400', 'integer', 'Time to display a large I for inactive users in galaxy and ranking. Time in seconds.', NULL, '5', '1', '0'),
('MIN_VACATION_MODE', '172800', 'integer', 'Minimum time to stay in vacation mode. Time in seconds.', NULL, '8', '1', '0'),
('SHIPYARD_MAX_UNITS_PER_ORDER', '1000', 'integer', 'Number of maximum allowed units per order.', NULL, 8, 1, 0);

UPDATE `bengine_phrases` SET `title` = 'REGISTRATION_MAIL_1', `content` = 'danke für deine Anmeldung bei {config}pagetitle{/config}!' WHERE `title` = 'REGISTRATION_MAIL' AND `languageid` = 1;
UPDATE `bengine_phrases` SET `content` = 'dein letzter Login bei {config}pagetitle{/config} war am {@reminderLast}. Vielleicht hast du uns ja vergessen, aber dein Konto ist immer noch verfügbar. Wir würden uns freuen, wenn du dich mal wieder meldest.' WHERE `title` = 'REMINDER_MAIL_MESSAGE' AND `languageid` = 1;
UPDATE `bengine_phrases` SET `content` = 'du hast deine E-Mail Adrese bei {config=pagetitle} geändert. Die geänderte E-Mail benötigt eine Aktivierung bevor der Account wieder genutzt werden kann. Verwende dazu den unten stehenden Link.' WHERE `title` = 'EMAIL_EMAIL_MESSAGE' AND `languageid` = 1;
UPDATE `bengine_phrases` SET `content` = 'du hast dein Passwort bei {config=pagetitle} geändert. Das geänderte Passwort benötigt eine Aktivierung bevor es genutzt werden kann. Verwende dazu den unten stehenden Link.', `title` = 'EMAIL_PASSWORD_MESSAGE_1' WHERE `title` = 'EMAIL_PASSWORD_MESSAGE' AND `languageid` = 1;
UPDATE `bengine_phrases` SET `content` = 'Solltest du dein Passwort bei {config}pagetitle{/config} vergessen haben, kannst du über den folgenden Link ein neues Passwort setzen:', `title` = 'REQUEST_PASSWORD_1' WHERE `title` = 'REQUEST_PASSWORD' AND `languageid` = 1;
UPDATE `bengine_phrases` SET `content` = 'Solltest du deinen Benutzernamen bei {config}pagetitle{/config} vergessen haben, kannst du dich jetzt wieder anmelden.', `title` = 'REQUEST_USERNAME_1' WHERE `title` = 'REQUEST_USERNAME' AND `languageid` = 1;
UPDATE `bengine_phrases` SET `content` = 'Eine neuartige Methode um Energie zu gewinnen: Es wird ein Deuterium- und ein Tritiumkern zu einem Heliumkern verschmolzen. Beide Teilchen werden aus Wasserstoff gewonnen.' WHERE `title` = 'HYDROGEN_PLANT_DESC' AND `languageid` = 1;
UPDATE `bengine_phrases` SET `content` = 'Planeten sortieren', `title` = 'EDIT_PLANET_SORTING', `phrasegroupid` = 1 WHERE `title` = 'PLANET_ORDER' AND `languageid` = 1;
UPDATE `bengine_phrases` SET `content` = 'Sortierung speichern', `title` = 'SAVE_PLANET_SORTING', `phrasegroupid` = 1 WHERE `title` = 'EVOLUTION' AND `languageid` = 1;
UPDATE `bengine_phrases` SET `content` = 'Geschwindigkeit' WHERE `title` = 'SPEED' AND `languageid` = 1;
UPDATE `bengine_phrases` SET `content` = 'Deine Flotte erreicht den Planeten %s %s und liefert ihre Ladung ab:\r\nMetall: %s Silizium: %s Wasserstoff: %s.' WHERE `title` = 'TRANSPORT_ACCOMPLISHED' AND `languageid` = 1;
UPDATE `bengine_phrases` SET `content` = 'Achtung: Willst du wirklich {@totalQty} Einheiten f&uuml;r {@metalCredit} Metall, {@siliconCredit} Silizium und {@hydrogenCredit} Wasserstoff verkaufen?' WHERE `title` = 'SCRAP_MERCHANT_CHANGE' AND `languageid` = 1;
UPDATE `bengine_phrases` SET `content` = 'Die Nachricht(en) wurden erfolgreich an einen Moderator &uuml;bermittelt.' WHERE `title` = 'MESSAGES_REPORTED' AND `languageid` = 1;

INSERT INTO `bengine_phrases` (`languageid`, `phrasegroupid`, `title`, `content`) VALUES
(1, 7, 'EMAIL_CLOSING_SENDER', 'dein {config}pagetitle{/config}-Team'),
(1, 7, 'EMAIL_GREETINGS', 'Mit besten Grüßen'),
(1, 7, 'REGISTRATION_MAIL_2', 'Dein Passwort lautet: {@regPassword}'),
(1, 7, 'REGISTRATION_MAIL_3', 'Du musst jetzt deinen Account aktivieren. Klicke dazu auf den unten stehen Link, um die Aktivieren abzuschließen. Sollte der Link nicht anklickbar sein, kopiere ihn in die Adressleiste deines Browsers.'),
(1, 7, 'REGISTRATION_MAIL_4', 'Solltest du noch Fragen haben, kannst du gerne in unserem Forum vorbei schauen.'),
(1, 7, 'EMAIL_SALUTATION', 'Hallo {@username},'),
(1, 7, 'REQUEST_USERNAME_2', 'Benutzername: {@username}'),
(1, 7, 'REQUEST_USERNAME_3', 'Hast du deinen Benutzernamen nicht vergessen, empfiehlt es sich die E-Mail Adresse in den Account-Einstellungen zu ändern. Der Clienet dieser Anfrage hatte die IP-Adresse {@ipaddress}.'),
(1, 7, 'REQUEST_PASSWORD_2', 'Hast du dein Passwort nicht vergessen, empfiehlt es sich die E-Mail Adresse in den Account-Einstellungen zu ändern. Der Client dieser Anfrage hatte die IP-Adresse {@ipaddress}.'),
(1, 7, 'REQUEST_PASSWORD_3', 'Um den Account ohne Passwortänderung wieder zu aktivieren, verwende bitte den folgenden Link:'),
(1, 8, 'EMAIL_PASSWORD_MESSAGE_2', 'Dein neues Passwort:'),
(1, 7, 'REGISTER_NOW', 'Jetzt registrieren!'),
(1, 5, 'OCCUPIED_FIELDS', '{@occupiedFields} von {@maxFields} Feldern'),
(1, 11, 'MODERATOR_REPORT_COMBAT', '{@reportSender} meldete folgenden Kampfbericht vom {@reportSendTime}: {@reportLink}');

ALTER TABLE `bengine_assault` CHANGE `result` `result` TINYINT( 1 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `bengine_user` DROP `planetorder`;

ALTER TABLE `bengine_user` DROP `qpoints`;

DELETE FROM `bengine_requirements` WHERE `needs` = 54 AND `level` = 100;