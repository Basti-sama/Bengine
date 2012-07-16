<?php
/**
 * Shows messages.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: MSG.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_Page_MSG extends Bengine_Page_Abstract
{
	/**
	 * Constructor: Displays message folders.
	 *
	 * @return Bengine_Page_MSG
	 */
	protected function init()
	{
		Core::getLanguage()->load(array("Message"));
		return parent::init();
	}

	/**
	 * Index action.
	 *
	 * @return Bengine_Page_MSG
	 */
	protected function indexAction()
	{
		$select = array("f.folder_id", "f.label", "f.is_standard", "COUNT(m.msgid) AS messages", "SUM(m.read) AS `read`", "SUM(LENGTH(m.message)) AS `storage`");
		$joins = "LEFT JOIN ".PREFIX."message m ON (m.mode = f.folder_id AND m.receiver = '".Core::getUser()->get("userid")."')";
		$where = "f.userid = '".Core::getUser()->get("userid")."' OR f.is_standard = '1'";
		$result = Core::getQuery()->select("folder f", $select, $joins, $where, "", "", "f.folder_id");
		$folders = array();
		while($row = Core::getDB()->fetch($result))
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
			$link = "game.php/".SID."/MSG/ReadFolder/".$row["folder_id"];
			$folders[] = array(
				"image"		=> Link::get("game.php/".SID."/MSG/markasread/".$row["folder_id"], Image::getImage(strtolower($read).".gif", Core::getLang()->get($read))),
				"label"		=> Link::get($link, $label, Core::getLang()->get($read)),
				"messages"	=> fNumber($row["messages"]),
				"newMessages" => $newMessages,
				"size"		=> File::bytesToString($row["storage"]),
			);
		}
		Core::getDB()->free_result($result);
		Core::getTPL()->addLoop("folders", $folders);
		return $this;
	}

	/**
	 * Deletes all messages of the user.
	 *
	 * return Bengine_Page_MSG
	 */
	protected function deleteAllAction()
	{
		Core::getQuery()->delete("message", "receiver = '".Core::getUser()->get("userid")."'");
		return $this->redirect("game.php/".SID."/MSG");
	}

	/**
	 * Form to send a new message.
	 *
	 * @param string $receiver	Receiver
	 * @param string $reply		Reply subject
	 *
	 * @return Bengine_Page_MSG
	 */
	protected function writeAction($receiver, $reply)
	{
		if($this->isPost())
		{
			$this->sendMessage($this->getParam("receiver"), $this->getParam("subject"), $this->getParam("message"));
		}
		Core::getLanguage()->load("Message");
		Core::getTPL()->assign("receiver", $receiver);
		Core::getTPL()->assign("subject", ($reply != "") ? $reply : Core::getLanguage()->getItem("NO_SUBJECT"));
		Core::getTPL()->assign("maxpmlength", fNumber(Core::getOptions()->get("MAX_PM_LENGTH")));
		$sendAction = BASE_URL."game.php/".SID."/MSG/Write/".rawurlencode($receiver);
		if($reply != "")
		{
			$sendAction .= "/".rawurlencode($reply);
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
	 * @return Bengine_Page_MSG
	 */
	protected function sendMessage($receiver, $subject, $message)
	{
		$receiver = trim($receiver);
		$subject = trim($subject);
		$message = richText($message);
		$messageLength = Str::length(strip_tags($message));
		if($messageLength >= 3 && $messageLength <= Core::getOptions()->get("MAX_PM_LENGTH") && Str::length($subject) > 0 && Str::length($subject) < 101)
		{
			$result = Core::getQuery()->select("user", "userid", "", "username = '".$receiver."'");
			if($row = Core::getDB()->fetch($result))
			{
				Core::getDB()->free_result($result);
				if($row["userid"] != Core::getUser()->get("userid"))
				{
					$subject = preg_replace("#((RE|FW):\s)+#is", "\\1", $subject); // Remove excessive reply or forward notes
					Hook::event("SendPrivateMessage", array(&$row, $receiver, &$subject, &$message));
					Core::getQuery()->insert("message", array("mode", "time", "sender", "receiver", "subject", "message", "read"), array(1, TIME, Core::getUser()->get("userid"), $row["userid"], Str::validateXHTML($subject), $message, 0));
					Core::getQuery()->insert("message", array("mode", "time", "sender", "receiver", "subject", "message", "read"), array(2, TIME, $row["userid"], Core::getUser()->get("userid"), Str::validateXHTML($subject), $message, 1));
					$msgid = Core::getDB()->insert_id();
					if($msgid > 10000000)
					{
						$TableCleaner = new Recipe_Maintenance_TableCleaner("message", "msgid");
						$TableCleaner->clean();
						$TableCleaner->kill();
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
				Core::getDB()->free_result($result);
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
	 * @return Bengine_Page_MSG
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
		Core::getQuery()->delete("message", "time <= '".$deltime."'");
		switch($option)
		{
			case 1:
				foreach($msgs as $msgid)
				{
					Core::getQuery()->delete("message", "msgid = '".$msgid."' AND receiver = '".Core::getUser()->get("userid")."'");
				}
			break;
			case 2:
				$result = Core::getQuery()->select("message", "msgid", "", "receiver = '".Core::getUser()->get("userid")."' AND mode = '".$folder."'", "time DESC", $pagination->getStart().", ".Core::getOptions()->get("MAX_PMS"));
				while($row = Core::getDB()->fetch($result))
				{
					if(!in_array($row["msgid"], $msgs))
					{
						Core::getQuery()->delete("message", "msgid = '".$row["msgid"]."'");
					}
				}
				Core::getDB()->free_result($result);
			break;
			case 3:
				$result = Core::getQuery()->select("message", array("msgid"), "", "receiver = '".Core::getUser()->get("userid")."' AND mode = '".$folder."'", "time DESC", $pagination->getStart().", ".Core::getOptions()->get("MAX_PMS"));
				while($row = Core::getDB()->fetch($result))
				{
					Core::getQuery()->delete("message", "msgid = '".$row["msgid"]."'");
				}
			break;
			case 4:
				Core::getQuery()->delete("message", "receiver = '".Core::getUser()->get("userid")."' AND mode = '".$folder."'");
			break;
			case 5:
				$reports = array();
				$modId = Bengine::getRandomModerator();
				foreach($msgs as $msgid)
				{
					$result = Core::getQuery()->select("message m", array("m.sender", "m.mode", "m.message", "m.time", "u.username"), "LEFT JOIN ".PREFIX."user u ON (u.userid = m.sender)", "m.msgid = '".$msgid."' AND m.receiver = '".Core::getUser()->get("userid")."'");
					if($row = Core::getDB()->fetch($result))
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
							$assault = Bengine::getModel("assault")->load((int) $report["message"]);
							$url = BASE_URL.Core::getLang()->getOpt("langcode")."/combat/report/".$assault->get("assaultid")."/".$assault->get("key");
							$gentime = $assault->get("gentime") / 1000;
							$label = Core::getLanguage()->getItem("ASSAULT_REPORT")." (A: ".fNumber($assault->get("lostunits_attacker")).", D: ".fNumber($assault->get("lostunits_defender")).") ".$gentime."s";
							Core::getLang()->assign("reportLink", "<span class=\"assault-report\" onclick=\"window.open('".$url."')\">".$label."</span>");
							$message = Core::getDB()->real_escape_string(Core::getLang()->get("MODERATOR_REPORT_COMBAT"));
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
						Core::getQuery()->insertInto("message", $spec);
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
		$messages = Bengine::getCollection("message");
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
	 * @return Bengine_Page_MSG
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
		$messages = Bengine::getCollection("message");
		$messages->addTimeOrder()
			->addFolderJoin()
			->addReceiverFilter(Core::getUser()->get("userid"))
			->addFolderFilter($id)
			->setPagination($pagination);

		$folderClassCache = array();
		foreach($messages as $message)
		{
			Hook::event("ReadMessageFirst", array($message));
			if(!isset($folderClassCache[$message->get("folder_class")]))
			{
				$folderClass = explode("/", $message->get("folder_class"));
				$folderClass[0] = ucwords($folderClass[0]);
				$folderClass[1] = ucwords($folderClass[1]);
				$folderClass = $folderClass[0]."_MessageFolder_".$folderClass[1];
				$folderClass = new $folderClass();
				$folderClassCache[$message->get("folder_class")] = $folderClass;
			}
			else
			{
				$folderClass = $folderClassCache[$message->get("folder_class")];
			}
			$folderClass->formatMessage($message);
			if(!$message->get("read"))
			{
				$readMessages[] = $message->get("msgid");
			}
			Hook::event("ReadMessageLast", array($message));
		}
		if(count($readMessages) > 0)
		{
			Core::getQuery()->update("message", "read", 1, "msgid = ".implode(" OR msgid = ", $readMessages));
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
	 * @return Bengine_Page_MSG
	 */
	protected function markAsReadAction($folderId)
	{
		Hook::event("MarkFolderAsRead", array($folderId));
		Core::getQuery()->update("message", array("read"), array(1), "mode = '".$folderId."' AND receiver = '".Core::getUser()->get("userid")."'");
		$this->redirect("game.php/".SID."/MSG");
		return $this;
	}
}
?>