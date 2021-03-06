<?php
/**
 * Shipyard page. Shows ships and defense and allows to construct them.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Shipyard.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_Game_Controller_Shipyard extends Bengine_Game_Controller_Construction_Abstract
{
	/**
	 * @var integer
	 */
	const FLEET_CONSTRUCTION_TYPE = 3;

	/**
	 * @var integer
	 */
	const DEFENSE_CONSTRUCTION_TYPE = 4;

	/**
	 * Shipyard display mode.
	 * 3: Fleet, 4: Defense
	 *
	 * @var integer
	 */
	protected $mode = 0;

	/**
	 * If units can be build.
	 *
	 * @var boolean
	 */
	protected $canBuildUnits = false;

	/**
	 * If rockets can be build.
	 *
	 * @var boolean
	 */
	protected $canBuildRockets = true;

	/**
	 * Number of existing rockets
	 * (Even working).
	 *
	 * @var integer
	 */
	protected $existingRockets = 0;

	/**
	 * Constructor: Shows unit selection.
	 *
	 * @return Bengine_Game_Controller_Shipyard
	 */
	protected function init()
	{
		$this->canBuildUnits = Game::getEH()->canBuildUnits();

		switch(Core::getRequest()->getGET("controller"))
		{
			case "Shipyard": $this->mode = self::FLEET_CONSTRUCTION_TYPE; break;
			case "Defense": $this->mode = self::DEFENSE_CONSTRUCTION_TYPE; break;
		}

		if(Game::getPlanet()->getBuilding("ROCKET_STATION") > 0 && $this->mode == self::DEFENSE_CONSTRUCTION_TYPE)
		{
			$_result = Core::getQuery()->select("unit2shipyard", "quantity", "", Core::getDB()->quoteInto("unitid = '51' AND planetid = ?", Core::getUser()->get("curplanet")));
			$_row = $_result->fetchRow();
			$this->existingRockets = $_row["quantity"];
			$_result = Core::getQuery()->select("unit2shipyard", "quantity", "", Core::getDB()->quoteInto("unitid = '52' AND planetid = ?", Core::getUser()->get("curplanet")));
			$_row = $_result->fetchRow();
			$this->existingRockets += $_row["quantity"] * 2;
			$this->existingRockets += Game::getEH()->getWorkingRockets();

			$maxsize = Game::getRocketStationSize();

			// Check max. size
			if($this->existingRockets >= $maxsize)
			{
				$this->canBuildRockets = false;
			}
		}

		return parent::init();
	}

	/**
	 * Index action.
	 *
	 * @return Bengine_Game_Controller_Shipyard
	 */
	protected function indexAction()
	{
		Core::getLanguage()->load(array("info", "buildings"));
		$this->setTemplate("shipyard/index");

		/* @var Bengine_Game_Model_Collection_Construction $collection */
		$collection = Application::getCollection("game/construction", "game/unit");
		$collection->addTypeFilter($this->mode, Game::getPlanet()->getData("ismoon"))
			->addShipyardJoin(Core::getUser()->get("curplanet"))
			->addDisplayOrder();

		if(Game::getPlanet()->getBuilding("SHIPYARD") == 0)
		{
			Logger::dieMessage("SHIPYARD_REQUIRED");
		}
		if($this->mode == self::FLEET_CONSTRUCTION_TYPE)
			Core::getTPL()->assign("shipyard", Core::getLanguage()->getItem("SHIP_CONSTRUCTION"));
		else
			Core::getTPL()->assign("shipyard", Core::getLanguage()->getItem("DEFENSE"));

		if(!$this->canBuildUnits)
		{
			Logger::addMessage("SHIPYARD_UPGRADING", "info");
		}

		$missions = Game::getEH()->getShipyardEvents();
		if(count($missions))
		{
			Core::getTPL()->addHTMLHeaderFile("lib/jquery.countdown.js", "js");
			/* @var Bengine_Game_Model_Event $event */
			$event = $missions->getFirstItem();
			Core::getTPL()->assign("remainingTime", $event->getTimeLeft());
			Core::getTPL()->assign("currentWork", Core::getLanguage()->getItem($event->getData("mission")));
			Core::getTPL()->assign("currentMissionImage", BASE_URL."img/buildings/".$event->getData("mission").".gif");
			Core::getLang()->assign("unitName", Core::getLanguage()->getItem($event->getData("mission")));
		}

		Core::getTPL()->addLoop("events", $missions);
		Core::getTPL()->addLoop("units", $collection);
		Core::getTPL()->assign("canBuildUnits", $this->canBuildUnits);
		Core::getTPL()->assign("canBuildRockets", $this->canBuildRockets);
		$this->assign("orderAction", BASE_URL."game/".SID."/".$this->getParam("controller")."/Order");
		$this->assign("cancelAction", BASE_URL."game/".SID."/".$this->getParam("controller")."/Cancel");
		return $this;
	}

	/**
	 * Starts an event for building units.
	 *
	 * @throws Recipe_Exception_Generic
	 * @return Bengine_Game_Controller_Shipyard
	 */
	protected function orderAction()
	{
		if(Core::getUser()->get("umode") || Game::isDbLocked())
		{
			$this->redirect("game/".SID."/".Core::getRequest()->getGET("controller"));
		}
		if(!$this->canBuildUnits)
			throw new Recipe_Exception_Generic("Shipyard or nano factory in progress.");
		$post = Core::getRequest()->getPOST();
		/* @var Bengine_Game_Model_Collection_Construction $collection */
		$collection = Application::getCollection("game/construction", "game/unit");
		$collection->addTypeFilter($this->mode, Game::getPlanet()->getData("ismoon"))
			->addShipyardJoin(Core::getUser()->get("curplanet"));
		foreach($collection as $construction)
		{
			/* @var Bengine_Game_Model_Unit $construction */
			$id = $construction->getId();
			if(isset($post[$id]) && $post[$id] > 0)
			{
				// Check for requirements
				if(!Game::canBuild($id))
					throw new Recipe_Exception_Generic("You cannot build this.");

				$quantity = _pos($post[$id]);
				if($quantity > Core::getConfig()->get("SHIPYARD_MAX_UNITS_PER_ORDER"))
					$quantity = (int) Core::getConfig()->get("SHIPYARD_MAX_UNITS_PER_ORDER");

				Hook::event("UnitOrderStart", array($construction, &$quantity));

				if($id == 49 || $id == 50)
				{
					$where = Core::getDB()->quoteInto("unitid = ? AND planetid = ?", array(
						$id, Core::getUser()->get("curplanet")
					));
					$_result = Core::getQuery()->select("unit2shipyard", "quantity", "", $where);
					if($_result->rowCount() > 0)
					{
						throw new Recipe_Exception_Generic("You cannot build this.");
					}
					$_result->closeCursor();
					if(Game::getEH()->hasSmallShieldDome() && $id == 49)
					{
						throw new Recipe_Exception_Generic("You cannot build this.");
					}
					if(Game::getEH()->hasLargeShieldDome() && $id == 50)
					{
						throw new Recipe_Exception_Generic("You cannot build this.");
					}
					$quantity = 1; // Make sure that shields cannot be build twice
				}
				if($id == 51 || $id == 52)
				{
					$maxsize = Game::getRocketStationSize();

					// Check max. size
					if(!$this->canBuildRockets)
					{
						continue;
					}

					// Decrease quantity, if necessary
					if($id == 52) { $quantity *= 2; }
					if($quantity > $maxsize - $this->existingRockets)
					{
						$quantity = $maxsize - $this->existingRockets;
					}
					if($id == 52) { $quantity = floor($quantity / 2); }
				}

				// Check resources
				$quantity = $this->checkResourceForQuantity($quantity, $construction);

				// make event
				if($quantity > 0)
				{
					$latest = TIME;
					$existingEvents = Game::getEH()->getShipyardEvents();
					foreach($existingEvents as $existingEvent)
					{
						if($existingEvent->getData("finished") > $latest)
						{
							$latest = $existingEvent->getData("finished");
						}
					}

					$time = getBuildTime($construction->get("basic_metal"), $construction->get("basic_silicon"), $this->mode);

					$data["one"] = $time;
					$data["quantity"] = $quantity;
					$data["finished"] = $time * $quantity + $latest;
					$data["mission"] = $construction->get("name");
					$data["buildingid"] = $id;
					$data["metal"] = $construction->get("basic_metal");
					$data["silicon"] = $construction->get("basic_silicon");
					$data["hydrogen"] = $construction->get("basic_hydrogen");
					$data["points"] = ($construction->get("basic_metal") + $construction->get("basic_silicon") + $construction->get("basic_hydrogen")) / 1000;
					$mode = ($this->mode == self::FLEET_CONSTRUCTION_TYPE) ? 4 : 5;
					Hook::event("UnitOrderLast", array($construction, $quantity, $mode, &$time, &$latest, &$data));
					Game::getEH()->addEvent($mode, $time + $latest, Core::getUser()->get("curplanet"), Core::getUser()->get("userid"), null, $data);
				}
			}
		}
		$this->redirect("game/".SID."/".Core::getRequest()->getGET("controller"));
		return $this;
	}

	/**
	 * Checks the resource against the given quantity and reduce it if needed.
	 *
	 * @param integer $qty								Quantity
	 * @param Bengine_Game_Model_Unit $construction		Ship data row
	 *
	 * @return integer
	 */
	protected function checkResourceForQuantity($qty, Bengine_Game_Model_Unit $construction)
	{
		$basicMetal = $construction->get("basic_metal");
		$basicSilicon = $construction->get("basic_silicon");
		$basicHydrogen = $construction->get("basic_hydrogen");
		$basicEnergy = $construction->get("basic_energy");

		$metal = _pos(Game::getPlanet()->getData("metal"));
		$silicon = _pos(Game::getPlanet()->getData("silicon"));
		$hydrogen = _pos(Game::getPlanet()->getData("hydrogen"));
		$energy = _pos(Game::getPlanet()->getEnergy());

		$requiredMetal = $basicMetal * $qty;
		$requiredSilicon = $basicSilicon * $qty;
		$requiredHydrogen = $basicHydrogen * $qty;
		$requiredEnergy = $basicEnergy * $qty;
		if($requiredMetal > $metal || $requiredSilicon > $silicon || $requiredHydrogen > $hydrogen || $requiredEnergy > $energy)
		{
			// Try to reduce quantity
			$q = array();
			$q[] = ($basicMetal > 0) ? floor($metal / $basicMetal) : $qty;
			$q[] = ($basicSilicon > 0) ? floor($silicon / $basicSilicon) : $qty;
			$q[] = ($basicHydrogen > 0) ? floor($hydrogen / $basicHydrogen) : $qty;
			$q[] = ($basicEnergy > 0) ? floor($energy / $basicEnergy) : $qty;
			$qty = min($q);
		}

		return $qty;
	}

	/**
	 * Scrap merchant.
	 *
	 * @return Bengine_Game_Controller_Shipyard
	 */
	protected function merchantAction()
	{
		if(!Core::getConfig()->get("SCRAP_MERCHANT_RATE"))
		{
			$this->redirect("game/".SID."/Shipyard");
		}
		Core::getLanguage()->load(array("info", "buildings"));
		$this->setTemplate("shipyard/merchant");
		/* @var Bengine_Game_Model_Collection_Fleet $units */
		$units = Application::getCollection("game/fleet", "game/unit");
		$units->addPlanetFilter(Core::getUser()->get("curplanet"));
		$this->assign("units", $units);
		Core::getLang()->assign("merchantRate", Core::getConfig()->get("SCRAP_MERCHANT_RATE")*100);
		return $this;
	}

	/**
	 * Changes the ships against resources.
	 *
	 * @return Bengine_Game_Controller_Shipyard
	 */
	protected function changeAction()
	{
		if(!Core::getConfig()->get("SCRAP_MERCHANT_RATE"))
		{
			$this->redirect("game/".SID."/Shipyard");
		}
		Core::getLanguage()->load(array("info", "buildings"));
		$selUnits = Core::getRequest()->getPOST("unit");
		/* @var Bengine_Game_Model_Collection_Fleet $availUnits */
		$availUnits = Application::getCollection("game/fleet", "game/unit");
		$availUnits->addPlanetFilter(Core::getUser()->get("curplanet"));
		$metalCredit = 0;
		$siliconCredit = 0;
		$hydrogenCredit = 0;
		$totalQty = 0;
		$realUnits = array();
		$rate = (double) Core::getConfig()->get("SCRAP_MERCHANT_RATE");
		/* @var Bengine_Game_Model_Unit $unit */
		foreach($availUnits as $unit)
		{
			$unitId = $unit->getUnitid();
			if(isset($selUnits[$unitId]) && $selUnits[$unitId] > 0)
			{
				$qty = _pos((int) $selUnits[$unitId]);
				$qty = min($qty, $unit->getQty());
				$metalCredit += $qty*$unit->get("basic_metal");
				$siliconCredit += $qty*$unit->get("basic_silicon");
				$hydrogenCredit += $qty*$unit->get("basic_hydrogen");
				$totalQty += $qty;
				$realUnits[(int) $unitId] = $qty;
			}
		}
		$points = ($metalCredit + $siliconCredit + $hydrogenCredit) / 1000;
		$metalCredit = floor($metalCredit*$rate);
		$siliconCredit = floor($siliconCredit*$rate);
		$hydrogenCredit = floor($hydrogenCredit*$rate);
		Core::getLang()->assign(array(
			"metalCredit" => fNumber($metalCredit),
			"siliconCredit" => fNumber($siliconCredit),
			"hydrogenCredit" => fNumber($hydrogenCredit),
			"totalQty" => fNumber($totalQty),
		));
		if(Core::getRequest()->getPOST("verify") == "yes")
		{
			/* @var Bengine_Game_Model_Unit $unit */
			foreach($availUnits as $unit)
			{
				$unitId = (int) $unit->getUnitid();
				if(isset($realUnits[$unitId]))
				{
					$qty = $realUnits[$unitId];
					if($unit->getQty() <= $qty)
					{
						Core::getQuery()->delete("unit2shipyard", "`unitid` = ? AND `planetid` = ?", null, null, array($unitId, Core::getUser()->get("curplanet")));
					}
					else
					{
						$sql = "UPDATE `".PREFIX."unit2shipyard` SET `quantity` = `quantity` - ? WHERE `unitid` = ? AND `planetid` = ?";
						Core::getDatabase()->query($sql, array($qty, $unitId, Core::getUser()->get("curplanet")));
					}
				}
			}
			$sql = "UPDATE `".PREFIX."planet` SET `metal` = `metal` + ?, `silicon` = `silicon` + ?, `hydrogen` = `hydrogen` + ? WHERE `planetid` = ?";
			Core::getDatabase()->query($sql, array($metalCredit, $siliconCredit, $hydrogenCredit, Core::getUser()->get("curplanet")));
			$sql = "UPDATE `".PREFIX."user` SET `points` = `points` - ? WHERE `userid` = ?";
			Core::getDatabase()->query($sql, array($points, Core::getUser()->get("userid")));
			$this->redirect("game/".SID."/Shipyard");
		}
		$this->setTemplate("shipyard/change");
		$this->assign("units", $realUnits);
		return $this;
	}

	/**
	 * @return Bengine_Game_Controller_Shipyard
	 */
	protected function cancelAction()
	{
		$events = Core::getRequest()->getPOST("shipyard_events");
		foreach($events as $eventId)
		{
			$event = Game::getModel("game/event")->load((int)$eventId);
			if($event->get("userid") == Core::getUser()->get("userid"))
			{
				Game::getEH()->removeEvent($event);
			}
		}
		$this->redirect("game/".SID."/".Core::getRequest()->getGET("controller"));
		return $this;
	}
}
?>