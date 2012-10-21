<?php
/**
 * Cleans points.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: PointClean.php 26 2011-06-17 13:09:26Z secretchampion $
 */

class Bengine_Game_Cronjob_PointClean extends Recipe_CronjobAbstract
{
	/**
	 * Recalculate the points.
	 *
	 * @return Bengine_Game_Cronjob_PointClean
	 */
	protected function cleanPoints()
	{
		Hook::event("CleanPointsBegin");
		$_result = Core::getQuery()->select("user", "userid");
		while($_row = Core::getDB()->fetch($_result))
		{
			$points = 0;
			$fpoints = 0;
			$result = Core::getQuery()->select("planet", "planetid", "", "userid = '".$_row["userid"]."'");
			while($row = Core::getDB()->fetch($result))
			{
				$points += Bengine_Game_PointRenewer::getBuildingPoints($row["planetid"]);
				$points += Bengine_Game_PointRenewer::getFleetPoints($row["planetid"]);
				$fpoints += Bengine_Game_PointRenewer::getFleetPoints_Fleet($row["planetid"]);
			}
			Core::getDB()->free_result($result);
			$points += Bengine_Game_PointRenewer::getResearchPoints($_row["userid"]);
			$points += Bengine_Game_PointRenewer::getFleetEventPoints($_row["userid"]);
			$fpoints += Bengine_Game_PointRenewer::getFleetEvent_Fleet($_row["userid"]);
			$rpoints = Bengine_Game_PointRenewer::getResearchPoints_r($_row["userid"]);
			Core::getQuery()->update("user", array("points", "fpoints", "rpoints"), array($points, $fpoints, $rpoints), "userid = '".$_row["userid"]."'");
		}
		Core::getDB()->free_result($_result);
		return $this;
	}

	/**
	 * Executes this cronjob.
	 *
	 * @return Bengine_Game_Cronjob_PointClean
	 */
	protected function _execute()
	{
		$this->cleanPoints();
	}
}
?>