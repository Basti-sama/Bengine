<?php
/**
 * Allows the user to change the preferences.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Preferences.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_Page_Preferences extends Bengine_Page_Abstract
{
	/**
	 * Delete protection time in seconds.
	 *
	 * @var integer
	 */
	const DELETE_PROTECTION_TIME = 604800;

	/**
	 * Shows the preferences form.
	 *
	 * @return Bengine_Page_Preferences
	 */
	protected function init()
	{
		Core::getLanguage()->load(array("Prefs"));
		Hook::event("ShowUserPreferences");
		return parent::init();
	}

	/**
	 * Index action.
	 *
	 * @return Bengine_Page_Preferences
	 */
	protected function indexAction()
	{
		/* @var Bengine_Model_Collection_Event $events */
		$events = Bengine::getCollection("event");
		$events->addVacationModeFilter(Core::getUser()->get("userid"));
		if($this->isPost())
		{
			if(!Core::getUser()->get("umode") && $this->getParam("saveuserdata"))
			{
				$this->updateUserData(
					$this->getParam("username"), $this->getParam("usertitle"), $this->getParam("email"), $this->getParam("password"), $this->getParam("theme"), $this->getParam("language"), $this->getParam("templatepackage"),
					$this->getParam("umode"), $this->getParam("delete"), $this->getParam("ipcheck"), $this->getParam("planetorder"), $this->getParam("esps"), $this->getParam("generate_key"),
					$this->getParam("js_interface")
				);
			}
			else if(TIME > Core::getUser()->get("umodemin") && $this->getParam("disable_umode"))
			{
				$this->disableUmode();
			}
			if($this->getParam("update_deletion"))
			{
				$this->updateDeletion($this->getParam("delete"));
			}
		}
		if(Core::getUser()->get("delete") > 0)
		{
			$delmsg = Date::timeToString(2, Core::getUser()->get("delete"), "", false);
			$delmsg = sprintf(Core::getLanguage()->getItem("DELETE_DATE"), $delmsg);
			Core::getTPL()->assign("delmessage", $delmsg);
		}
		if(Core::getUser()->get("umode"))
		{
			$canDisableUmode = true;
			if(Core::getUser()->get("umodemin") > TIME)
			{
				$canDisableUmode = false;
				$umodemsg = Date::timeToString(1, Core::getUser()->get("umodemin"), "", false);
				$umodemsg = sprintf(Core::getLanguage()->getItem("UMODE_DATE"), $umodemsg);
				Core::getTPL()->assign("umode_to", $umodemsg);
			}
			Core::getTPL()->assign("can_disable_umode", $canDisableUmode);
		}
		$packs = array();
		$excludedPackages = explode(",", Core::getOptions()->get("EXCLUDE_TEMPLATE_PACKAGE"));
		$excludedPackages = array_map("trim", $excludedPackages);
		$dir = new DirectoryIterator(APP_ROOT_DIR."app/templates/");
		/* @var DirectoryIterator $package */
		foreach($dir as $package)
		{
			if(!$package->isDot() && $package->isDir() && !in_array($package->getBasename(), $excludedPackages))
			{
				$packs[] = array("package" => $package->getFilename());
			}
		}
		Hook::event("LoadTemplatePackages", array(&$packs));
		Core::getTPL()->addLoop("templatePacks", $packs);

		$result = Core::getQuery()->select("languages", array("languageid", "title"), "", "", "title ASC");
		Core::getTPL()->addLoop("langs", $result);

		$result = Core::getQuery()->select("feed_keys", array("feed_key"), "", "user_id = ".Core::getUser()->get("userid"));
		if(Core::getDB()->num_rows($result) > 0)
		{
			$feed_key = Core::getDatabase()->fetch_field($result, "feed_key");
			$link = BASE_URL.Core::getLang()->getOpt("langcode")."/feed/{type}/".Core::getUser()->get("userid")."/".$feed_key;
			$rss = Str::replace("{type}", "rss", $link);
			$atom = Str::replace("{type}", "atom", $link);
			$this->assign("rss_feed_url", Link::get($rss, $rss, "", "", "target=\"_blank\""));
			$this->assign("atom_feed_url", Link::get($atom, $atom, "", "", "target=\"_blank\""));
		}
		else
		{
	  		$this->assign("rss_feed_url", "-");
			$this->assign("atom_feed_url", "-");
		}

		$this->assign("goToSignature", Link::get("game.php/".SID."/Preferences/Signature", Core::getLang()->get("GO_TO_SIGNATURE")));
		return $this;
	}

	/**
	 * Updates the deletion flag for this user.
	 *
	 * @param boolean $deletion	Enable/Disable deletion
	 *
	 * @return Bengine_Page_Preferences
	 */
	protected function updateDeletion($deletion)
	{
		$delete = 0;
		// Deletion
		if($deletion)
		{
			$delete = TIME + self::DELETE_PROTECTION_TIME;
		}
		Hook::event("UpdateUserDeletion", array(&$delete));
		Core::getQuery()->update("user", "delete", $delete, "userid = '".Core::getUser()->get("userid")."'");
		Core::getUser()->rebuild();
		$this->redirect("game.php/".SID."/Preferences");
		return $this;
	}

	/**
	 * Saves the entered preferences.
	 *
	 * @return Bengine_Page_Preferences
	 */
	protected function updateUserData($username, $usertitle, $email, $pw, $theme, $language, $templatepackage, $umode, $delete, $ipcheck, $planetorder, $esps, $generate_key, $js_interface)
	{
		if(Core::getUser()->get("umode")) { throw new Recipe_Exception_Generic("Vacation mode is still enabled."); }
		Core::getLanguage()->load("Registration");
		Hook::event("SaveUserDataFirst");
		$username = trim(str_replace("  ", " ", $username));
		$usertitle = trim($usertitle);
		$js_interface = trim($js_interface);
		$language = (int) (empty($language) ? Core::getConfig()->get("defaultlanguage") : $language);
		if(!preg_match("/^.+\/$/i", $theme) && $theme != "")
		{
			$theme .= "/";
		}
		$templatepackage = (empty($templatepackage)) ? "standard" : $templatepackage;
		if(!is_dir(APP_ROOT_DIR."templates/".$templatepackage))
		{
			$templatepackage = Core::getUser()->get("templatepackage");
		}
		$activation = "";

		// Check language
		if(Core::getUser()->get("languageid") != $language)
		{
			$result = Core::getQuery()->select("languages", "languageid", "", "languageid = '".$language."'");
			if(Core::getDB()->num_rows($result) <= 0)
			{
				$language = Core::getUser()->get("languageid");
			}
			Core::getDB()->free_result($result);
		}

		// Check username
		if(!Str::compare($username, Core::getUser()->get("username")))
		{
			$result = Core::getQuery()->select("user", "userid", "", "username = '$username'");
			if(Core::getDB()->num_rows($result) == 0)
			{
				Core::getDB()->free_result($result);
				if(!checkCharacters($username))
				{
					$username = Core::getUser()->get("username");
					Logger::addMessage("USERNAME_INVALID");
				}
				else
				{
					Logger::addMessage("USERNAME_CHANGED", "success");
				}
			}
			else
			{
				Core::getDB()->free_result($result);
				$username = Core::getUser()->get("username");
				Logger::addMessage("USERNAME_EXISTS");
			}
		}

		// Check user title
		if(!Str::compare($usertitle, Core::getUser()->get("usertitle")))
		{
			$length = Str::length($usertitle);
			if($length < Core::getOptions()->get("MIN_USER_CHARS") || $length > Core::getOptions()->get("MAX_USER_CHARS"))
			{
				$usertitle = Core::getUser()->get("usertitle");
			}
		}

		// Check email
		if(!Str::compare($email, Core::getUser()->get("email")))
		{
			$result = Core::getQuery()->select("user", "userid", "", "email = '$email'");
			if(Core::getDB()->num_rows($result) == 0)
			{
				Core::getDB()->free_result($result);
				if(!checkEmail($email))
				{
					$email = Core::getUser()->get("email");
					Logger::addMessage("EMAIL_INVALID");
				}
				else
				{
					$successMsg = "EMAIL_CHANGED";
					if(Core::getConfig()->get("EMAIL_ACTIVATION_CHANGED_EMAIL"))
					{
						$activation = randString(8);
						$url = BASE_URL.Core::getLang()->getOpt("langcode")."/signup/activation/key:".$activation;
						Core::getLang()->assign("username", $username);
						Core::getTemplate()->assign("activationUrl", $url);
						$template = new Recipe_Email_Template("email_changed");
						$mail = new Email($email, Core::getLanguage()->getItem("EMAIL_ACTIVATION"));
						$template->send($mail);
						$successMsg .= "_REVALIDATE";
					}
					Logger::addMessage($successMsg, "success");
				}
			}
			else
			{
				Core::getDB()->free_result($result);
				Logger::addMessage("EMAIL_EXISTS");
				$email = Core::getUser()->get("email");
			}
		}

		// Check password
		$pwLength = Str::length($pw);
		if($pwLength > 0)
		{
			if($pwLength >= Core::getOptions()->get("MIN_PASSWORD_LENGTH") && $pwLength <= Core::getOptions()->get("MAX_PASSWORD_LENGTH"))
			{
				$encryption = Core::getOptions("USE_PASSWORD_SALT") ? "md5_salt" : "md5";
				$pw = Str::encode($pw, $encryption);
				Core::getQuery()->updateSet("password", array("password" => $pw, "time" => TIME), "userid = '".Core::getUser()->get("userid")."'");
				$successMsg = "PASSWORD_CHANGED";
				if($activation == "" && Core::getConfig()->get("EMAIL_ACTIVATION_CHANGED_PASSWORD"))
				{
					$activation = randString(8);
					$url = BASE_URL.Core::getLang()->getOpt("langcode")."/signup/activation/key:".$activation;
					Core::getLang()->assign("username", $username);
					Core::getTemplate()->assign("activationUrl", $url);
					Core::getTemplate()->assign("newPassword", $pw);
					$template = new Recipe_Email_Template("changed_password");
					$mail = new Email($email, Core::getLanguage()->getItem("PASSWORD_ACTIVATION"));
					$template->send($mail);
					$successMsg .= "_REVALIDATE";
				}
				Logger::addMessage($successMsg, "success");
			}
			else
			{
				Logger::addMessage("PASSWORD_INVALID");
			}
		}

		// Umode
		if($umode == 1)
		{
			// Check if umode can be activated
			/* @var Bengine_Model_Collection_Event $events */
			$events = Bengine::getCollection("event");
			$events->addVacationModeFilter(Core::getUser()->get("userid"));
			$eventCount = $events->getCalculatedSize();
			if($eventCount > 0)
			{
				Logger::dieMessage("CANNOT_ACTIVATE_UMODE");
			}

			$umodemin = TIME + Core::getConfig()->get("MIN_VACATION_MODE");
			setProdOfUser(Core::getUser()->get("userid"), 0);
		}
		else
		{
			$umodemin = 0;
			$umode = 0;
		}

		// Deletition
		$delete = (!$delete) ? 0 : TIME + self::DELETE_PROTECTION_TIME;

		// Other prefs
		$ipcheck = (int) $ipcheck;
		if(!Core::getConfig()->get("USER_EDIT_IP_CHECK"))
		{
			$ipcheck = Core::getUser()->get("ipcheck");
		}
		else if($ipcheck > 0)
		{
			$ipcheck = 1;
		}
		switch($planetorder)
		{
			case 1:case 2:case 3: break;
			default: $planetorder = 1; break;
		}
		if($esps > 99) { $esps = 99; }
		else if($esps < 0) { $esps = 1; }

		Hook::event("SaveUserDataLast", array(&$username, &$usertitle, &$email, &$templatepackage, &$theme, &$umode, &$umodemin, &$planetorder, &$delete, $ipcheck, $esps, &$js_interface));

		// Save it
		$atts = array("username", "usertitle", "email", "temp_email", "activation", "languageid", "templatepackage", "theme", "ipcheck", "umode", "umodemin", "planetorder", "delete", "esps", "js_interface");
		$vals = array($username, $usertitle, $email, $email, $activation, $language, $templatepackage, $theme, $ipcheck, $umode, $umodemin, $planetorder, $delete, $esps, $js_interface);

		// Feeds
		if($generate_key)
		{
			$new_key = randString(16);
			$result = Core::getQuery()->select("feed_keys", array("feed_key"), "", "user_id = '".Core::getUser()->get("userid")."'");
			if(Core::getDB()->num_rows($result) > 0)
			{
				// User has a feed key
				Core::getQuery()->updateSet("feed_keys", array("feed_key" => $new_key), "user_id = '".Core::getUser()->get("userid")."'");
			}
			else
			{
				Core::getQuery()->insertInto("feed_keys", array("user_id" => Core::getUser()->get("userid"), "feed_key" => $new_key));
			}
		}

		Core::getQuery()->update("user", $atts, $vals, "userid = '".Core::getUser()->get("userid")."'");
		Core::getUser()->rebuild();
		return $this;
	}

	/**
	 * Disables the vacation mode and starts the resource production.
	 *
	 * @return Bengine_Page_Preferences
	 */
	protected function disableUmode()
	{
		setProdOfUser(Core::getUser()->get("userid"), 100);
		Core::getQuery()->updateSet("user", array("umode" => 0), "userid = '".Core::getUser()->get("userid")."'");
		Core::getQuery()->updateSet("planet", array("last" => TIME), "userid = '".Core::getUser()->get("userid")."' AND planetid != '".Core::getUser()->get("curplanet")."'");
		Core::getUser()->rebuild();
		Hook::event("DisableVacationMode");
		Logger::dieMessage("UMODE_DISABLED", "info");
		return $this;
	}

	/**
	 * Shows the signature generator.
	 *
	 * @return Bengine_Page_Preferences
	 */
	protected function signatureAction()
	{
		$this->assign("imgUrl", BASE_URL.Core::getLang()->getOpt("langcode")."/signature/image/".Core::getUser()->get("userid").".png");
		return $this;
	}
}
?>