<?php
/**
 * Abstract message folder.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Abstract.php 8 2010-10-17 20:55:04Z secretchampion $
 */

abstract class Bengine_Game_MessageFolder_Abstract
{
	/**
	 * Empty constructor.
	 */
	public function __construct(){}

	/**
	 * Formats the message.
	 *
	 * @param Bengine_Game_Model_Message $message Message model
	 * @param boolean $isFeed Format for news feed
	 * @param boolean $replace Replace message object
	 *
	 * @return Bengine_Game_Model_Message
	 */
	public function formatMessage(Bengine_Game_Model_Message $message, $isFeed = false, $replace = false)
	{
		if($replace)
			$_message = clone $message;
		else
			$_message = $message;
		if(!$isFeed)
			$this->_format($_message);
		else
			$this->_formatFeed($_message);
		return $_message;
	}

	/**
	 * Formates the message for news feeds.
	 *
	 * @param Bengine_Game_Model_Message $message
	 *
	 * @return Bengine_Game_MessageFolder_Abstract
	 */
	protected function _formatFeed(Bengine_Game_Model_Message $message)
	{
		$message->set("link", BASE_URL);
		$message->set("message", strip_tags($message->get("message"), "<p><div><table><thead><tbody><tfoot><tr><th><td><span><b><i><strong>"));
		return $this;
	}

	/**
	 * @param Bengine_Game_Model_Message $message
	 * @return Bengine_Game_MessageFolder_Abstract
	 */
	protected function replaceForeignSessionId(Bengine_Game_Model_Message $message)
	{
		$str = $message->get("message");
		$str = preg_replace("#(".BASE_URL."game)/([a-f0-9]{32})#i", "$1/".SID, $str);
		$message->set("message", $str);
		return $this;
	}

	/**
	 * Formats the message.
	 *
	 * @param Bengine_Game_Model_Message $message
	 *
	 * @return Bengine_Game_MessageFolder_Abstract
	 */
	abstract protected function _format(Bengine_Game_Model_Message $message);
}