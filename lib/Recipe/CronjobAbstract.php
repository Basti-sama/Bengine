<?php
/**
 * Abstract cronjob class.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2011, Sebastian Noll
 * @license Proprietary
 * @version $Id: CronjobAbstract.php 26 2011-06-17 13:09:26Z secretchampion $
 */

abstract class Recipe_CronjobAbstract
{
	/**
	 * Contains business logic for a cronjob.
	 *
	 * @return Recipe_CronjobAbstract
	 */
	abstract protected function _execute();

	/**
	 * Executes the cronjob.
	 *
	 * @param integer $cronId The Cronjob ID
	 * @param integer $execTime Execution time
	 * @param integer $nextTime Next execution time
	 * @return Recipe_CronjobAbstract
	 */
	public function execute($cronId, $execTime, $nextTime)
	{
		Core::getQuery()->update("cronjob", array("xtime" => $nextTime, "last" => $execTime), "cronid = ?", array($cronId));
		$this->_execute();
		return $this;
	}
}