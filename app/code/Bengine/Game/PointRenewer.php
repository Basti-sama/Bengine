<?php
/**
 * Recalcultes points.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: PointRenewer.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Game_PointRenewer
{
	/**
	 * Caclulates points of buildings.
	 *
	 * @param integer $planetid
	 *
	 * @return float	Points
	 */
	public static function getBuildingPoints($planetid)
	{
		$points = 0;
		$result = Core::getQuery()->select("building2planet b2p", array("b2p.level", "c.basic_metal", "c.basic_silicon", "c.basic_hydrogen", "c.charge_metal", "c.charge_silicon", "c.charge_hydrogen"), "LEFT JOIN ".PREFIX."construction c ON (c.buildingid = b2p.buildingid)", "b2p.planetid = '".$planetid."' AND (c.mode = '1' OR c.mode = '5')");
		while($row = Core::getDB()->fetch($result))
		{
			for($i = 1; $i <= $row["level"]; $i++)
			{
				if($row["basic_metal"] > 0)
				{
					$points += parseSimpleFormula($row["charge_metal"], $row["basic_metal"], $i);
				}
				if($row["basic_silicon"] > 0)
				{
					$points += parseSimpleFormula($row["charge_silicon"], $row["basic_silicon"], $i);
				}
				if($row["basic_hydrogen"] > 0)
				{
					$points += parseSimpleFormula($row["charge_hydrogen"], $row["basic_hydrogen"], $i);
				}
			}
		}
		Core::getDB()->free_result($result);
		return $points / 1000;
	}

	/**
	 * Caclulates research points.
	 *
	 * @param integer $userid
	 *
	 * @return integer	Points
	 */
	public static function getResearchPoints_r($userid)
	{
		$points = 0;
		$result = Core::getQuery()->select("research2user", array("SUM(level) AS points"), "", "userid = '".$userid."'");
		if($row = Core::getDB()->fetch($result))
		{
			$points = $row["points"];
		}
		Core::getDB()->free_result($result);
		return $points;
	}

	/**
	 * Caclulates points of research.
	 *
	 * @param integer $userid
	 *
	 * @return float	Points
	 */
	public static function getResearchPoints($userid)
	{
		$points = 0;
		$result = Core::getQuery()->select("research2user r2u", array("r2u.level", "c.basic_metal", "c.basic_silicon", "c.basic_hydrogen", "c.charge_metal", "c.charge_silicon", "c.charge_hydrogen"), "LEFT JOIN ".PREFIX."construction c ON (c.buildingid = r2u.buildingid)", "r2u.userid = '".$userid."' AND c.mode = '2'");
		while($row = Core::getDB()->fetch($result))
		{
			for($i = 1; $i <= $row["level"]; $i++)
			{
				if($row["basic_metal"] > 0)
				{
					$points += parseSimpleFormula($row["charge_metal"], $row["basic_metal"], $i);
				}
				if($row["basic_silicon"] > 0)
				{
					$points += parseSimpleFormula($row["charge_silicon"], $row["basic_silicon"], $i);
				}
				if($row["basic_hydrogen"] > 0)
				{
					$points += parseSimpleFormula($row["charge_hydrogen"], $row["basic_hydrogen"], $i);
				}
			}
		}
		Core::getDB()->free_result($result);
		return $points / 1000;
	}

	/**
	 * Caclulates points of fleet.
	 *
	 * @param integer $planetid
	 *
	 * @return float	Points
	 */
	public static function getFleetPoints($planetid)
	{
		$points = 0;
		$result = Core::getQuery()->select("unit2shipyard u2s", array("u2s.quantity", "c.basic_metal", "c.basic_silicon", "c.basic_hydrogen"), "LEFT JOIN ".PREFIX."construction c ON (c.buildingid = u2s.unitid)", "u2s.planetid = '".$planetid."' AND (c.mode = '3' OR c.mode = '4')");
		while($row = Core::getDB()->fetch($result))
		{
			$points += $row["quantity"] * $row["basic_metal"] + $row["quantity"] * $row["basic_silicon"] + $row["quantity"] * $row["basic_hydrogen"];
		}
		Core::getDB()->free_result($result);
		return $points / 1000;
	}

	/**
	 * Caclulates points of fleet from events.
	 *
	 * @param integer $userid
	 *
	 * @return float	Points
	 */
	public static function getFleetEventPoints($userid)
	{
		$points = 0;
		$result = Core::getQuery()->select("events", "data", "", "mode > '5' AND mode < '21' AND user = '".$userid."'");
		while($row = Core::getDB()->fetch($result))
		{
			$row["data"] = unserialize($row["data"]);
			if(!is_array($row["data"]["ships"]))
			{
				continue;
			}
			foreach($row["data"]["ships"] as $ship)
			{
				$_result = Core::getQuery()->select("construction", array("basic_metal", "basic_silicon", "basic_hydrogen"), "", "buildingid = '".$ship["id"]."'");
				$shipData = Core::getDB()->fetch($_result);
				Core::getDB()->free_result($_result);
				$points += ($shipData["basic_metal"] + $shipData["basic_silicon"] + $shipData["basic_hydrogen"]) * $ship["quantity"];
			}
		}
		Core::getDB()->free_result($result);
		return $points / 1000;
	}

	/**
	 * Caclulates fleet points.
	 *
	 * @param integer $planetid
	 *
	 * @return integer	Points
	 */
	public static function getFleetPoints_Fleet($planetid)
	{
		$points = 0;
		$result = Core::getQuery()->select("unit2shipyard u2s", array("SUM(u2s.quantity) AS points"), "LEFT JOIN ".PREFIX."construction c ON (c.buildingid = u2s.unitid)", "u2s.planetid = '".$planetid."' AND c.mode = '3'", "", "", "u2s.planetid");
		while($row = Core::getDB()->fetch($result))
		{
			$points = $row["points"];
		}
		Core::getDB()->free_result($result);
		return $points;
	}

	/**
	 * Caclulates fleet points from events.
	 *
	 * @param integer $userid
	 *
	 * @return integer	Fleet points
	 */
	public static function getFleetEvent_Fleet($userid)
	{
		$fpoints = 0;
		$result = Core::getQuery()->select("events", "data", "", "mode > '5' AND mode < '21' AND user = '".$userid."'");
		while($row = Core::getDB()->fetch($result))
		{
			$row["data"] = unserialize($row["data"]);
			if(!is_array($row["data"]["ships"]))
			{
				continue;
			}
			foreach($row["data"]["ships"] as $ship)
			{
				$fpoints += $ship["quantity"];
			}
		}
		Core::getDB()->free_result($result);
		return $fpoints;
	}
}
?>