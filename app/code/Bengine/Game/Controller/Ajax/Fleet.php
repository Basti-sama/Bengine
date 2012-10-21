<?php
/**
 * Sends fleet via AjaxRequest.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Fleet.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_Game_Controller_Ajax_Fleet extends Bengine_Game_Controller_Ajax_Abstract
{
	/**
	 * Main method of sending fleet via Ajax.
	 *
	 * @return Bengine_Game_Controller_Ajax_Fleet
	 */
	protected function init()
	{
		Core::getLanguage()->load("Galaxy");

		$events = Game::getEH()->getOwnFleetEvents();
		if($events !== false && Game::getResearch(14) + 1 <= count($events))
		{
			Core::getLanguage()->load("mission");
			$this->display($this->format(Core::getLanguage()->getItem("NO_FREE_FLEET_SLOTS")));
		}
		$this->setIsAjax();
		Hook::event("AjaxSendFleet", array($this));
		return parent::init();
	}

	/**
	 * Index action.
	 *
	 * @return Bengine_Game_Controller_Ajax_Fleet
	 */
	protected function indexAction()
	{
		$this->display("Unkown request.");
		return $this;
	}

	/**
	 * Sends espionage probes.
	 *
	 * @return Bengine_Game_Controller_Ajax_Fleet
	 */
	protected function espionageAction($target)
	{
		if(Core::getConfig()->get("ATTACKING_STOPPAGE"))
		{
			$this->display($this->format(Core::getLang()->get("TARGET_IN_UMODE")));
		}
		$select = array("p.planetid", "g.galaxy", "g.system", "g.position", "u2s.quantity", "sd.capicity", "sd.speed", "sd.consume", "b.name AS shipname");
		$joins  = "LEFT JOIN ".PREFIX."galaxy g ON (g.planetid = p.planetid)";
		$joins .= "LEFT JOIN ".PREFIX."unit2shipyard u2s ON (u2s.planetid = p.planetid)";
		$joins .= "LEFT JOIN ".PREFIX."ship_datasheet sd ON (sd.unitid = u2s.unitid)";
		$joins .= "LEFT JOIN ".PREFIX."construction b ON (b.buildingid = u2s.unitid)";
		$result = Core::getQuery()->select("planet p", $select, $joins, "p.planetid = '".Core::getUser()->get("curplanet")."' AND u2s.unitid = '38'");
		if($row = Core::getDB()->fetch($result))
		{
			Core::getDB()->free_result($result);
			$joins  = "LEFT JOIN ".PREFIX."planet p ON (g.planetid = p.planetid)";
			$joins .= "LEFT JOIN ".PREFIX."user u ON (u.userid = p.userid)";
			$joins .= "LEFT JOIN ".PREFIX."ban_u b ON (u.userid = b.userid)";
			$result = Core::getQuery()->select("galaxy g", array("g.galaxy", "g.system", "g.position", "u.points", "u.last", "u.umode", "b.to"), $joins, "g.planetid = '".$target."' OR g.moonid = '".$target."'");
			if($tar = Core::getDB()->fetch($result))
			{
				Core::getDB()->free_result($result);
				Hook::event("AjaxSendFleetEspionage", array($this, &$row, &$tar));
				$ignoreNP = false;
				if($tar["last"] <= TIME - 604800)
				{
					$ignoreNP = true;
				}
				else if($tar["to"] >= TIME)
				{
					$ignoreNP = true;
				}

				$data = array();
				// Check for newbie protection
				if($ignoreNP === false)
				{
					$isProtected = isNewbieProtected(Core::getUser()->get("points"), $tar["points"]);
					if($isProtected == 1)
					{
						$this->display($this->format(Core::getLanguage()->getItem("TARGET_TOO_WEAK")));
					}
					else if($isProtected == 2)
					{
						$this->display($this->format(Core::getLanguage()->getItem("TARGET_TOO_STRONG")));
					}
				}

				// Check for vacation mode
				if($tar["umode"] || Core::getUser()->get("umode"))
				{
					$this->display($this->format(Core::getLanguage()->getItem("TARGET_IN_UMODE")));
				}

				// Get quantity
				if($row["quantity"] >= Core::getUser()->get("esps"))
				{
					$data["ships"][38]["quantity"] = (Core::getUser()->get("esps") > 0) ? Core::getUser()->get("esps") : 1;
				}
				else
				{
					$data["ships"][38]["quantity"] = $row["quantity"];
				}
				$data["ships"][38]["id"] = 38;
				$data["ships"][38]["name"] = $row["shipname"];

				$data["maxspeed"] = Game::getSpeed(38, $row["speed"]);
				$distance = Game::getDistance($tar["galaxy"], $tar["system"], $tar["position"]);
				$time = Game::getFlyTime($distance, $data["maxspeed"]);

				$data["consumption"] = Game::getFlyConsumption($data["ships"][38]["quantity"] * $row["consume"], $distance);

				if(Game::getPlanet()->getData("hydrogen") < $data["consumption"])
				{
					$this->display($this->format(Core::getLanguage()->getItem("DEFICIENT_CONSUMPTION")));
				}
				if($row["capicity"] * $data["ships"][38]["quantity"] - $data["consumption"] < 0)
				{
					$this->display($this->format(Core::getLanguage()->getItem("DEFICIENT_CAPACITY")));
				}

				Game::getPlanet()->setData("hydrogen", Game::getPlanet()->getData("hydrogen") - $data["consumption"]);
				$data["time"] = $time;
				$data["galaxy"] = $tar["galaxy"];
				$data["system"] = $tar["system"];
				$data["position"] = $tar["position"];
				$data["sgalaxy"] = $row["galaxy"];
				$data["ssystem"] = $row["system"];
				$data["sposition"] = $row["position"];
				$data["metal"] = 0;
				$data["silicon"] = 0;
				$data["hydrogen"] = 0;
				Hook::event("AjaxSendFleetEspionageStartEvent", array($row, $tar, &$data));
				Game::getEH()->addEvent(11, $time + TIME, Core::getUser()->get("curplanet"), Core::getUser()->get("userid"), $target, $data);
				$this->display($this->format(sprintf(Core::getLanguage()->getItem("ESPS_SENT"), $data["ships"][38]["quantity"], $data["galaxy"], $data["system"], $data["position"]), 1));
			}
			else
			{
				Core::getDB()->free_result($result);
				$this->display($this->format("Unkown destination"));
			}
		}
		else
		{
			Core::getDB()->free_result($result);
			$this->display($this->format(Core::getLanguage()->getItem("DEFICIENT_ESPS")));
		}
		return $this;
	}

	/**
	 * Formats the output text.
	 *
	 * @param string Text to format.
	 * @param boolean Success or error format.
	 *
	 * @return string Formatted text.
	 */
	protected function format($text, $success = 0)
	{
		$class = ($success == 1) ? "success" : "false";
		return "<span class=\"".$class."\" style=\"font-weight: bold;\">".$text."</span>";
	}
}
?>