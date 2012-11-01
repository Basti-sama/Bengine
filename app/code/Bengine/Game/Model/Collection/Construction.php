<?php
/**
 * Construction collection.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Construction.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Game_Model_Collection_Construction extends Recipe_Model_Collection_Abstract
{
	/**
	 * Adds a construction type filter.
	 *
	 * @param integer $type Type ID
	 * @param boolean $isMoon
	 * @param integer $moonType Moon type ID
	 * @return Bengine_Game_Model_Collection_Construction
	 */
	public function addTypeFilter($type, $isMoon = false, $moonType = null)
	{
		if($isMoon)
		{
			$this->getSelect()
				->where("(c.mode = ? AND c.allow_on_moon = 1)", (int) $type, "OR");
		}
		else
		{
			$this->getSelect()
				->where("c.mode = ?", (int) $type);
		}
		if($moonType)
		{
			$this->getSelect()
				->where("c.mode = ?", (int) $moonType, "OR");
		}
		return $this;
	}

	/**
	 * Adds a planet join.
	 *
	 * @param integer $planetId
	 * @return Bengine_Game_Model_Collection_Construction
	 */
	public function addPlanetJoin($planetId)
	{
		$planetId = (int) $planetId;
		$select = $this->getSelect();
		$select->join(array("b2p" => "building2planet"), "b2p.buildingid = c.buildingid AND b2p.planetid = {$planetId}");
		$attributes = $select->getAttributes();
		$attributes["b2p"] = array("level", "planetid", "prod_factor");
		$select->attributes($attributes);
		return $this;
	}

	/**
	 * Adds a user join.
	 *
	 * @param integer $userId
	 * @return Bengine_Game_Model_Collection_Construction
	 */
	public function addUserJoin($userId)
	{
		$userId = (int) $userId;
		$select = $this->getSelect();
		$select->join(array("r2u" => "research2user"), "r2u.buildingid = c.buildingid AND r2u.userid = {$userId}");
		$attributes = $select->getAttributes();
		$attributes["r2u"] = array("level");
		$select->attributes($attributes);
		return $this;
	}

	/**
	 * Adds a shipyard join.
	 *
	 * @param integer $planetId
	 * @return Bengine_Game_Model_Collection_Construction
	 */
	public function addShipyardJoin($planetId)
	{
		$planetId = (int) $planetId;
		$select = $this->getSelect();
		$select->join(array("u2s" => "unit2shipyard"), "u2s.unitid = c.buildingid AND u2s.planetid = {$planetId}");
		$attributes = $select->getAttributes();
		$attributes["u2s"] = array("quantity");
		$select->attributes($attributes);
		return $this;
	}

	/**
	 * Add display order.
	 *
	 * @param string $dir
	 * @return Bengine_Game_Model_Collection_Construction
	 */
	public function addDisplayOrder($dir = "ASC")
	{
		$this->getSelect()
			->order("display_order", $dir);
		return $this;
	}
}
?>