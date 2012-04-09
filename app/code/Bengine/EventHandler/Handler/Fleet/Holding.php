<?php
/**
 * Time when a fleet holds at an allied planet.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Holding.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_EventHandler_Handler_Fleet_Holding extends Bengine_EventHandler_Handler_Fleet_Abstract
{
	/**
	 * Executes the event.
	 *
	 * @param Bengine_Model_Event $event
	 * @param array $data
	 *
	 * @return Bengine_EventHandler_Handler_Fleet_Holding
	 */
	protected function _execute(Bengine_Model_Event $event, array $data)
	{
		Hook::event("EhHolding", array($event, &$data, $this));
		$this->sendBack($data, $data["time"] + $event["time"], $event["destination"], $event["planetid"]);
		return $this;
	}

	/**
	 * Execute when creating the event.
	 *
	 * @param Bengine_Model_Event $event
	 * @param array $data
	 *
	 * @return Bengine_EventHandler_Handler_Fleet_Holding
	 */
	protected function _add(Bengine_Model_Event $event, array $data)
	{
		$this->prepareFleet($data);
		return $this;
	}

	/**
	 * Execute when cancel the event.
	 *
	 * @param Bengine_Model_Event $event
	 * @param array $data
	 *
	 * @return Bengine_EventHandler_Handler_Fleet_Holding
	 */
	protected function _remove(Bengine_Model_Event $event, array $data)
	{
		$time = TIME + $data["time"];
		$this->sendBack($data, $time);
		return $this;
	}

	/**
	 * Checks the event for validation.
	 *
	 * @return boolean
	 */
	protected function _isValid()
	{
		return false;
	}
}
?>