<?php
/**
 * Profile model.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Profile.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Game_Model_Profile extends Recipe_Model_Abstract
{
	/**
	 * Holds the setup of the profile field.
	 *
	 * @var array
	 */
	protected $_setup = array();

	/**
	 * Initializes the model.
	 *
	 * @return Bengine_Game_Model_Profile
	 */
	protected function init()
	{
		$this->setTableName("profile");
		$this->setModelName("game/profile");
		return parent::init();
	}

	/**
	 * Retrieves the user model.
	 *
	 * @return Bengine_Game_Model_User
	 */
	public function getUser()
	{
		if(!$this->exists("user"))
		{
			$this->set("user", Game::getModel("game/user")->load($this->getUserId()));
		}
		return $this->get("user");
	}

	/**
	 * Loads the profile field by code.
	 *
	 * @param string $code
	 * @param integer|Bengine_Game_Model_User $user
	 *
	 * @return Bengine_Game_Model_Profile
	 */
	public function loadByCode($code, $user)
	{
		$data = $this->getResource()->loadByCode($code, $user);
		if(is_array($data))
		{
			$this->set($data);
		}
		$this->_afterLoad();
		return $this;
	}

	/**
	 * Returns the field object.
	 *
	 * @return Bengine_Game_Profile_Field_Abstract
	 */
	public function getFieldObject()
	{
		if(!$this->exists("field_object"))
		{
			$field = Game::factory("game/profile_field_".strtolower($this->getType()), $this);
			$field->setData($this->getData());
			$this->set("field_object", $field);
		}
		return $this->get("field_object");
	}

	/**
	 * Saves the user data for the profile.
	 *
	 * @return Bengine_Game_Model_Profile
	 */
	protected function _afterSave()
	{
		if($this->getUserId())
		{
			if($this->getIsNew())
			{
				Core::getQuery()->insert("profile2user", array("profile_id" => $this->getProfileId(), "user_id" => $this->getUserId(), "data" => $this->getData()));
			}
			else
			{
				Core::getQuery()->update("profile2user", array("data" => $this->getData()), "profile_id = '".$this->getProfileId()."' AND user_id = '".$this->getUserId()."'");
			}
		}
		return parent::_afterSave();
	}

	/**
	 * Prepares the setup array for save.
	 *
	 * @return Bengine_Game_Model_Profile
	 */
	protected function _beforeSave()
	{
		$this->set("setup", serialize($this->_setup));
		return parent::_beforeSave();
	}

	/**
	 * Loading the setup data.
	 *
	 * @return Bengine_Game_Model_Profile
	 */
	protected function _afterLoad()
	{
		if($this->get("setup") != "")
		{
			$this->_setup = unserialize($this->get("setup"));
		}
		return parent::_afterLoad();
	}

	/**
	 * Returns the translated name.
	 *
	 * @return string
	 */
	public function getTranslatedName()
	{
		return Core::getLang()->get($this->getName());
	}

	/**
	 * Returns a setup parameter.
	 *
	 * @param string $var
	 * @param mixed $default
	 *
	 * @return array|mixed
	 */
	public function getSetup($var = null, $default = null)
	{
		if(is_null($var))
		{
			return $this->_setup;
		}
		return (isset($this->_setup[$var])) ? $this->_setup[$var] : $default;
	}

	/**
	 * Sets a setup parameter.
	 *
	 * @param string|array $var
	 * @param mixed $value
	 *
	 * @return Bengine_Game_Model_Profile
	 */
	public function setSetup($var, $value = null)
	{
		if(is_array($var))
		{
			$this->_setup = array_merge($this->_setup, $var);
		}
		else
		{
			$this->_setup[$var] = $value;
		}
		return $this;
	}
}
?>