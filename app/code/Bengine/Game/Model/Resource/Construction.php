<?php
/**
 * Construction resource model.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Construction.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Game_Model_Resource_Construction extends Recipe_Model_Resource_Abstract
{
	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Database/Recipe_Database_Table#init()
	 */
	protected function init()
	{
		$this->setMainTable(array("c" => "construction"));
		return parent::init();
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/Recipe/Database/Recipe_Database_Table#getSelect()
	 */
	public function getSelect()
	{
		$select = parent::getSelect();
		$select->attributes($this->getAttributes());
		$select->order(array("c" => "display_order"), "ASC");
		$select->order(array("c" => "buildingid"), "ASC");
		return $select;
	}

	/**
	 * Returns the attributes to select.
	 *
	 * @return array
	 */
	public function getAttributes()
	{
		return array(
			"c" => array(
				"buildingid", "mode", "allow_on_moon", "name", "special", "demolish", "display_order",
				"basic_metal", "basic_silicon", "basic_hydrogen", "basic_energy",
				"prod_metal", "prod_silicon", "prod_hydrogen", "prod_energy",
				"cons_metal", "cons_silicon", "cons_hydrogen", "cons_energy",
				"charge_metal", "charge_silicon", "charge_hydrogen", "charge_energy",
			),
		);
	}
}
?>