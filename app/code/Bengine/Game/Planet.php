<?php
/**
 * Planet class. Loads planet data, updates production.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 */

class Bengine_Game_Planet
{
	/**
	 * Name ID of the solar satellite.
	 *
	 * @var string
	 */
	const SOLAR_SAT_ID = "SOLAR_SATELLITE";

	/**
	 * Planet data.
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * Id of current used planet.
	 *
	 * @var integer
	 */
	protected $planetid = 0;

	/**
	 * Production.
	 *
	 * @var array
	 */
	protected $prod = array();

	/**
	 * Remaining energy.
	 *
	 * @var integer
	 */
	protected $energy = 0;

	/**
	 * Available builings and their level.
	 *
	 * @var array
	 */
	protected $building = array();

	/**
	 * Energy consumption of all buildings.
	 *
	 * @var array
	 */
	protected $consumption = array();

	/**
	 * Production to building relation.
	 *
	 * @var array
	 */
	protected $buildingProd = array();

	/**
	 * Consumption to building relation.
	 *
	 * @var array
	 */
	protected $buildingCons = array();

	/**
	 * Production factor of a building.
	 *
	 * @var array
	 */
	protected $factors = array();

	/**
	 * Metal, silicon and hydrogen storage.
	 *
	 * @var array
	 */
	protected $storage = array();

	/**
	 * The planet's owner.
	 *
	 * @var integer
	 */
	protected $userid = 0;

	/**
	 * Constructor: Starts planet functions.
	 *
	 * @param integer $planetid	Planet id
	 * @param integer $userid	User id
	 * @param boolean $checkUser	Enable user check
	 *
	 * @return Bengine_Game_Planet
	 */
	public function __construct($planetid, $userid, $checkUser = true)
	{
		$this->planetid = $planetid;
		$this->userid = $userid;
		$this->loadData($checkUser);
	}

	/**
	 * Loads all planet data.
	 *
	 * @param boolean $checkUser	Enable user check
	 *
	 * @return Bengine_Game_Planet
	 *
	 * @throws Recipe_Exception_Generic
	 */
	protected function loadData($checkUser = true)
	{
		$where = Core::getDB()->quoteInto("p.planetid = ?", $this->planetid);
		if($checkUser)
		{
			$where .= Core::getDB()->quoteInto(" AND p.userid = ?", $this->userid);
		}
		$atts = array("p.planetname", "p.userid", "p.ismoon", "p.picture", "p.temperature", "p.diameter", "p.last", "p.solar_satellite_prod", "g.galaxy", "g.system", "g.position", "gm.galaxy AS moongala", "gm.system AS moonsys", "gm.position AS moonpos", "g.moonid");
		$result = Core::getQuery()->select("planet p", $atts, "LEFT JOIN ".PREFIX."galaxy g ON (g.planetid = p.planetid) LEFT JOIN ".PREFIX."galaxy gm ON (gm.moonid = p.planetid)", $where);
		if(!($this->data = $result->fetchRow()))
		{
			$result->closeCursor();
			throw new Recipe_Exception_Generic("Could not found planet ".$this->planetid.".");
		}
		$result->closeCursor();
		if($this->data["ismoon"])
		{
			$this->data["galaxy"] = $this->data["moongala"];
			$this->data["system"] = $this->data["moonsys"];
			$this->data["position"] = $this->data["moonpos"];
		}
		return $this;
	}

	/**
	 * Sets some standard values to avoid confusion.
	 *
	 * @return Bengine_Game_Planet
	 */
	protected function setStandardValues()
	{
		$this->prod["metal"] = 0;
		$this->prod["silicon"] = 0;
		$this->prod["hydrogen"] = 0;
		$this->prod["energy"] = 0;
		$this->consumption["metal"] = 0;
		$this->consumption["silicon"] = 0;
		$this->consumption["hydrogen"] = 0;
		$this->consumption["energy"] = 0;
		$this->storage["metal"] = 100000;
		$this->storage["silicon"] = 100000;
		$this->storage["hydrogen"] = 100000;
		return $this;
	}

