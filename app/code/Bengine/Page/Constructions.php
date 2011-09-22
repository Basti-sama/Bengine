<?php
/**
 * Construction & builings page.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Constructions.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_Page_Constructions extends Bengine_Page_Construction_Abstract
{
	/**
	 * Building construction type.
	 *
	 * @var integer
	 */
	const BUILDING_CONSTRUCTION_TYPE = 1;

	/**
	 * Building event of the current planet.
	 *
	 * @var mixed
	 */
	protected $event = false;

	/**
	 * Displays list of available buildings.
	 *
	 * @return Bengine_Page_Constructions
	 */
	protected function init()
	{
		// Get construction
		$this->event = Bengine::getEH()->getCurPlanetBuildingEvent();
		return parent::init();
	}

	/**
	 * Index action.
	 *
	 * @return Bengine_Page_Constructions
	 */
	protected function indexAction()
	{
		Core::getLanguage()->load(array("info", "buildings"));
		$mode = (Bengine::getPlanet()->getData("ismoon")) ? 5 : 1;
		if(!Bengine::getPlanet()->planetFree())
			Logger::addMessage("PLANET_FULL", "info");
		$shipyardSize = Bengine::getEH()->getShipyardEvents()->getCalculatedSize();
		Core::getTPL()->assign("shipyardSize", $shipyardSize);

		$collection = Application::getCollection("construction");
		$collection->addTypeFilter($mode)
			->addPlanetJoin(Core::getUser()->get("curplanet"));

		Core::getTPL()->addHTMLHeaderFile("lib/jquery.countdown.js", "js");
		Hook::event("ConstructionsLoaded", array($collection));
		Core::getTPL()->addLoop("constructions", $collection);
		Core::getTPL()->assign("event", $this->event);
		return $this;
	}

	 /**
	 * Check for sufficient resources and start to upgrade building.
	 *
	 * @param integer	Building id to upgrade
	 *
	 * @return Bengine_Page_Constructions
	 */
	protected function upgradeAction($id)
	{
		// Check events
		if($this->event != false || Core::getUser()->get("umode"))
			$this->redirect("game.php/".SID."/Constructions");
		if($id == 12 && Bengine::getEH()->getResearchEvent())
			throw new Recipe_Exception_Generic("Do not mess with the url.");
		$shipyardSize = Bengine::getEH()->getShipyardEvents()->getCalculatedSize();
		if(($id == 8 || $id == 7) && $shipyardSize>0)
			throw new Recipe_Exception_Generic("Do not mess with the url.");

		// Check fields
		if(!Bengine::getPlanet()->planetFree())
		{
			Logger::dieMessage("PLANET_FULLY_DEVELOPED");
		}

		// Check for requirements
		if(!Bengine::canBuild($id))
		{
			throw new Recipe_Exception_Generic("You does not fulfil the requirements to build this.");
		}

		// Load building data
		Core::getLanguage()->load(array("info" , "buildings"));
		if(Bengine::getPlanet()->getData("ismoon")) { $mode = 5; } else { $mode = 1; }
		$result = Core::getQuery()->select("construction", array("name", "basic_metal", "basic_silicon", "basic_hydrogen", "basic_energy", "charge_metal", "charge_silicon", "charge_hydrogen", "charge_energy"), "", "buildingid = '".$id."' AND mode = '".$mode."'");
		if(!$row = Core::getDB()->fetch($result))
		{
			Core::getDB()->free_result($result);
			throw new Recipe_Exception_Generic("Unkown building :(");
		}
		Core::getDB()->free_result($result);

		Hook::event("UpgradeBuildingFirst", array(&$row));

		// Get required resources
		$level = Bengine::getPlanet()->getBuilding($id);
		if($level > 0) { $level = $level + 1; } else { $level = 1; }
		$this->setRequieredResources($level, $row);

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
			$data["buildingname"] = $row["name"];
			Hook::event("UpgradeBuildingLast", array($row, &$data, &$time));
			Bengine::getEH()->addEvent(1, $time + TIME, Core::getUser()->get("curplanet"), Core::getUser()->get("userid"), null, $data);
			$this->redirect("game.php/".SID."/Constructions");
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
	 * @param integer	Building id
	 *
	 * @return Bengine_Page_Constructions
	 */
	protected function abortAction($id)
	{
		if(!$this->event || !$id || Core::getUser()->get("umode"))
		{
			$this->redirect("game.php/".SID."/Constructions");
		}
		$result = Core::getQuery()->select("construction", array("buildingid"), "", "buildingid = '".$id."' AND mode = '".$this->getMode()."'");
		if($row = Core::getDB()->fetch($result))
		{
			Core::getDB()->free_result($result);
			Hook::event("AbortBuilding", array($this));
			Bengine::getEH()->removeEvent($this->event->get("eventid"));
			$this->redirect("game.php/".SID."/Constructions");
		}
		Core::getDB()->free_result($result);
		$this->setNoDisplay();
		return $this;
	}

	/**
	 * Demolish a building ...
	 *
	 * @param integer	Building id
	 *
	 * @return Bengine_Page_Constructions
	 */
	protected function demolishAction($id)
	{
		if($this->event != false || Core::getUser()->get("umode"))
		{
			Recipe_Header::redirect("game.php/".SID."/Constructions", false);
		}
		$result = Core::getQuery()->select("construction", array("basic_metal", "basic_silicon", "basic_hydrogen", "charge_metal", "charge_silicon", "charge_hydrogen", "name", "demolish"), "", "buildingid = '".$id."' AND mode = '".$this->getMode()."'");
		if(!$row = Core::getDB()->fetch($result))
		{
			Core::getDB()->free_result($result);
			throw new Recipe_Exception_Generic("Unkown building :(");
		}
		Core::getDB()->free_result($result);
		$level = Bengine::getPlanet()->getBuilding($id);
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
		if($data["metal"] <= Bengine::getPlanet()->getData("metal") && $data["silicon"] <= Bengine::getPlanet()->getData("silicon") && $data["hydrogen"] <= Bengine::getPlanet()->getData("hydrogen"))
		{
			$data["buildingname"] = $row["name"];
			$data["buildingid"] = $id;
			$time = getBuildTime($data["metal"], $data["silicon"], self::BUILDING_CONSTRUCTION_TYPE);
			Hook::event("DemolishBuldingLast", array(&$data, &$time));
			Bengine::getEH()->addEvent(2, $time + TIME, Core::getUser()->get("curplanet"), Core::getUser()->get("userid"), null, $data);
			$this->redirect("game.php/".SID."/Constructions");
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
		return (Bengine::getPlanet()->getData("ismoon")) ? 5 : 1;
	}

	/**
	 * Shows all building information.
	 *
	 * @param integer	Building id
	 *
	 * @return Bengine_Page_Constructions
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
		$result = Core::getQuery()->select("construction", $select, "", "buildingid = '".$id."' AND (mode = '1' OR mode = '2' OR mode = '5')");
		if($row = Core::getDB()->fetch($result))
		{
			Core::getDB()->free_result($result);
			Core::getLanguage()->load("info,Resource");
			Hook::event("BuildingInfoBefore", array(&$row));

			// Assign general building data
			Core::getTPL()->assign("buildingName", Core::getLanguage()->getItem($row["name"]));
			Core::getTPL()->assign("buildingDesc", Core::getLanguage()->getItem($row["name"]."_FULL_DESC"));
			Core::getTPL()->assign("buildingImage", Image::getImage("buildings/".$row["name"].".gif", Core::getLanguage()->getItem($row["name"]), null, null, "leftImage"));
			Core::getTPL()->assign("edit", Link::get("game.php/".SID."/Construction_Edit/Index/".$id, "[".Core::getLanguage()->getItem("EDIT")."]"));

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
			$chartType = "error";
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

				if(Bengine::getPlanet()->getBuilding($id) - 7  < 0)
				{
					$start = 7;
				}
				else { $start = Bengine::getPlanet()->getBuilding($id); }

				for($i = $start - 7; $i <= Bengine::getPlanet()->getBuilding($id) + 7; $i++)
				{
					$chart[$i]["level"] = $i;
					$chart[$i]["s_prod"] = ($prodFormula) ? parseFormula($prodFormula, $baseCost, $i) : 0;
					$chart[$i]["s_diffProd"] = ($prodFormula) ? $chart[$i]["s_prod"] - parseFormula($prodFormula, $baseCost, Bengine::getPlanet()->getBuilding($id)) : 0;
					$chart[$i]["s_cons"] = ($consFormula) ? parseFormula($consFormula, 0, $i) : 0;
					$chart[$i]["s_diffCons"] = ($consFormula) ? parseFormula($consFormula, 0, Bengine::getPlanet()->getBuilding($id)) - $chart[$i]["s_cons"] : 0;

					$chart[$i]["prod"] = fNumber($chart[$i]["s_prod"]);
					$chart[$i]["diffProd"] = fNumber($chart[$i]["s_diffProd"]);
					$chart[$i]["cons"] = fNumber($chart[$i]["s_cons"]);
					$chart[$i]["diffCons"] = fNumber($chart[$i]["s_diffCons"]);
				}
				Hook::event("BuildingInfoProduction", array(&$chart));
				Core::getTPL()->addLoop("chart", $chart);
			}
			Core::getTPL()->assign("chartType", $chartType);

			// Show demolish function
			$factor = floatval($row["demolish"]);
			if(Bengine::getPlanet()->getBuilding($id) > 0 && $factor > 0.0)
			{
				Core::getTPL()->assign("buildingLevel", Bengine::getPlanet()->getBuilding($id));
				Core::getTPL()->assign("demolish", true);

				$metal = ""; $_metal = 0;
				$silicon = ""; $_silicon = 0;
				$hydrogen = ""; $_hydrogen = 0;

				if($row["basic_metal"] > 0)
				{
					$_metal = (1 / $factor) * parseFormula($row["charge_metal"], $row["basic_metal"], Bengine::getPlanet()->getBuilding($id));
					$metal = Core::getLanguage()->getItem("METAL").": ".fNumber($_metal);
				}
				Core::getTPL()->assign("metal", $metal);

				if($row["basic_silicon"] > 0)
				{
					$_silicon = (1 / $factor) * parseFormula($row["charge_silicon"], $row["basic_silicon"], Bengine::getPlanet()->getBuilding($id));
					$silicon = Core::getLanguage()->getItem("SILICON").": ".fNumber($_silicon);
				}
				Core::getTPL()->assign("silicon", $silicon);

				if($row["basic_hydrogen"] > 0)
				{
					$_hydrogen = (1 / $factor) * parseFormula($row["charge_hydrogen"], $row["basic_hydrogen"], Bengine::getPlanet()->getBuilding($id));
					$hydrogen = Core::getLanguage()->getItem("HYDROGEN").": ".fNumber($_hydrogen);
				}
				Core::getTPL()->assign("hydrogen", $hydrogen);

				$time = getBuildTime($_metal, $_silicon, self::BUILDING_CONSTRUCTION_TYPE);
				Core::getTPL()->assign("dimolishTime", getTimeTerm($time));

				$showLink = (Bengine::getPlanet()->getData("metal") >= $_metal && Bengine::getPlanet()->getData("silicon") >= $_silicon && Bengine::getPlanet()->getData("hydrogen") >= $_hydrogen);
				Core::getTPL()->assign("showLink", $showLink && !$this->event);

				Core::getTPL()->assign("demolishNow", Link::get("game.php/".SID."/Constructions/Demolish/{$id}", Core::getLanguage()->getItem("DEMOLISH_NOW")));
			}
			else
			{
				Core::getTPL()->assign("demolish", false);
			}
			Hook::event("BuildingInfoAfter", array(&$row));
		}
		else
		{
			Core::getDB()->free_result($result);
			throw new Recipe_Exception_Generic("Unkown building. You'd better don't manipulate the URL. We see everything ;)");
		}
		return $this;
	}
}
?>