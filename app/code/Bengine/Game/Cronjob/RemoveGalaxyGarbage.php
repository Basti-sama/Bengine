<?php
/**
 * Removes destroyed planets.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: RemoveGalaxyGarbage.php 26 2011-06-17 13:09:26Z secretchampion $
 */

class Bengine_Game_Cronjob_RemoveGalaxyGarbage extends Recipe_CronjobAbstract
{
	/**
	 * Removes galaxy garbage.
	 *
	 * @return Bengine_Game_Cronjob_RemoveGalaxyGarbage
	 */
	protected function removeGalaxyGarbage()
	{
		$result = Core::getQuery()->select("galaxy g", array("g.planetid"), "LEFT JOIN ".PREFIX."events e ON (e.destination = g.planetid OR e.planetid = g.planetid)", "g.destroyed = '1' AND e.eventid IS NULL");
		foreach($result->fetchAll() as $row)
		{
			Core::getQuery()->delete("planet", "planetid = ?", null, null, array($row["planetid"]));
		}
		$result->closeCursor();
		return $this;
	}

	/**
	 * Executes this cronjob.
	 *
	 * @return Bengine_Game_Cronjob_RemoveGalaxyGarbage
	 */
	protected function _execute()
	{
		$this->removeGalaxyGarbage();
		return $this;
	}
}
?>