<?php
/**
 * Main programm. Handles all application-related classes.
 *
 * @package Bengine\Game
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 */

class Game extends Application
{
	/**
	 * Planet object.
	 *
	 * @var Bengine_Game_Planet
	 */
	protected static $planet = null;

	/**
	 * EventHandler object.
	 *
	 * @var Bengine_Game_EventHandler
	 */
	protected static $eh = null;

	/**
	 * Holds a list of all user's planets.
	 *
	 * @var array
	 */
	protected static $planetStack = array();

	/**
	 * Holds a list of all available research levels.
	 *
	 * @var array
	 */
	protected static $research = array();

	/**
	 * If research labs are already loaded.
	 *
	 * @var boolean
	 */
	protected static $labsLoaded = false;

	/**
	 * Holds a list of all available research labs.
	 *
	 * @var array
	 */
	protected static $researchLabs = array();

	/**
	 * Holds all requirements.
	 *
	 * @var array
	 */
	protected static $requirements = null;

	/**
	 * Starts Bengine.
	 *
	 * @return Game
	 */
	public function run()
	{
		parent::run();

		// Update last activity
		Core::getQuery()->update("user", array("last" => TIME, "db_lock" => TIME), "userid = ?", array(Core::getUser()->userid));

		if($planetid = Core::getRequest()->getPOST("planetid"))
		{
			Core::getUser()->set("curplanet", $planetid);
		}
		Hook::event("YuStart"); // @deprecated: Will be removed in Bengine 0.40
		Hook::event("GameStart");
		self::loadResearch();
		self::setPlanet();
		self::setEventHandler();
		self::getEH()->init();
		self::getPlanet()->getProduction()->addProd();
		self::globalTPLAssigns();

		// Asteroid event (Once per week).
		$first = mt_rand(0,100);
		$second = mt_rand(0,100);
		if($first == 42 && $second == 49 && Core::getUser()->get("asteroid") < TIME - 604800)
		{
			$data["metal"] = mt_rand(1, Core::getOptions()->get("MAX_ASTEROID_SIZE")) * 1000;
			$data["silicon"] = mt_rand(1, $data["metal"]/1000) * 1000;
			$data["galaxy"] = self::getPlanet()->getData("galaxy");
			$data["system"] = self::getPlanet()->getData("system");
			$data["position"] = self::getPlanet()->getData("position");
			$data["planet"] = self::getPlanet()->getData("planetname");
			Hook::event("YuAsteroidEvent", array(&$data)); // @deprecated: Will be removed in Bengine 0.40
			Hook::event("GameAsteroidEvent", array(&$data));
			Core::getQuery()->update("user", array("asteroid" => TIME), "userid = ?", array(Core::getUser()->get("userid")));
			$what = (self::getPlanet()->getData("ismoon")) ? "moonid" : "planetid";
			Core::getDB()->query("UPDATE ".PREFIX."galaxy SET metal = metal + ?, silicon = silicon + ? WHERE ".$what." = ?", array($data["metal"], $data["silicon"], self::getPlanet()->getPlanetId()));
			new Bengine_Game_AutoMsg(22, Core::getUser()->get("userid"), TIME, $data);
			Core::getUser()->rebuild();
		}

		$this->dispatch();
		Game::unlock();
		return $this;
	}

	/**
	 * Unlock db functions.
	 *
	 * @return void
	 */
	public static function unlock()
	{
		if(!self::isDbLocked())
		{
			Core::getQuery()->update("user", array("db_lock" => 0), "userid = ?", array(Core::getUser()->userid));
		}
		return;
	}

	/**
	 * Returns wether to execute Event-Handler actions.
	 *
	 * @return boolean
	 */
	public static function isDbLocked()
	{
		return Core::getUser()->get("db_lock") > 0 || TIME - Core::getUser()->get("db_lock") < 30;
	}

	/**
	 * Sets EventHandler object.
	 *
	 * @return void
	 */
	protected static function setEventHandler()
	{
		self::$eh = new Bengine_Game_EventHandler();
		return;
	}

	/**
	 * Returns EventHandler objeckt.
	 *
	 * @return Bengine_Game_EventHandler
	 */
	public static final function getEH()
	{
		return self::$eh;
	}

