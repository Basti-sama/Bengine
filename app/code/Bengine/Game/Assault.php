<?php
/**
 * This class handles combats by starting the Java-App.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Assault.php 41 2011-07-24 11:01:31Z secretchampion $
 */

class Bengine_Game_Assault
{
	/**
	 * Assault location (Planet id).
	 *
	 * @var integer
	 */
	protected $location = 0;

	/**
	 * User id of the planet owner.
	 *
	 * @var integer
	 */
	protected $owner = 0;

	/**
	 * The assault id.
	 *
	 * @var integer
	 */
	protected $assaultid = 0;

	/**
	 * Holds a list of all attackers and defenders.
	 *
	 * @var Map
	 */
	protected $attackers = null, $defenders = null;

	/**
	 * The assault results.
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * Creates a new Assault Object.
	 *
	 * @param integer $location    Assault location
	 * @param integer $owner    Owner of the attacked planet
	 *
	 * @return Bengine_Game_Assault
	 */
	public function __construct($location = null, $owner = null)
	{
		$this->attackers = new Map();
		$this->defenders = new Map();
		$this->location = $location;
		$this->owner = $owner;

		// Create a new assault in the database.
		Core::getQuery()->insert("assault", array("planetid", "time"), array($this->location, TIME));
		$this->assaultid = Core::getDB()->insert_id();

		// If the location is a planet, we have to update the ressource production.
		if(!is_null($this->location))
		{
			$planet = new Bengine_Game_Planet($this->location, $this->owner, false);
			$planet->getProduction()->addProd();
			$this->loadDefenders();
		}
		return $this;
	}

	/**
	 * Adds a new participant.
	 *
	 * @param integer $mode		Participant mode (0: defender, 1: attacker)
	 * @param integer $userid
	 * @param integer $planetid
	 * @param integer $time
	 * @param array $data
	 *
	 * @return Bengine_Game_Assault
	 */
	public function addParticipant($mode, $userid, $planetid, $time, array $data)
	{
		Hook::event("AssaultAddParticipant", array($this, $mode, $userid, $planetid, $time, &$data));
		$Participant = new Bengine_Game_Assault_Participant();
		$Participant->setAssaultId($this->assaultid)
					->setUserId($userid)
					->setMode($mode)
					->setMode($mode)
					->setPlanetId($planetid)
					->setTime($time)
					->setData($data)
					->setLocation($this->location)
					->setDBEntry();
		if($mode === 1)
		{
			$this->attackers->push($Participant);
		}
		else
		{
			$this->defenders->push($Participant);
		}
		return $this;
	}

	/**
	 * Loads the ships of the defender including the
	 * participants halting at the planet.
	 *
	 * @return Bengine_Game_Assault
	 */
	protected function loadDefenders()
	{
		Hook::event("AssaultLoadDefender", array($this));
		Core::getQuery()->insert("assaultparticipant", array("assaultid", "userid", "planetid", "mode"), array($this->assaultid, $this->owner, $this->location, 0));
		$participantid = Core::getDB()->insert_id();
		$joins = "LEFT JOIN ".PREFIX."planet p ON (p.planetid = u2s.planetid)";
		$result = Core::getQuery()->select("unit2shipyard u2s", array("u2s.unitid", "u2s.quantity"), $joins, "u2s.planetid = '".$this->location."'");
		while($row = Core::getDB()->fetch($result))
		{
			Core::getQuery()->insert("fleet2assault", array("assaultid", "participantid", "userid", "unitid", "quantity", "mode"), array($this->assaultid, $participantid, $this->owner, $row["unitid"], $row["quantity"], 0));
		}
		Core::getDB()->free_result($result);

		// Get holding fleets.
		$result = Core::getQuery()->select("events", array("eventid", "user AS userid", "planetid", "time", "data"), "", "mode = '17' AND destination = '".$this->location."'");
		while($row = Core::getDB()->fetch($result))
		{
			$participant = new Bengine_Game_Assault_Participant();
			$participant->setMode(Bengine_Game_Assault_Participant::DEFENDER_MODE)
						->setAssaultId($this->assaultid)
						->setUserId($row["userid"])
						->setPlanetId($row["planetid"])
						->setTime($row["time"])
						->setData(unserialize($row["data"]))
						->setEventId($row["eventid"])
						->setDBEntry();
			$this->defenders->push($participant);
		}
		Core::getDB()->free_result($result);
		return $this;
	}

