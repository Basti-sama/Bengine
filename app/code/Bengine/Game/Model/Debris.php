<?php
/**
 * Debris model.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Debris.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Game_Model_Debris extends Recipe_Model_Abstract
{
	/**
	 * (non-PHPdoc)
	 * @see lib/Object#init()
	 */
	protected function init()
	{
		$this->setTableName("galaxy");
		$this->setPrimaryKey("planetid");
		$this->setModelName("game/debris");
		return parent::init();
	}

	/**
	 * Adds metal to the debris.
	 *
	 * @param integer
	 *
	 * @return Bengine_Game_Model_Debris
	 */
	public function addMetal($metal)
	{
		if($this->getId())
		{
			$metal = (int) $metal;
			$this->setMetal($this->getMetal()+$metal);
		}
		return $this;
	}

	/**
	 * Adds silicon to the debris.
	 *
	 * @param integer
	 *
	 * @return Bengine_Game_Model_Debris
	 */
	public function addSilicon($silicon)
	{
		if($this->getId())
		{
			$silicon = (int) $silicon;
			$this->setSilicon($this->getSilicon()+$silicon);
		}
		return $this;
	}

	/**
	 * Retrieves the owner user of the planet belonging to the debris.
	 *
	 * @return Bengine_Game_Model_User
	 */
	public function getUser()
	{
		if(!$this->exists("user"))
		{
			$this->set("user", Game::getModel("user")->load($this->getUserid()));
		}
		return $this->get("user");
	}
}
?>