<?php
/**
 * Message collection.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Message.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Model_Collection_Message extends Recipe_Model_Collection_Abstract
{
	/**
	 * Adds a time order.
	 *
	 * @param string
	 *
	 * @return Bengine_Model_Collection_Message
	 */
	public function addTimeOrder($order = "DESC")
	{
		$this->getSelect()->order(array("m" => "time"), $order);
		return $this;
	}

	/**
	 * Adds receiver filter.
	 *
	 * @param integer|Bengine_Model_User
	 *
	 * @return Bengine_Model_Collection_Message
	 */
	public function addReceiverFilter($receiver)
	{
		if($receiver instanceof Bengine_Model_User)
		{
			$receiver = $receiver->getId();
		}
		$this->getSelect()->where(array("m" => "receiver"), $receiver);
		return $this;
	}

	/**
	 * Adds sender filter.
	 *
	 * @param integer|Bengine_Model_User
	 *
	 * @return Bengine_Model_Collection_Message
	 */
	public function addSenderFilter($sender)
	{
		if($sender instanceof Bengine_Model_User)
		{
			$sender = $sender->getId();
		}
		$this->getSelect()->where(array("m" => "sender"), $sender);
		return $this;
	}

	/**
	 * Adds folder filter.
	 *
	 * @param integer
	 *
	 * @return Bengine_Model_Collection_Message
	 */
	public function addFolderFilter($folder)
	{
		$this->getSelect()->where(array("m" => "mode"), $folder);
		return $this;
	}

	/**
	 * Adds the folder join.
	 *
	 * @return Bengine_Model_Collection_Message
	 */
	public function addFolderJoin()
	{
		$this->getSelect()
			->join(array("f" => "folder"), array("f" => "folder_id", "m" => "mode"))
			->attributes(array("f" => array("folder_label" => "label", "folder_class" => "class")));
		return $this;
	}
}
?>