<?php
class Bengine_Admin_Controller_User extends Bengine_Admin_Controller_Abstract
{
	protected function init()
	{
		Core::getLanguage()->load("AI_User");
		return parent::init();
	}

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
		Core::getTPL()->addLoop("langs", $langs);
		Core::getTPL()->addLoop("groups", $groups);
		return $this;
	}

	protected function getTemplatePackages($asArray = true, $package = "none")
	{
		$templates = ($asArray) ? array() : "";
		if($directory = new DirectoryIterator(APP_ROOT_DIR."app/templates/"))
		{
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

	protected function add($username, $email, $languageid, $templatepackage, $ipcheck, $password, $usergroup)
	{
		$spec = array("username" => $username, "email" => $email, "languageid" => $languageid, "templatepackage" => $templatepackage, "ipcheck" => $ipcheck);
		Core::getQuery()->insertInto("user", $spec);
		$userid = Core::getDB()->insert_id();
		$password = Str::encode($password, Core::getConfig()->get("USE_PASSWORD_SALT") ? "md5_salt" : "md5");
		Core::getQuery()->insertInto("password", array("userid" => $userid, "password" => $password, "time" => TIME));
		foreach($usergroup as $groupid)
		{
			if($groupid > 0) { Core::getQuery()->insert("user2group", array("usergroupid", "userid"), array($groupid, $userid)); }
		}
		return $this;
	}

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
			if($userwhere != "")
			{
				$where = $userwhere;
				if($mailwhere != "")
				{
					$where .= " OR ".$mailwhere;
				}
			}
			else if($mailwhere != "") { $where = $mailwhere; }
			$result = Core::getQuery()->select("user", array("userid", "username", "email"), "", $where);
			while($row = Core::getDB()->fetch($result))
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
		if($row = Core::getDB()->fetch($result))
		{
			if($this->getParam("add_usermembership"))
			{
				$this->addUser2Group($this->getParam("usergroup"), $this->getParam("userid"), $this->getParam("data", ""));
			}
			$langs = Core::getQuery()->select("languages", array("languageid", "title"), "", "", "title ASC");
			$groups = Core::getQuery()->select("usergroup", array("usergroupid", "grouptitle"), "", "", "grouptitle ASC");
			$membership = array();
			$_result = Core::getQuery()->select("user2group u2g", array("g.grouptitle", "u2g.usergroupid"), "LEFT JOIN ".PREFIX."usergroup g ON (g.usergroupid = u2g.usergroupid)", "u2g.userid = '".$userid."'");
			while($_row = Core::getDB()->fetch($_result))
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

	protected function addUser2Group($usergroup, $userid, $data)
	{
		Core::getQuery()->insert("user2group", array("usergroupid", "userid", "data"), array($usergroup, $userid, $data));
		return $this;
	}

	protected function saveUser($userid, $username, $email, $languageid, $templatepackage, $ipcheck, $password)
	{
		$atts = array("username", "email", "languageid", "templatepackage", "ipcheck");
		$vals = array($username, $email, $languageid, $templatepackage, $ipcheck);
		Core::getQuery()->update("user", $atts, $vals, "userid = '".$userid."'");
		if($password != "")
		{
			$password = Str::encode($password, Core::getConfig()->get("USE_PASSWORD_SALT") ? "md5_salt" : "md5");
			Core::getQuery()->updateSet("password", array("password" => $password, "time" => TIME), "userid = '".$userid."'");
		}
		return $this;
	}

	protected function deleteUser($users)
	{
		foreach($users as $userid)
		{
			Core::getQuery()->delete("user2group", "userid = '".$userid."'");
			Core::getQuery()->delete("password", "userid = '".$userid."'");
			Core::getQuery()->delete("user", "userid = '".$userid."'");
		}
		return $this;
	}

	protected function deletefromgroupAction($userid, $groupid)
	{
		Core::getQuery()->delete("user2group", "userid = '".$userid."' AND usergroupid = '".$groupid."'");
		$this->redirect("user/edit/".$userid);
		return $this;
	}
}
?>