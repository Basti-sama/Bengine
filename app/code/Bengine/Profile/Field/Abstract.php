<?php
/**
 * Abstract profile field.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Abstract.php 8 2010-10-17 20:55:04Z secretchampion $
 */

abstract class Bengine_Profile_Field_Abstract
{
	/**
	 * Holds errors raised during validation.
	 *
	 * @var array
	 */
	protected $errors = array();

	/**
	 * Holds the profile model.
	 *
	 * @var Bengine_Model_Profile
	 */
	protected $model = null;

	/**
	 * Creates a new profile field object.
	 *
	 * @param Bengine_Model_Profile	Profile model
	 *
	 * @return Bengine_Profile_Field_Abstract
	 */
	public function __construct(Bengine_Model_Profile $model = null)
	{
		if(!is_null($model))
		{
			$this->setModel($model);
		}
		return $this;
	}

	/**
	 * Validate the data.
	 *
	 * @return boolean
	 */
	public abstract function validate();

	/**
	 * Returns the form html for the field.
	 *
	 * @return string
	 */
	public abstract function getHtml();

	/**
	 * Adds an error.
	 *
	 * @param string	Error message
	 *
	 * @return Bengine_Profile_Field_Abstract
	 */
	public function addError($error)
	{
		$this->errors[] = Logger::getMessageField($error);
		return $this;
	}

	/**
	 * Returns the errors.
	 *
	 * @return array
	 */
	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 * Returns the field data.
	 *
	 * @return string
	 */
	public function getData()
	{
		return $this->getModel()->getData();
	}

	/**
	 * Returns the profile model.
	 *
	 * @return Bengine_Model_Profile
	 */
	public function getModel()
	{
		return $this->model;
	}

	/**
	 * Sets the profile model.
	 *
	 * @param Bengine_Model_Profile
	 *
	 * @return Bengine_Profile_Field_Abstract
	 */
	public function setModel(Bengine_Model_Profile $model)
	{
		$this->model = $model;
		return $this;
	}

	/**
	 * Returns a setup parameter.
	 *
	 * @param string	Parameter key
	 * @param mixed		Default return value
	 *
	 * @return array|mixed
	 */
	public function getSetup($key = null, $default = null)
	{
		return $this->getModel()->getSetup($key, $default);
	}

	/**
	 * Sets the field data.
	 *
	 * @param string
	 *
	 * @return Bengine_Profile_Field_Abstract
	 */
	public function setData($data)
	{
		$this->getModel()->setData($data);
		return $this;
	}
}
?>