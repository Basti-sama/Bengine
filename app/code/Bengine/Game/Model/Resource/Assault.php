<?php
/**
 * Assault resource model.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Assault.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Game_Model_Resource_Assault extends Recipe_Model_Resource_Abstract
{
	/**
	 * Initilizing method.
	 *
	 * @return Bengine_Game_Model_Resource_Debris
	 */
	protected function init()
	{
		$this->setMainTable(array("a" => "assault"));
		return $this;
	}

	/**
	 * Retrieves a raw select object for the table.
	 *
	 * @return Recipe_Database_Select
	 */
	public function getSelect()
	{
		$select = parent::getSelect();
		$select->join(array("p" => "planet"), array("p" => "planetid", "a" => "planetid"))
			->join(array("g" => "galaxy"), "a.planetid = g.planetid OR a.planetid = g.moonid");
		$select->attributes(array(
			"a" => array(new Recipe_Database_Expr("*")),
			"g" => array("galaxy", "system", "position"),
			"p" => array("planetname")
		));
		return $select;
	}
}
?>