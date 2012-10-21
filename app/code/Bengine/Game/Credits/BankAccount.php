<?php
/**
 * Bank account class for credits.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: BankAccount.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Game_Credits_BankAccount
{
	/**
	 * Holds all taxes for the current bank account.
	 *
	 * @var array
	 */
	protected $taxes = array();

	/**
	 * The userid the bank account belongs to.
	 *
	 * @var integer
	 */
	protected $userid = -1;

	/**
	 * Creates a bank account for the user.
	 *
	 * @param integer	User id
	 */
	public function __construct($userid)
	{
		$this->userid = $userid;
		$this->addTax("Default");
	}

	/**
	 * Returns the current credits in the bank account.
	 *
	 * @return integer
	 */
	public function getBalance()
	{
		$user = Game::getModel("user");
		$user->load($this->userid);

		return $user->getCredits();
	}

	/**
	 * Adds credits to the current bank account.
	 *
	 * @param float		Credits
	 * @param boolean	Subtract taxes?
	 * @param boolean	Send message?
	 *
	 * @return boolean	true on success, false otherwise
	 */
	public function addCredits($credits, $taxes = true, $message = false)
	{
		$oldcredits = $credits;
		$user = Game::getModel("user");
		$user->load($this->userid);

		if($taxes)
		{
			foreach ($this->taxes as $tax)
			{
				$credits = $tax->execute($credits, false);
			}
		}

		$user->setCredits(($user->getCredits() + $credits));
		$user->save();

		if($message !== false)
		{

			$messageData = array(
				"credits" => $credits,
				"message" => $message,
				"tax" => (($oldcredits - $credits) / $oldcredits),
			);
			$msg = new Bengine_Game_AutoMsg(201, $this->userid, TIME, $messageData);
		}
		return true;
	}

	/**
	 * Deletes credits from the current bank account.
	 *
	 * @param integer	Credits to reduce
	 * @param boolean	Subtract taxes?
	 * @param boolean	Send message?
	 *
	 * @return boolean	true on success, false otherwise
	 */
	public function deleteCredits($credits, $taxes = true, $message = false)
	{
		$oldcredits = $credits;
		$user = Game::getModel("user");
		$user->load($this->userid);

		if($taxes)
		{
			foreach($this->taxes as $tax)
			{
				$credits = $tax->execute($credits, true);
			}
		}

		if($user->getCredits() >= $credits)
		{
			$user->setCredits(($user->getCredits() - $credits));
			$user->save();

			if($message)
			{

				$messageData = array(
					"credits" => $credits,
					"username" => $user->getUsername(),
					"tax" => (($oldcredits - $credits) / $oldcredits),
				);
				$msg = new Bengine_Game_AutoMsg(201, $this->userid, TIME, $messageData);
			}
			return true;
		}

		return false;
	}


	/**
	 * Adds a tax to the current bank account.
	 *
	 * @param string
	 *
	 * @return Bengine_Game_Credits_BankAccount
	 */
	public function addTax($tax)
	{
		if(!array_key_exists($tax, $this->taxes))
		{
			$tax_name = "Bengine_Game_Credits_Tax_".$tax;
			$this->taxes[$tax] = new $tax_name;
		}

		return $this;
	}

	/**
	 * Deletes a tax from the current bank account.
	 *
	 * @param string
	 *
	 * @return Bengine_Game_Credits_BankAccount
	 */
	public function deleteTax($tax)
	{
		if(array_key_exists($tax, $this->taxes))
		{
			unset($this->taxes[$tax]);
		}

		return $this;
	}
}
?>