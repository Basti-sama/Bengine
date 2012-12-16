<?php
/**
 * Construction & builings page.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Constructions.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_Game_Controller_Constructions extends Bengine_Game_Controller_Construction_Abstract
{
	/**
	 * Building construction type.
	 *
	 * @var integer
	 */
	const BUILDING_CONSTRUCTION_TYPE = 1;

	/**
	 * Moon construction type.
	 *
	 * @var integer
	 */
	const MOON_CONSTRUCTION_TYPE = 5;

	/**
	 * Building event of the current planet.
	 *
	 * @var mixed
	 */
	protected $event = false;

	/**
	 * Displays list of available buildings.
	 *
	 * @return Bengine_Game_Controller_Constructions
	 */
	protected function init()
	{
		// Get construction
		$this->event = Game::getEH()->getCurPlanetBuildingEvent();
		return parent::init();
	}

	/**
	 * Index action.
	 *
	 * @return Bengine_Game_Controller_Constructions
	 */
	protected function indexAction()
	{
		Core::getLanguage()->load(array("info", "buildings"));
		$mode = self::BUILDING_CONSTRUCTION_TYPE;
		$moonType = Game::getPlanet()->getData("ismoon") ? self::MOON_CONSTRUCTION_TYPE : null;
		if(!Game::getPlanet()->planetFree())
			Logger::addMessage("PLANET_FULL", "info");
		$shipyardSize = Game::getEH()->getShipyardEvents()->getCalculatedSize();
		Core::getTPL()->assign("shipyardSize", $shipyardSize);
		Core::getLang()->assign("maxFields", fNumber(Game::getPlanet()->getMaxFields()));
		Core::getLang()->assign("occupiedFields", Game::getPlanet()->getFields(true));

		/* @var Bengine_Game_Model_Collection_Construction $collection */
		$collection = Application::getCollection("game/construction");
		$collection->addTypeFilter($mode, $moonType ? true : false, $moonType)
			->addPlanetJoin(Core::getUser()->get("curplanet"))
			->addDisplayOrder();

		Core::getTPL()->addHTMLHeaderFile("lib/jquery.countdown.js", "js");
		Hook::event("ConstructionsLoaded", array($collection));
		Core::getTPL()->addLoop("constructions", $collection);
		Core::getTPL()->assign("event", $this->event);
		return $this;
	}

	/**
	 * Check for sufficient resources and start to upgrade building.
	 *
	 * @param integer $id    Building id to upgrade
	 * @throws Recipe_Exception_Generic
	 * @return Bengine_Game_Controller_Constructions
	 */
	protected function upgradeAction($id)
	{
		// Check events
		if($this->event != false || Core::getUser()->get("umode"))
			$this->redirect("game/".SID."/Constructions");
		if($id == 12 && Game::getEH()->getResearchEvent())
			throw new Recipe_Exception_Generic("Do not mess with the url.");
		$shipyardSize = Game::getEH()->getShipyardEvents()->getCalculatedSize();
		if(($id == 8 || $id == 7) && $shipyardSize>0)
			throw new Recipe_Exception_Generic("Do not mess with the url.");

		// Check fields
		if(!Game::getPlanet()->planetFree())
		{
			Logger::dieMessage("PLANET_FULLY_DEVELOPED");
		}

		// Check for requirements
		if(!Game::canBuild($id))
		{
			throw new Recipe_Exception_Generic("You do not fulfil the requirements to build this.");
		}

		// Load building data
		Core::getLanguage()->load(array("info" , "buildings"));
		$isMoon = Game::getPlanet()->getData("ismoon");

		/* @var Bengine_Game_Model_Construction $construction */
		$construction = Game::getModel("game/construction");
		$construction->load($id);
		if(!$construction->getId())
		{
			throw new Recipe_Exception_Generic("Unkown building :(");
		}
		$mode = $construction->get("mode");
		if($isMoon && $mode != self::MOON_CONSTRUCTION_TYPE)
		{
			if($mode == self::BUILDING_CONSTRUCTION_TYPE && !$construction->get("allow_on_moon"))
			{
				throw new Recipe_Exception_Generic("Building not allowed.");
			}
		}
		if(!$isMoon && $mode != self::BUILDING_CONSTRUCTION_TYPE)
		{
			throw new Recipe_Exception_Generic("Building not allowed.");
		}

		Hook::event("UpgradeBuildingFirst", array($construction));

		// Get required resources
		$level = Game::getPlanet()->getBuilding($id);
		if($level > 0) { $level = $level + 1; } else { $level = 1; }
		$this->setRequieredResources($level, $construction);

		// Check resources
		if($this->checkResources())
		{
			$data["metal"] = $this->requiredMetal;
			$data["silicon"] = $this->requiredSilicon;
			$data["hydrogen"] = $this->requiredHydrogen;
			$data["energy"] = $this->requiredEnergy;
			$time = getBuildTime($data["metal"], $data["silicon"], self::BUILDING_CONSTRUCTION_TYPE);
			$data["level"] = $level;
			$data["buildingid"] = $id;
			$data["buildingname"] = $construction->get("name");
			Hook::event("UpgradeBuildingLast", array($construction, &$data, &$time));
			Game::getEH()->addEvent(1, $time + TIME, Core::getUser()->get("curplanet"), Core::getUser()->get("userid"), null, $data);
			$this->redirect("game/".SID."/Constructions");
		}
		else
		{
			Logger::dieMessage("INSUFFICIENT_RESOURCES");
		}
		return $this;
	}

	/**
	 * Aborts the current building event.
	 *
	 * @param integer $id	Building id
	 *
	 * @return Bengine_Game_Controller_Constructions
	 */
	protected function abortAction($id)
	{
		if(!$this->event || !$id || Core::getUser()->get("umode"))
		{
			$this->redirect("game/".SID."/Constructions");
		}
		$result = Core::getQuery()->select("construction", array("buildingid"), "", Core::getDB()->quoteInto("buildingid = ?", $id));
		if($row = $result->fetchRow())
		{
			$result->closeCursor();
			Hook::event("AbortBuilding", array($this));
			Game::getEH()->removeEvent($this->event->get("eventid"));
			$this->redirect("game/".SID."/Constructions");
		}
		$result->closeCursor();
		$this->setNoDisplay();
		return $this;
	}

	/**
	 * Demolish a building ...
	 *
	 * @param integer $id
	 * @throws Recipe_Exception_Generic
	 * @return Bengine_Game_Controller_Constructions
	 */
	protected function demolishAction($id)
	{
		if($this->event != false || Core::getUser()->get("umode"))
		{
			Recipe_Header::redirect("game/".SID."/Constructions", false);
		}
		$result = Core::getQuery()->select("building2planet b2p", array("c.basic_metal", "c.basic_silicon", "c.basic_hydrogen", "c.charge_metal", "c.charge_silicon", "c.charge_hydrogen", "c.name", "c.demolish"), "LEFT JOIN ".PREFIX."construction c ON (c.buildingid = b2p.buildingid)", Core::getDB()->quoteInto("b2p.buildingid = ?", $id));
		if(!($row = $result->fetchRow()))
		{
			$result->closeCursor();
			throw new Recipe_Exception_Generic("Unkown building :(");
		}
		$result->closeCursor();
		$level = Game::getPlanet()->getBuilding($id);
		if($level < 1) { throw new Recipe_Exception_Generic("Wut?"); }
		Hook::event("DemolishBuldingFirst", array(&$row, $level));

		$data["metal"] = 0;
		$data["silicon"] = 0;
		$data["hydrogen"] = 0;

		$data["level"] = $level - 1;
		if($row["basic_metal"] > 0) { $data["metal"] = parseFormula($row["charge_metal"], $row["basic_metal"], $level); }
		if($row["basic_silicon"] > 0) { $data["silicon"] = parseFormula($row["charge_silicon"], $row["basic_silicon"], $level); }
		if($row["basic_hydrogen"] > 0) { $data["hydrogen"] = parseFormula($row["charge_hydrogen"], $row["basic_hydrogen"], $level); }
		$factor = floatval($row["demolish"]);
		if($factor <= 0.0)
		{
			throw new Recipe_Exception_Generic("The building cannot be demolished.");
		}
		$data["metal"] = (1 / $factor) * $data["metal"];
		$data["silicon"] = (1 / $factor) * $data["silicon"];
		$data["hydrogen"] = (1 / $factor) * $data["hydrogen"];
		if($data["metal"] <= Game::getPlanet()->getData("metal") && $data["silicon"] <= Game::getPlanet()->getData("silicon") && $data["hydrogen"] <= Game::getPlanet()->getData("hydrogen"))
		{
			$data["buildingname"] = $row["name"];
			$data["buildingid"] = $id;
			$time = getBuildTime($data["metal"], $data["silicon"], self::BUILDING_CONSTRUCTION_TYPE);
			Hook::event("DemolishBuldingLast", array(&$data, &$time));
			Game::getEH()->addEvent(2, $time + TIME, Core::getUser()->get("curplanet"), Core::getUser()->get("userid"), null, $data);
			$this->redirect("game/".SID."/Constructions");
		}
		else
		{
			throw new Recipe_Exception_Generic("Not enough resources to build this.");
		}
		return $this;
	}

	/**
	 * Returns the planet building mode.
	 *
	 * @return integer
	 */
	protected function getMode()
	{
		return (Game::getPlanet()->getData("ismoon")) ? 5 : 1;
	}

	/**
	 * Shows all building information.
	 *
	 * @param integer $id
	 * @throws Recipe_Exception_Generic
	 * @return Bengine_Game_Controller_Constructions
	 */
	protected function infoAction($id)
	{
		$select = array(
			"name", "demolish",
			"basic_metal", "basic_silicon", "basic_hydrogen", "basic_energy",
			"prod_metal", "prod_silicon", "prod_hydrogen", "prod_energy", "special",
			"cons_metal", "cons_silicon", "cons_hydrogen", "cons_energy",
			"charge_metal", "charge_silicon", "charge_hydrogen", "charge_energy"
		);
		$result = Core::getQuery()->select("construction", $select, "", Core::getDB()->quoteInto("buildingid = ? AND (mode = '1' OR mode = '2' OR mode = '5')", $id));
		if($row = $result->fetchRow())
		{
			$result->closeCursor();
			Core::getLanguage()->load("info,Resource");
			Hook::event("BuildingInfoBefore", array(&$row));

			// Assign general building data
			Core::getTPL()->assign("buildingName", Core::getLanguage()->getItem($row["name"]));
			Core::getTPL()->assign("buildingDesc", Core::getLanguage()->getItem($row["name"]."_FULL_DESC"));
			Core::getTPL()->assign("buildingImage", Image::getImage("buildings/".$row["name"].".gif", Core::getLanguage()->getItem($row["name"]), null, null, "leftImage"));
			Core::getTPL()->assign("edit", Link::get("game/".SID."/Construction_Edit/Index/".$id, "[".Core::getLanguage()->getItem("EDIT")."]"));

			// Production and consumption of the building
			$prodFormula = false;
			if(!empty($row["prod_metal"]))
			{
				$prodFormula = $row["prod_metal"];
				$baseCost = $row["basic_metal"];
			}
			else if(!empty($row["prod_silicon"]))
			{
				$prodFormula = $row["prod_silicon"];
				$baseCost = $row["basic_metal"];
			}
			else if(!empty($row["prod_hydrogen"]))
			{
				$prodFormula = $row["prod_hydrogen"];
				$baseCost = $row["basic_hydrogen"];
			}
			else if(!empty($row["prod_energy"]))
			{
				$prodFormula = $row["prod_energy"];
				$baseCost = $row["basic_energy"];
			}
			else if(!empty($row["special"]))
			{
				$prodFormula = $row["special"];
				$baseCost = 0;
			}
			$consFormula = false;
			if(!empty($row["cons_metal"]))
			{
				$consFormula = $row["cons_metal"];
			}
			else if(!empty($row["cons_silicon"]))
			{
				$consFormula = $row["cons_silicon"];
			}
			else if(!empty($row["cons_hydrogen"]))
			{
				$consFormula = $row["cons_hydrogen"];
			}
			else if(!empty($row["cons_energy"]))
			{
				$consFormula = $row["cons_energy"];
			}

			// Production and consumption chart
			$chartType = false;
			if($prodFormula != false || $consFormula != false)
			{
				$chart = array();
				$chartType = "cons_chart";
				if($prodFormula && $consFormula)
				{
					$chartType = "prod_and_cons_chart";
				}
				else if($prodFormula)
				{
					$chartType = "prod_chart";
				}

				if(Game::getPlanet()->getBuilding($id) - 7  < 0)
				{
					$start = 7;
				}
				else { $start = Game::getPlanet()->getBuilding($id); }

				$productionFactor = (double) Core::getConfig()->get("PRODUCTION_FACTOR");
				if(!empty($row["prod_energy"]))
				{
					$productionFactor = 1;
				}
				$currentProduction = 0;
				if($prodFormula)
				{
					$currentProduction = parseFormula($prodFormula, $baseCost, Game::getPlanet()->getBuilding($id)) * $productionFactor;
				}
				$currentConsumption = 0;
				if($consFormula)
				{
					$currentConsumption = parseFormula($consFormula, 0, Game::getPlanet()->getBuilding($id));
				}
				for($i = $start - 7; $i <= Game::getPlanet()->getBuilding($id) + 7; $i++)
				{
					$chart[$i]["level"] = $i;
					$chart[$i]["s_prod"] = ($prodFormula) ? parseFormula($prodFormula, $baseCost, $i) * $productionFactor : 0;
					$chart[$i]["s_diffProd"] = ($prodFormula) ? $chart[$i]["s_prod"] - $currentProduction : 0;
					$chart[$i]["s_cons"] = ($consFormula) ? parseFormula($consFormula, 0, $i) : 0;
					$chart[$i]["s_diffCons"] = ($consFormula) ? $currentConsumption - $chart[$i]["s_cons"] : 0;

					$chart[$i]["prod"] = fNumber($chart[$i]["s_prod"]);
					$chart[$i]["diffProd"] = fNumber($chart[$i]["s_diffProd"]);
					$chart[$i]["cons"] = fNumber($chart[$i]["s_cons"]);
					$chart[$i]["diffCons"] = fNumber($chart[$i]["s_diffCons"]);
				}
				Hook::event("BuildingInfoProduction", array(&$chart));
				Core::getTPL()->addLoop("chart", $chart);
			}
			if($chartType)
			{
				Core::getTPL()->assign("chartType", "game/constructions/".$chartType);
			}

			// Show demolish function
			$factor = floatval($row["demolish"]);
			if(Game::getPlanet()->getBuilding($id) > 0 && $factor > 0.0)
			{
				Core::getTPL()->assign("buildingLevel", Game::getPlanet()->getBuilding($id));
				Core::getTPL()->assign("demolish", true);

				$metal = ""; $_metal = 0;
				$silicon = ""; $_silicon = 0;
				$hydrogen = ""; $_hydrogen = 0;

				if($row["basic_metal"] > 0)
				{
					$_metal = (1 / $factor) * parseFormula($row["charge_metal"], $row["basic_metal"], Game::getPlanet()->getBuilding($id));
					$metal = Core::getLanguage()->getItem("METAL").": ".fNumber($_metal);
				}
				Core::getTPL()->assign("metal", $metal);

				if($row["basic_silicon"] > 0)
				{
					$_silicon = (1 / $factor) * parseFormula($row["charge_silicon"], $row["basic_silicon"], Game::getPlanet()->getBuilding($id));
					$silicon = Core::getLanguage()->getItem("SILICON").": ".fNumber($_silicon);
				}
				Core::getTPL()->assign("silicon", $silicon);

				if($row["basic_hydrogen"] > 0)
				{
					$_hydrogen = (1 / $factor) * parseFormula($row["charge_hydrogen"], $row["basic_hydrogen"], Game::getPlanet()->getBuilding($id));
					$hydrogen = Core::getLanguage()->getItem("HYDROGEN").": ".fNumber($_hydrogen);
				}
				Core::getTPL()->assign("hydrogen", $hydrogen);

				$time = getBuildTime($_metal, $_silicon, self::BUILDING_CONSTRUCTION_TYPE);
				Core::getTPL()->assign("dimolishTime", getTimeTerm($time));

				$showLink = (Game::getPlanet()->getData("metal") >= $_metal && Game::getPlanet()->getData("silicon") >= $_silicon && Game::getPlanet()->getData("hydrogen") >= $_hydrogen);
				Core::getTPL()->assign("showLink", $showLink && !$this->event);

				Core::getTPL()->assign("demolishNow", Link::get("game/".SID."/Constructions/Demolish/{$id}", Core::getLanguage()->getItem("DEMOLISH_NOW")));
			}
			else
			{
				Core::getTPL()->assign("demolish", false);
			}
			Hook::event("BuildingInfoAfter", array(&$row));
		}
		else
		{
			$result->closeCursor();
			throw new Recipe_Exception_Generic("Unkown building. You'd better don't manipulate the URL. We see everything ;)");
		}
		return $this;
	}
}
?>