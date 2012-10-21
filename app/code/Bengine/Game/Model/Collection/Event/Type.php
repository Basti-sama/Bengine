<?php
/**
 * Event type collection.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Type.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Game_Model_Collection_Event_Type extends Recipe_Model_Collection_Abstract
{
	/**
	 * Adds a base type filter.
	 *
	 * @param string	fleet or construct
	 *
	 * @return Bengine_Game_Model_Collection_Event_Type
	 */
	public function addBaseTypeFilter($typeName)
	{
		$this->getSelect()->where(array("main_table" => "base_type"), $typeName);
		return $this;
	}
}
?>