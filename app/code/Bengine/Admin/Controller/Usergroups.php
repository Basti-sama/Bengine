<?php
class Bengine_Admin_Controller_Usergroups extends Bengine_Admin_Controller_Abstract
{
	protected function init()
	{
		Core::getLanguage()->load("AI_User");
		$perms = Core::getQuery()->select("permissions", array("permissionid", "permission"), "ORDER BY permission ASC");
		Core::getTPL()->addLoop("perms", $perms);
		return parent::init();
	}

	protected function indexAction()
	{
		if($this->getParam("delete_usergroups"))
		{
			$this->delete($this->getParam("delete"));
		}
		else if($this->getParam("add_usergroup"))
		{
			$this->add($this->getParam("grouptitle"), $this->getParam("permissions"));
		}
		$groups = array();
		$result = Core::getQuery()->select("usergroup", array("usergroupid", "grouptitle", "standard"), "ORDER BY grouptitle ASC");
		while($row = Core::getDB()->fetch($result))
		{
			$id = $row["usergroupid"];
			$groups[$id]["usergroupid"] = $id;
			$groups[$id]["grouptitle"] = $row["grouptitle"];
			$groups[$id]["edit"] = Link::get("admin/usergroups/edit/".$id, Core::getLanguage()->getItem("Edit"));
			$groups[$id]["standard"] = $row["standard"];
		}
		Core::getTPL()->addLoop("groups", $groups);
		return $this;
	}

	protected function editAction($groupid)
	{
		if($this->isPost() && $this->getParam("save_usergroup"))
		{
			$this->save($this->getParam("groupid"), $this->getParam("grouptitle"), $this->getParam("permissions", array()));
		}
		$permissions = array();
		$result = Core::getQuery()->select("group2permission g2p", array("g2p.permissionid", "g2p.value", "g.grouptitle"), "LEFT JOIN ".PREFIX."usergroup g ON (g.usergroupid = g2p.groupid)", "g2p.groupid = '".$groupid."'");
		while($row = Core::getDB()->fetch($result))
		{
			$grouptitle = $row["grouptitle"];
			($row["value"])? $permissions[] = $row["permissionid"] : 0;
		}
		if(!$grouptitle)
		{
			$result = Core::getQuery()->select("usergroup", "grouptitle", "", "usergroupid = '".$groupid."'");
			if($row = Core::getDB()->fetch($result))
			{
				$grouptitle = $row["grouptitle"];
			}
		}
		Core::getTPL()->assign("grouptitle", $grouptitle);
		Core::getTPL()->assign("perms", $permissions);
		Admin::rebuildCache("perm");
		return $this;
	}

	protected function add($grouptitle, $perms)
	{
		Core::getQuery()->insert("usergroup", array("grouptitle", "standard"), array($grouptitle, 0));
		$groupid = Core::getDB()->insert_id();
		if(count($perms) > 0)
		{
			foreach($perms as $permid)
			{
				($permid > 0) ? Core::getQuery()->insert("group2permission", array("permissionid", "groupid", "value"), array($permid, $groupid, 1)) : false;
			}
		}
		Admin::rebuildCache("perm");
		return $this;
	}

	protected function delete($delete)
	{
		foreach($delete as $groupid)
		{
			Core::getQuery()->delete("usergroup", "usergroupid = '".$groupid."'");
			Core::getQuery()->delete("group2permission", "groupid = '".$groupid."'");
		}
		return $this;
	}

	protected function save($groupid, $grouptitle, $perms)
	{
		Core::getQuery()->update("usergroup", "grouptitle", $grouptitle, "usergroupid = '".$groupid."'");
		Core::getQuery()->delete("group2permission", "groupid = '".$groupid."'");
		if(count($perms) > 0)
		{
			foreach($perms as $permid)
			{
				($permid > 0) ? Core::getQuery()->insert("group2permission", array("permissionid", "groupid", "value"), array($permid, $groupid, 1)) : false;
			}
		}
		return $this;
	}
}
?>