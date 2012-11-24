<?php
/**
 * Config controller.
 *
 * @package Recipe PHP5 Admin Interface
 * @author Sebastian Noll
 * @copyright Copyright (c) 2012, Sebastian Noll
 * @license Proprietary
 */

class Bengine_Admin_Controller_Config extends Bengine_Admin_Controller_Abstract
{
	/**
	 * @var array
	 */
	protected $inputTypes = array(
		"char" => "Text field",
		"string" => "Text area",
		"boolean" => "Check box",
		"integer" => "Number",
		"enum" => "Select box"
	);

	/**
	 * @var array
	 */
	protected $configGroups = null;

	/**
	 * @return Bengine_Admin_Controller_Abstract
	 */
	protected function init()
	{
		Core::getLanguage()->load("AI_Configuration");
		return parent::init();
	}

	/**
	 * @return Bengine_Admin_Controller_Config
	 */
	protected function indexAction()
	{
		if($this->isPost() && $this->getParam("add_var"))
		{
			$this->addVar($this->getParam("var"), $this->getParam("value"), $this->getParam("type"), $this->getParam("options"), $this->getParam("groupid"));
		}
		else if($this->isPost() && $this->getParam("save_groups"))
		{
			$this->saveGroups($this->getParam("groups"), $this->getParam("delete"));
		}
		Core::getTPL()->assign("varTypes", $this->getInputTypeSelect());
		$groups = array(); $i = 0;
		$result = Core::getQuery()->select("configgroups", array("groupid", "groupname"), "ORDER BY groupname ASC");
		foreach($result->fetchAll() as $row)
		{
			$groups[$i]["groupid"] = $row["groupid"];
			$groups[$i]["title"] = $row["groupname"];
			$groups[$i]["showvars"] = Link::get("admin/config/showVariables/".$row["groupid"], "[".Core::getLanguage()->getItem("Show_Vars")."]");
			$i++;
		}
		Core::getTPL()->addLoop("groups", $groups);
		return $this;
	}

	/**
	 * @param array $groups
	 * @param array $delete
	 * @return Bengine_Admin_Controller_Config
	 */
	protected function saveGroups($groups, $delete)
	{
		foreach($groups as $groupid)
		{
			Core::getQuery()->update("configgroups", array("groupname" => Core::getRequest()->getPOST("title_".$groupid)), "groupid = '".$groupid."'");
		}
		foreach($delete as $del)
		{
			Core::getQuery()->delete("configgroups", "groupid = '".$del."'");
			Core::getQuery()->delete("config", "groupid = '".$del."'");
		}
		return $this;
	}

	/**
	 * @param integer $groupid
	 * @param string $var
	 * @return Bengine_Admin_Controller_Config
	 */
	protected function deleteVariableAction($groupid, $var)
	{
		Core::getQuery()->delete("config", "var = '".$var."'");
		$this->setTemplate("config/showvariables");
		$this->showVariablesAction($groupid);
		return $this;
	}

	/**
	 * @param integer $groupid
	 * @param string $var
	 * @return Bengine_Admin_Controller_Config
	 */
	protected function editVariableAction($groupid, $var)
	{
		if($this->getParam("save_var"))
		{
			$this->saveVariable($this->getParam("var"), $this->getParam("type"), $this->getParam("options"), $this->getParam("description"), $this->getParam("groupid"));
		}
		$result = Core::getQuery()->select("config", array("var", "type", "description", "options", "groupid"), "", "var = '".$var."' AND islisted = '1'");
		$row = $result->fetchRow();
		$row["groups"] = $this->getGroupSelect($row["groupid"]);
		$row["type"] = $this->getInputTypeSelect($row["type"]);
		$row["options"] = $this->unserialize($row["options"]);
		Core::getTPL()->assign($row);
		return $this;
	}

	/**
	 * @param string $var
	 * @param string $type
	 * @param string $options
	 * @param string $description
	 * @param integer $groupid
	 * @return Bengine_Admin_Controller_Config
	 */
	protected function saveVariable($var, $type, $options, $description, $groupid)
	{
		$options = $this->serialize($options);
		$spec = array("type" => $type, "options" => $options, "description" => Str::validateXHTML($description), "groupid" => $groupid);
		$where = "var = '".$var."'";
		Core::getQuery()->update("config", $spec, $where);
		Logger::addMessage("Saving_Successful", "success");
		return $this;
	}

	/**
	 * @param string $var
	 * @param string $value
	 * @param string $type
	 * @param string $options
	 * @param integer $groupid
	 * @return Bengine_Admin_Controller_Config
	 */
	protected function addVar($var, $value, $type, $options, $groupid)
	{
		$options = $this->serialize($options);
		$spec = array("var" => Str::replace(" ", "_", $var), "value" => $value, "type" => $type, "options" => $options, "groupid" => $groupid, "islisted" => 1);
		Core::getQuery()->insert("config", $spec);
		return $this;
	}

