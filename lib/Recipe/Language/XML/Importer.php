<?php
/**
 * This class imports XML language files into the database.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Importer.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Recipe_Language_XML_Importer extends Recipe_Language_Importer
{
	/**
	 * XML data.
	 *
	 * @var SimpleXMLElement
	 */
	protected $xml = null;

	/**
	 * Constructor
	 *
	 * @param string $data    XML-file or XML-data
	 * @return \Recipe_Language_XML_Importer
	 */
	public function __construct($data)
	{
		$this->setData($data);
	}

	/**
	 * Sets the XML data
	 *
	 * @param string $data	XML-file or XML-data
	 * @return Recipe_Language_XML_Importer
	 */
	public function setData($data)
	{
		$this->xml = new XML($data);
		$this->xml = $this->xml->get();
		return $this;
	}

	/**
	 * Proceeds the import.
	 *
	 * @throws Recipe_Exception_Generic
	 * @return Recipe_Language_XML_Importer
	 */
	public function proceed()
	{
		if(is_null($this->xml))
		{
			throw new Recipe_Exception_Generic("No XML data set.");
		}
		$defaultLang = false;
		/* @var XMLObj $lang */
		foreach($this->xml->children() as $lang)
		{
			if($lang->getAttribute("action") == "setDefault")
			{
				$defaultLang = $lang->getName();
			}
			$createData = array(
				"langcode"	=> $lang->getName(),
				"title"		=> $lang->getAttribute("title"),
				"charset"	=> $lang->getAttribute("charset")
			);
			$this->getFromLangCode($lang->getName(), $createData);
			/* @var XMLObj $group */
			foreach($lang->getChildren() as $group)
			{
				$this->getFromGroupName($group->getName());
				$this->importData = array();
				/* @var XMLObj $phrase */
				foreach($group->getChildren() as $phrase)
				{
					$this->importData[$phrase->getName()] = $phrase->getString();
				}
				$this->import();
			}
		}
		if($defaultLang)
		{
			$this->setDefaultLanguage($defaultLang);
		}
		return $this;
	}
}
?>