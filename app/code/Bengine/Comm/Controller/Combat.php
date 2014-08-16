<?php
/**
 * Combat report controller.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Combat.php 46 2011-07-30 14:38:11Z secretchampion $
 */

class Bengine_Comm_Controller_Combat extends Bengine_Comm_Controller_Abstract
{
	/**
	 * Combat report action.
	 *
	 * @return Bengine_Comm_Controller_Combat
	 */
	public function reportAction()
	{
		if(!defined("SID"))
		{
			define("SID", "");
		}
		Core::getLanguage()->load(array("info", "AssaultReport"));
		Core::getTPL()->clearHTMLHeaderFiles();
		Core::getTPL()->addHTMLHeaderFile("game.css", "css");
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
		$result = $select->getStatement();
		$row = $result->fetchRow();
		if($row)
		{
			$report = $row["report"];
			$report = preg_replace_callback("/\{lang}([^\"]+)\{\/lang}/siU", function($matches) {
				return Core::getLanguage()->getItem($matches[1]);
			}, $report);
			$report = preg_replace_callback("/\{embedded\[([^\"]+)]}(.*)\{\/embedded}/siU", function($matches) {
				return sprintf(Core::getLanguage()->getItem($matches[1]), $matches[2]);
			}, $report);
			$this->assign("report", $report);
			$this->assign("planetName", $row["planetname"]);
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