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
			$_result = Core::getQuery()->select("unit2shipyard", "quantity", "", "unitid = '51' AND planetid = '".Core::getUser()->get("curplanet")."'");
			$_row = Core::getDB()->fetch($_result);
			$this->existingRockets = $_row["quantity"];
			$_result = Core::getQuery()->select("unit2shipyard", "quantity", "", "unitid = '52' AND planetid = '".Core::getUser()->get("curplanet")."'");
			$_row = Core::getDB()->fetch($_result);
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
		$collection = Application::getCollection("construction", "unit");
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

		// Output shipyard missions
		Core::getQuery()->delete("shipyard", "finished <= '".TIME."'");
		$missions = array();
		$result = Core::getQuery()->select("shipyard s", array("s.quantity", "s.one", "s.time", "s.finished", "b.name"), "LEFT JOIN ".PREFIX."construction b ON (b.buildingid = s.unitid)", "s.planetid = '".Core::getUser()->get("curplanet")."' ORDER BY s.time ASC");
		if($row = Core::getDB()->fetch($result))
		{
			Core::getTPL()->assign("hasEvent", true);
			Core::getTPL()->assign("currentWork", Core::getLanguage()->getItem($row["name"]));
			$x = TIME - $row["time"];
			$lefttime = $row["one"] - ($x - floor($x / $row["one"]) * $row["one"]);
			Core::getTPL()->assign("remainingTime", getTimeTerm($lefttime));

			Core::getDB()->reset_resource($result);
			while($row = Core::getDB()->fetch($result))
			{
				$quantity = ($row["time"] < TIME) ? ceil(($row["finished"] - TIME) / $row["one"]) : $row["quantity"];
				$missions[] = array(
					"quantity" => $quantity,
					"mission" => Core::getLanguage()->getItem($row["name"])
				);
			}
			Core::getDB()->free_result($result);
		}
		else
		{
			Core::getDB()->free_result($result);
			Core::getTPL()->assign("hasEvent", false);
		}

		Core::getTPL()->addLoop("events", $missions);
		Core::getTPL()->addLoop("units", $collection);
		Core::getTPL()->assign("canBuildUnits", $this->canBuildUnits);
		Core::getTPL()->assign("canBuildRockets", $this->canBuildRockets);
		$this->assign("orderAction", BASE_URL."game.php/".SID."/".$this->getParam("controller")."/Order");
		return $this;
	}

	/**
	 * Starts an event for building units.
	 *
	 * @return Bengine_Game_Controller_Shipyard
	 */
	protected function orderAction()
	{
		if(Core::getUser()->get("umode") || Game::isDbLocked())
		{
			$this->redirect("game.php/".SID."/".Core::getRequest()->getGET("controller"));
		}
		if(!$this->canBuildUnits)
			throw new Recipe_Exception_Generic("Shipyard or nano factory in progress.");
		$post = Core::getRequest()->getPOST();
		/* @var Bengine_Game_Model_Collection_Construction $collection */
		$collection = Application::getCollection("construction", "unit");
		$collection->addTypeFilter($this->mode, Game::getPlanet()->getData("ismoon"))
			->addShipyardJoin(Core::getUser()->get("curplanet"));
		foreach($collection as $construction)
		{
			/* @var Bengine_Game_Model_Unit $collection */
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
					$_result = Core::getQuery()->select("unit2shipyard", "quantity", "", "unitid = '".$id."' AND planetid = '".Core::getUser()->get("curplanet")."'");
					if(Core::getDB()->num_rows($_result) > 0)
					{
						throw new Recipe_Exception_Generic("You cannot build this.");
					}
					Core::getDB()->free_result($_result);
					if(Game::getEH()->hasSShieldDome() && $id == 49)
					{
						throw new Recipe_Exception_Generic("You cannot build this.");
					}
					if(Game::getEH()->hasLShieldDome() && $id == 50)
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
					$_result = Core::getQuery()->select("events", array("MAX(time) as latest"), "", "planetid = '".Core::getUser()->get("curplanet")."' AND (mode = '4' OR mode = '5') LIMIT 1");
					$_row = Core::getDB()->fetch($_result);
					Core::getDB()->free_result($_result);
					if($_row["latest"] >= TIME)
					{
						$latest = $_row["latest"];
					}
					unset($_result); unset($_row);

					$time = getBuildTime($construction->get("basic_metal"), $construction->get("basic_silicon"), $this->mode);

					// This is just for the output below the shipyard, it does not represent the actual event!
					Core::getQuery()->insert("shipyard", array("planetid", "unitid", "quantity", "one", "time", "finished"), array(Core::getUser()->get("curplanet"), $id, $quantity, $time, $latest, $time * $quantity + $latest));

					$data["time"] = $time;
					$data["quantity"] = $quantity;
					$data["mission"] = $construction->get("name");
					$data["buildingid"] = $id;
					$data["metal"] = $construction->get("basic_metal");
					$data["silicon"] = $construction->get("basic_silicon");
					$data["hydrogen"] = $construction->get("basic_hydrogen");
					$data["points"] = ($construction->get("basic_metal") + $construction->get("basic_silicon") + $construction->get("basic_hydrogen")) / 1000;
					$data["dont_save_resources"] = true; // We save the subtracted resources manually to avoid unnecessary SQL queries.
					if($this->mode == self::FLEET_CONSTRUCTION_TYPE)
						$mode = 4;
					else
						$mode = 5;
					Hook::event("UnitOrderLast", array($construction, $quantity, $mode, &$time, &$latest, &$data));
					for($i = 1; $i <= $quantity; $i++)
					{
						Game::getEH()->addEvent($mode, $time * $i + $latest, Core::getUser()->get("curplanet"), Core::getUser()->get("userid"), null, $data);
					}
					$planet = Game::getPlanet();
					Core::getQuery()->updateSet("planet", array("metal" => $planet->getData("metal"), "silicon" => $planet->getData("silicon"), "hydrogen" => $planet->getData("hydrogen")), "planetid = '".Core::getUser()->get("curplanet")."'");
				}
			}
		}
		$this->redirect("game.php/".SID."/".Core::getRequest()->getGET("controller"));
		return $this;
	}

	/**
	 * Checks the resource against the given quantity and reduce it if needed.
	 *
	 * @param integer $qty							Quantity
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
			$this->redirect("game.php/".SID."/Shipyard");
		}
		Core::getLanguage()->load(array("info", "buildings"));
		$this->setTemplate("shipyard/merchant");
		$units = Application::getCollection("fleet", "unit");
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
			$this->redirect("game.php/".SID."/Shipyard");
		}
		Core::getLanguage()->load(array("info", "buildings"));
		$selUnits = Core::getRequest()->getPOST("unit");
		$availUnits = Application::getCollection("fleet", "unit");
		$availUnits->addPlanetFilter(Core::getUser()->get("curplanet"));
		$metalCredit = 0;
		$siliconCredit = 0;
		$hydrogenCredit = 0;
		$totalQty = 0;
		$realUnits = array();
		$rate = (double) Core::getConfig()->get("SCRAP_MERCHANT_RATE");
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
			foreach($availUnits as $unit)
			{
				$unitId = (int) $unit->getUnitid();
				if(isset($realUnits[$unitId]))
				{
					$qty = $realUnits[$unitId];
					if($unit->getQty() <= $qty)
					{
						Core::getQuery()->delete("unit2shipyard", "`unitid` = '{$unitId}' AND `planetid` = '".Core::getUser()->get("curplanet")."'");
					}
					else
					{
						$sql = "UPDATE `".PREFIX."unit2shipyard` SET `quantity` = `quantity` - '{$qty}' WHERE `unitid` = '{$unitId}' AND `planetid` = '".Core::getUser()->get("curplanet")."'";
						Core::getDatabase()->query($sql);
					}
				}
			}
			$sql = "UPDATE `".PREFIX."planet` SET `metal` = `metal` + '{$metalCredit}', `silicon` = `silicon` + '{$siliconCredit}', `hydrogen` = `hydrogen` + '{$hydrogenCredit}' WHERE `planetid` = '".Core::getUser()->get("curplanet")."'";
			Core::getDatabase()->query($sql);
			$sql = "UPDATE `".PREFIX."user` SET `points` = `points` - '{$points}' WHERE `userid` = '".Core::getUser()->get("userid")."'";
			Core::getDatabase()->query($sql);
			$this->redirect("game.php/".SID."/Shipyard");
		}
		$this->setTemplate("shipyard/change");
		$this->assign("units", $realUnits);
		return $this;
	}
}
?>