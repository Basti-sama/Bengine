<?php

abstract class Bengine_Game_Achievement_Reward_Abstract
{
	/**
	 * @var Bengine_Game_Model_User
	 */
	protected $user;

	/**
	 * @var mixed
	 */
	protected $type;

	/**
	 * @var mixed
	 */
	protected $value;

	/**
	 * @var Bengine_Game_Model_Planet
	 */
	protected $planet;

	/**
	 * @return bool
	 */
	abstract protected function _reward();

	/**
	 * @return string
	 */
	abstract public function getLabel();

	/**
	 *
	 * @param mixed $type
	 * @param mixed $value
	 * @param Bengine_Game_Model_User $user
	 * @param Bengine_Game_Model_Planet $planet
	 */
	public function __construct($type = null, $value = null, Bengine_Game_Model_User $user = null, Bengine_Game_Model_Planet $planet = null)
	{
		if(null !== $type)
			$this->setType($type);
		if(null !== $value)
			$this->setValue($value);
		if(null !== $user)
			$this->setUser($user);
		if(null !== $planet)
			$this->setPlanet($planet);
	}

	/**
	 * @return bool
	 */
	public function reward()
	{
		return $this->_reward();
	}

	/**
	 * @param Bengine_Game_Model_User $user
	 * @return Bengine_Game_Achievement_Reward_Abstract
	 */
	public function setUser(Bengine_Game_Model_User $user)
	{
		$this->user = $user;
		return $this;
	}

	/**
	 * @return Bengine_Game_Model_User
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @param mixed $type
	 * @return Bengine_Game_Achievement_Reward_Abstract
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @param mixed $value
	 * @return Bengine_Game_Achievement_Reward_Abstract
	 */
	public function setValue($value)
	{
		$this->value = $value;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @param Bengine_Game_Model_Planet $planet
	 * @return Bengine_Game_Achievement_Reward_Abstract
	 */
	public function setPlanet(Bengine_Game_Model_Planet $planet)
	{
		$this->planet = $planet;
		return $this;
	}

	/**
	 * @return Bengine_Game_Model_Planet
	 */
	public function getPlanet()
	{
		return $this->planet;
	}
}