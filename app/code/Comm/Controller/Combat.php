<?php
/**
 * Combat report controller.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Combat.php 46 2011-07-30 14:38:11Z secretchampion $
 */

class Comm_Controller_Combat extends Comm_Controller_Abstract
{
	public function reportAction()
	{
		if(!defined("SID"))
		{
			define("SID", "");
		}
		Core::getLanguage()->load(array("info", "AssaultReport"));
		Core::getTPL()->clearHTMLHeaderFiles();
		Core::getTPL()->addHTMLHeaderFile("style.css", "css");
		Core::getTPL()->addHTMLHeaderFile("lib/jquery.js", "js");
		$select = new Recipe_Database_Select();
		$select->from(array("a" => "assault"))
			->join(array("p" => "planet"), "p.planetid = a.planetid")
			->attributes(array(
				"a" => array("report"),
				"p" => array("planetname")
			))
			->where(array("a" => "assaultid"), $this->getParam("1"))
			->where(array("a" => "key"), $this->getParam("2"));
		$result = $select->getResource();
		$row = Core::getDB()->fetch($result);
		Core::getDB()->free_result($result);
		if($row)
		{
			$report = $row["report"];
			$report = preg_replace("/\{lang}([^\"]+)\{\/lang}/siUe", 'Core::getLanguage()->getItem("$1")', $report);
			$report = preg_replace("/\{embedded\[([^\"]+)]}(.*)\{\/embedded}/siUe", 'sprintf(Core::getLanguage()->getItem("$1"), "$2")', $report);
			$this->report = $report;
			$this->planetName = $row["planetname"];
			$this->setIsAjax();
		}
		else
		{
			$this->setNoDisplay(true);
		}
		return $this;
	}
}
?>