<?php

class Bengine_Game_Achievement_Reward_Resource extends Bengine_Game_Achievement_Reward_Abstract
{
	/**
	 * @throws Recipe_Exception_Generic
	 * @return bool
	 */
	protected function _reward()
	{
		switch($this->getType())
		{
			case "metal":
				$this->getPlanet()->addMetal($this->getValue());
			break;
			case "silicon":
				$this->getPlanet()->addSilicon($this->getValue());
			break;
			case "hydrogen":
				$this->getPlanet()->addHydrogen($this->getValue());
			break;
			default:
				throw new Recipe_Exception_Generic("Invalid resource type '{$this->getType()}'");
			break;
		}
		$value = (int) $this->getValue();
		Core::getQuery()->update("planet", array(
			$this->getType() => new Recipe_Database_Expr("{$this->getType()} + $value")
		), "planetid = ?", array($this->getPlanet()->get("planetid")));
		return true;
	}

	/**
	 * @return string
	 */
	public function getLabel()
	{
		return Core::getLang()->get(strtoupper($this->getType()));
	}
}