	/**
	 * Starts the assault.
	 *
	 * @param int $galaxy
	 * @param int $system
	 * @param int $position
	 * @return Bengine_Game_Assault
	 */
	public function startAssault($galaxy = 0, $system = 0, $position = 0)
	{
		Hook::event("StartAssaultFirst", array($this, $this->owner, $galaxy, $system, $position));

		// Include database access
		$database = array();
		$cs = array();
		require(APP_ROOT_DIR."etc/Config.inc.php");

		// Run java
		$jrePath = $cs["jre"];
		$jarPath = APP_ROOT_DIR."app/Assault.jar";
		$cmd = $jrePath.' -jar "'.$jarPath.'" "'.$cs["host"].'" "'.$cs["user"].'" "'.$cs["userpw"].'" "'.$database["databasename"].'" "'.$database["tableprefix"].'" '.escapeshellarg($this->assaultid).' 2>&1';
		$output = "";
		$commandResult = null;
		exec($cmd, $output, $commandResult);

		$result = Core::getQuery()->select("assault", array("result", "moonchance", "moon", "accomplished", "lostunits_defender"), "", "assaultid = '".$this->assaultid."'");
		$row = Core::getDB()->fetch($result);
		Core::getDB()->free_result($result);
		$this->data = $row;

		if($commandResult !== 0 || !$row["accomplished"])
		{
			$output = implode("<br/>", $output);
			Logger::addMessage("Sorry, could not start battle <strong>".$this->assaultid."</strong>:<br/>".$output);
		}
		Hook::event("StartAssaultLast", array($this, &$row, $this->owner));

		if($row["moon"])
		{
			new Bengine_Game_Planet_Creator($this->owner, $galaxy, $system, $position, 1, $row["moonchance"]);
		}
		return $this;
	}

	/**
	 * Finishs the assault.
	 *
	 * @throws Recipe_Exception_Generic
	 * @return Bengine_Game_Assault
	 */
	public function finish()
	{
		if(!isset($this->data["accomplished"]))
		{
			throw new Recipe_Exception_Generic("Trying to finish an unaccomplished combat.");
		}
		while($this->attackers->next())
		{
			$participant = $this->attackers->current();
			if($participant instanceof Bengine_Game_Assault_Participant)
			{
				$participant->finish($this->data["result"]);
			}
		}
		while($this->defenders->next())
		{
			$participant = $this->defenders->current();
			if($participant instanceof Bengine_Game_Assault_Participant)
			{
				$participant->finish($this->data["result"]);
			}
		}
		$this->updateMainDefender($this->data["lostunits_defender"]);
		return $this;
	}

	/**
	 * Updates units after the combat for the main defender.
	 *
	 * @param integer $lostUnits	Total number of lost units
	 *
	 * @return Bengine_Game_Assault
	 */
	public function updateMainDefender($lostUnits)
	{
		if($lostUnits > 0 && !is_null($this->owner))
		{
			$result = Core::getQuery()->select("fleet2assault", array("unitid", "quantity"), "", "userid = '".$this->owner."' AND assaultid = '".$this->assaultid."'");
			while($row = Core::getDB()->fetch($result))
			{
				if($row["quantity"] > 0)
				{
					Core::getQuery()->update("unit2shipyard", "quantity", $row["quantity"], "unitid = '".$row["unitid"]."' AND planetid = '".$this->location."'");
				}
				else
				{
					Core::getQuery()->delete("unit2shipyard", "unitid = '".$row["unitid"]."' AND planetid = '".$this->location."'");
				}
			}
			Core::getDB()->free_result($result);
		}
		return $this;
	}

	/**
	 * Returns the assault id.
	 *
	 * @return integer
	 */
	public function getAssaultId()
	{
		return $this->assaultid;
	}

	/**
	 * Returns the assault location.
	 *
	 * @return integer
	 */
	public function getLocation()
	{
		return $this->location;
	}

	/**
	 * Returns the attackers.
	 *
	 * @return Map
	 */
	public function getAttackers()
	{
		return $this->attackers;
	}

	/**
	 * Returns the defenders.
	 *
	 * @return Map
	 */
	public function getDefenders()
	{
		return $this->defenders;
	}

	/**
	 * Returns the assault result data.
	 *
	 * @param string $param	Data parameter [optional]
	 *
	 * @return mixed	Data value or false
	 */
	public function getData($param)
	{
		if(is_null($param))
		{
			return $this->data;
		}
		return (isset($this->data[$param])) ? $this->data[$param] : false;
	}
}
?>