	/**
	 * Fetches building and production data.
	 *
	 * @return Bengine_Game_Planet
	 */
	public function getProduction()
	{
		$this->setStandardValues();
		$solarSatId = self::SOLAR_SAT_ID;

		$where  = Core::getDB()->quoteInto("u2s.planetid = ?", $this->planetid);
		$where .= Core::getDB()->quoteInto(" AND c.name = ?", $solarSatId);
		$result = Core::getQuery()->select(
			"unit2shipyard u2s",
			array("u2s.unitid", "u2s.quantity"),
			"LEFT JOIN ".PREFIX."construction c ON (u2s.unitid = c.buildingid)",
			$where);
		if($row = $result->fetchRow())
		{
			$this->building[$solarSatId] = $row["quantity"];
			$this->building[$row["unitid"]] = $row["quantity"];
		}
		else
		{
			$this->building[$solarSatId] = 0;
		}
		$result->closeCursor();

		$join = "LEFT JOIN ".PREFIX."building2planet b2p ON (b2p.buildingid = b.buildingid)";
		$atts = array("b.buildingid", "b.name", "b.prod_metal", "b.prod_silicon", "b.prod_hydrogen", "b.prod_energy", "b.cons_metal", "b.cons_silicon", "b.cons_hydrogen", "b.cons_energy", "b.special", "b2p.level", "b2p.prod_factor AS factor");
		$result = Core::getQuery()->select("construction b", $atts, $join, Core::getDB()->quoteInto("(b.mode = '1' OR b.mode = '5') AND b2p.planetid = ?", $this->planetid), "b.display_order ASC, b.buildingid ASC");
		foreach($result->fetchAll() as $row)
		{
			Hook::event("RessProdGetBuilding", array(&$row, $this));

			$id = (int) $row["buildingid"];
			$level = (int) $row["level"];
			$this->building[$id] = $level;
			$this->building[$row["name"]] = $level;
			$_factor = $row["factor"] / 100;

			// Production
			if($row["prod_metal"] != "")
			{
				$this->buildingProd["metal"][$id] = $this->parseFormula($row["prod_metal"], $level) * $_factor;
				$this->prod["metal"] += $this->buildingProd["metal"][$id];
				$this->factors[$id] = $row["factor"];
			}
			else if($row["prod_silicon"] != "")
			{
				$this->buildingProd["silicon"][$id] = $this->parseFormula($row["prod_silicon"], $level) * $_factor;
				$this->prod["silicon"] += $this->buildingProd["silicon"][$id];
				$this->factors[$id] = $row["factor"];
			}
			else if($row["prod_hydrogen"] != "")
			{
				$this->buildingProd["hydrogen"][$id] = $this->parseFormula($row["prod_hydrogen"], $level) * $_factor;
				$this->prod["hydrogen"] += $this->buildingProd["hydrogen"][$id];
				$this->factors[$id] = $row["factor"];
			}
			else if($row["prod_energy"] != "")
			{
				$this->buildingProd["energy"][$id] = $this->parseFormula($row["prod_energy"], $level) * $_factor;
				$this->prod["energy"] += $this->buildingProd["energy"][$id];
				$this->factors[$id] = $row["factor"];
			}

			// Consumption
			if($row["cons_metal"] != "")
			{
				$this->buildingCons["metal"][$id] = $this->parseFormula($row["cons_metal"], $level) * $_factor;
				$this->consumption["metal"] += $this->buildingCons["metal"][$id];
			}
			else if($row["cons_silicon"] != "")
			{
				$this->buildingCons["silicon"][$id] = $this->parseFormula($row["cons_silicon"], $level) * $_factor;
				$this->consumption["silicon"] += $this->buildingCons["silicon"][$id];
			}
			else if($row["cons_hydrogen"] != "")
			{
				$this->buildingCons["hydrogen"][$id] = $this->parseFormula($row["cons_hydrogen"], $level) * $_factor;
				$this->consumption["hydrogen"] += $this->buildingCons["hydrogen"][$id];
			}
			else if($row["cons_energy"] != "")
			{
				$this->buildingCons["energy"][$id] = $this->parseFormula($row["cons_energy"], $level) * $_factor;
				$this->consumption["energy"] += $this->buildingCons["energy"][$id];
			}

			switch($id)
			{
				case 9:
					$this->storage["metal"] = $this->parseFormula($row["special"], $level);
				break;
				case 10:
					$this->storage["silicon"] = $this->parseFormula($row["special"], $level);
				break;
				case 11:
					$this->storage["hydrogen"] = $this->parseFormula($row["special"], $level);
				break;
			}
		}
		$result->closeCursor();

		// Add solar satellite prod
		if($this->getBuilding($solarSatId) > 0)
		{
			$this->buildingProd["energy"][$solarSatId] = $this->getBuilding($solarSatId) * floor($this->data["temperature"] / 4 + 20);
			$this->buildingProd["energy"][$solarSatId] = $this->buildingProd["energy"][$solarSatId] / 100 * $this->data["solar_satellite_prod"];
			if($this->buildingProd["energy"][$solarSatId] < 0)
			{
				$this->buildingProd["energy"][$solarSatId] = 0;
			}
			$this->prod["energy"] += $this->buildingProd["energy"][$solarSatId];
		}

		// Subtract consumption from production
		$this->prod["metal"] -= $this->consumption["metal"];
		$this->prod["silicon"] -= $this->consumption["silicon"];
		$this->prod["hydrogen"] -= $this->consumption["hydrogen"];

		// Increase storage by production factor
		$this->storage["metal"] *= Core::getConfig()->get("PRODUCTION_FACTOR");
		$this->storage["silicon"] *= Core::getConfig()->get("PRODUCTION_FACTOR");
		$this->storage["hydrogen"] *= Core::getConfig()->get("PRODUCTION_FACTOR");

		// Reduce production regarding the energy.
		$this->energy = $this->prod["energy"] - $this->consumption["energy"];
		if($this->energy < 0)
		{
			$factor = $this->prod["energy"] / $this->consumption["energy"];
			if($this->prod["metal"] > 0) { $this->prod["metal"] *= $factor; }
			if($this->prod["silicon"] > 0) { $this->prod["silicon"] *= $factor; }
			if($this->prod["hydrogen"] > 0) { $this->prod["hydrogen"] *= $factor; }
		}
		$this->prod["metal"] += (int) Core::getOptions()->get("METAL_BASIC_PROD");
		$this->prod["silicon"] += (int) Core::getOptions()->get("SILICON_BASIC_PROD");
		$this->prod["hydrogen"] += (int) Core::getOptions()->get("HYDROGEN_BASIC_PROD");
		Hook::event("RessProdFinished", array($this));
		return $this;
	}

