<?php
/**
 * Profile image field.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Image.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Profile_Field_Image extends Bengine_Profile_Field_Abstract
{
	private $allowedMimes = array("image/gif", "image/jpeg", "image/png");

	/**
	 * Validate the data.
	 *
	 * @param string	Data
	 *
	 * @return boolean
	 */
	public function validate()
	{
		if($this->getData() != "")
		{
			if(!preg_match("~(?:([^:/?#]+):)?(?://([^/?#]*))?([^?#]*\.(?:jpg|gif|png))(?:\?([^#]*))?(?:#(.*))?~i", $this->getData()))
			{
				$this->addError("INVALID_IMAGE_URL");
				return false;
			}
			$stats = @getimagesize($this->getData());
			if(!$stats)
			{
				$this->addError("INVALID_IMAGE_URL");
				return false;
			}
			$width = $stats[0];
			$height = $stats[1];
			if(!in_array($stats["mime"], $this->allowedMimes))
			{
				$this->addError("INVALID_IMAGE_FORMAT");
				return false;
			}
			if($this->getSetup("min_width", 80) > $width || $this->getSetup("min_height", 80) > $height)
			{
				$this->addError("IMAGE_TOO_SMALL");
				return false;
			}
			if($this->getSetup("max_width", 200) < $width || $this->getSetup("max_height", 200) < $height)
			{
				$this->addError("IMAGE_TOO_LARGE");
				return false;
			}
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