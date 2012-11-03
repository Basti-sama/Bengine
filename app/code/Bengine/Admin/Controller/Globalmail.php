<?php
class Bengine_Admin_Controller_Globalmail extends Bengine_Admin_Controller_Abstract
{
	/**
	 * Initialisation
	 *
	 * @return Bengine_Admin_Controller_Globalmail
	 */
	protected function init()
	{
		Core::getLanguage()->load("AI_Globalmail");
		return parent::init();
	}

	/**
	 * Index page
	 *
	 * @return Bengine_Admin_Controller_Globalmail
	 */
	protected function indexAction()
	{
		if($this->getParam("send"))
		{
			$this->send();
		}
		Core::getTPL()->assign("APP_ROOT_DIR", substr(BU, 0, -3));
		Core::getTPL()->assign("langcode", "de");
		return $this;
	}

	protected function send()
	{
		$subject = Str::validateXHTML($this->getParam("subject"));
		$message = richtext($this->getParam("message"));
		if(Str::length($message) < 10)
		{
			Core::getTPL()->assign("messageError", Logger::getMessageField("MESSAGE_TOO_SHORT"));
			$error = true;
		}
		if(Str::length($subject) == 0)
		{
			Core::getTPL()->assign("subjectError", Logger::getMessageField("SUBJECT_TOO_SHORT"));
			$error = true;
		}
		if($error)
		{
			return $this;
		}

		$sql = "INSERT INTO `".PREFIX."message` (`mode`, `time`, `sender`, `receiver`, `subject`, `message`, `read`) SELECT '1', '".TIME."', NULL, ".PREFIX."user.userid, '".$subject."', '".$message."', '0' FROM ".PREFIX."user";
		Core::getDB()->query($sql);
		return $this;
	}
}
?>