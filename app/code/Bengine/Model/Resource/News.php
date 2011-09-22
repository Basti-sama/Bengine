<?php
/**
 * News resource model.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: News.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Model_Resource_News extends Recipe_Model_Resource_Abstract
{
	/**
	 * Initilizing method.
	 *
	 * @return Recipe_Database_Table
	 */
	protected function init()
	{
		$this->setMainTable(array("n" => "news"));
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
			"n" => array("title", "text", "user_id", "time"),
			"u" => array("username", "userid"),
		));
		$select->join(array("u" => "user"), array("u" => "userid", "n" => "user_id"));
		return $select;
	}
}