<?php
/**
 * Alliance model.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Alliance.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Model_Alliance extends Recipe_Model_Abstract
{
	/**
	 * Initializes the model.
	 *
	 * @return Bengine_Model_Alliance
	 */
	protected function init()
	{
		$this->setTableName("alliance");
		return parent::init();
	}

	/**
	 * Generates link to the alliance page.
	 *
	 * @return string
	 */
	public function getPageLink()
	{
		return Link::get("game.php/".SID."/Alliance/Page/".$this->getAid(), $this->getTag(), $this->getName());
	}
}
?>