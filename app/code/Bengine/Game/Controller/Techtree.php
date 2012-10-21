<?php
/**
 * Shows all available constructions and their requirements.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Techtree.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_Game_Controller_Techtree extends Bengine_Game_Controller_Abstract
{
	/**
	 * Main method to display techtree.
	 *
	 * @return Bengine_Game_Controller_Techtree
	 */
	protected function init()
	{
		Core::getLanguage()->load(array("info", "buildings"));
		return parent::init();
	}

	/**
	 * Index action.
	 *
	 * @return Bengine_Game_Controller_Techtree
	 */
	protected function indexAction()
	{
		$reqs = Game::getAllRequirements();
		$cons = array(); $research = array(); $ships = array(); $def = array(); $moon = array();
		$result = Core::getQuery()->select("construction", array("buildingid", "mode", "name"), "ORDER BY display_order ASC, buildingid ASC");
		while($row = Core::getDB()->fetch($result))
		{
			Hook::event("LoadTechtree", array(&$row));
			$bid = $row["buildingid"];
			$requirements = "";
			if(!isset($reqs[$bid]))
			{
				$reqs[$bid] = array();
			}
			foreach($reqs[$bid] as $r)
			{
				$buffer = Core::getLanguage()->getItem($r["name"])." (".Core::getLanguage()->getItem("LEVEL")." ".$r["level"].")</span><br />";
				if($r["mode"] == 1 || $r["mode"] == 5) { $rLevel = Game::getPlanet()->getBuilding($r["needs"]); }
				else if($r["mode"] == 2) { $rLevel = Game::getResearch($r["needs"]); }
				if($rLevel >= $r["level"])
				{
					$requirements .= "<span class=\"true\">".$buffer;
				}
				else
				{
					$requirements .= "<span class=\"false\">".$buffer;
				}
			}
			$name = Core::getLanguage()->getItem($row["name"]);
			switch($row["mode"])
			{
				case 1:
					$cons[$bid]["name"] = Link::get("game.php/".SID."/Constructions/Info/".$row["buildingid"], $name);
					$cons[$bid]["requirements"] = $requirements;
				break;
				case 2:
					$research[$bid]["name"] = Link::get("game.php/".SID."/Constructions/Info/".$row["buildingid"], $name);
					$research[$bid]["requirements"] = $requirements;
				break;
				case 3:
					$ships[$bid]["name"] = Link::get("game.php/".SID."/Unit/Info/".$row["buildingid"], $name);
					$ships[$bid]["requirements"] = $requirements;
				break;
				case 4:
					$def[$bid]["name"] = Link::get("game.php/".SID."/Unit/Info/".$row["buildingid"], $name);
					$def[$bid]["requirements"] = $requirements;
				break;
				case 5:
					$moon[$bid]["name"] = Link::get("game.php/".SID."/Constructions/Info/".$row["buildingid"], $name);
					$moon[$bid]["requirements"] = $requirements;
				break;
			}
		}
		Core::getDB()->free_result($result);
		Hook::event("ShowLoadedTechtree", array(&$cons, &$research, &$ships, &$def, &$moon, &$moon));
		Core::getTPL()->addLoop("construction", $cons);
		Core::getTPL()->addLoop("research", $research);
		Core::getTPL()->addLoop("shipyard", $ships);
		Core::getTPL()->addLoop("defense", $def);
		Core::getTPL()->addLoop("moon", $moon);
		return $this;
	}
}
?>