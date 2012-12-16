<?php
/**
 * Represents an assault participant.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Participant.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_Game_Assault_Participant
{
	/**
	 * Defender mode.
	 *
	 * @var integer
	 */
	const DEFENDER_MODE = 0;

	/**
	 * Attacker mode.
	 *
	 * @var integer
	 */
	const ATTACKER_MODE = 1;

	/**
	 * Assault id.
	 *
	 * @var integer
	 */
	protected $assaultid = 0;

	/**
	 * Event id (When defender).
	 *
	 * @var integer
	 */
	protected $eventid = 0;

	/**
	 * User id.
	 *
	 * @var integer
	 */
	protected $userid = 0;

	/**
	 * Participant id (0: defender, 1: attacker).
	 *
	 * @var integer
	 */
	protected $mode = 0;

	/**
	 * Source planet
	 *
	 * @var integer
	 */
	protected $planetid = 0;

	/**
	 * Assault start time.
	 *
	 * @var integer
	 */
	protected $time = 0;

	/**
	 * Event data field.
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * The participant's ships.
	 *
	 * @var array
	 */
	protected $ships = array();

	/**
	 * Participant id.
	 *
	 * @var integer
	 */
	protected $participantid = 0;

	/**
	 * Planet location of the combat.
	 *
	 * @var integer
	 */
	protected $location = 0;

	/**
	 * Creates a new participant.
	 *
	 * @return \Bengine_Game_Assault_Participant
	 */
	public function __construct()
	{

	}

	/**
	 * Sets the data and ships.
	 *
	 * @param array $data
	 *
	 * @return Bengine_Game_Assault_Participant
	 */
	public function setData(array $data)
	{
		$this->ships = $data["ships"];
		$data["ships"] = null;
		$this->data = $data;
		return $this;
	}

	/**
	 * Retuns the data.
	 *
	 * @param string $param	Data parameter [optional]
	 *
	 * @return mixed	Data value or false
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
	 * Returns the ships.
	 *
	 * @return array
	 */
	public function getShips()
	{
		return $this->ships;
	}

	/**
	 * Saves the participant data into database.
	 *
	 * @return Bengine_Game_Assault_Participant
	 */
	public function setDBEntry()
	{
		Hook::event("SetAssaultShips", array($this->assaultid, $this->userid, &$this->ships));
		$preloaded = $this->data["metal"] + $this->data["silicon"] + $this->data["hydrogen"];
		if(isset($this->data["attack"]) && isset($this->data["shell"]) && isset($this->data["shield"]))
		{
			$data = "attack:" . $this->data["attack"];
			$data .= ",shell:" . $this->data["shell"];
			$data .= ",shield:" . $this->data["shield"];
			$data .= ",username:" . $this->data["username"];
			$data .= ",galaxy:" . $this->data["galaxy"];
			$data .= ",system:" . $this->data["system"];
			$data .= ",position:" . $this->data["position"];
		}
		else
		{
			$data = "";
		}
		$spec = array(
			"assaultid" => $this->assaultid,
			"userid" => $this->userid,
			"planetid" => $this->planetid,
			"mode" => $this->mode,
			"consumption" => $this->data["consumption"],
			"preloaded" => $preloaded,
			"data" => $data,
		);
		Core::getQuery()->insert("assaultparticipant", $spec);
		$this->participantid = Core::getDB()->lastInsertId();
		foreach($this->ships as $ship)
		{
			$spec = array(
				"assaultid" => $this->assaultid,
				"participantid" => $this->participantid,
				"userid" => $this->userid,
				"unitid" => $ship["id"],
				"quantity" => $ship["quantity"],
				"mode" => $this->mode,
			);
			Core::getQuery()->insert("fleet2assault", $spec);
		}
		return $this;
	}

	/**
	 * Makes final calculations and sends the fleet back.
	 *
	 * @param integer $result	Assault result
	 *
	 * @return Bengine_Game_Assault_Participant
	 */
	public function finish($result)
	{
		// Update units after battle.
		if((($result == 0 || $result == 1) && $this->mode == 1) || (($result == 1 || $result == 2) && $this->mode == 0))
		{
			$this->data["ships"] = array();
			$metal = 0; $silicon = 0; $hydrogen = 0;
			$result = Core::getQuery()->select("assaultparticipant ap", array("f2a.unitid", "f2a.quantity", "b.name", "ap.haul_metal", "ap.haul_silicon", "ap.haul_hydrogen"), "LEFT JOIN ".PREFIX."fleet2assault f2a ON (ap.participantid = f2a.participantid) LEFT JOIN ".PREFIX."construction b ON (b.buildingid = f2a.unitid)", Core::getDB()->quoteInto("f2a.participantid = ?", $this->participantid));
			foreach($result->fetchAll() as $row)
			{
				if($row["quantity"] > 0)
				{
					$id = $row["unitid"];
					$this->data["ships"][$id]["id"] = $row["unitid"];
					$this->data["ships"][$id]["quantity"] = $row["quantity"];
					$this->data["ships"][$id]["name"] = $row["name"];
				}
				$metal = $row["haul_metal"];
				$silicon = $row["haul_silicon"];
				$hydrogen = $row["haul_hydrogen"];
			}
			$result->closeCursor();
			$this->data["metal"] += $metal;
			$this->data["silicon"] += $silicon;
			$this->data["hydrogen"] += $hydrogen;
			$this->data["oldmode"] = 10;
			if(count($this->data["ships"]) > 0 && $this->mode == 1)
			{
				$event = Game::getModel("game/event");
				$event->setMode(20)
					->setTime($this->data["time"] + $this->time)
					->setUserid($this->userid)
					->setPlanetid($this->location)
					->setDestination($this->planetid)
					->setData($this->data);
				$event->save();
			}
			else if($this->mode == 0 && $this->eventid > 0)
			{
				if(count($this->data["ships"]) > 0)
				{
					Core::getQuery()->update("events", array("data" => serialize($this->data)), "eventid = ?", array($this->eventid));
				}
				else
				{
					Core::getQuery()->delete("events", "eventid = ?", null, null, array($this->eventid));
				}
			}
		}
		return $this;
	}

	/**
	 * Setter-method for event id.
	 *
	 * @param integer
	 *
	 * @return Bengine_Game_Assault_Participant
	 */
	public function setEventId($eventid)
	{
		$this->eventid = $eventid;
		return $this;
	}

	/**
	 * Setter-method for combat location.
	 *
	 * @param integer $planetid
	 *
	 * @return Bengine_Game_Assault_Participant
	 */
	public function setLocation($planetid)
	{
		$this->location = $planetid;
		return $this;
	}

	/**
	 * Getter-method for the participant id.
	 *
	 * @return integer
	 */
	public function getParticipantId()
	{
		return $this->participantid;
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
	 * Returns the mode.
	 *
	 * @return integer
	 */
	public function getMode()
	{
		return $this->mode;
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
	 * Returns the planet id.
	 *
	 * @return integer
	 */
	public function getPlanetId()
	{
		return $this->planetid;
	}

	/**
	 * Returns the time.
	 *
	 * @return integer
	 */
	public function getTime()
	{
		return $this->time;
	}

	/**
	 * Sets the assault id.
	 *
	 * @param integer $assaultid
	 *
	 * @return Bengine_Game_Assault_Participant
	 */
	public function setAssaultId($assaultid)
	{
		$this->assaultid = $assaultid;
		return $this;
	}

	/**
	 * Sets the mode.
	 *
	 * @param integer
	 *
	 * @return Bengine_Game_Assault_Participant
	 */
	public function setMode($mode)
	{
		$this->mode = $mode;
		return $this;
	}

	/**
	 * Sets the user id.
	 *
	 * @param integer
	 *
	 * @return Bengine_Game_Assault_Participant
	 */
	public function setUserId($userid)
	{
		$this->userid = $userid;
		return $this;
	}

	/**
	 * Sets the planet id.
	 *
	 * @param integer
	 *
	 * @return Bengine_Game_Assault_Participant
	 */
	public function setPlanetId($planetid)
	{
		$this->planetid = $planetid;
		return $this;
	}

	/**
	 * Sets the time.
	 *
	 * @param integer
	 *
	 * @return Bengine_Game_Assault_Participant
	 */
	public function setTime($time)
	{
		$this->time = $time;
		return $this;
	}
}
?>