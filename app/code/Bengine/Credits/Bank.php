<?php
/**
 * Bank class for credits.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Bank.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Credits_Bank
{
	/**
	 * Instance of the bengine_bank.
	 *
	 * @var Bengine_Bank
	 */
	protected static $instance = null;

	/**
	 * List of all bank accounts in the bank.
	 *
	 * @var array
	 */
	protected $bank_accounts = array();

	/**
	 * List of all taxes for the accounts in the bank.
	 *
	 * @var array
	 */
	protected $taxes = array();

	/**
	 * Constructor, protected (singleton pattern).
	 *
	 */
	protected function __construct()
	{

	}

	/**
	 * Clone function, private (singleton pattern).
	 *
	 */
	private function __clone()
	{

	}

	/**
	 * Returns an instance of the bengine_bank class (singleton pattern).
	 *
	 * @return Bengine_Bank	An instance of the bengine_bank
	 */
	public static function getInstance()
	{
		if(self::$instance == null)
		{
			self::$instance = new Bengine_Credits_Bank();
		}

		return self::$instance;
	}

	/**
	 * Returns a bank account for a userid.
	 *
	 * @param integer	User id
	 *
	 * @return Bengine_Bank_Account
	 */
	public function getBankAccount($userid)
	{
		if(!array_key_exists($userid, $this->bank_accounts))
		{
			$this->bank_accounts[$userid] = new Bengine_Credits_BankAccount($userid);

			foreach($this->taxes as $tax)
			{
				$this->bank_accounts[$userid]->addTax($tax);
			}
		}

		return $this->bank_accounts[$userid];
	}

	/**
	 * Sets one tax for all bank accounts currently loaded in the bank.
	 *
	 * @param string
	 *
	 * @return Bengine_Bank_Account
	 */
	public function addGlobalTax($tax)
	{
		$this->taxes[] = $tax;

		foreach($this->bank_accounts as $bank_account)
		{
			$bank_account->addTax($tax);
		}

		return $this;
	}

	/**
	 * Deletes one tax from all bank accounts currently in the bank.
	 *
	 * @param string
	 *
	 * @return Bengine_Bank_Account
	 */
	public function deleteGlobalTax($tax)
	{
		if(in_array($tax, $this->taxes))
		{
			unset($this->taxes[$tax]);
		}

		foreach($this->bank_accounts as $bank_account)
		{
			$bank_account->deleteTax($tax);
		}

		return $this;
	}
}
?>