<?php
/**
 * Function to clear old sessions.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: ClearSessions.php 8 2010-10-17 20:55:04Z secretchampion $
 *
 * @param integer	Delete sessions older than "days"
 *
 * @return void
 */

function clearSessions($days = 0)
{
	if($days > 0)
	{
		$deldate = $days * 86400;
		$and = " AND time < '".$deldate."'";
	}
	else { $and = ""; }
	Core::getQuery()->delete("sessions", "logged = '0'".$and);
	return;
}
?>
