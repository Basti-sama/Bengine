<?php
/**
 * Sends error message to logger.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 */

class Recipe_Exception_Issue extends Exception implements Recipe_Exception_Global
{
	/**
	 * Constructor.
	 *
	 * @param string	Message
	 * @param string	File
	 * @param integer	Line
	 * @param integer	Additional code
	 *
	 * @return void
	 */
	public function __construct($message, $file = "", $line = 0, $code = 0)
	{
		$this->file = $file;
		$this->line = $line;
		parent::__construct($message, $code);
		return;
	}

	/**
	 * Add the message to logger.
	 *
	 * @return void
	 */
	public function printError()
	{
		Logger::addMessage($this->message);
		return;
	}

	/**
	 * Displays the message and shut program down.
	 *
	 * @return void
	 */
	public function diePrintError()
	{
		Logger::dieMessage($this->message);
		return;
	}

	/**
	 * Sums the exception up.
	 *
	 * @return string	Formatted message
	 */
	public function __toString()
	{
		if($this->file != "" && $this->line != 0)
		{
			return "Error in class ".$this->file." (".$this->line.") occurred: ".$this->message;
		}
		return "Error: $this->message";
	}

	/**
	 * Returns field error.
	 *
	 * @return string	Error message
	 */
	public function getFieldError()
	{
		return Logger::getMessageField($this->message);
	}
}
?>