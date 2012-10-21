<?php
/**
 * Fleet model.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Fleet.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Game_Model_Fleet extends Bengine_Game_Model_Construction
{
	/**
	 * (non-PHPdoc)
	 * @see lib/Object#init()
	 */
	protected function init()
	{
		$this->setModelName("fleet");
		return parent::init();
	}

	/**
	 * Returns the formatted speed.
	 *
	 * @return string
	 */
	public function getFormattedSpeed()
	{
		return fNumber(Game::getSpeed($this->getId(),$this->get("speed")));
	}

	/**
	 * Wrapper for getQuantity().
	 *
	 * @return integer
	 */
	public function getQty()
	{
		return $this->get("quantity");
	}

	/**
	 * Returns the formatted quantity.
	 *
	 * @return string
	 */
	public function getFormattedQty()
	{
		return fNumber($this->get("quantity"));
	}

	/**
	 * Returns the formatted capacity of the unit.
	 *
	 * @return string
	 */
	public function getFormattedCapacity()
	{
		$capacity = $this->get("capicity");
		if($this->getQty() > 0)
		{
			$capacity *= $this->getQty();
		}
		return fNumber($capacity);
	}

	/**
	 * Spelling fix for "capicity".
	 *
	 * @return integer
	 */
	public function getCapacity()
	{
		return $this->get("capicity");
	}

	/**
	 * Spelling fix for "capicity".
	 *
	 * @param integer	Capacity value
	 *
	 * @return Bengine_Game_Model_Fleet
	 */
	public function setCapacity($capacity)
	{
		return $this->set("capicity", $capacity);
	}

	/**
	 * Adds quantity to the ship.
	 *
	 * @param integer
	 *
	 * @return Bengine_Game_Model_Fleet
	 */
	public function addQty($qty)
	{
		$qty = (int) $qty;
		return $this->set("quantity", $this->get("quantity", 0)+$qty);
	}

	/**
	 * Subtracts quantity from the ship.
	 *
	 * @param integer
	 *
	 * @return Bengine_Game_Model_Fleet
	 */
	public function subtractQty($qty)
	{
		$qty = (int) $qty;
		$qty = $this->get("quantity", 0)-$qty;
		return $this->set("quantity", ($qty < 0) ? 0 : $qty);
	}
}