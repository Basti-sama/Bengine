<?php
/**
 * Ajax account class.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 */

class Bengine_Game_Account_Ajax extends Bengine_Game_Controller_Ajax_Abstract
{
	/**
	 * Displays the text for clear Ajax output.
	 *
	 * @param string $outstream	The text to output
	 *
	 * @return Bengine_Game_Account_Ajax
	 */
	protected function display($outstream)
	{
		if(!$this->silence)
			terminate($outstream);
		return $this;
	}
}