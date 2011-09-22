<?php
/**
 * Bank tax abstract.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Abstract.php 8 2010-10-17 20:55:04Z secretchampion $
 */

abstract class Bengine_Credits_Tax_Abstract
{
	/**
	 * Should the tax be applyed when money is deleted?
	 *
	 * @var boolean
	 */
	protected $doOnDelete = false;

	/**
	 * Should the tax be applyed when money is added?
	 *
	 * @var boolean
	 */
	protected $doOnAdd = true;

	/**
	 * The percentage of the tax, a float number. If it is below 0.00, money is added on tax.
	 *
	 * @var float
	 */
	protected $percent = 5.00;

	/**
	 * Description ...
	 *
	 * @param integer
	 * @param boolean
	 *
	 * @return Bengine_Credits_Tax_Abstract
	 */
	public function execute($money, $deleteMoney)
	{
		if($deleteMoney)
		{
			if($this->doOnDelete)
			{
				return ($money * ((100 - $this->percent) / 100));
			}
			else
			{
				return $money;
			}
		}
		else
		{
			if($this->doOnAdd)
			{
				return ($money * ((100 - $this->percent) / 100));
			}
			else
			{
				return $money;
			}
		}
		return $this;
	}
}
?>