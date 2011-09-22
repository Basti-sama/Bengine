<?php
/**
 * Debris collection.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Debris.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Model_Collection_Debris extends Recipe_Model_Collection_Abstract
{
	/**
	 * Adds a galaxy filter.
	 *
	 * @param integer	Galaxy
	 *
	 * @return Bengine_Model_Collection_Debris
	 */
	public function addGalaxyFilter($galaxy)
	{
		$this->getSelect()->where(array("g" => "galaxy"), $galaxy);
		return $this;
	}

	/**
	 * Adds a solar system filter.
	 *
	 * @param integer	Solar system
	 *
	 * @return Bengine_Model_Collection_Debris
	 */
	public function addSystemFilter($system)
	{
		$this->getSelect()->where(array("g" => "system"), $system);
		return $this;
	}

	/**
	 * Adds a position filter.
	 *
	 * @param integer	Position
	 *
	 * @return Bengine_Model_Collection_Debris
	 */
	public function addPositionFilter($position)
	{
		$this->getSelect()->where(array("g" => "position"), $position);
		return $this;
	}
}
?>