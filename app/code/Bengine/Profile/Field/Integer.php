<?php
/**
 * Profile integer field.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Integer.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Profile_Field_Integer extends Bengine_Profile_Field_Abstract
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
		$this->setData((int) $this->getData());
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
		return '<input type="text" name="'.$field->getName().'" id="'.strtolower($field->getName()).'" maxlength="'.$this->getSetup("max_length", 3).'" size="'.$this->getSetup("max_size", 3).'" value="'.$this->getData().'"/>';
	}
}
?>