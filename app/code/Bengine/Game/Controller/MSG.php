<?php
/**
 * Shows messages.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: MSG.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_Game_Controller_MSG extends Bengine_Game_Controller_Abstract
{
	/**
	 * Constructor: Displays message folders.
	 *
	 * @return Bengine_Game_Controller_MSG
	 */
	protected function init()
	{
		Core::getLanguage()->load(array("Message"));
		return parent::init();
	}

	/**
	 * Index action.
	 *
	 * @return Bengine_Game_Controller_MSG
	 */
	protected function indexAction()
	{
		$select = array("f.folder_id", "f.label", "f.is_standard", "COUNT(m.msgid) AS messages", "SUM(m.read) AS `read`", "SUM(LENGTH(m.message)) AS `storage`");
		$joins = "LEFT JOIN ".PREFIX."message m ON (m.mode = f.folder_id AND m.receiver = '".Core::getUser()->get("userid")."')";
		$where = Core::getDB()->quoteInto("f.userid = ? OR f.is_standard = '1'", Core::getUser()->get("userid"));
		$result = Core::getQuery()->select("folder f", $select, $joins, $where, "", "", "f.folder_id");
		$folders = array();
		foreach($result->fetchAll() as $row)
		{
			$unreadMessages = $row["messages"] - (int) $row["read"];
			if($unreadMessages > 1)
			{
				$read = "UNREAD";
				$newMessages = sprintf(Core::getLanguage()->getItem("F_NEW_MESSAGES"), fNumber($unreadMessages));
			}
			else if($unreadMessages > 0)
			{
				$read = "UNREAD";
				$newMessages = Core::getLanguage()->getItem("F_NEW_MESSAGE");
			}
			else
			{
				$read = "READ";
				$newMessages = "";
			}
			$label = ($row["is_standard"]) ? Core::getLang()->get($row["label"]) : $row["label"];
			$link = "game/".SID."/MSG/ReadFolder/".$row["folder_id"];
			$folders[] = array(
				"image"		=> Link::get("game/".SID."/MSG/markasread/".$row["folder_id"], Image::getImage(strtolower($read).".gif", Core::getLang()->get($read))),
				"label"		=> Link::get($link, $label, Core::getLang()->get($read)),
				"messages"	=> fNumber($row["messages"]),
				"newMessages" => $newMessages,
				"size"		=> File::bytesToString($row["storage"]),
			);
		}
		$result->closeCursor();
		Core::getTPL()->addLoop("folders", $folders);
		return $this;
	}

	/**
	 * Deletes all messages of the user.
	 *
	 * return Bengine_Game_Controller_MSG
	 */
	protected function deleteAllAction()
	{
		Core::getQuery()->delete("message", "receiver = ?", null, null, array(Core::getUser()->get("userid")));
		return $this->redirect("game/".SID."/MSG");
	}

	/**
	 * Form to send a new message.
	 *
	 * @param string $receiver	Receiver
	 * @param string $reply		Reply subject
	 *
	 * @return Bengine_Game_Controller_MSG
	 */
	protected function writeAction($receiver, $reply)
	{
		if($this->isPost())
		{
			$this->sendMessage($this->getParam("receiver"), $this->getParam("subject"), $this->getParam("message"));
		}
		Core::getLanguage()->load("Message");
		Core::getTPL()->assign("receiver", $receiver);
		Core::getTPL()->assign("subject", ($reply != "") ? Link::urlDecode($reply) : Core::getLanguage()->getItem("NO_SUBJECT"));
		Core::getTPL()->assign("maxpmlength", fNumber(Core::getOptions()->get("MAX_PM_LENGTH")));
		$sendAction = BASE_URL."game/".SID."/MSG/Write/".rawurlencode($receiver);
		if($reply != "")
		{
			$sendAction .= "/".Link::urlEncode($reply);
		}
		Core::getTPL()->assign("sendAction", $sendAction);
		Core::getLang()->assign("receiver", empty($receiver) ? "[".Core::getLang()->get("RECEIVER")."]" : $receiver);
		Core::getTPL()->assign("defaultMessageTemplate", Core::getLang()->get("WRITE_MESSAGE_TEMPLATE"));
		return $this;
	}

	/**
	 * Sends a message.
	 *
	 * @param string $receiver	Receiver name
	 * @param string $subject	Subject
	 * @param string $message	Message
	 *
	 * @return Bengine_Game_Controller_MSG
	 */
	protected function sendMessage($receiver, $subject, $message)
	{
		$this->checkForSpam();
		$receiver = trim($receiver);
		$subject = trim($subject);
		$message = richText($message);
		$messageLength = Str::length(strip_tags($message));
		if($messageLength >= 3 && $messageLength <= Core::getOptions()->get("MAX_PM_LENGTH") && Str::length($subject) > 0 && Str::length($subject) < 101)
		{
			$result = Core::getQuery()->select("user", "userid", "", Core::getDB()->quoteInto("username = ?", $receiver));
			if($row = $result->fetchRow())
			{
				$result->closeCursor();
				if($row["userid"] != Core::getUser()->get("userid"))
				{
					$subject = preg_replace("#((RE|FW):\s)+#is", "\\1", $subject); // Remove excessive reply or forward notes
					Hook::event("SendPrivateMessage", array(&$row, $receiver, &$subject, &$message));
					Core::getQuery()->insert("message", array("mode" => 1, "time" => TIME, "sender" => Core::getUser()->get("userid"), "receiver" => $row["userid"], "subject" => Str::validateXHTML($subject), "message" => $message, "read" => 0));
					Core::getQuery()->insert("message", array("mode" => 2, "time" => TIME, "sender" => $row["userid"], "receiver" => Core::getUser()->get("userid"), "subject" => Str::validateXHTML($subject), "message" => $message, "read" => 1));
					$msgid = Core::getDB()->lastInsertId();
					if($msgid > 10000000)
					{
						$TableCleaner = new Recipe_Maintenance_TableCleaner("message", "msgid");
						$TableCleaner->clean();
					}
					Logger::addMessage("SENT_SUCCESSFUL", "success");
				}
				else
				{
					Core::getTPL()->assign("userError", Logger::getMessageField("SELF_MESSAGE"));
				}
			}
			else
			{
				$result->closeCursor();
				Core::getTPL()->assign("userError", Logger::getMessageField("USER_NOT_FOUND"));
			}
		}
		else
		{
			if($messageLength < 3) { Core::getTPL()->assign("messageError", Logger::getMessageField("MESSAGE_TOO_SHORT")); }
			if($messageLength > Core::getOptions()->get("MAX_PM_LENGTH")) { Core::getTPL()->assign("messageError", Logger::getMessageField("MESSAGE_TOO_LONG")); }
			if(Str::length($subject) == 0 || Str::length($subject) > 50) { Core::getTPL()->assign("subjectError", Logger::getMessageField("SUBJECT_TOO_SHORT")); }
		}
		return $this;
	}

	/**
	 * Deletes messages.
	 *
	 * @param integer $folder	Folder id
	 * @param integer $option	Mode to delete content
	 * @param array $msgs		Messages
	 *
	 * @return Bengine_Game_Controller_MSG
	 */
	protected function deleteMessages($folder, $option, array $msgs)
	{
		$pagination = $this->getPagination($folder);
		$deltime = 604800;
		if(is_numeric(Core::getOptions()->get("DEL_MESSAGE_DAYS")) && Core::getOptions()->get("DEL_MESSAGE_DAYS") > 0)
		{
			$deltime = ((int) Core::getOptions()->get("DEL_MESSAGE_DAYS")) * 86400;
		}
		$deltime = TIME - $deltime;
		Core::getQuery()->delete("message", "time <= ?", null, null, array($deltime));
		switch($option)
		{
			case 1:
				foreach($msgs as $msgid)
				{
					Core::getQuery()->delete("message", "msgid = ? AND receiver = ?", null, null, array($msgid, Core::getUser()->get("userid")));
				}
			break;
			case 2:
				$where = Core::getDB()->quoteInto("receiver = ? AND mode = ?", array(Core::getUser()->get("userid"), $folder));
				$result = Core::getQuery()->select("message", "msgid", "", $where, "time DESC", $pagination->getStart().", ".Core::getOptions()->get("MAX_PMS"));
				foreach($result->fetchAll() as $row)
				{
					if(!in_array($row["msgid"], $msgs))
					{
						Core::getQuery()->delete("message", "msgid = ?", null, null, array($row["msgid"]));
					}
				}
				$result->closeCursor();
			break;
			case 3:
				$where = Core::getDB()->quoteInto("receiver = ? AND mode = ?", array(Core::getUser()->get("userid"), $folder));
				$result = Core::getQuery()->select("message", array("msgid"), "", $where, "time DESC", $pagination->getStart().", ".Core::getOptions()->get("MAX_PMS"));
				foreach($result->fetchAll() as $row)
				{
					Core::getQuery()->delete("message", "msgid = ?", null, null, array($row["msgid"]));
				}
			break;
			case 4:
				Core::getQuery()->delete("message", "receiver = ? AND mode = ?", null, null, array(Core::getUser()->get("userid"), $folder));
			break;
			case 5:
				$reports = array();
				$modId = Game::getRandomModerator();
				foreach($msgs as $msgid)
				{
					$where = Core::getDB()->quoteInto("m.msgid = ? AND m.receiver = ?", array(
						$msgid, Core::getUser()->get("userid")
					));
					$result = Core::getQuery()->select("message m", array("m.sender", "m.mode", "m.message", "m.time", "u.username"), "LEFT JOIN ".PREFIX."user u ON (u.userid = m.sender)", $where);
					if($row = $result->fetchRow())
					{
						if(($row["sender"] > 0 || $row["mode"] == 5) && $row["sender"] != $modId)
						{
							$reports[] = $row;
						}
					}
				}
				if(count($reports) > 0)
				{
					Core::getLang()->assign("reportSender", Core::getUser()->get("username"));
					foreach($reports as $report)
					{
						Core::getLang()->assign("reportMessage", $report["message"]);
						Core::getLang()->assign("reportUser", $report["username"]);
						Core::getLang()->assign("reportSendTime", Date::timeToString(1, $report["time"], "", false));
						if($report["mode"] == 5)
						{
							$assault = Game::getModel("game/assault")->load((int) $report["message"]);
							$url = BASE_URL.Core::getLang()->getOpt("langcode")."/combat/report/".$assault->get("assaultid")."/".$assault->get("key");
							$gentime = $assault->get("gentime") / 1000;
							$label = Core::getLanguage()->getItem("ASSAULT_REPORT")." (A: ".fNumber($assault->get("lostunits_attacker")).", D: ".fNumber($assault->get("lostunits_defender")).") ".$gentime."s";
							Core::getLang()->assign("reportLink", "<span class=\"assault-report\" onclick=\"window.open('".$url."')\">".$label."</span>");
							$message = Core::getDB()->escape(Core::getLang()->get("MODERATOR_REPORT_COMBAT"));
						}
						else
						{
							richText($message = Core::getLang()->get("MODERATOR_REPORT_MESSAGE"));
						}
						$subject = Core::getLang()->get("MODERATOR_REPORT_SUBJECT");
						$spec = array(
							"sender" => null,
							"mode" => 1,
							"subject" => $subject,
							"message" => $message,
							"receiver" => $modId,
							"time" => TIME,
							"read" => 0,
						);
						Core::getQuery()->insert("message", $spec);
					}
					Logger::addMessage("MESSAGES_REPORTED", "success");
				}
			break;
		}
		return $this;
	}

	/**
	 * Creates a new pagination object for the given folder.
	 *
	 * @param integer $id	Folder id
	 *
	 * @return Pagination
	 */
	protected function getPagination($id)
	{
		/* @var Bengine_Game_Model_Collection_Message $messages */
		$messages = Game::getCollection("game/message");
		$messages->addReceiverFilter(Core::getUser()->get("userid"))
			->addFolderFilter($id);
		$pagination = new Pagination(Core::getOptions()->get("MAX_PMS"), $messages->getCalculatedSize(false));
		$pagination->setMaxPagesToShow(Core::getConfig()->get("MAX_MESSAGE_PAGES"));
		return $pagination;
	}

	/**
	 * Shows content of a message folder.
	 *
	 * @param integer $id	Folder id
	 *
	 * @return Bengine_Game_Controller_MSG
	 */
	protected function readFolderAction($id)
	{
		if($this->isPost())
		{
			$this->deleteMessages($id, $this->getParam("deleteOption"), $this->getParam("msgid", array()));
		}
		$pagination = $this->getPagination($id);
		$pagination->render();

		$readMessages = array();
		/* @var Bengine_Game_Model_Collection_Message $messages */
		$messages = Game::getCollection("game/message");
		$messages->addTimeOrder()
			->addFolderJoin()
			->addReceiverFilter(Core::getUser()->get("userid"))
			->addFolderFilter($id)
			->setPagination($pagination);

		$folderObjCache = array();
		/* @var Bengine_Game_Model_Message $message */
		foreach($messages as $message)
		{
			Hook::event("ReadMessageFirst", array($message));
			$folderCode = $message->get("folder_class");
			if(!isset($folderObjCache[$folderCode]))
			{
				$folderClass = explode("/", $folderCode);
				$folderClass = $folderClass[0]."/messageFolder_".$folderClass[1];
				$folderObj = Application::factory($folderClass);
				$folderObjCache[$folderCode] = $folderObj;
			}
			else
			{
				$folderObj = $folderObjCache[$folderCode];
			}
			$folderObj->formatMessage($message);
			if(!$message->get("read"))
			{
				$readMessages[] = (int) $message->get("msgid");
			}
			Hook::event("ReadMessageLast", array($message));
		}
		if(count($readMessages) > 0)
		{
			Core::getQuery()->update("message", array("read" => 1), "msgid = ".implode(" OR msgid = ", $readMessages));
		}
		Core::getTPL()->addLoop("messages", $messages);
		Core::getTPL()->assign("mode", $id);
		Core::getTPL()->assign("pagination", $pagination);
		return $this;
	}

	/**
	 * Mark a folder as read.
	 *
	 * @param integer $folderId	Folder id to mark
	 *
	 * @return Bengine_Game_Controller_MSG
	 */
	protected function markAsReadAction($folderId)
	{
		Hook::event("MarkFolderAsRead", array($folderId));
		Core::getQuery()->update("message", array("read" => 1), "mode = ? AND receiver = ?", array($folderId, Core::getUser()->get("userid")));
		$this->redirect("game/".SID."/MSG");
		return $this;
	}

	/**
	 * Checks if the user send to many message (spam protection).
	 *
	 * @return Bengine_Game_Controller_MSG
	 */
	protected function checkForSpam()
	{
		if($max = Core::getConfig()->get("MESSAGE_FLOOD_MAX"))
		{
			$bind = array(
				Core::getUser()->get("userid"),
				TIME - Core::getConfig()->get("MESSAGE_FLOOD_SPAN"),
				Bengine_Game_Model_Message::USER_FOLDER_ID
			);
			$result = Core::getQuery()->select("message", array("COUNT(msgid)"), "", "sender = ? AND time >= ? AND mode = ?", "", 1, "", "", $bind);
			if($result->fetchColumn() >= $max)
			{
				Logger::dieMessage("MESSAGE_FLOOD_INFO", "warning");
			}
		}
		return $this;
	}
}
?>