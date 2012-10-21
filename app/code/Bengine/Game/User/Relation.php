<?php
/**
 * This class loads all relations of an user and his alliance.
 * Afterwards we can check the relations to another user.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Relation.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_Game_User_Relation
{
	/**
	 * The user's id.
	 *
	 * @var integer
	 */
	protected $userid = 0;

	/**
	 * The user's alliance.
	 *
	 * @var integer
	 */
	protected $aid = 0;

	/**
	 * Available alliance relations.
	 *
	 * @var Map
	 */
	protected $alliances = null;

	/**
	 * Avaiable player relations (from buddylist).
	 *
	 * @var Map
	 */
	protected $players = null;

	/**
	 * Ensures that we do not load the alliance relations twice.
	 *
	 * @var boolean
	 */
	protected $alliancesLoaded = false;

	/**
	 * Ensures that we do not load the player relations twice.
	 *
	 * @var boolean
	 */
	protected $playersLoaded = false;

	/**
	 * Holds CSS classes to format username or alliance.
	 *
	 * @var array
	 */
	protected $cssClasses = array(
		"friend"	=> "friend",
		"ally"		=> "alliance",
		"self"		=> "ownPosition"
	);

	/**
	 * Creates a new relation object.
	 *
	 * @param integer	User id
	 * @param integer	Alliance id
	 *
	 * @return void
	 */
	public function __construct($userid, $aid = false)
	{
		$this->userid = $userid;
		$this->aid = $aid;
		$this->alliances = new Map();
		$this->players = new Map();
		return;
	}

	/**
	 * Checks is the users has positive relation.
	 *
	 * @param integer	User id
	 * @param integer	Alliance id
	 *
	 * @return boolean
	 */
	public function hasRelation($userid, $aid = false)
	{
		$alliance = $this->getAllyRelation($aid);
		$friend = $this->getPlayerRelation($userid);

		if(!($alliance instanceof stdClass))
		{
			$alliance = new stdClass;
			$alliance->acs = false;
		}
		if($alliance->acs || $friend || (!empty($aid) && $aid == $this->aid))
		{
			return true;
		}
		return false;
	}

	/**
	 * Checks an relation to the indicated alliance.
	 *
	 * @param integer	Alliance id to check
	 *
	 * @return mixed	The relation mode or false
	 */
	protected function getAllyRelation($aid = false)
	{
		if(!$aid || !$this->aid)
		{
			return false;
		}

		$this->loadAllianceRelations();

		if($this->alliances->size() > 0 && $this->alliances->exists($aid))
		{
			return $this->alliances->get($aid);
		}
		return false;
	}

	/**
	 * Loads the alliance relations.
	 *
	 * @return Bengine_Game_User_Relation
	 */
	protected function loadAllianceRelations()
	{
		if($this->alliancesLoaded)
		{
			return $this;
		}

		$select = array("ar.rel1", "ar.rel2", "ar.mode", "rt.acs", "rt.name", "rt.css");
		$joins  = "LEFT JOIN ".PREFIX."ally_relationship_type rt ON (rt.type_id = ar.mode)";
		$where  = "(ar.rel1 = '".$this->aid."' OR ar.rel2 = '".$this->aid."')";
		$result = Core::getQuery()->select("ally_relationships ar", $select, $joins, $where);
		while($row = Core::getDB()->fetch($result))
		{
			$rel = ($row["rel1"] == $this->aid) ? $row["rel2"] : $row["rel1"];
			$data = new stdClass;
			$data->mode = $row["mode"];
			$data->css = $row["css"];
			$data->name = $row["name"];
			$data->acs = $row["acs"];
			$this->alliances->set($rel, $data);
		}
		Core::getDB()->free_result($result);
		$this->alliancesLoaded = true;
		Hook::event("AllianceRelationsLoaded", array(&$this->alliances));
		return $this;
	}

	/**
	 * Checks an relation to the indicated user.
	 *
	 * @param integer	Userid
	 *
	 * @return boolean	True if a relation exists, false if not
	 */
	protected function getPlayerRelation($userid)
	{
		if(!$userid || !$this->userid)
		{
			return false;
		}
		$this->loadPlayerRelations();
		if($this->players->size() > 0 && $this->players->contains($userid))
		{
			return true;
		}
		return false;
	}

	/**
	 * Loads the player relations (from buddylist).
	 *
	 * @return Bengine_Game_User_Relation
	 */
	protected function loadPlayerRelations()
	{
		if($this->playersLoaded)
		{
			return $this;
		}

		$result = Core::getQuery()->select("buddylist", array("friend1", "friend2"), "", "accepted = '1' AND (friend1 = '".$this->userid."' OR friend2 = '".$this->userid."')");
		while($row = Core::getDB()->fetch($result))
		{
			$rel = ($row["friend1"] == $this->userid) ? $row["friend2"] : $row["friend1"];
			$this->players->push($rel);
		}
		Core::getDB()->free_result($result);
		$this->playersLoaded = true;
		Hook::event("PlayerRelationsLoaded", array(&$this->players));
		return $this;
	}

	/**
	 * Fetches the alliance relations and returns the CSS class.
	 *
	 * @param integer	Alliance id
	 *
	 * @return string	CSS class to format the name
	 */
	public function getAllyRelationClass($aid = false)
	{
		if($aid == $this->aid && $aid !== false)
		{
			return $this->cssClasses["ally"];
		}
		$relations = $this->getAllyRelation($aid);
		return ($relations instanceof stdClass) ? $relations->css : "";
	}

	/**
	 * Fetches the player relations and returns the CSS class.
	 *
	 * @param integer	User id
	 *
	 * @return string	CSS class to format the name
	 */
	public function getPlayerRelationClass($userid, $aid = false)
	{
		if($this->userid == $userid)
		{
			return $this->cssClasses["self"];
		}

		if($this->getPlayerRelation($userid))
		{
			return $this->cssClasses["friend"];
		}
		if(!$aid || !$this->aid)
		{
			return "";
		}
		if($this->aid == $aid)
		{
			return $this->cssClasses["ally"];
		}
		$relations = $this->getAllyRelation($aid);
		return ($relations instanceof stdClass) ? $relations->css : "";
	}
}
?>