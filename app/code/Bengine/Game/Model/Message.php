<?php
/**
 * Message model.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Message.php 54 2011-08-14 16:19:51Z secretchampion $
 */

class Bengine_Game_Model_Message extends Recipe_Model_Abstract
{
	/**
	 * Folder id for user messages.
	 *
	 * @var integer
	 */
	const USER_FOLDER_ID = 1;

	/**
	 * Folder id for the message outbox.
	 *
	 * @var integer
	 */
	const OUTBOX_FOLDER_ID = 2;

	/**
	 * Folder id for fleet reports.
	 *
	 * @var integer
	 */
	const FLEET_REPORTS_FOLDER_ID = 3;

	/**
	 * Folder id for espionage reports.
	 *
	 * @var integer
	 */
	const ESPIONAGE_REPORTS_FOLDER_ID = 4;

	/**
	 * Folder id for combat reports.
	 *
	 * @var integer
	 */
	const COMBAT_REPORTS_FOLDER_ID = 5;

	/**
	 * Folder id for alliance messages.
	 *
	 * @var integer
	 */
	const ALLIANCE_FOLDER_ID = 6;

	/**
	 * (non-PHPdoc)
	 * @see lib/Object#init()
	 */
	protected function init()
	{
		$this->setTableName("message");
		$this->setPrimaryKey("msgid");
		$this->setModelName("game/message");
		return parent::init();
	}

	/**
	 * Sets the folder id.
	 *
	 * @param integer
	 *
	 * @return Bengine_Game_Model_Message
	 */
	public function setFolder($folderId)
	{
		$this->setMode($folderId);
		return $this;
	}

	/**
	 * Returns the folder id.
	 *
	 * @return integer
	 */
	public function getFolder()
	{
		return $this->getMode();
	}

	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine/Model/Bengine_Game_Model_Abstract#_beforeSave()
	 * TODO
	 */
	public function send()
	{
		if(!$this->getReceiver() || !$this->getMode() || !$this->getMessage() || !$this->getSubject())
		{
			throw new Recipe_Exception_Generic("Missing required data to send message.");
		}
		if(is_null($this->getRead()))
		{
			$this->setRead(0);
		}
		if(!$this->getTime())
		{
			$this->setTime(TIME);
		}
		if($this->getSender() && $this->getSendToOutbox())
		{
			$message = Application::getModel("game/message");
			$message->setReceiver($this->getSender())
				->setMode(self::OUTBOX_FOLDER_ID)
				->setMessage($this->getMessage())
				->setSender($this->getReceiver())
				->setSubject($this->getSubject())
				->setSendToOutbox(false);
			$message->save();
		}
		$this->save();
		return $this;
	}
}
?>