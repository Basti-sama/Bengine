<?php
/**
 * Session log collection.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: SessionLog.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Model_Collection_SessionLog extends Recipe_Model_Collection_Abstract
{
	/**
	 * Adds a user filter.
	 *
	 * @param integer	User ID
	 *
	 * @return Bengine_Model_Collection_SessionLog
	 */
	public function addUserFilter($userId)
	{
		$this->getSelect()
			->where(array("s" => "userid"), (int) $userId);
		return $this;
	}

	/**
	 * Adds time order.
	 *
	 * @param string	Order direction
	 *
	 * @return Bengine_Model_Collection_SessionLog
	 */
	public function addTimeOrder($dir = "DESC")
	{
		$this->getSelect()
			->order(array("s" => "time"), $dir);
		return $this;
	}

	/**
	 * Adds an IP filter.
	 *
	 * @param string	IP addres
	 *
	 * @return Bengine_Model_Collection_SessionLog
	 */
	public function addIpFilter($ip)
	{
		$this->getSelect()
			->where(array("s" => "ipaddress"), $ip);
		return $this;
	}
}