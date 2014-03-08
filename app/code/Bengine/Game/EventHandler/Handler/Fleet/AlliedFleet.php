<?php
/**
 * Handler for allied fleets in an alliance attack.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: AlliedFleet.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Game_EventHandler_Handler_Fleet_AlliedFleet extends Bengine_Game_EventHandler_Handler_Fleet_Abstract
{
	/**
	 * Holds the formation data.
	 *
	 * @var array|bool
	 */
	protected $_formations = null;

	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine/EventHandler/Handler/Bengine_Game_EventHandler_Handler_Abstract#_execute($event, $data)
	 */
	protected function _execute(Bengine_Game_Model_Event $event, array $data)
	{
		Hook::event("EhAlliedFleet", array($event, &$data, $this));
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine/EventHandler/Handler/Bengine_Game_EventHandler_Handler_Abstract#_add($event, $data)
	 */
	protected function _add(Bengine_Game_Model_Event $event, array $data)
	{
		$event->setParentId($data["alliance_attack"]["eventid"]);
		$this->prepareFleet($data);
		$time = $event->getTime();
		if($time > $data["alliance_attack"]["time"])
		{
			Core::getQuery()->update("events", array("time" => $time), "eventid = ? OR parent_id = ?", array($data["alliance_attack"]["eventid"], $data["alliance_attack"]["eventid"]));
			Core::getQuery()->update("attack_formation", array("time" => $time), "eventid = ?", array($data["alliance_attack"]["eventid"]));
		}
		else
		{
			$event->setTime($data["alliance_attack"]["time"]);
		}
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine/EventHandler/Handler/Bengine_Game_EventHandler_Handler_Abstract#_remove($event, $data)
	 */
	protected function _remove(Bengine_Game_Model_Event $event, array $data)
	{
		$this->sendBack($data);
		return $this;
	}

	/**
	 * Checks the event for validation.
	 *
	 * @return boolean
	 */
	protected function _isValid()
	{
		$formations = $this->getSpecialData();
		if(!empty($this->_target["userid"]) &&
			$this->_target["userid"] != Core::getUser()->get("userid") &&
			$this->_targetType != "tf" &&
			!$this->_target["umode"] &&
			!$this->isNewbieProtected() &&
			!Game::attackingStoppageEnabled() &&
			is_array($formations))
		{
			return true;
		}
		return false;
	}

	/**
	 * Returns formation data.
	 *
	 * @return array|bool
	 */
	public function getSpecialData()
	{
		if($this->_formations === null)
		{
			$this->_formations = false;
			if(!empty($this->_target))
			{
				$joins  = "LEFT JOIN ".PREFIX."events e ON (e.eventid = fi.eventid)";
				$joins .= "LEFT JOIN ".PREFIX."attack_formation af ON (e.eventid = af.eventid)";
				$select = array("af.eventid", "af.time");
				$where = Core::getDB()->quoteInto("fi.userid = ? AND af.time > ? AND e.destination = ?", array(Core::getUser()->get("userid"), TIME, $this->_target["planetid"]));
				if(!empty($this->_target["formation"]))
				{
					$where .= Core::getDB()->quoteInto(" AND e.eventid = ?", $this->_target["formation"]);
				}
				$result = Core::getQuery()->select("formation_invitation fi", $select, $joins, $where);
				if($row = $result->fetchRow())
				{
					$this->_formations = $row;
				}
				$result->closeCursor();
			}
		}
		return $this->_formations;
	}
}
?>