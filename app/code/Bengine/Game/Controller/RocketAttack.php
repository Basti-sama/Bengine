<?php
/**
 * Starting rocket attacks.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: RocketAttack.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_Game_Controller_RocketAttack extends Bengine_Game_Controller_Abstract
{
	/**
	 * Target id.
	 *
	 * @var integer
	 */
	protected $target = 0;

	/**
	 * The target data.
	 *
	 * @var array
	 */
	protected $t = array();

	/**
	 * Number of available rockets.
	 *
	 * @var integer
	 */
	protected $rockets = 0;

	/**
	 * Constructor: Shows form to start rocket attack.
	 *
	 * @return void
	 */
	protected function init()
	{
		$fleetEvents = Game::getEH()->getOwnFleetEvents();
		if(!$fleetEvents)
		{
			$fleetEvents = array();
		}
		if(Game::getResearch(14) + 1 <= count($fleetEvents))
		{
			Core::getLanguage()->load("mission");
			Logger::dieMessage("NO_FREE_FLEET_SLOTS");
		}
		if(Game::attackingStoppageEnabled())
		{
			Core::getLanguage()->load("mission");
			Logger::dieMessage("ATTACKING_STOPPAGE_ENABLED");
		}

		$this->target = Core::getRequest()->getGET("1");
		Core::getLanguage()->load("Galaxy,info");
		$select = new Recipe_Database_Select();
		$select->from(array("p" => "planet"))
			->attributes(array(
			"p" => array("planetname"),
			"g" => array("galaxy", "system", "position"),
			"u" => array("points", "last", "umode"),
			"b" => array("to")
		))
		->join(array("u" => "user"), array("u" => "userid", "p" => "userid"))
		->join(array("b" => "ban_u"), array("u" => "userid", "b" => "userid"))
		->where(array("p" => "planetid"), $this->target);
		if(Core::getRequest()->getGET("2"))
		{
			$select->join(array("g" => "galaxy"), array("g" => "moonid", "p" => "planetid"));
		}
		else
		{
			$select->join(array("g" => "galaxy"), array("g" => "planetid", "p" => "planetid"));
		}
		$result = $select->getStatement();
		if($this->t = $result->fetchRow())
		{
			$result->closeCursor();
			$ignoreNP = false;
			if($this->t["last"] <= TIME - 604800)
			{
				$ignoreNP = true;
			}
			else if($this->t["to"] >= TIME)
			{
				$ignoreNP = true;
			}

			Hook::event("RocketAttackFormLoad", array($this, &$ignoreNP));

			// Check for newbie protection
			if($ignoreNP === false)
			{
				$isProtected = isNewbieProtected(Core::getUser()->get("points"), $this->t["points"]);
				if($isProtected == 1)
				{
					Logger::dieMessage("TARGET_TOO_WEAK");
				}
				else if($isProtected == 2)
				{
					Logger::dieMessage("TARGET_TOO_STRONG");
				}
			}

			// Check for vacation mode
			if($this->t["umode"] || Core::getUser()->get("umode"))
			{
				Logger::dieMessage("TARGET_IN_UMODE");
			}

			$result = Core::getQuery()->select("unit2shipyard", "quantity", "", Core::getDB()->quoteInto("unitid = '52' AND planetid = ?", Core::getUser()->get("curplanet")));
			$qty = $result->fetchColumn();
			$result->closeCursor();
			$this->rockets = ($qty) ? $qty : 0;
		}
		return;
	}

	/**
	 * Index action.
	 *
	 * @return Bengine_Game_Controller_RocketAttack
	 */
	protected function indexAction()
	{
		if($this->t)
		{
			if($this->isPost() && $this->getParam("start"))
			{
				$this->sendRockets($this->getParam("quantity"), $this->getParam("target"));
			}
			$d = array();
			$result = Core::getQuery()->select("unit2shipyard u2s", array("u2s.unitid", "b.name"), "LEFT JOIN ".PREFIX."construction b ON (b.buildingid = u2s.unitid)", Core::getDB()->quoteInto("b.mode = '4' AND u2s.planetid = ?", $this->target));
			foreach($result->fetchAll() as $dest)
			{
				if($dest["unitid"] == 51 || $dest["unitid"] == 52) { continue; }
				$d[] = array(
					"name" => Core::getLanguage()->getItem($dest["name"]),
					"unitid" => $dest["unitid"]
				);
			}
			$result->closeCursor();
			Hook::event("RocketAttackShowForm", array($this, &$d));
			Core::getTPL()->assign("target", $this->t["planetname"]." ".getCoordLink($this->t["galaxy"], $this->t["system"], $this->t["position"]));
			Core::getTPL()->assign("rockets", $this->rockets);
			$diff = abs($this->t["system"] - Game::getPlanet()->getData("system"));
			Core::getTPL()->assign("flightDuration", getTimeTerm(getRocketFlightDuration($diff)));
			Core::getTPL()->addLoop("destionations", $d);
		}
		return $this;
	}

	/**
	 * Starts the actual rocket attack event.
	 *
	 * @param integer $quantity
	 * @param integer $primaryTarget
	 *
	 * @return Bengine_Game_Controller_RocketAttack
	 */
	protected function sendRockets($quantity, $primaryTarget)
	{
		$quantity = _pos($quantity);
		$diff = abs($this->t["system"] - Game::getPlanet()->getData("system"));

		if($this->rockets <= 0 || !$quantity)
		{
			Logger::addMessage("NO_MISSLES_TO_SEND", "info");
		}
		// Check max. range
		else if($this->t["galaxy"] == Game::getPlanet()->getData("galaxy") && Game::getRocketRange() >= $diff)
		{
			if($quantity <= $this->rockets)
			{
				$this->rockets = $quantity;
			}

			// Load attacking value for interplanetary rocket
			$result = Core::getQuery()->select("ship_datasheet sds", array("sds.attack", "basic_metal", "basic_silicon", "basic_hydrogen"), "LEFT JOIN ".PREFIX."construction b ON (b.buildingid = sds.unitid)", "sds.unitid = '52'");
			$data = $result->fetchRow();
			$result->closeCursor();

			$data["rockets"] = $this->rockets;
			$data["ships"][52]["id"] = 52;
			$data["ships"][52]["quantity"] = $this->rockets;
			$data["ships"][52]["name"] = "INTERPLANETARY_ROCKET";
			$data["sgalaxy"] = Game::getPlanet()->getData("galaxy");
			$data["ssystem"] = Game::getPlanet()->getData("system");
			$data["sposition"] = Game::getPlanet()->getData("position");
			$data["galaxy"] = $this->t["galaxy"];
			$data["system"] = $this->t["system"];
			$data["position"] = $this->t["position"];
			$data["planetname"] = $this->t["planetname"];
			$data["primary_target"] = $primaryTarget;
			Game::getEH()->addEvent(16, TIME + getRocketFlightDuration($diff), Core::getUser()->get("curplanet"), Core::getUser()->get("userid"), $this->target, $data);
			$this->redirect("game/".SID."/Index");
		}
		return $this;
	}
}
?>