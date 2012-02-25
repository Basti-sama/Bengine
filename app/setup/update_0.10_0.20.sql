INSERT INTO `bengine_phrases` (`languageid`, `phrasegroupid`, `title`, `content`) VALUES
(1, 5, 'ON', 'auf'),
(1, 9, 'GALAXY_END_REACHED', 'Du hast bereits das Ende der Galaxie erreicht. Weiter geht es nicht.');

INSERT INTO `bengine_config` (`var`, `value`, `type`, `description`, `options`, `groupid`, `islisted`, `sort_index`) VALUES
('EXCLUDE_TEMPLATE_PACKAGE', NULL, 'text', 'Exclude template package from drop-down in user preferences (comma separated).', NULL, '8', '1', '0'),
('TERRAFORMER_ADDITIONAL_FIELDS', 5, 'text', 'Number of fields that will be added to a planet per terraformer level.', NULL, '2', '1', '0');