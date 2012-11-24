<?php
/**
 * Static event handler functions.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Static.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Game_EventHandler_Static
{
	/**
	 * The name of the configuration file.
	 *
	 * @var string
	 */
	const CONFIGURATION_FILE = "Events";

	/**
	 * Contains the configuration file for events.
	 *
	 * @var array
	 */
	protected static $handlers = array();

	/**
	 * Translates the event code.
	 *
	 * @param string|integer $code
	 *
	 * @return Bengine_Game_Model_Event_Type
	 *
	 * @throws Recipe_Exception_Generic
	 */
	public static function translateCode($code)
	{
		self::loadFile();
		$code = strval($code);
		if(!isset(self::$handlers[$code]))
		{
			throw new Recipe_Exception_Generic("Unable to translate handler code '".$code."'.");
		}
		return self::$handlers[$code];
	}

	/**
	 * Retrieves the handler object of the code or the mode.
	 *
	 * @param string|integer $code
	 * @param Bengine_Game_Model_Event $event
	 * @throws Recipe_Exception_Generic
	 * @return Bengine_Game_EventHandler_Handler_Abstract
	 */
	public static function getHandlerObject($code, Bengine_Game_Model_Event $event = null)
	{
		$handler = self::translateCode($code);
		list($package, $class) = explode("/", $handler->get("code"));
		$class = $package."_EventHandler_Handler_".$handler->get("base_type")."_".$class;
		if($handlerObj = Application::factory($class, $event))
		{
			return $handlerObj;
		}
		throw new Recipe_Exception_Generic("Unable to instantiate handler code '".$code."'.");
	}

	/**
	 * Loads the event types from
	 *
	 * @return array	The handlers
	 */
	protected static function loadFile()
	{
		if(empty(self::$handlers))
		{
			$types = Application::getModel("game/event_type")->getCollection();
			foreach($types as $handler)
			{
				self::$handlers[$handler->get("code")] = $handler;
				self::$handlers[$handler->getEventTypeId()] = $handler;
			}
		}
		return self::$handlers;
	}
}
?>