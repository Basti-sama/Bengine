<?php
/**
 * This class imports language files into the database.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Importer.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Recipe_Language_Importer
{
	/**
	 * Holds data to import.
	 *
	 * @var mixed
	 */
	protected $importData = array();

	/**
	 * Language id.
	 *
	 * @var integer
	 */
	protected $langId = 0;

	/**
	 * Phrase group id.
	 *
	 * @var integer
	 */
	protected $groupId = 0;

	/**
	 * Constructor.
	 *
	 * @param mixed File name / File content / Array
	 * @param mixed Language code or id.
	 */
	public function __construct($data, $lang, $group)
	{
		// Find out which language we got
		if(is_numeric($lang))
		{
			try { $this->getFromLangFromId($lang); }
			catch(Exception $e) { $e->printError(); }
		}
		else
		{
			try { $this->getFromLangCode($lang); }
			catch(Exception $e) { $e->printError(); }
		}

		// Find out which phrase group we got
		if(is_numeric($group))
		{
			try { $this->getGroupFromId($group); }
			catch(Exception $e) { $e->printError(); }
		}
		else
		{
			try { $this->getFromGroupName($group); }
			catch(Exception $e) { $e->printError(); }
		}

		// Check which type the import data is
		if(is_array($data))
		{
			$this->importData = $data;
		}
		else if(file_exists($data))
		{
			try { $this->getDataFromFile($data); }
			catch(Exception $e) { $e->printError(); }
		}
		else
		{
			throw new Recipe_Exception_Generic("Unkown data supplyed for importer.");
		}

		// Start import
		try { $this->import(); }
		catch(Exception $e) { $e->printError(); }
		return;
	}

	/**
	 * Actual import function.
	 *
	 * @return Recipe_Language_Importer
	 */
	protected function import()
	{
		// Check for valid import data
		if(!is_array($this->importData))
		{
			throw new Recipe_Exception_Generic("Supplied data for importer is not an array.");
		}

		// Importing ...
		foreach($this->importData as $key => $value)
		{
			Core::getQuery()->insert("phrases", array("languageid", "phrasegroupid", "title", "content"), array($this->langId, $this->groupId, $key, $value));
		}
		return $this;
	}

	/**
	 * Loads import data from a file.
	 *
	 * @param string	Path to file
	 *
	 * @return Recipe_Language_Importer
	 */
	protected function getDataFromFile($file)
	{
		if(!file_exists($file))
		{
			throw new Recipe_Exception_Generic("Could not found import file. Make sure the path is correct or try absolute path. Given path: ".$file);
		}

		// If import file is PHP we can require it
		if(preg_match("/^.+\.php$/i", $file))
		{
			$item = array();
			require_once($file);
			$this->importData = $item;
			return;
		}

		// Get the file's contents
		$data = file_get_contents($file);
		$this->extractFromText($data);
		return $this;
	}

	/**
	 * Extract the contents of an import file into an array.
	 *
	 * @param string	Text content
	 *
	 * @return Recipe_Language_Importer
	 */
	protected function extractFromText($text)
	{
		// First of all extract rows
		$row = explode("\r\n", $text);

		// Extract phrase group from phrase
		$size = count($row);
		for($i = 0; $i < $size; $i++)
		{
			$cell = explode("=>", $row[$i]);
			$this->importData[$cell[0]] = $cell[1];
		}
		return $this;
	}

	/**
	 * Checks the given language id for validation.
	 *
	 * @param integer	Language id
	 *
	 * @return Recipe_Language_Importer
	 */
	protected function getFromLangFromId($lang)
	{
		$result = Core::getQuery()->select("languages", array("languageid"), "", "languageid = '".$lang."'");
		if(Core::getDB()->num_rows($result) > 0)
		{
			$this->langId = $lang;
			return $this;
		}
		throw new Recipe_Exception_Generic("The given language id does not exist. Please create language first before import data.");
	}

	/**
	 * Gets the language id for given language code.
	 *
	 * @param string	Language code
	 *
	 * @return Recipe_Language_Importer
	 */
	protected function getFromLangCode($lang, $createData = null)
	{
		$result = Core::getQuery()->select("languages", array("languageid"), "", "langcode = '".$lang."'");
		$row = Core::getDB()->fetch($result);
		Core::getDB()->free_result($result);
		if(!$row)
		{
			if(is_array($createData))
			{
				Core::getQuery()->insert("languages", array("langcode", "title", "charset"), $createData);
				$this->langId = Core::getDB()->insert_id();
				return $this;
			}
			else
			{
				throw new Recipe_Exception_Generic("The given language code does not exist. Please create language first before import data.");
			}
		}
		$this->langId = $row["languageid"];
		return $this;
	}

	/**
	 * Checks the given phrase group id for validation.
	 *
	 * @param string	Group id
	 *
	 * @return Recipe_Language_Importer
	 */
	protected function getGroupFromId($group)
	{
		$result = Core::getQuery()->select("phrasesgroups", array("phrasegroupid"), "", "phrasegroupid = '".$group."'");
		if(Core::getDB()->num_rows($result) > 0)
		{
			$this->groupId = $group;
			return $this;
		}
		throw new Recipe_Exception_Generic("The given phrase group does not exist. Please create group first before import data.");
	}

	/**
	 * Gets the phrase group id for given group name.
	 *
	 * @param string	Group name
	 *
	 * @return Recipe_Language_Importer
	 */
	protected function getFromGroupName($group)
	{
		$result = Core::getQuery()->select("phrasesgroups", array("phrasegroupid"), "", "title = '".$group."'");
		$row = Core::getDB()->fetch($result);
		Core::getDB()->free_result($result);
		if(!$row)
		{
			// Create unkown group
			Core::getQuery()->insert("phrasesgroups", array("title"), array($group));
			$this->groupId = Core::getDB()->insert_id();
			return $this;
		}
		$this->groupId = $row["phrasegroupid"];
		return $this;
	}

	/**
	 * Sets the default language.
	 *
	 * @param mixed		Language code or id
	 *
	 * @return Recipe_Language_Importer
	 */
	public function setDefaultLanguage($langId)
	{
		if(!is_numeric($langId))
		{
			$result = Core::getQuery()->select("languages", array("languageid"), "", "langcode = '".$langId."'", "", "1");
			if($row = Core::getDB()->fetch($result))
			{
				$langId = $row["languageid"];
			}
		}
		if(is_numeric($langId))
		{
			Core::getConfig()->set("defaultlanguage", $langId);
		}
		return $this;
	}
}
?>