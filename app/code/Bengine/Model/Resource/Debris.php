<?php
/**
 * Debris resource model.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Debris.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Model_Resource_Debris extends Recipe_Model_Resource_Abstract
{
	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Database/Recipe_Database_Table#init()
	 */
	protected function init()
	{
		$this->setMainTable(array("g" => "galaxy"));
		return parent::init();
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Database/Recipe_Database_Table#getSelect()
	 */
	public function getSelect()
	{
		$select = parent::getSelect();
		$select->join(array("p" => "planet"), array("p" => "planetid", "g" => "planetid"));
		$select->attributes(array(
			"g" => array("metal", "silicon"),
			"p" => array("userid")
		));
		return $select;
	}
}
?>