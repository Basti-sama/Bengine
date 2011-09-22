<?php
/**
 * Event resource model.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Event.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Model_Resource_Event extends Recipe_Model_Resource_Abstract
{
	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Database/Recipe_Database_Table#init()
	 */
	protected function init()
	{
		$this->setMainTable(array("e" => "events"));
		return parent::init();
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Database/Recipe_Database_Table#getSelect()
	 */
	public function getSelect()
	{
		$select = parent::getSelect();
		$select
			->join(array("et" => "event_type"), array("et" => "event_type_id", "e" => "mode"))
			->join(array("p1" => "planet"), array("p1" => "planetid", "e" => "planetid"))
			->join(array("p2" => "planet"), array("p2" => "planetid", "e" => "destination"))
			->join(array("g1" => "galaxy"), array("g1" => "planetid", "e" => "planetid"))
			->join(array("g2" => "galaxy"), array("g2" => "planetid", "e" => "destination"))
			->join(array("m1" => "galaxy"), array("m1" => "moonid", "e" => "planetid"))
			->join(array("m2" => "galaxy"), array("m2" => "moonid", "e" => "destination"))
			->join(array("u1" => "user"), array("u1" => "userid", "e" => "user"))
			->join(array("u2" => "user"), array("u2" => "userid", "p2" => "userid"))
			->attributes(array(
				"e"  => array("eventid", "parent_id", "mode", "start", "time", "planetid", "destination", "data", "userid" => "user"),
				"et" => array("code", "base_type"),
				"p1" => array("planetname", "ismoon"),
				"p2" => array("destination_planetname" => "planetname", "destination_ismoon" => "ismoon", "destination_user_id" => "userid"),
				"u1" => array("points", "username"),
				"u2" => array("destination_username" => "username"),
				new Recipe_Database_Expr("IFNULL(`g1`.`galaxy`, `m1`.`galaxy`) AS `galaxy`, IFNULL(`g1`.`system`, `m1`.`system`) AS `system`, IFNULL(`g1`.`position`, `m1`.`position`) AS `position`"),
				new Recipe_Database_Expr("IFNULL(`g2`.`galaxy`, `m2`.`galaxy`) AS `galaxy2`, IFNULL(`g2`.`system`, `m2`.`system`) AS `system2`, IFNULL(`g2`.`position`, `m2`.`position`) AS `position2`"),
			));
		return $select;
	}
}
?>