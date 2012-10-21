<?php
/**
 * Alliance resource model.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Alliance.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Game_Model_Resource_Alliance extends Recipe_Model_Resource_Abstract
{
	/**
	 * Initilizing method.
	 *
	 * @return Bengine_Game_Model_Resource_Alliance
	 */
	protected function init()
	{
		$this->setMainTable(array("a" => "alliance"));
		$this->setPrimaryKey("aid");
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
		$select->attributes(array(
			new Recipe_Database_Expr("COUNT(u2a.userid) AS member"),
			new Recipe_Database_Expr("SUM(u.points) AS points"),
			new Recipe_Database_Expr("SUM(u.fpoints) AS fpoints"),
			new Recipe_Database_Expr("SUM(u.rpoints) AS rpoints"),
			"a" => array("aid", "name", "tag", "textextern", "textintern", "founder", "foundername", "logo", "homepage", "showmember", "showhomepage")
		));
		$select->join(array("u2a" => "user2ally"), array("u2a" => "aid", "a" => "aid"))
			->join(array("u" => "user"), array("u" => "userid", "u2a" => "userid"));
		$select->group(array("u2a" => "aid"));
		return $select;
	}
}
?>