<?php

abstract class Bengine_Game_Achievement_Requirement_Abstract
{
	/**
	 * @var Bengine_Game_Model_User
	 */
	protected $user;

	/**
	 * @var mixed
	 */
	protected $id;

	/**
	 * @var mixed
	 */
	protected $value;

	/**
	 * @var array
	 */
	protected $config;

	/**
	 * @return bool
	 */
	abstract protected function _match();

	/**
	 * @param Bengine_Game_Model_User $user
	 * @param mixed $id
	 * @param mixed $value
	 * @param array $config
	 */
	public function __construct(Bengine_Game_Model_User $user = null, $id = null, $value = null, array $config = null)
	{
		if(null !== $user)
			$this->setUser($user);
		if(null !== $id)
			$this->setId($id);
		if(null !== $value)
			$this->setValue($value);
		if(null !== $config)
			$this->setConfig($config);
	}

	/**
	 * @return bool
	 */
	public function match()
	{
		return $this->_match();
	}

	/**
	 * @param Bengine_Game_Model_User $user
	 * @return Bengine_Game_Achievement_Requirement_Abstract
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
	 * @param array $config
	 */
	public function setConfig(array $config)
	{
		$this->config = $config;
	}

	/**
	 * @return array
	 */
	public function getConfig()
	{
		return $this->config;
	}

	/**
	 * @param int $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param mixed $value
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}

	/**
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}
}