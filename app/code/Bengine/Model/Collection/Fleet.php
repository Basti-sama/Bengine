<?php
/**
 * Fleet collection.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Fleet.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Model_Collection_Fleet extends Bengine_Model_Collection_Construction
{
	/**
	 * Adds a planet filter.
	 *
	 * @param integer|Bengine_Model_Planet
	 *
	 * @return Bengine_Model_Collection_Fleet
	 */
	public function addPlanetFilter($planet)
	{
		if($planet instanceof Bengine_Model_Planet)
		{
			$planet = $planet->getId();
		}
		$select = $this->getSelect();
		$select->join(
			array("u2s" => "unit2shipyard"),
			array("u2s" => "unitid", "c" => "buildingid")
		);
		$select->where(array("u2s" => "planetid"), $planet);
		$attrs = $select->getAttributes();
		$attrs["u2s"] = array("quantity", "planetid");
		$select->attributes($attrs);
		return $this;
	}
}
?>