	/**
	 * Parses a formula and return its result.
	 *
	 * @param string $formula
	 * @param int $level
	 *
	 * @return integer    Result
	 */
	protected function parseFormula($formula, $level)
	{
		$formula = Str::replace("{level}", $level, $formula);
		$formula = Str::replace("{temp}", $this->data["temperature"], $formula);
		$formula = preg_replace("#\{tech\=([0-9]+)\}#ie", 'Game::getResearch(\\1)', $formula);
		$result = 0;
		eval("\$result = ".$formula.";");
		return (int) $result;
	}

	/**
	 * Reloads the resources.
	 *
	 * @return Bengine_Game_Planet
	 */
	public function loadResources()
	{
		$result = Core::getQuery()->select("planet", array("metal", "silicon", "hydrogen"), "", Core::getDB()->quoteInto("planetid = ?", $this->getPlanetId()), "", 1);
		$row = $result->fetchRow();
		$this->setData("metal", $row["metal"])->setData("silicon", $row["silicon"])->setData("hydrogen", $row["hydrogen"]);
		return $this;
	}

	/**
	 * Write production into db.
	 *
	 * @return Bengine_Game_Planet
	 */
	public function addProd()
	{
		$this->loadResources();

		// Skip production if needed
		if(Game::isDbLocked())
		{
			return $this;
		}

		if($this->data["ismoon"])
		{
			$this->prod["metal"] = 0;
			$this->prod["silicon"] = 0;
			$this->prod["hydrogen"] = 0;
			return $this;
		}

		// Get new resource number.
		// Storage capacity exceeded?
		$tmp = Core::getConfig()->get("PRODUCTION_FACTOR") / 3600 * (TIME - $this->data["last"]);
		$totalMetProd = $this->prod["metal"] * $tmp;
		$totalSilProd = $this->prod["silicon"] * $tmp;
		$totalHydProd = $this->prod["hydrogen"] * $tmp;

		if($this->getData("metal") + $totalMetProd > $this->storage["metal"])
		{
			if($this->getData("metal") > $this->storage["metal"])
			{
				$totalMetProd = 0;
			}
			else
			{
				$totalMetProd = $this->storage["metal"] - $this->getData("metal");
			}
		}
		$this->data["metal"] += $totalMetProd;
		if($this->getData("silicon") + $totalSilProd > $this->storage["silicon"])
		{
			if($this->getData("silicon") > $this->storage["silicon"])
			{
				$totalSilProd = 0;
			}
			else
			{
				$totalSilProd = $this->storage["silicon"] - $this->getData("silicon");
			}
		}
		$this->data["silicon"] += $totalSilProd;
		if($this->getData("hydrogen") + $totalHydProd > $this->storage["hydrogen"])
		{
			if($this->getData("hydrogen") > $this->storage["hydrogen"])
			{
				$totalHydProd = 0;
			}
			else
			{
				$totalHydProd = $this->storage["hydrogen"] - $this->getData("hydrogen");
			}
		}
		$this->data["hydrogen"] += $totalHydProd;

		Hook::event("AddRessProd", array($this, &$totalMetProd, &$totalHydProd, &$totalSilProd));

		// Now write new resources into db and update times.
		$spec = array(
			"metal" => new Recipe_Database_Expr("metal + ?"),
			"silicon" => new Recipe_Database_Expr("silicon + ?"),
			"hydrogen" => new Recipe_Database_Expr("hydrogen + ?"),
			"last" => new Recipe_Database_Expr("?"),
		);
		$bind = array(
			$totalMetProd,
			$totalSilProd,
			$totalHydProd,
			TIME,
			$this->planetid
		);
		Core::getQuery()->update("planet", $spec, "planetid = ?", $bind);

		// Truncate resources.
		$this->data["metal"] = floor($this->data["metal"]);
		$this->data["silicon"] = floor($this->data["silicon"]);
		$this->data["hydrogen"] = floor($this->data["hydrogen"]);
		return $this;
	}