	/**
	 * @param array $options
	 * @return string
	 */
	protected function serialize($options)
	{
		if(!empty($options))
		{
			$hasKeys = Str::inString("=>", $options);
			$options = Arr::trim(explode(",", $options));
			$opts = $options;
			if($hasKeys)
			{
				$opts = array();
				$size = count($options);
				for($i = 0; $i < $size; $i++)
				{
					$cell = Arr::trim(explode("=>", $options[$i]));
					$opts[$cell[0]] = $cell[1];
				}
			}
			return serialize($opts);
		}
		return "";
	}

	/**
	 * @param string $options
	 * @return string
	 */
	protected function unserialize($options)
	{
		if(!empty($options))
		{
			$options = unserialize($options);
			$return = array();
			foreach($options as $key => $value)
			{
				$return[] = $key." => ".$value;
			}
			return implode(", ", $return);
		}
		return "";
	}

	/**
	 * @param integer $id
	 * @return Bengine_Admin_Controller_Config
	 */
	protected function showvariablesAction($id)
	{
		if($this->isPost() && $this->getParam("save_vars"))
		{
			$this->saveVars();
		}
		$vars = array();
		$result = Core::getQuery()->select("config", array("var", "value", "type", "description", "options"), "", "groupid = '".$id."' AND islisted = '1'", "sort_index ASC");
		foreach($result->fetchAll() as $row)
		{
			$vars[] = array(
				"description" => $row["description"],
				"input"	=> $this->getInputType($row["type"], $row["var"], $row["value"], ($row["options"] == "") ? null : $row["options"]),
				"var" => (Core::getLanguage()->exists($row["var"])) ? Core::getLanguage()->getItem($row["var"]) : $row["var"],
				"delete" => Link::get("admin/config/deleteVariable/".$id."/".$row["var"], "[".Core::getLanguage()->getItem("Delete")."]"),
				"edit" => Link::get("admin/config/editVariable/".$id."/".$row["var"], "[".Core::getLang()->get("Edit")."]")
			);
		}
		Core::getTPL()->addLoop("vars", $vars);
		return $this;
	}

	/**
	 * @param string $type
	 * @return string
	 */
	protected function getInputTypeSelect($type = "none")
	{
		$select = "";
		foreach($this->inputTypes as $key => $value)
		{
			$select .= createOption($key, $value, ($key == $type) ? 1 : 0);
		}
		return $select;
	}

	/**
	 * @param string $type
	 * @param string $var
	 * @param string $value
	 * @param string $options
	 * @return string
	 */
	protected function getInputType($type, $var, $value, $options = null)
	{
		switch($type)
		{
			case "boolean":
			case "bool":
				$checked = ($value) ? " checked=\"checked\"" : "";
				return "<input type=\"checkbox\" value=\"1\" name=\"".$var."\" id=\"config-".$var."\"".$checked." class=\"boolean\"/>";
			break;
			case "char":
			case "int":
			case "integer":
			case "double":
				return "<input type=\"text\" value=\"".$value."\" name=\"".$var."\" id=\"config-".$var."\" class=\"".$type."\"/>";
			break;
			case "text":
			case "string":
				return "<textarea name=\"".$var."\" id=\"config-".$var."\" class=\"text\">".$value."</textarea>";
			break;
			case "select":
			case "enum":
				if($options === null) { return ""; }
				$options = unserialize($options);
				$select = "<select name=\"".$var."\" id=\"config-".$var."\" class=\"enum\">";
				foreach($options as $key => $val)
				{
					$select .= createOption($key, $val, ($value == $key) ? 1 : 0);
				}
				$select .= "</select>";
				return $select;
			break;
		}
		return "";
	}

	/**
	 * @return Bengine_Admin_Controller_Config
	 */
	protected function saveVars()
	{
		$result = Core::getQuery()->select("config", array("var"), "", "groupid = '".Core::getRequest()->getGET("1")."' AND islisted = '1'", "sort_index ASC");
		foreach($result->fetchAll() as $row)
		{
			Core::getQuery()->update("config", array("value" => Core::getRequest()->getPOST($row["var"])), "var = '".$row["var"]."'");
		}
		Admin::rebuildCache("config");
		return $this;
	}

	/**
	 * @return array
	 */
	protected function getConfigGroups()
	{
		if(is_null($this->configGroups))
		{
			$this->configGroups = array();
			$result = Core::getQuery()->select("configgroups", array("groupid", "groupname"), "", "", "groupname ASC");
			foreach($result->fetchAll() as $row)
			{
				$this->configGroups[] = $row;
			}
		}
		return $this->configGroups;
	}

	/**
	 * @param int $groupid
	 * @return string
	 */
	protected function getGroupSelect($groupid = 0)
	{
		$select = "";
		foreach($this->getConfigGroups() as $group)
		{
			$select .= createOption($group["groupid"], $group["groupname"], ($group["groupid"] == $groupid) ? 1 : 0);
		}
		return $select;
	}

	/**
	 * @return Bengine_Admin_Controller_Config
	 */
	protected function addGroupAction()
	{
		if($this->isPost())
		{
			Core::getQuery()->insert("configgroups", array("groupname" => $this->getParam("groupname")));
		}
		return $this;
	}
}
?>