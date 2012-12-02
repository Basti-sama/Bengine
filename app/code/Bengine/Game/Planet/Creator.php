<?php
/**
 * Creates new planets and moons.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Creator.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_Game_Planet_Creator
{
	/**
	 * User who will get this planet.
	 *
	 * @var integer
	 */
	protected $userid = 0;

	/**
	 * If planet will be a moon.
	 *
	 * @var boolean
	 */
	protected $moon = 0;

	/**
	 * Moon formation percent.
	 *
	 * @var integer
	 */
	protected $percent = 0;

	/**
	 * Galaxy where planet or moon is positionated.
	 *
	 * @var integer
	 */
	protected $galaxy = 0;

	/**
	 * Solar system where planet or moon is positionated.
	 *
	 * @var integer
	 */
	protected $system = 0;

	/**
	 * Planet position in solar system.
	 *
	 * @var integer
	 */
	protected $position = 0;

	/**
	 * Planet name.
	 *
	 * @var string
	 */
	protected $name = "";

	/**
	 * Planet diameter size.
	 *
	 * @var integer
	 */
	protected $size = 0;

	/**
	 * Picture name.
	 *
	 * @var string
	 */
	protected $picture = "";

	/**
	 * Planet temperature.
	 *
	 * @var integer
	 */
	protected $temperature = 0;

	/**
	 * Id of created planet
	 *
	 * @var integer
	 */
	protected $planetid = 0;

	/**
	 * Starts creator.
	 *
	 * @param integer $userid    User id of this planet/moon
	 * @param integer $galaxy    Galaxy
	 * @param integer $system    System
	 * @param integer $position    Position
	 * @param bool|int $ismoon If planet is moon
	 * @param integer $percent    Moon formation percent
	 *
	 * @return Bengine_Game_Planet_Creator
	 */
	public function __construct($userid, $galaxy = null, $system = null, $position = null, $ismoon = 0, $percent = 0)
	{
		$this->userid = $userid;
		$this->moon = $ismoon;
		$this->percent = $percent;
		if($galaxy == null)
		{
			$this->setRandPos();
			$this->size = Core::getOptions()->get("HOME_PLANET_SIZE");
			$this->name = Core::getLanguage()->getItem("HOME_PLANET");
		}
		else
		{
			$this->name = Core::getLanguage()->getItem("COLONY");
			$this->galaxy = $galaxy;
			$this->system = $system;
			$this->position = $position;
			if($this->moon)
			{
				$this->setRandMoonSize();
			}
			else
			{
				$this->setRandSize();
			}
		}
		$this->setRandTemperature();
		if(!$this->moon) { $this->setPicture(); }
		$this->createPlanet();
		return;
	}

	/**
	 * Sets a random position for this planet.
	 *
	 * @return Bengine_Game_Planet_Creator
	 */
	protected function setRandPos()
	{
		do {
			$this->galaxy = mt_rand(1, Core::getOptions()->get("GALAXYS"));
			$this->system = mt_rand(1, Core::getOptions()->get("SYSTEMS"));
			$this->position = mt_rand(4, 12);
			$result = Core::getQuery()->select("galaxy", "planetid", "", "galaxy = '".$this->galaxy."' AND system = '".$this->system."' AND position = '".$this->position."'");
		} while($result->rowCount() > 0);
		$result->closeCursor();
		return $this;
	}

	/**
	 * Generates moon size.
	 *
	 * @return Bengine_Game_Planet_Creator
	 */
	protected function setRandMoonSize()
	{
		$min = pow($this->percent, 2) * 10 + 4000;
		$this->size = mt_rand($min, $min + 1000);
		return $this;
	}

	/**
	 * Generates temperature.
	 *
	 * @return Bengine_Game_Planet_Creator
	 */
	protected function setRandTemperature()
	{
		$meta = Game::getMeta();
		$min = $meta["config"]["planet"]["position"][$this->position]["temperature"]["min"];
		$max = $meta["config"]["planet"]["position"][$this->position]["temperature"]["max"];
		$first = mt_rand($min, $max);
		$this->temperature = mt_rand($first, $max);
		return $this;
	}

	/**
	 * Generates planet size.
	 *
	 * @return Bengine_Game_Planet_Creator
	 */
	protected function setRandSize()
	{
		$offrange = mt_rand(0, 100);
		$sizeType = ($offrange > 60) ? "offrange" : "normal";
		$meta = Game::getMeta();
		$min = $meta["config"]["planet"]["position"][$this->position]["size"][$sizeType]["min"];
		$max = $meta["config"]["planet"]["position"][$this->position]["size"][$sizeType]["max"];
		$this->size = mt_rand($min, $max);
		return $this;
	}

	/**
	 * Generates picture.
	 *
	 * @return Bengine_Game_Planet_Creator
	 */
	protected function setPicture()
	{
		$planetTypes = new Map();
		$meta = Game::getMeta();
		foreach($meta["config"]["planet"]["type"] as $name => $planetType)
		{
			if($this->position >= $planetType["from"] && $this->position <= $planetType["to"])
			{
				$planetTypes->push(array(
					"name" => $name,
					"number" => $planetType["number"]
				));
			}
		}
		$randomPlanet = $planetTypes->getRandomElement();
		$this->picture = sprintf("%s%02d", $randomPlanet["name"], mt_rand(1, $randomPlanet["number"]));
		return $this;
	}

	/**
	 * Write final planet informations into database.
	 *
	 * @return Bengine_Game_Planet_Creator
	 */
	protected function createPlanet()
	{
		Hook::event("PlanetCreatorSavePlanet", array($this));
		if($this->moon == 0)
		{
			$spec = array(
				"userid" => $this->userid,
				"planetname" => $this->name,
				"diameter" => $this->size,
				"picture" => $this->picture,
				"temperature" => $this->temperature,
				"last" => TIME,
				"metal" => Core::getOptions()->get("DEFAULT_METAL"),
				"silicon" => Core::getOptions()->get("DEFAULT_SILICON"),
				"hydrogen" => Core::getConfig()->get("DEFAULT_HYDROGEN"),
				"solar_satellite_prod" => 100
			);
			Core::getQuery()->insert("planet", $spec);
			$this->planetid = Core::getDB()->lastInsertId();
			$spec = array("galaxy" => $this->galaxy, "system" => $this->system, "position" => $this->position, "planetid" => $this->planetid);
			Core::getQuery()->insert("galaxy", $spec);
		}
		else
		{
			$spec = array(
				"userid" => $this->userid,
				"ismoon" => 1,
				"planetname" => Core::getLanguage()->getItem("MOON"),
				"diameter" => $this->size,
				"picture" => "mond",
				"temperature" => $this->temperature - mt_rand(15, 35), // TODO: variable value
				"last" => TIME,
				"metal" => 0, // TODO: variable value
				"silicon" => 0 // TODO: variable value
			);
			Core::getQuery()->insert("planet", $spec);
			$this->planetid = Core::getDB()->lastInsertId();
			Core::getQuery()->update("galaxy", array("moonid" => $this->planetid), "galaxy = '".$this->galaxy."' AND system = '".$this->system."' AND position = '".$this->position."'");
		}
		return $this;
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
	 * Returns the user id.
	 *
	 * @return integer
	 */
	public function getUserId()
	{
		return $this->userid;
	}

	/**
	 * Returns the coordinates of the new planet.
	 *
	 * @return array
	 */
	public function getPosition()
	{
		return array(
			"galaxy" => $this->galaxy,
			"system" => $this->system,
			"position" => $this->position
		);
	}
}
?>