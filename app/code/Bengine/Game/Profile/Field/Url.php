<?php
/**
 * Profile URL field.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Url.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Game_Profile_Field_Url extends Bengine_Game_Profile_Field_Abstract
{
	/**
	 * Validate the data.
	 *
	 * @param string	Data
	 *
	 * @return boolean
	 */
	public function validate()
	{
		if(($this->getData() != "" && !filter_var($this->getData(), FILTER_VALIDATE_URL)) || Str::length($this->getData()) > $this->getSetup("max_length", 255))
		{
			$this->addError("INVALID_URL");
			return false;
		}
		return true;
	}

	/**
	 * Returns the form html for the field.
	 *
	 * @return string
	 */
	public function getHtml()
	{
		$field = $this->getModel();
		return '<input type="text" name="'.$field->getName().'" id="'.strtolower($field->getName()).'" maxlength="'.$this->getSetup("max_length", 255).'" value="'.$this->getData().'"/>';
	}
}
?>