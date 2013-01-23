<?php
/**
 * Feed controller
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Feed.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_Comm_Controller_Feed extends Bengine_Comm_Controller_Abstract
{
	/**
	 * Max items per feed.
	 *
	 * @var integer
	 */
	const MAX_FEED_ITEMS = 10;

	/**
	 * Folder id of the outbox.
	 *
	 * @var integer
	 */
	const OUTBOX_FOLDER_ID = 2;

	/**
	 * Folder id of combat reports.
	 *
	 * @var integer
	 */
	const COMBATS_REPORT_FOLDER_ID = 5;

	/**
	 * Userid for the feed.
	 *
	 * @var integer
	 */
	protected $user_id = 0;

	/**
	 * Index action.
	 *
	 * @return Bengine_Comm_Controller_Feed
	 */
	public function index()
	{
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine_Comm/Controller/Bengine_Comm_Controller_Abstract#init()
	 */
	protected function init()
	{
		Core::getLanguage()->load(array("Prefs", "Message"));
		$this->setIsAjax();
	}

	/**
	 * Checks if the given key is ok for the userid.
	 *
	 * @param integer $user_id
	 * @param string $key
	 *
	 * @return boolean
	 */
	public function checkKey($user_id, $key)
	{
		$where  = Core::getDB()->quoteInto("user_id = ?", $user_id);
		$where .= Core::getDB()->quoteInto(" AND feed_key = ?", $key);
		$result = Core::getQuery()->select("feed_keys", array("feed_key"), "", $where);
		return ($result->rowCount() > 0);
	}

	/**
	 * Creates and shows an rss feed.
	 *
	 * @return Bengine_Comm_Controller_Feed
	 */
	public function rssAction()
	{
		$this->assign("title", Core::getLang()->get("FEED"));
		$this->assign("langcode", Core::getLang()->getOpt("langcode"));
		$this->showFeed($this->getParam("action"));
		return $this;
	}

	/**
	 * Creates and shows an atom feed.
	 *
	 * @return Bengine_Comm_Controller_Feed
	 */
	public function atomAction()
	{
		$this->showFeed($this->getParam("action"));
		return $this;
	}

	/**
	 * Shows the feeds.
	 *
	 * @param string $type
	 * @throws Recipe_Exception_Generic
	 * @return Bengine_Comm_Controller_Feed
	 */
	protected function showFeed($type)
	{
		$this->user_id = $this->getParam("1");
		$key = $this->getParam("2");

		if(!$this->checkKey($this->user_id, $key))
		{
			throw new Recipe_Exception_Generic("You have followed an invalid feed link.");
		}

		Core::getTPL()->setContentType("application/xml");
		$this->assign("selfUrl", BASE_URL."feed/".$type);
		$this->assign("alternateUrl", BASE_URL."feed");
		$this->assign("title", Core::getLang()->get("FEED"));
		Core::getTPL()->addLoop("feed", $this->getItems(0, self::MAX_FEED_ITEMS));
		$this->setTemplate($type);
		return $this;
	}

	/**
	 * Returns the last messages for the user.
	 *
	 * @param integer $offset
	 * @param integer $count
	 *
	 * @return array
	 */
	protected function getItems($offset, $count)
	{
		$folderClassCache = array();
		$items = array();
		/* @var Bengine_Game_Model_Collection_Message $messages */
		$messages = Comm::getCollection("game/message");
		$messages->addTimeOrder()
			->addReceiverFilter($this->user_id)
			->addFolderJoin();
		$select = $messages->getSelect();
		$select->limit($offset, $count);
		$select->where("m.mode != ?", self::OUTBOX_FOLDER_ID);
		/* @var Bengine_Game_Model_Message $message */
		foreach($messages as $message)
		{
			/* @var Bengine_Game_MessageFolder_Abstract $folderObj */
			$folderCode = $message->get("folder_class");
			if(!isset($folderClassCache[$folderCode]))
			{
				$folderClass = explode("/", $folderCode);
				$folderClass = $folderClass[0]."/messageFolder_".$folderClass[1];
				$folderObj = Application::factory($folderClass);
				$folderClassCache[$folderCode] = $folderObj;
			}
			else
			{
				$folderObj = $folderClassCache[$folderCode];
			}
			$folderObj->formatMessage($message, true);

			$items[] = array(
				"date"	=> Date::timeToString(3, $message->getTime(), "D, d M Y H:i:s O", false),
				"author" => ($message->get("username")) ? $message->get("username") : "System",
				"title" => strip_tags($message->get("subject")),
				"text" => $message->get("message"),
				"link" => $message->get("link"),
				"date_atom" => Date::timeToString(3, $message->getTime(), "c", false),
				"guid" => $message->getId(),
			);
		}
		return $items;
	}
}
?>