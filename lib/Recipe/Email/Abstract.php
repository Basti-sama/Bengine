<?php
/**
 * Abstract email adapter.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2011, Sebastian Noll
 * @license Proprietary
 * @version $Id$
 */
abstract class Recipe_Email_Abstract
{
	/**
	 * The email object.
	 *
	 * @var Email
	 */
	protected $email = null;

	/**
	 * Create the adapter object.
	 *
	 * @param Email $email
	 *
	 * @return void
	 */
	public function __construct(Email $email = null)
	{
		if($email !== null)
		{
			$this->setEmail($email);
		}
		$this->_init();
	}

	/**
	 * Returns the email object.
	 *
	 * @return Email
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * Sets the email object.
	 *
	 * @param Email $email
	 *
	 * @return Recipe_Email_Abstract
	 */
	public function setEmail(Email $email)
	{
		$this->email = $email;
		return $this;
	}

	/**
	 * Sends the email.
	 *
	 * @param Email $email
	 *
	 * @return Recipe_Email_Abstract
	 */
	public function send(Email $email = null)
	{
		if($email !== null)
		{
			$this->setEmail($email);
		}
		$this->_send();
		return $this;
	}

	/**
	 * Initializing method.
	 *
	 * @return Recipe_Email_Abstract
	 */
	protected function _init()
	{
		return $this;
	}

	/**
	 * Contains adapter logic.
	 *
	 * @return Recipe_Email_Abstract
	 */
	abstract protected function _send();
}