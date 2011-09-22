<?php
/**
 * Exports all languages into a XML file.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Exporter.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Recipe_Language_Exporter
{
	/**
	 * Destination folder.
	 *
	 * @var string
	 */
	protected $destination = "";

	/**
	 * Destination file.
	 *
	 * @var string
	 */
	protected $file = "";

	/**
	 * Holds the phrases groups.
	 *
	 * @var array
	 */
	protected $groups = null;

	/**
	 * Indention characters.
	 *
	 * @var string
	 */
	protected $i = "\t";

	/**
	 * New line characters.
	 *
	 * @var string
	 */
	protected $n = "\n";

	/**
	 * Language id or code to export.
	 *
	 * @var mixed
	 */
	protected $langId = null;

	/**
	 * Constructor.
	 *
	 * @param string	Destination folder
	 * @param string	Destination file
	 *
	 * @return void
	 */
	public function __construct($destination, $filename)
	{
		$this->setDestination($destination);
		$this->setFile($filename);
		return;
	}

	/**
	 * Sets the destination folder.
	 *
	 * @param string
	 *
	 * @return Recipe_Language_Exporter
	 */
	public function setDestination($destination)
	{
		if(!is_dir($destination) || !is_writable($destination))
		{
			throw new Recipe_Exception_Generic("Export destination folder does not exist or is not writable.");
		}
		if(Str::substring($destination, 0, Str::length($destination) - 1) != "/")
		{
			$destination .= "/";
		}
		$this->destination = $destination;
		return $this;
	}

	/**
	 * Sets the destination file.
	 *
	 * @param string
	 *
	 * @return Recipe_Language_Exporter
	 */
	public function setFile($filename)
	{
		if(file_exists($this->destination.$filename))
		{
			throw new Recipe_Exception_Generic("Export destination file already exists.");
		}
		$this->file = $filename;
		return $this;
	}

	/**
	 * Starts the export process.
	 *
	 * @return LanguageExporter
	 */
	public function startExport()
	{
		$data  = '<?xml version="1.0" encoding="UTF-8"?>'.$this->n;
		$data .= '<lang>'.$this->n;
		$where = (is_null($this->langId)) ? "" : "languageid = '".$this->langId."' OR langcode = '".$this->langId."'";
		$result = Core::getQuery()->select("languages", array("languageid", "langcode", "title", "charset"), "", $where);
		while($row = Core::getDB()->fetch($result))
		{
			$data .= $this->getI().'<'.$row["langcode"].' title="'.$row["title"].'" charset="'.$row["charset"].'">'.$this->n;
			$groups = $this->getGroups();
			foreach($groups as $group)
			{
				$data .= $this->getI(2).'<'.$group["title"].'>'.$this->n;
				$_result = Core::getQuery()->select("phrases", array("title", "content"), "", "languageid = '".$row["languageid"]."' AND phrasegroupid = '".$group["phrasegroupid"]."'", "title ASC");
				while($_row = Core::getDB()->fetch($_result))
				{
					$data .= $this->getI(3).'<'.$_row["title"].'><![CDATA['.$_row["content"].']]></'.$_row["title"].'>'.$this->n;
				}
				$data .= $this->getI(2).'</'.$group["title"].'>'.$this->n;
			}
			$data .= $this->getI().'</'.$row["langcode"].'>'.$this->n;
		}
		$data .= '</lang>';
		return $this->write($data);
	}

	/**
	 * Writes the XML data into the export file.
	 *
	 * @param string	XML data
	 *
	 * @return Recipe_Language_Exporter
	 */
	protected function write($data)
	{
		file_put_contents($this->getFile(), $data);
		return $this;
	}

	/**
	 * Loads the phrases groups and return them.
	 *
	 * @return array
	 */
	protected function getGroups()
	{
		if(is_null($this->groups))
		{
			$result = Core::getQuery()->select("phrasesgroups", array("phrasegroupid", "title"), "", "", "title ASC");
			while($row = Core::getDB()->fetch($result))
			{
				$this->groups[] = $row;
			}
		}
		return $this->groups;
	}

	/**
	 * Returns destination folder + filename.
	 *
	 * @return string
	 */
	public function getFile()
	{
		return $this->destination.$this->file;
	}

	/**
	 * Gets indent characters.
	 *
	 * @param integer	Number of repetitions
	 *
	 * @return string
	 */
	protected function getI($multiplier = 1)
	{
		return str_repeat($this->i, $multiplier);
	}

	/**
	 * Sets a specific language id or code that will be exported.
	 *
	 * @param mixed		Language id or code
	 *
	 * @return Recipe_Language_Exporter
	 */
	public function setLanguageId($langId)
	{
		$this->langId = $langId;
		return $this;
	}
}
?>