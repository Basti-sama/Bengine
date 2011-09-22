<?php
/**
 * Alliance page controller.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Alliance.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Comm_Controller_Alliance extends Comm_Controller_Abstract
{
	public function pageAction()
	{
		Core::getLanguage()->load("Alliance");
		Core::getTPL()->clearHTMLHeaderFiles();
		Core::getTPL()->addHTMLHeaderFile("style.css", "css");
		Core::getTPL()->addHTMLHeaderFile("lib/jquery.js", "js");
		$tag = $this->getParam("1");
		$fNumber = array("member", "points", "rpoints", "fpoints");
		$attr = array("a.aid", "a.name", "a.tag", "a.logo", "a.textextern", "a.homepage", "a.showhomepage", "COUNT(u2a.userid) AS member", "SUM(u.points) AS points", "SUM(u.rpoints) AS rpoints", "SUM(u.fpoints) AS fpoints");
		$joins  = "LEFT JOIN ".PREFIX."user2ally u2a ON (u2a.aid = a.aid) ";
		$joins .= "LEFT JOIN ".PREFIX."user u ON (u2a.userid = u.userid) ";
		$result = Core::getQuery()->select("alliance a", $attr, $joins, "tag = '".$tag."'", "", 1, "a.aid");
		$row = Core::getDB()->fetch($result);
		Core::getDB()->free_result($result);
		if($row)
		{
			foreach($fNumber as $field)
			{
				$row[$field] = fNumber($row[$field]);
			}
			$parser = new Bengine_Alliance_Page_Parser($row["aid"]);
			if(Str::length(strip_tags($row["textextern"])) > 0)
			{
				$row["textextern"] = $parser->startParser($row["textextern"]);
			}
			else
			{
				$row["textextern"] = Core::getLang()->get("WELCOME");
			}
			$row["homepage"] = ($row["homepage"] != "") ? Link::get($row["homepage"], $row["homepage"], $row["homepage"]) : "";
			$row["logo"] = ($row["logo"] != "") ? Image::getImage($row["logo"], "") : "";
			$this->assign($row);
			$this->setIsAjax();
		}
		else
		{
			$this->setNoDisplay(true);
		}
	}
}
?>