<?php
/**
 * Transaction class for credits.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Transaction.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Credits_Transaction
{
	/**
	 * The amount of credits to be transfered.
	 *
	 * @var integer
	 */
	protected $credits = 0;

	/**
	 * Userid of the user who will lose the credits.
	 *
	 * @var integer
	 */
	protected $from = 0;

	/**
	 * Userid of the user who will gain the credits.
	 *
	 * @var integer
	 */
	protected $receiver = 0;

	/**
	 * Should an automated message be send to both users?
	 *
	 * @var boolean
	 */
	protected $message = false;

	/**
	 * Should taxes apply to the transaction?
	 *
	 * @var boolean
	 */
	protected $taxes = true;


	/**
	 * Starts the transaction.
	 *
	 * @return boolean	true on success, false otherwise
	 */
	public function commit()
	{
		$bank = Bengine::getBank();
		$transfer = false;
		$bank_account_from = $bank->getBankAccount($this->from);
		$bank_account_to = $bank->getBankAccount($this->receiver);
		if($bank_account_from->deleteCredits($this->credits, $this->taxes, $this->message))
		{
			$transfer = $bank_account_to->addCredits($this->credits, $this->taxes, $this->message);
		}
		if(!$transfer)
		{
			$bank_account_from->addCredits($this->credits, false, false);
		}
		return $transfer;
	}

	/**
	 * Sets the amount of credits to be transfered.
	 *
	 * @param integer
	 *
	 * @return Bengine_Transaction
	 */
	public function setCredits($credits)
	{
		$this->credits = $credits;
		return $this;
	}

	/**
	 * Sets the userid of the user who will lose the credits.
	 *
	 * @param integer	User id
	 *
	 * @return Bengine_Transaction
	 */
	public function setFrom($id)
	{
		$this->from = $id;
		return $this;
	}

	/**
	 * Sets the userid of the user who will gain the credits
	 *
	 * @param integer	User id
	 *
	 * @return Bengine_Transaction
	 */
	public function setTo($id)
	{
		$this->receiver = $id;
		return $this;
	}

	/**
	 * Sets wether the taxes should apply for this transaction or not.
	 *
	 * @param boolean
	 *
	 * @return Bengine_Transaction
	 */
	public function setTaxes($taxes)
	{
		$this->taxes = $taxes;
		return $this;
	}

	/**
	 * Sets if automated messages should be send to both users.
	 *
	 * @param string
	 *
	 * @return Bengine_Transaction
	 */
	public function setMessage($message)
	{
		$this->message = $message;
		return $this;
	}
}
?>