<?php
/**
 * User group controller.
 *
 * @package Recipe PHP5 Admin Interface
 * @author Sebastian Noll
 * @copyright Copyright (c) 2012, Sebastian Noll
 * @license Proprietary
 */

class Bengine_Admin_Controller_Usergroups extends Bengine_Admin_Controller_Abstract
{
	/**
	 * @return Bengine_Admin_Controller_Abstract
	 */
	protected function init()
	{
		Core::getLanguage()->load("AI_User");
		$perms = Core::getQuery()->select("permissions", array("permissionid", "permission"), "ORDER BY permission ASC");
		Core::getTPL()->addLoop("perms", $perms->fetchAll());
		return parent::init();
	}

	/**
	 * @return Bengine_Admin_Controller_Usergroups
	 */
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
		foreach($result->fetchAll() as $row)
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

	/**
	 * @param integer $groupid
	 * @return Bengine_Admin_Controller_Usergroups
	 */
	protected function editAction($groupid)
	{
		if($this->isPost() && $this->getParam("save_usergroup"))
		{
			$this->save($this->getParam("groupid"), $this->getParam("grouptitle"), $this->getParam("permissions", array()));
		}
		$permissions = array();
		$result = Core::getQuery()->select("group2permission g2p", array("g2p.permissionid", "g2p.value", "g.grouptitle"), "LEFT JOIN ".PREFIX."usergroup g ON (g.usergroupid = g2p.groupid)", "g2p.groupid = '".$groupid."'");
		foreach($result->fetchAll() as $row)
		{
			$grouptitle = $row["grouptitle"];
			if($row["value"]) $permissions[] = $row["permissionid"];
		}
		if(empty($grouptitle))
		{
			$grouptitle = "";
			$result = Core::getQuery()->select("usergroup", "grouptitle", "", "usergroupid = '".$groupid."'");
			if($row = $result->fetchRow())
			{
				$grouptitle = $row["grouptitle"];
			}
		}
		Core::getTPL()->assign("grouptitle", $grouptitle);
		Core::getTPL()->assign("perms", $permissions);
		Admin::rebuildCache("perm");
		return $this;
	}

	/**
	 * @param string $grouptitle
	 * @param array $perms
	 * @return Bengine_Admin_Controller_Usergroups
	 */
	protected function add($grouptitle, array $perms)
	{
		Core::getQuery()->insert("usergroup", array("grouptitle" => $grouptitle, "standard" => 0));
		$groupid = Core::getDB()->lastInsertId();
		if(count($perms) > 0)
		{
			foreach($perms as $permid)
			{
				if($permid > 0) Core::getQuery()->insert("group2permission", array("permissionid" => $permid, "groupid" => $groupid, "value" => 1));
			}
		}
		Admin::rebuildCache("perm");
		return $this;
	}

	/**
	 * @param array $delete
	 * @return Bengine_Admin_Controller_Usergroups
	 */
	protected function delete(array $delete)
	{
		foreach($delete as $groupid)
		{
			Core::getQuery()->delete("usergroup", "usergroupid = ?", null, null, array($groupid));
			Core::getQuery()->delete("group2permission", "groupid = ?", null, null, array($groupid));
		}
		return $this;
	}

	/**
	 * @param integer $groupid
	 * @param string $grouptitle
	 * @param array $perms
	 * @return Bengine_Admin_Controller_Usergroups
	 */
	protected function save($groupid, $grouptitle, array $perms)
	{
		Core::getQuery()->update("usergroup", array("grouptitle" => $grouptitle), "usergroupid = ?", array($groupid));
		Core::getQuery()->delete("group2permission", "groupid = ?", null, null, array($groupid));
		if(count($perms) > 0)
		{
			foreach($perms as $permid)
			{
				if($permid > 0) Core::getQuery()->insert("group2permission", array("permissionid" => $permid, "groupid" => $groupid, "value" => 1));
			}
		}
		return $this;
	}
}
?>