	/**
	 * Sets planet object.
	 *
	 * @return void
	 */
	protected static function setPlanet()
	{
		self::$planet = new Bengine_Game_Planet(Core::getUser()->get("curplanet"), Core::getUser()->get("userid"));
		self::$planet->setResearch(self::$research);
		return;
	}

	/**
	 * Returns Planet object.
	 *
	 * @return Bengine_Game_Planet
	 */
	public static final function getPlanet()
	{
		return self::$planet;
	}

	/**
	 * Generic template assignments.
	 *
	 * @return void
	 */
	protected static function globalTPLAssigns()
	{
		// JavaScript & CSS
		Core::getTPL()->addHTMLHeaderFile("game.css", "css"); // TODO: change to responsive
		Core::getTPL()->addHTMLHeaderFile("jquery-ui.css", "css");
		Core::getTPL()->addHTMLHeaderFile("lib/jquery.js", "js");
		Core::getTPL()->addHTMLHeaderFile("lib/jquery-ui.js", "js");
		Core::getTPL()->addHTMLHeaderFile("main.js", "js");

		// Set planets for right menu and fill planet stack.
		$planets = array(); $i = 0;
		$order = "p.sort_index ASC, p.planetid ASC";
		$joins  = "LEFT JOIN ".PREFIX."galaxy g ON (g.planetid = p.planetid)";
		$joins .= "LEFT JOIN ".PREFIX."planet m ON (g.moonid = m.planetid)";
		$atts = array("p.planetid", "p.ismoon", "p.planetname", "p.picture", "g.galaxy", "g.system", "g.position", "m.planetid AS moonid", "m.planetname AS moon", "m.picture AS mpicture");
		$where  = Core::getDB()->quoteInto("p.userid = ?", Core::getUser()->get("userid"));
		$where .= Core::getDB()->quoteInto(" AND p.ismoon = ?", 0);
		$result = Core::getQuery()->select("planet p", $atts, $joins, $where, $order);
		unset($order);
		foreach($result->fetchAll() as $row)
		{
			$planets[$i] = $row;
			$coords = $row["galaxy"].":".$row["system"].":".$row["position"];
			$coords = "[".$coords."]";
			$planets[$i]["coords"] = $coords;
			$planets[$i]["picture"] = Image::getImage("planets/small/s_".$row["picture"].Core::getConfig()->get("PLANET_IMG_EXT"), $row["planetname"]." ".$coords, 60, 60);
			if($row["moonid"])
			{
				$planets[$i]["mpicture"] = Image::getImage("planets/small/s_".$row["mpicture"].Core::getConfig()->get("PLANET_IMG_EXT"), $row["moon"]." ".$coords, 20, 20);
			}
			self::$planetStack[] = $row["planetid"];
			$i++;
		}
		$result->closeCursor();
		Hook::event("YuPlanetList", array(&$planets)); // @deprecated: Will be removed in Bengine 0.40
		Hook::event("GamePlanetList", array(&$planets));
		Core::getTPL()->addLoop("planetHeaderList", $planets);

		// Menu
		Core::getTPL()->addLoop("navigation", new Bengine_Game_Menu("Menu"));

		// Assignments
		$planet = self::getPlanet();
		Core::getTPL()->assign("themePath", (Core::getUser()->get("theme")) ? Core::getUser()->get("theme") : HTTP_HOST.REQUEST_DIR);
		Core::getTPL()->assign("planetImageSmall", Image::getImage("planets/small/s_".$planet->getData("picture").Core::getConfig()->get("PLANET_IMG_EXT"), $planet->getData("planetname"), 88, 88));
		Core::getTPL()->assign("currentPlanet", Link::get("game/".SID."/Index/PlanetOptions", $planet->getData("planetname")));
		Core::getTPL()->assign("currentCoords", $planet->getCoords());

		// Show message if user is in vacation or deletion mode.
		$delete = false;
		if(Core::getUser()->get("delete") > 0)
		{
			$delete = Core::getLanguage()->getItem("ACCOUNT_WILL_BE_DELETED");
		}
		$umode = false;
		if(Core::getUser()->get("umode"))
		{
			$umode = Core::getLanguage()->getItem("UMODE_ENABLED");
		}
		Core::getTPL()->assign("delete", $delete);
		Core::getTPL()->assign("umode", $umode);
		return;
	}

