<?php
/**
 * Function to resort phrases.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: SortPhrases.php 8 2010-10-17 20:55:04Z secretchampion $
 */

function sortPhrases()
{
	try
	{
		Core::getDB()->query("DROP TABLE IF EXISTS ".PREFIX."buffer");
	}
	catch(Exception $e) { $e->printError(); }

	try
	{
		Core::getDB()->query("CREATE TABLE ".PREFIX."buffer " .
				"(phraseid int(10) unsigned NOT NULL auto_increment, " .
				"languageid int(4) unsigned NOT NULL, " .
				"phrasegroupid int(4) unsigned NOT NULL, " .
				"title varchar(128) NOT NULL, " .
				"content text NOT NULL, " .
				"PRIMARY KEY (phraseid), " .
				"KEY languageid (languageid,phrasegroupid), " .
				"KEY phrasegroupid (phrasegroupid)) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");
	}
	catch(Exception $e) { $e->printError(); }

	try
	{
		Core::getDB()->query("INSERT INTO ".PREFIX."buffer (languageid, phrasegroupid, title, content) SELECT languageid, phrasegroupid, title, content FROM ".PREFIX."phrases ORDER BY languageid ASC, phrasegroupid ASC, title ASC");
	}
	catch(Exception $e) { $e->printError(); }

	try
	{
		Core::getDB()->query("TRUNCATE TABLE ".PREFIX."phrases");
	}
	catch(Exception $e) { $e->printError(); }

	try
	{
		Core::getDB()->query("INSERT INTO ".PREFIX."phrases (phraseid, languageid, phrasegroupid, title, content) SELECT phraseid, languageid, phrasegroupid, title, content FROM ".PREFIX."buffer ORDER BY phraseid ASC");
	}
	catch(Exception $e) { $e->printError(); }

	try
	{
		Core::getDB()->query("DROP TABLE IF EXISTS ".PREFIX."buffer");
	}
	catch(Exception $e) { $e->printError(); }
}
?>