	/**
	 * Maximum available fields.
	 *
	 * @return integer	Max. fields
	 */
	public function getMaxFields()
	{
		$fmax = floor(pow($this->data["diameter"] / 1000, 2));
		if($this->getBuilding("TERRA_FORMER") > 0)
		{
			$fmax += $this->getBuilding("TERRA_FORMER") * (int) Core::getOptions()->get("TERRAFORMER_ADDITIONAL_FIELDS");
		}
		else if($this->data["ismoon"])
		{
			$fields = $this->getBuilding("MOON_BASE") * (int) Core::getOptions()->get("MOON_BASE_FIELDS") + 1;
			if($fields < $fmax)
			{
				$fmax = $fields;
			}
		}
		Hook::event("GetMaxFields", array(&$fmax, $this));
		$addition = ($this->data["ismoon"]) ? 0 : Core::getOptions()->get("PLANET_FIELD_ADDITION");
		return $fmax + $addition;
	}

	/**
	 * Returns the number of occupied fields.
	 *
	 * @param boolean $formatted	Format the number
	 *
	 * @return integer	Fields
	 */
	public function getFields($formatted = false)
	{
		$fields = array_sum($this->building) / 2 - $this->getBuilding(self::SOLAR_SAT_ID);
		if($formatted)
		{
			return fNumber($fields);
		}
		return $fields;
	}

	/**
	 * Checks if a planet has still free space.
	 *
	 * @return boolean
	 */
	public function planetFree()
	{
		if($this->getFields() < $this->getMaxFields())
		{
			return true;
		}
		return false;
	}

	/**
	 * Coordinates of this planet.
	 *
	 * @param boolean	Link or simple string
	 *
	 * @return string	Coordinates
	 */
	public function getCoords($link = true)
	{
		if($link) { return getCoordLink($this->data["galaxy"], $this->data["system"], $this->data["position"]); }
		return $this->data["galaxy"].":".$this->data["system"].":".$this->data["position"];
	}

	/**
	 * Returns building data.
	 *
	 * @return array
	 */
	public function fetchBuildings()
	{
		return $this->building;
	}

