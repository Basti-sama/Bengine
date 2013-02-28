<?php
/**
 * Fleet resource model.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Fleet.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Game_Model_Resource_Fleet extends Bengine_Game_Model_Resource_Construction
{
	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine/Model/Resource/Bengine_Game_Model_Resource_Construction#getSelect()
	 */
	public function getSelect()
	{
		$select = parent::getSelect();
		$select->join(array("s" => "ship_datasheet"), array("s" => "unitid", "c" => "buildingid"));
		return $select;
	}

	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine/Model/Resource/Bengine_Game_Model_Resource_Construction#_getAttributes()
	 */
	public function getAttributes()
	{
		$attrs = parent::getAttributes();
		$attrs["s"] = array(
			"unitid", "capicity", "speed", "consume", "attack", "shield"
		);
		return $attrs;
	}
}