	/**
	 * Returns the research level.
	 *
	 * @param integer $id	Id of requested research
	 *
	 * @return integer	Level of requested research
	 */
	public static function getResearch($id)
	{
		if(isset(self::$research[$id]))
		{
			return self::$research[$id];
		}
		return 0;
	}

	/**
	 * Find out whether contruction is available.
	 *
	 * @param integer $bid	Construction id
	 *
	 * @return boolean	True, if construction can be upgraded, false if not
	 */
	public static function canBuild($bid)
	{
		if(self::$requirements === null)
		{
			self::loadRequirements();
		}

		if(!isset(self::$requirements[$bid]))
		{
			return true;
		}
		foreach(self::$requirements[$bid] as $r)
		{
			$rLevel = 0;
			if($r["mode"] == 1 || $r["mode"] == 5)
				$rLevel = self::$planet->getBuilding($r["needs"]);
			else if($r["mode"] == 2)
				$rLevel = self::getResearch($r["needs"]);
			if($rLevel < $r["level"])
				return false;
		}
		return true;
	}

	/**
	 * Loads all requirements from the cache.
	 *
	 * @return void
	 */
	protected static function loadRequirements()
	{
		if(!Core::getCache()->objectExists("requirements"))
		{
			$result = Core::getQuery()->select("requirements r", array("r.buildingid", "r.needs", "b.name", "b.mode", "r.level"), "LEFT JOIN ".PREFIX."construction b ON (b.buildingid = r.needs)", "", "b.display_order ASC, r.buildingid ASC");
			Core::getCache()->buildObject("requirements", $result, "buildingid");
		}
		self::$requirements = Core::getCache()->readObject("requirements");
		return;
	}

	/**
	 * Loads all research items.
	 *
	 * @return void
	 */
	protected static function loadResearch()
	{
		$result = Core::getQuery()->select("research2user", array("buildingid", "level"), "", Core::getDB()->quoteInto("userid = ?", Core::getUser()->get("userid")));
		foreach($result->fetchAll() as $row)
		{
			self::$research[$row["buildingid"]] = $row["level"];
		}
		$result->closeCursor();
		Hook::event("YuResearchLoaded", array(&self::$research)); // @deprecated: Will be removed in Bengine 0.40
		Hook::event("GameResearchLoaded", array(&self::$research));
		return;
	}

	/**
	 * Calculates full speed of a spaceship due to the engine level.
	 *
	 * @param integer $id		Ship id
	 * @param integer $speed	basic speed
	 *
	 * @return integer	Full speed
	 */
	public static function getSpeed($id, $speed)
	{
		$select = array(
			"s2e.engineid",
			"s2e.level",
			"s2e.base_speed",
			"e.factor"
		);
		$joins  = "LEFT JOIN ".PREFIX."engine e ON (e.engineid = s2e.engineid) ";
		$joins .= "LEFT JOIN ".PREFIX."research2user r2u ON (r2u.buildingid = s2e.engineid) ";
		$where  = Core::getDB()->quoteInto("s2e.unitid = ? AND r2u.level >= s2e.level", $id);
		$where .= Core::getDB()->quoteInto(" AND r2u.userid = ?", Core::getUser()->get("userid"));
		$order  = "s2e.level DESC";
		$result = Core::getQuery()->select("ship2engine s2e", $select, $joins, $where, $order, "1");
		if($row = $result->fetchRow())
		{
			Hook::event("YuGetFlySpeedFirst", array($id, &$speed, &$row)); // @deprecated: Will be removed in Bengine 0.40
			Hook::event("GameGetFlySpeedFirst", array($id, &$speed, &$row));
			if($row["base_speed"] > 0)
			{
				$speed = (int) $row["base_speed"];
			}
			$level = self::getResearch($row["engineid"]);
			$multiplier = $level * (int) $row["factor"];
			if($multiplier > 0) { return $speed / 100 * $multiplier + $speed; }
		}
		$result->closeCursor();
		Hook::event("YuGetFlySpeedLast", array($id, &$speed)); // @deprecated: Will be removed in Bengine 0.40
		Hook::event("GameGetFlySpeedLast", array($id, &$speed));
		return $speed;
	}

