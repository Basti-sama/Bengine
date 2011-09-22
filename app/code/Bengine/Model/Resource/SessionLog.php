<?php
/**
 * Session log resource model.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: SessionLog.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Model_Resource_SessionLog extends Recipe_Model_Resource_Abstract
{
	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Database/Recipe_Database_Table#init()
	 */
	protected function init()
	{
		$this->setMainTable(array("s" => "sessions"));
		return parent::init();
	}

	/**
	 * Retrieves a raw select object for the table.
	 *
	 * @return Recipe_Database_Select
	 */
	public function getSelect()
	{
		$select = parent::getSelect();
		$select->join(array("u" => "user"), array("u" => "userid", "s" => "userid"))
			->attributes(array(
				"s" => array("sessionid", "userid", "ipaddress", "useragent", "time"),
				"u" => array("username")
			));
		return $select;
	}
}