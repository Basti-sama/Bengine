<?php
/**
 * Event type model.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Type.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Game_Model_Event_Type extends Recipe_Model_Abstract
{
	/**
	 * Initializes the model.
	 *
	 * @return Bengine_Game_Model_Event_Type
	 */
	protected function init()
	{
		$this->setTableName("event_type");
		$this->setModelName("game/event_type");
		return parent::init();
	}

	/**
	 * Loads the event type data by code.
	 *
	 * @param string $code	Code name
	 *
	 * @return Bengine_Game_Model_Event_Type
	 */
	public function getByCode($code)
	{
		$data = $this->getResource()->loadByCode($code);
		return $this->set($data);
	}

	/**
	 * Retrieves the event handler for this event type.
	 *
	 * @return Bengine_Game_EventHandler_Handler_Abstract
	 */
	public function getEventHandler()
	{
		return Bengine_Game_EventHandler_Static::getHandlerObject($this->get("event_type_id"));
	}

	/**
	 * Returns the translated event mode name.
	 *
	 * @return string
	 */
	public function getModeName()
	{
		return Game::getMissionName($this->get("event_type_id"));
	}
}
?>