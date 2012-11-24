<?php
/**
 * Handler for recycling a debris.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Recycling.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_Game_EventHandler_Handler_Fleet_Recycling extends Bengine_Game_EventHandler_Handler_Fleet_Abstract
{
	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine/EventHandler/Handler/Bengine_Game_EventHandler_Handler_Abstract#_execute($event, $data)
	 */
	protected function _execute(Bengine_Game_Model_Event $event, array $data)
	{
		Hook::event("EhRecycling", array($event, &$data, $this));
		$result = Core::getQuery()->select("galaxy", array("metal", "silicon"), "", "galaxy = '".$data["galaxy"]."' AND system = '".$data["system"]."' AND position = '".$data["position"]."'");
		if($_row = $result->fetchRow())
		{
			$result->closeCursor();
			$capacityMetal = (int) (($data["capacity"] * 66.7) / 100);
			$capacitySilicon = (int) (($data["capacity"] * 33.3) / 100);

			$data["debrismetal"] = $_row["metal"];
			$data["debrissilicon"] = $_row["silicon"];

			$capacitySilicon += ($capacityMetal > $_row["metal"]) ? ($capacityMetal - $_row["metal"]) : 0;
			$capacityMetal += ($capacitySilicon > $_row["silicon"]) ? ($capacitySilicon - $_row["silicon"]) : 0;

			$data["metal"] += min($capacityMetal, $_row["metal"]);
			$restMetal = $_row["metal"] - min($capacityMetal, $_row["metal"]);

			$data["silicon"] += min($capacitySilicon, $_row["silicon"]);
			$restSilicon = $_row["silicon"] - min($capacitySilicon, $_row["silicon"]);

			$data["recycledmetal"] = abs($_row["metal"] - $restMetal);
			$data["recycledsilicon"] = abs($_row["silicon"] - $restSilicon);

			if($_row["silicon"] != 0 || $_row["metal"] != 0)
			{
				Core::getQuery()->update("galaxy", array("metal" => $restMetal, "silicon" => $restSilicon), "galaxy = '".$data["galaxy"]."' AND system = '".$data["system"]."' AND position = '".$data["position"]."'");
			}
		}
		new Bengine_Game_AutoMsg($event["mode"], $event["userid"], $event["time"], $data);
		$this->sendBack($data, $data["time"] + $event["time"]);
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine/EventHandler/Handler/Bengine_Game_EventHandler_Handler_Abstract#_add($event, $data)
	 */
	protected function _add(Bengine_Game_Model_Event $event, array $data)
	{
		$this->prepareFleet($data);
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
		if($this->_targetType == "tf" && isset($this->_ships[37]) && !empty($this->_target["planetid"]))
		{
			return true;
		}
		return false;
	}
}
?>