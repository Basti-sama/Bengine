<?php
/**
 * Displays message in error box.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Logger.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Logger
{
	/**
	 * Add a message to log.
	 *
	 * @param string $message	Message to log
	 * @param string $mode		Log mode
	 *
	 * @return void
	 */
	public static function addMessage($message, $mode = "error")
	{
		Core::getLanguage()->load("error");
		$message = Core::getLanguage()->getItem($message);
		$message = "<div class=\"".$mode."\">".$message."</div>";
		Hook::event("AddMessageToLog", array(&$message));
		Core::getTPL()->addLogMessage($message);
		return;
	}

	/**
	 * Displays a message and shut program down.
	 *
	 * @param string $message	Message to log
	 * @param string $mode		Log mode
	 *
	 * @return void
	 */
	public static function dieMessage($message, $mode = "error")
	{
		Core::getLanguage()->load("error");
		$message = Core::getLanguage()->getItem($message);
		Core::getTPL()->addLogMessage("<div class=\"".$mode."\">".$message."</div>");
		Core::getTemplate()->display("error");
		exit;
	}

	/**
	 * Formats a message.
	 *
	 * @param string $message	Raw log message
	 * @param string $mode		Log mode
	 *
	 * @return string	Formatted message
	 */
	public static function getMessageField($message, $mode = "error")
	{
		Core::getLanguage()->load("error");
		$message = Core::getLanguage()->getItem($message);
		$message = "<span class=\"field_".$mode."\">".$message."</span>";
		Hook::event("MessageField", array(&$message));
		return $message;
	}
}
?>