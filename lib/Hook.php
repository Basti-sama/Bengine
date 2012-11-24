<?php
/**
 * Hook and event class.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Hook.php 53 2011-08-13 16:05:23Z secretchampion $
 */

class Hook
{
	/**
	 * Global hook variable.
	 *
	 * @var array
	 */
	protected static $hooks = array();

	/**
	 * Appends an hook.
	 *
	 * @param string $event
	 * @param Recipe_PluginAbstract $pluginObj
	 *
	 * @return void
	 */
	public static function addHook($event, Recipe_PluginAbstract $pluginObj)
	{
		self::$hooks[$event][] = $pluginObj;
		return;
	}

	/**
	 * When an event is triggered.
	 *
	 * @param string $event
	 * @param array $args
	 *
	 * @return string
	 */
	public static function event($event, array $args = null)
	{
		if(empty(self::$hooks[$event]))
		{
			return null;
		}

		try {
			$return = self::runHooks(self::$hooks[$event], $event, $args);
		} catch(Recipe_Exception_Generic $e) {
			$e->printError();
		}
		return $return;
	}

	/**
	 * Executes an hook.
	 *
	 * @param mixed $hook	The hook to execute.
	 * @param string $event	Event name
	 * @param array $args	Event arguments [optional]
	 *
	 * @return string
	 */
	protected static function runHooks($hook, $event, array $args = null)
	{
		$return = null;
		foreach($hook as $pluginObj)
		{
			$method = "on".$event;
			if(!method_exists($pluginObj, $method) && !method_exists($pluginObj, "__call"))
			{
				throw new Recipe_Exception_Generic(sprintf("The plugin '%s' does not implement the event '%s' correctly.", get_class($pluginObj), $event));
			}
			$_return = call_user_func_array(array($pluginObj, $method), $args);
			if($_return === false)
			{
				throw new Recipe_Exception_Generic("The plugin '".get_class($pluginObj)."' returned an unknown error.");
			}
			if(!empty($_return) && is_string($_return))
			{
				if($return === null)
					$return = $_return;
				else
					$return .= $_return;
			}
		}
		return $return;
	}
}
?>