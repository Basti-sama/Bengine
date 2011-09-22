<?php
/**
 * Assault model.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Assault.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Model_Assault extends Recipe_Model_Abstract
{
	/**
	 * Initializes the object.
	 *
	 * @return Bengine_Model_Assault
	 */
	protected function init()
	{
		$this->setTableName("assault")
			->setModelName("assault")
			->setPrimaryKey("assaultid");
		return $this;
	}

	/**
	 * Coordinates of the planet.
	 *
	 * @param boolean	Link or simple string
	 * @param boolean	Replace session with wildcard
	 *
	 * @return string	Coordinates
	 */
	public function getCoords($link = true, $sidWildcard = false)
	{
		if($link)
		{
			return getCoordLink($this->get("galaxy"), $this->get("system"), $this->get("position"), $sidWildcard);
		}
		return $this->get("galaxy").":".$this->get("system").":".$this->get("position");
	}
}
?>