	/**
	 * Calculates the flying time.
	 *
	 * @param integer $distance	Distance to destination
	 * @param integer $maxspeed	Maximum speed of the slowest ship
	 * @param integer $speed	Speed factor (Can throttel the max. speed)
	 *
	 * @return integer	Flying time in seconds
	 */
	public static function getFlyTime($distance, $maxspeed, $speed = 100)
	{
		$speed /= 10;
		$time = round((35000 / $speed) * sqrt($distance * 10 / $maxspeed) + 10);
		if(is_numeric(Core::getOptions()->get("GAMESPEED")) && Core::getOptions()->get("GAMESPEED") > 0)
		{
			$time *= floatval(Core::getOptions()->get("GAMESPEED"));
		}
		return ceil($time);
	}

	/**
	 * Calculates the flying consumption.
	 *
	 * @param integer $basicConsumption	Basic consumption
	 * @param integer $dist				Distance to destination
	 * @param integer $speed			Speed factor (Can decrease the consumption)
	 *
	 * @return integer	Full consumption
	 */
	public static function getFlyConsumption($basicConsumption, $dist, $speed = 100)
	{
		$speed /= 10;
		return round($basicConsumption * $dist / 35000 * (($speed / 10) + 1) * (($speed / 10) + 1)) + 1;
	}

	/**
	 * Calculates the distance.
	 *
	 * @param integer $galaxy	Destination galaxy
	 * @param integer $system	Destination system
	 * @param integer $pos		Destination position
	 *
	 * @return integer	Distance
	 */
	public static function getDistance($galaxy, $system, $pos)
	{
		$oGalaxy = self::getPlanet()->getData("galaxy");
		$oSystem = self::getPlanet()->getData("system");
		$oPos = self::getPlanet()->getData("position");
		Hook::event("YuGetDistance", array(&$galaxy, &$system, &$pos)); // @deprecated: Will be removed in Bengine 0.40
		Hook::event("GameGetDistance", array(&$galaxy, &$system, &$pos));
		if($galaxy - $oGalaxy != 0)
		{
			return abs($galaxy - $oGalaxy) * 20000;
		}
		else if($system - $oSystem != 0)
		{
			return abs($system - $oSystem) * 5 * 19 + 2700;
		}
		else if($pos - $oPos != 0)
		{
			return abs($pos - $oPos) * 5 + 1000;
		}
		return 5;
	}

	/**
	 * Returns the mission name due to an id.
	 *
	 * @param integer $id	Mission id
	 *
	 * @return string	Mission name
	 */
	public static function getMissionName($id)
	{
		switch($id)
		{
			case 6: $return = "STATIONATE"; break;
			case 7: $return = "TRANSPORT"; break;
			case 8: $return = "COLONIZE"; break;
			case 9: $return = "RECYCLING"; break;
			case 10: $return = "ATTACK"; break;
			case 11: $return = "SPY"; break;
			case 12: case 18: $return = "ALLIANCE_ATTACK"; break;
			case 13: case 17: $return = "HALT"; break;
			case 14: $return = "MOON_DESTRUCTION"; break;
			case 15: $return = "EXPEDITION"; break;
			case 16: $return = "ROCKET_ATTACK"; break;
			case 20: $return = "RETURN_FLY"; break;
			default: $return = "UNKOWN"; break;
		}
		Hook::event("YuGetMissionName", array(&$return)); // @deprecated: Will be removed in Bengine 0.40
		Hook::event("GameGetMissionName", array(&$return));
		return Core::getLanguage()->getItem($return);
	}

	/**
	 * Returns a list of user's planets.
	 *
	 * @return array
	 */
	public static function getPlanetStack()
	{
		return self::$planetStack;
	}