	/**
	 * Returns the level of a building.
	 *
	 * @param integer|string	Building ID
	 *
	 * @return integer	Building level
	 */
	public function getBuilding($id)
	{
		if(isset($this->building[$id]) && is_numeric($this->building[$id]))
		{
			return $this->building[$id];
		}
		return 0;
	}

	/**
	 * Returns the production of a building.
	 *
	 * @param string $res	Resource type
	 * @param integer $id	Building id
	 *
	 * @return integer	Production per hour
	 */
	public function getBuildingProd($res, $id)
	{
		if(isset($this->buildingProd[$res][$id]) && is_numeric($this->buildingProd[$res][$id]))
		{
			$productFactor = (double) Core::getConfig()->get("PRODUCTION_FACTOR");
			if($res == "energy")
			{
				$productFactor = 1;
			}
			$energyFactor = 1;
			if($this->energy < 0 && $res != "energy")
			{
				$energyFactor = $this->prod["energy"] / $this->consumption["energy"];
			}
			return $this->buildingProd[$res][$id] * $productFactor * $energyFactor;
		}
		return 0;
	}

	/**
	 * Returns the consumption of a building.
	 *
	 * @param string $res	Resource type
	 * @param integer $id	Building id
	 *
	 * @return integer	Consumption per hour
	 */
	public function getBuildingCons($res, $id)
	{
		if(isset($this->buildingCons[$res][$id]) && is_numeric($this->buildingCons[$res][$id]))
		{
			$productFactor = (double) Core::getConfig()->get("PRODUCTION_FACTOR");
			if($res == "energy")
			{
				$productFactor = 1;
			}
			$energyFactor = 1;
			if($this->energy < 0 && $res != "energy")
			{
				$energyFactor = $this->prod["energy"] / $this->consumption["energy"];
			}
			return $this->buildingCons[$res][$id] * $productFactor * $energyFactor;
		}
		return 0;
	}

	/**
	 * Returns the production factor of a building.
	 *
	 * @param integer $id	Building id
	 *
	 * @return integer	Production factor
	 */
	public function getBuildingFactor($id)
	{
		if(isset($this->factors[$id]) && is_numeric($this->factors[$id]))
		{
			return $this->factors[$id];
		}
		return 0;
	}

	/**
	 * Returns the given data field.
	 *
	 * @param string $param
	 *
	 * @return mixed    Data
	 */
	public function getData($param = null)
	{
		if(is_null($param))
		{
			return $this->data;
		}
		return (isset($this->data[$param])) ? $this->data[$param] : false;
	}

	/**
	 * Sets a data field.
	 *
	 * @param string $param
	 * @param mixed $value
	 *
	 * @return Bengine_Game_Planet
	 */
	public function setData($param, $value)
	{
		$this->data[$param] = $value;
		return $this;
	}

	/**
	 * Returns the remaining energy.
	 *
	 * @return integer
	 */
	public function getEnergy()
	{
		return $this->energy;
	}

	/**
	 * Returns the planet id.
	 *
	 * @return integer
	 */
	public function getPlanetId()
	{
		return $this->planetid;
	}

	/**
	 * Returns the production of a resource.
	 *
	 * @param string	Resource name
	 *
	 * @return integer
	 */
	public function getProd($resource)
	{
		$productFactor = (double) Core::getConfig()->get("PRODUCTION_FACTOR");
		if($resource == "energy")
		{
			$productFactor = 1;
		}
		return (isset($this->prod[$resource])) ? $this->prod[$resource] * $productFactor : false;
	}

	/**
	 * Returns the consumption of a resource.
	 *
	 * @param string	Resource name
	 *
	 * @return integer
	 */
	public function getConsumption($resource)
	{
		return (isset($this->consumption[$resource])) ? $this->consumption[$resource] : false;
	}

	/**
	 * Returns the max. storage of a resource.
	 *
	 * @param string	Resource name
	 *
	 * @return integer
	 */
	public function getStorage($resource)
	{
		return (isset($this->storage[$resource])) ? $this->storage[$resource] : false;
	}

	/**
	 * Checks the time since the last resource production.
	 *
	 * @return boolean	True if double click committed, false otherwise
	 */
	public function checkDoubleClick()
	{
		return (TIME - $this->getData("last") <= 4) ? true : false;
	}
}
?>