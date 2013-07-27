<?php
/**
 * Assault collection.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 */

class Bengine_Game_Model_Collection_Assault extends Recipe_Model_Collection_Abstract
{
	/**
	 * @param int|Bengine_Game_Model_Planet $planet
	 * @return Bengine_Game_Model_Collection_Assault
	 */
	public function addPlanetFilter($planet)
	{
		if($planet instanceof Bengine_Game_Model_Planet)
		{
			$planet = $planet->getId();
		}
		$this->getSelect()
			->where("a.planetid = ?", (int) $planet);
		return $this;
	}

	/**
	 * @param bool $accomplished
	 * @return Bengine_Game_Model_Collection_Assault
	 */
	public function addAccomplishedFilter($accomplished = true)
	{
		$this->getSelect()
			->where("a.accomplished = ?", $accomplished ? 1 : 0);
		return $this;
	}

	/**
	 * @param int|Bengine_Game_Model_User $user
	 * @param int $mode
	 * @return Bengine_Game_Model_Collection_Assault
	 */
	public function addParticipantFilter($user, $mode = null)
	{
		if($user instanceof Bengine_Game_Model_User)
		{
			$user = $user->get("userid");
		}
		$on  = Core::getDB()->quoteInto("pa.assaultid = a.assaultid AND pa.userid = ?", (int) $user);
		if(null !== $mode)
		{
			$on .= Core::getDB()->quoteInto(" AND mode = ?", (int) $mode);
		}
		$this->getSelect()
			->join(array("pa" => "assaultparticipant"), $on);
		return $this;
	}

	/**
	 * @param int $result
	 * @return Bengine_Game_Model_Collection_Assault
	 */
	public function addResultFilter($result)
	{
		$this->getSelect()
			->where("a.result = ?", (int) $result);
		return $this;
	}
}