	/**
	 * Calculates the research time.
	 *
	 * @param integer $ress	Expensed metal + silicon
	 *
	 * @return integer	Time to finish research
	 */
	public static function getResearchTime($ress)
	{
		if(!self::$labsLoaded) { self::loadReasearchLabs(); }
		$labs = self::$researchLabs;
		$ign = self::getResearch(26); // Intergalactic research network
		$lab = self::$planet->getBuilding("RESEARCH_LAB");
		if($ign > 0)
		{
			for($i = 0; $i < $ign; $i++)
			{
				$lab += (isset($labs[$i])) ? $labs[$i] : 0;
			}
		}
		$time = $ress / (1000 * (1 + $lab));
		return $time;
	}

	/**
	 * Returns the range for rocket attacks.
	 *
	 * @return integer
	 */
	public static function getRocketRange()
	{
		return self::getResearch(21) * 5 - 1;
	}

	/**
	 * Returns the capacity of the current rocket station.
	 *
	 * @return integer
	 */
	public static function getRocketStationSize()
	{
		return self::$planet->getBuilding("ROCKET_STATION") * 10;
	}

	/**
	 * Returns the loaded requirements.
	 *
	 * @return array
	 */
	public static function getAllRequirements()
	{
		if(count(self::$requirements) <= 0)
		{
			self::loadRequirements();
		}
		return self::$requirements;
	}

	/**
	 * Loads all research labs.
	 *
	 * @return void
	 */
	public static function loadReasearchLabs()
	{
		if(self::$labsLoaded) { return; }
		$where  = Core::getDB()->quoteInto("p.userid = ?", Core::getUser()->get("userid"));
		$where .= Core::getDB()->quoteInto(" AND b2p.buildingid = ? AND ", 12);
		$where .= Core::getDB()->quoteInto("b2p.planetid != ?", Core::getUser()->get("curplanet"));
		$result = Core::getQuery()->select("building2planet b2p", array("b2p.level", "b2p.planetid"), "LEFT JOIN ".PREFIX."planet p ON (p.planetid = b2p.planetid)", $where, "b2p.level DESC");
		foreach($result->fetchAll() as $row)
		{
			self::$researchLabs[] = $row["level"];
		}
		$result->closeCursor();
		self::$labsLoaded = true;
		Hook::event("YuLoadResearchLabs", array(&self::$researchLabs)); // @deprecated: Will be removed in Bengine 0.40
		Hook::event("GameLoadResearchLabs", array(&self::$researchLabs));
		return;
	}

	/**
	 * Returns a random Modorator.
	 *
	 * @return integer	User id
	 */
	public static function getRandomModerator()
	{
		$mods = array();
		$result = Core::getQuery()->select("user2group", array("userid"), "", "usergroupid = '2' OR usergroupid = '4'");
		foreach($result->fetchAll() as $row)
		{
			if(!in_array($row["userid"], $mods))
			{
				$mods[] = $row["userid"];
			}
		}
		$result->closeCursor();
		$i = mt_rand(0, count($mods) - 1);
		return isset($mods[$i]) ? $mods[$i] : 1;
	}

	/**
	 * Returns the current controller object.
	 *
	 * @return Bengine_Game_Controller_Abstract
	 */
	public static function getController()
	{
		return self::$controller;
	}

	/**
	 * Sets the controller object.
	 *
	 * @param Bengine_Game_Controller_Abstract
	 *
	 * @return void
	 */
	public static function setController(Bengine_Game_Controller_Abstract $controller)
	{
		self::$controller = $controller;
		return;
	}

	/**
	 * @return string
	 */
	public static function getVersion()
	{
		$meta = self::getMeta();
		return $meta["packages"]["bengine"]["game"]["version"];
	}

	/**
	 * @return bool
	 */
	public static function attackingStoppageEnabled()
	{
		if(Core::getOptions()->get("ATTACKING_STOPPAGE_END_DATE") > 0 && Core::getOptions()->get("ATTACKING_STOPPAGE_END_DATE") < TIME)
		{
			Core::getOptions()->setValue("ATTACKING_STOPPAGE", 0);
			Core::getOptions()->setValue("ATTACKING_STOPPAGE_END_DATE", 0, true);
		}
		return (bool) Core::getOptions()->get("ATTACKING_STOPPAGE");
	}
}
?>