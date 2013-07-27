<?php
/**
 * Achievement requirement resource model.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 */

class Bengine_Game_Model_Resource_AchievementRequirement extends Recipe_Model_Resource_Abstract
{
	protected function init()
	{
		$this->setMainTable("achievement_requirement");
		return parent::init();
	}
}
?>