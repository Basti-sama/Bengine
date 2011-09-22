<?php
/**
 * Message resource model.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Message.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Model_Resource_Message extends Recipe_Model_Resource_Abstract
{
	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Database/Recipe_Database_Table#init()
	 */
	protected function init()
	{
		$this->setMainTable(array("m" => "message"));
		return parent::init();
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Database/Recipe_Database_Table#getSelect()
	 */
	public function getSelect()
	{
		$select = parent::getSelect();
		$select->join(array("u" => "user"), array("u" => "userid", "m" => "sender"));
		$select->join(array("g" => "galaxy"), array("g" => "planetid", "u" => "hp"));
		$select->attributes(array(
			"m.*",
			"u" => array("username"),
			"g" => array("galaxy", "system", "position")
		));
		return $select;
	}
}
?>