<?php
/**
 * User controller.
 *
 * @package Recipe PHP5 Admin Interface
 * @author Sebastian Noll
 * @copyright Copyright (c) 2012, Sebastian Noll
 * @license Proprietary
 */

class Bengine_Admin_Controller_User extends Bengine_Admin_Controller_Abstract
{
	/**
	 * @return Bengine_Admin_Controller_Abstract
	 */
	protected function init()
	{
		Core::getLanguage()->load("AI_User");
		return parent::init();
	}

	/**
	 * @return Bengine_Admin_Controller_User
	 */
	protected function indexAction()
	{
		if($this->getParam("add_user"))
		{
			$this->add(
				$this->getParam("username"),
				$this->getParam("email"),
				$this->getParam("languageid"),
				$this->getParam("templatepackage"),
				$this->getParam("ipcheck"),
				$this->getParam("password"),
				$this->getParam("usergroup")
			);
		}
		$langs = Core::getQuery()->select("languages", array("languageid", "title"), "", "", "title ASC");
		$groups = Core::getQuery()->select("usergroup", array("usergroupid", "grouptitle"), "", "", "grouptitle ASC");

		Core::getTPL()->addLoop("templatepacks", $this->getTemplatePackages());
		Core::getTPL()->addLoop("langs", $langs->fetchAll());
		Core::getTPL()->addLoop("groups", $groups->fetchAll());
		return $this;
	}

	/**
	 * @param bool $asArray
	 * @param string $package
	 * @return array|string
	 */
	protected function getTemplatePackages($asArray = true, $package = "none")
	{
		$templates = ($asArray) ? array() : "";
		if($directory = new DirectoryIterator(APP_ROOT_DIR."app/templates/"))
		{
			/* @var DirectoryIterator $dir */
			foreach($directory as $dir)
			{
				$dirname = $dir->getFilename();
				if(!$dir->isDot() && $dir->isDir() && $dirname != ".svn")
				{
					if($asArray)
					{
						$templates[]["template"] = $dirname;
					}
					else
					{
						$templates .= createOption($dirname, $dirname, ($dirname == $package) ? 1 : 0);
					}
				}
			}
		}
		return $templates;
	}

	/**
	 * @param string $username
	 * @param string $email
	 * @param integer $languageid
	 * @param string $templatepackage
	 * @param integer $ipcheck
	 * @param string $password
	 * @param array $usergroup
	 * @return Bengine_Admin_Controller_User
	 */
	protected function add($username, $email, $languageid, $templatepackage, $ipcheck, $password, array $usergroup)
	{
		$spec = array("username" => $username, "email" => $email, "languageid" => $languageid, "templatepackage" => $templatepackage, "ipcheck" => $ipcheck);
		Core::getQuery()->insert("user", $spec);
		$userid = Core::getDB()->lastInsertId();
		$password = Str::encode($password, Core::getConfig()->get("USE_PASSWORD_SALT") ? "md5_salt" : "md5");
		Core::getQuery()->insert("password", array("userid" => $userid, "password" => $password, "time" => TIME));
		foreach($usergroup as $groupid)
		{
			if($groupid > 0) { Core::getQuery()->insert("user2group", array("usergroupid" => $groupid, "userid" => $userid)); }
		}
		return $this;
	}

	/**
	 * @return Bengine_Admin_Controller_User
	 */
	protected function seekAction()
	{
		$username = $this->getParam("username");
		$email = $this->getParam("email");
		if($this->getParam("delete_user"))
		{
			$this->deleteUser($this->getParam("delete"));
		}
		$s = false;
		$sr = array();
		if(!$username || !$email)
		{
			$username = Str::replace("%", "", $username);
			$username = Str::replace("*", "%", $username);
			$userwhere = "";
			if(Str::length(Str::replace("%", "", $username)) > 0)
			{
				$userwhere = "username LIKE '%".$username."%'";
				$s = true;
			}
			$email = Str::replace("%", "", $email);
			$email = Str::replace("*", "%", $email);
			$mailwhere = "";
			if(Str::length(Str::replace("%", "", $email)) > 0)
			{
				$mailwhere = "email LIKE '%".$email."%'";
				$s = true;
			}
		}
		if($s)
		{
			$where = "";
			if(!empty($userwhere))
			{
				$where = $userwhere;
				if(!empty($mailwhere))
				{
					$where .= " OR ".$mailwhere;
				}
			}
			else if(!empty($mailwhere))
			{
				$where = $mailwhere;
			}
			$result = Core::getQuery()->select("user", array("userid", "username", "email"), "", $where);
			foreach($result->fetchAll() as $row)
			{
				$id = $row["userid"];
				$sr[$id]["userid"] = $id;
				$sr[$id]["edit"] = Link::get("admin/user/edit/".$id, Core::getLanguage()->getItem("Edit"));
				$sr[$id]["username"] = $row["username"];
				$sr[$id]["email"] = "<a href=\"mailto:".$row["email"]."\">".$row["email"]."</a>";
			}
		}
		Core::getTPL()->addLoop("searchresult", $sr);
		Core::getTPL()->assign("searched", $s);
		return $this;
	}

