<?php
/**
 * Planet collection.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Planet.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Game_Model_Collection_Planet extends Recipe_Model_Collection_Abstract
{
	/**
	 * Adds a galaxy filter.
	 *
	 * @param integer $galaxy
	 *
	 * @return Bengine_Game_Model_Collection_Planet
	 */
	public function addGalaxyFilter($galaxy)
	{
		$this->getSelect()->where(array("g" => "galaxy"), $galaxy);
		return $this;
	}

	/**
	 * Adds a solar system filter.
	 *
	 * @param integer $system
	 *
	 * @return Bengine_Game_Model_Collection_Planet
	 */
	public function addSystemFilter($system)
	{
		$this->getSelect()->where(array("g" => "system"), $system);
		return $this;
	}

	/**
	 * Adds a position filter.
	 *
	 * @param integer $position
	 *
	 * @return Bengine_Game_Model_Collection_Planet
	 */
	public function addPositionFilter($position)
	{
		$this->getSelect()->where(array("g" => "position"), $position);
		return $this;
	}

	/**
	 * @param integer $user
	 * @return Bengine_Game_Model_Collection_Planet
	 */
	public function addUserFilter($user)
	{
		$this->getSelect()->where(array("p" => "userid"), (int) $user);
		return $this;
	}
}
?>