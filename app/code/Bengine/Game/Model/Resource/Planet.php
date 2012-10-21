<?php
/**
 * Planet resource model.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Planet.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Game_Model_Resource_Planet extends Recipe_Model_Resource_Abstract
{
	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Database/Recipe_Database_Table#init()
	 */
	protected function init()
	{
		$this->setMainTable(array("p" => "planet"));
		return parent::init();
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Database/Recipe_Database_Table#getSelect()
	 */
	public function getSelect()
	{
		$select = parent::getSelect();
		$select->join(array("g" => "galaxy"), array("p" => "planetid", "g" => "planetid"))
			->join(array("gm" => "galaxy"), array("p" => "planetid", "gm" => "moonid"))
			->join(array("u" => "user"), array("u" => "userid", "p" => "userid"));
		$select->attributes(array(
			"p" => array("planetid", "planetname", "userid", "ismoon", "picture", "temperature", "diameter", "last", "metal", "silicon", "hydrogen", "solar_satellite_prod"),
			"u" => array("username"),
			"IFNULL(g.`galaxy`, gm.`galaxy`) AS galaxy",
			"IFNULL(g.`system`, gm.`system`) AS system",
			"IFNULL(g.`position`, gm.`position`) AS position"
		));
		return $select;
	}
}
?>