<?php
/**
 * Executes events.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 */

class Bengine_Game_Cronjob_EventExecution extends Recipe_CronjobAbstract
{
	/**
	 * Executes all events that are queued, but max 1000 events.
	 *
	 * @return Bengine_Game_Cronjob_EventExecution
	 */
	protected function _execute()
	{
		require_once "Bengine/Game.php";
		$raceConditionKey = Str::substring(md5(microtime(true)), 0, 16);
		/* @var Bengine_Game_Model_Collection_Event $collection */
		$collection = Application::getModel("game/event")->getCollection();
		$collection->addRaceConditionFilter($raceConditionKey, Core::getConfig()->get("CRONJOB_MAX_EVENT_EXECUTION"));
		$collection->executeAll();
		if($collection->count() > 0)
		{
			Core::getQuery()->delete("events", "prev_rc = ?", null, null, array($raceConditionKey));
		}
		return $this;
	}
}

?>