	/**
	 * @param integer $userid
	 * @return Bengine_Admin_Controller_User
	 */
	protected function editAction($userid)
	{
		if($this->getParam("save_user"))
		{
			$this->saveUser(
				$this->getParam("userid"),
				$this->getParam("username"),
				$this->getParam("email"),
				$this->getParam("languageid"),
				$this->getParam("templatepackage"),
				$this->getParam("ipcheck"),
				$this->getParam("password")
			);
		}
		$result = Core::getQuery()->select("user", array("username", "email", "languageid", "templatepackage", "ipcheck"), "", "userid = '".$userid."'");
		if($row = $result->fetchRow())
		{
			if($this->getParam("add_usermembership"))
			{
				$this->addUser2Group($this->getParam("usergroup"), $this->getParam("userid"), $this->getParam("data", ""));
			}
			$langs = Core::getQuery()->select("languages", array("languageid", "title"), "", "", "title ASC");
			$groups = Core::getQuery()->select("usergroup", array("usergroupid", "grouptitle"), "", "", "grouptitle ASC");
			$membership = array();
			$_result = Core::getQuery()->select("user2group u2g", array("g.grouptitle", "u2g.usergroupid"), "LEFT JOIN ".PREFIX."usergroup g ON (g.usergroupid = u2g.usergroupid)", "u2g.userid = '".$userid."'");
			foreach($_result->fetchAll() as $_row)
			{
				$id = $_row["usergroupid"];
				$membership[$id]["grouptitle"] = $_row["grouptitle"];
				$membership[$id]["delete"] = Link::get("admin/user/deletefromgroup/".$userid."/".$id, Core::getLanguage()->getItem("Remove"));
			}
			Core::getTPL()->addLoop("membership", $membership);
			Core::getTPL()->addLoop("groups", $groups);
			Core::getTPL()->addLoop("langs", $langs);
			Core::getTPL()->assign("languageid", $row["languageid"]);
			Core::getTPL()->assign("ipcheck", $row["ipcheck"]);
			Core::getTPL()->assign("email", $row["email"]);
			Core::getTPL()->assign("username", $row["username"]);
			Core::getTPL()->assign("templates", $this->getTemplatePackages(false, $row["templatepackage"]));
		}
		return $this;
	}

	/**
	 * @param integer $usergroup
	 * @param integer $userid
	 * @param string $data
	 * @return Bengine_Admin_Controller_User
	 */
	protected function addUser2Group($usergroup, $userid, $data)
	{
		Core::getQuery()->insert("user2group", array("usergroupid" => $usergroup, "userid" => $userid, "data" => $data));
		return $this;
	}

	/**
	 * @param integer $userid
	 * @param string $username
	 * @param string $email
	 * @param integer $languageid
	 * @param string $templatepackage
	 * @param integer $ipcheck
	 * @param string $password
	 * @return Bengine_Admin_Controller_User
	 */
	protected function saveUser($userid, $username, $email, $languageid, $templatepackage, $ipcheck, $password)
	{
		$spec = array("username" => $username, "email" => $email, "languageid" => $languageid, "templatepackage" => $templatepackage, "ipcheck" => $ipcheck);
		Core::getQuery()->update("user", $spec, "userid = ?", array($userid));
		if($password != "")
		{
			$password = Str::encode($password, Core::getConfig()->get("USE_PASSWORD_SALT") ? "md5_salt" : "md5");
			Core::getQuery()->update("password", array("password" => $password, "time" => TIME), "userid = ?", array($userid));
		}
		return $this;
	}

	/**
	 * @param array $users
	 * @return Bengine_Admin_Controller_User
	 */
	protected function deleteUser(array $users)
	{
		foreach($users as $userid)
		{
			Core::getQuery()->delete("user2group", "userid = ?", null, null, array($userid));
			Core::getQuery()->delete("password", "userid = ?", null, null, array($userid));
			Core::getQuery()->delete("user", "userid = ?", null, null, array($userid));
		}
		return $this;
	}

	/**
	 * @param integer $userid
	 * @param integer $groupid
	 * @return Bengine_Admin_Controller_User
	 */
	protected function deletefromgroupAction($userid, $groupid)
	{
		Core::getQuery()->delete("user2group", "userid = ? AND usergroupid = ?", null, null, array($userid, $groupid));
		$this->redirect("admin/user/edit/".$userid);
		return $this;
	}
}
?>