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
	 * @param integer	User id of this planet/moon
	 * @param integer	Galaxy
	 * @param integer	System
	 * @param integer	Position
	 * @param boolean	If planet is moon
	 * @param integer	Moon formation percent
	 *
	 * @return void
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
		} while(Core::getDB()->num_rows($result) > 0);
		Core::getDB()->free_result($result);
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
		$config = $this->getXMLConfig("PlanetTemperature");
		$min = $config->getChildren("pos_".$this->position)->getInteger("min");
		$max = $config->getChildren("pos_".$this->position)->getInteger("max");
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
		$config = $this->getXMLConfig("PlanetSize");
		$config = $config->getChildren("pos_".$this->position)->getChildren($sizeType);
		$min = $config->getInteger("min");
		$max = $config->getInteger("max");
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
		$planetenArt = new Map();
		$config = $this->getXMLConfig("PlanetPictures");
		foreach($config as $planetType)
		{
			$from = (int) $planetType->getAttribute("from");
			$to = (int) $planetType->getAttribute("to");
			if($this->position >= $from && $this->position <= $to)
			{
				$planetenArt->push(array(
					"name" => $planetType->getName(),
					"number" => $planetType->getInteger()
				));
			}
		}
		$randomPlanet = $planetenArt->getRandomElement();
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
			$atts = array("userid", "planetname", "diameter", "picture", "temperature", "last", "metal", "silicon", "hydrogen", "solar_satellite_prod");
			$vals = array($this->userid, $this->name, $this->size, $this->picture, $this->temperature, TIME, Core::getOptions()->get("DEFAULT_METAL"), Core::getOptions()->get("DEFAULT_SILICON"), Core::getConfig()->get("DEFAULT_HYDROGEN"), 100);
			Core::getQuery()->insert("planet", $atts, $vals);
			$this->planetid = Core::getDB()->insert_id();
			$atts = array("galaxy", "system", "position", "planetid");
			$vals = array($this->galaxy, $this->system, $this->position, $this->planetid);
			Core::getQuery()->insert("galaxy", $atts, $vals);
		}
		else
		{
			$atts = array("userid", "ismoon", "planetname", "diameter", "picture", "temperature", "last", "metal", "silicon");
			$vals = array($this->userid, 1, Core::getLanguage()->getItem("MOON"), $this->size, "mond", $this->temperature - mt_rand(15, 35), TIME, 0, 0);
			Core::getQuery()->insert("planet", $atts, $vals);
			$this->planetid = Core::getDB()->insert_id();
			Core::getQuery()->update("galaxy", "moonid", $this->planetid, "galaxy = '".$this->galaxy."' AND system = '".$this->system."' AND position = '".$this->position."'");
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

	/**
	 * Returns the XML config data.
	 *
	 * @param string	Config name
	 *
	 * @return XMLObj
	 */
	protected function getXMLConfig($name)
	{
		$config = new XML(APP_ROOT_DIR."etc/".$name.".xml");
		return $config->get();
	}
}
?>