<?php
/**
 * Sendmail adapter.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2011, Sebastian Noll
 * @license Proprietary
 * @version $Id$
 */
class Recipe_Email_Sendmail extends Recipe_Email_Abstract
{
	/**
	 * Sendmail logic.
	 *
	 * @return Recipe_Email_Sendmail
	 */
	protected function _send()
	{
		$email = $this->getEmail();
		if(!@mail($email->getFormattedReceiver(), $email->getSubject(), (string) $email->getMessages(), $email->getRawHeader()))
		{
			throw new Recipe_Exception_Generic("There is an error with sending mail.<br />Receiver: ".$email->getReceiver().", Subject: ".$email->getSubject().", Header: ".$email->getRawHeader()."<br /><br />".$email->getMessage());
		}
		return $this;
	}
}