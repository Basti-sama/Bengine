<?php
/**
 * Achievement requirement model.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 */

class Bengine_Game_Model_AchievementRequirement extends Recipe_Model_Abstract
{
	/**
	 * @return Bengine_Game_Model_AchievementRequirement
	 */
	protected function init()
	{
		$this->setTableName("achievement_requirement");
		$this->setPrimaryKey("achievement_requirement_id");
		$this->setModelName("game/achievementRequirement");
		return parent::init();
	}

	/**
	 * @param Bengine_Game_Model_User $user
	 * @return bool
	 */
	public function checkIfRequirementMatched(Bengine_Game_Model_User $user)
	{
		$requirementObject =  $this->getRequirementObject();
		$requirementObject->setUser($user);
		return $requirementObject->match();
	}

	/**
	 * @return Bengine_Game_Achievement_Requirement_Abstract
	 */
	public function getRequirementObject()
	{
		if(!$this->exists("requirement_object"))
		{
			/* @var Bengine_Game_Achievement_Requirement_Abstract $object */
			$object = Application::factory($this->get("class"), null);
			$object->setValue($this->get("value"));
			$object->setId($this->get("id"));
			$config = $this->get("config");
			if(!empty($config))
			{
				$config = @json_decode($config, true);
				if(false === $config)
				{
					$config = @unserialize($config);
				}
			}
			if(is_array($config))
			{
				$object->setConfig($config);
			}
			$this->set("requirement_object", $object);
		}
		return $this->get("requirement_object");
	}
}
?>