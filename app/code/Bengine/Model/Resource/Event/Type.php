<?php
/**
 * Event type resource model.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Type.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Model_Resource_Event_Type extends Recipe_Model_Resource_Abstract
{
	/**
	 * Initilizing method.
	 *
	 * @return Bengine_Model_Resource_Event_Type
	 */
	protected function init()
	{
		$this->setMainTable("event_type");
		return parent::init();
	}

	/**
	 * Loads the event type data by code.
	 *
	 * @param string	Code name
	 *
	 * @return array	Data
	 */
	public function loadByCode($code)
	{
		return $this->fetchRow(array("main_table" => "code"), $code);
	}
}
?>