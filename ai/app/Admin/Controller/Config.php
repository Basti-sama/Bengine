<?php
class Admin_Controller_Config extends Admin_Controller_Abstract
{
	protected $inputTypes = array(
		"char" => "Text field",
		"string" => "Text area",
		"boolean" => "Check box",
		"integer" => "Number",
		"enum" => "Select box"
	);

	protected $configGroups = null;

	protected function init()
	{
		Core::getLanguage()->load("AI_Configuration");
		return parent::init();
	}

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
		while($row = Core::getDB()->fetch($result))
		{
			$groups[$i]["groupid"] = $row["groupid"];
			$groups[$i]["title"] = $row["groupname"];
			$groups[$i]["showvars"] = Link::get("config/showVariables/".$row["groupid"], "[".Core::getLanguage()->getItem("Show_Vars")."]");
			$i++;
		}
		Core::getTPL()->addLoop("groups", $groups);
		return $this;
	}

	protected function saveGroups($groups, $delete)
	{
		foreach($groups as $groupid)
		{
			Core::getQuery()->update("configgroups", "groupname", Core::getRequest()->getPOST("title_".$groupid), "groupid = '".$groupid."'");
		}
		foreach($delete as $del)
		{
			Core::getQuery()->delete("configgroups", "groupid = '".$del."'");
			Core::getQuery()->delete("config", "groupid = '".$del."'");
		}
		return $this;
	}

	protected function deleteVariableAction($groupid, $var)
	{
		Core::getQuery()->delete("config", "var = '".$var."'");
		$this->setTemplate("config_showVariables");
		$this->showVariablesAction($groupid);
		return $this;
	}

	protected function editVariableAction($groupid, $var)
	{
		if($this->getParam("save_var"))
		{
			$this->saveVariable($this->getParam("var"), $this->getParam("type"), $this->getParam("options"), $this->getParam("description"), $this->getParam("groupid"));
		}
		$result = Core::getQuery()->select("config", array("var", "type", "description", "options", "groupid"), "", "var = '".$var."' AND islisted = '1'");
		$row = Core::getDB()->fetch($result);
		Core::getDB()->free_result($result);
		$row["groups"] = $this->getGroupSelect($row["groupid"]);
		$row["type"] = $this->getInputTypeSelect($row["type"]);
		$row["options"] = $this->unserialize($row["options"]);
		Core::getTPL()->assign($row);
		return $this;
	}

	protected function saveVariable($var, $type, $options, $description, $groupid)
	{
		$options = $this->serialize($options);
		$atts = array("type", "options", "description", "groupid");
		$vals = array($type, $options, Str::validateXHTML($description), $groupid);
		$where = "var = '".$var."'";
		Core::getQuery()->update("config", $atts, $vals, $where);
		Logger::addMessage("Saving_Successful", "success");
		return $this;
	}

	protected function addVar($var, $value, $type, $options, $groupid)
	{
		$options = $this->serialize($options);
		$atts = array("var", "value", "type", "options", "groupid", "islisted");
		$vals = array(Str::replace(" ", "_", $var), $value, $type, $options, $groupid, 1);
		Core::getQuery()->insert("config", $atts, $vals);
		return $this;
	}

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

	protected function showvariablesAction($id)
	{
		if($this->isPost() && $this->getParam("save_vars"))
		{
			$this->saveVars();
		}
		$vars = array();
		$result = Core::getQuery()->select("config", array("var", "value", "type", "description", "options"), "", "groupid = '".$id."' AND islisted = '1'", "sort_index ASC");
		while($row = Core::getDB()->fetch($result))
		{
			$vars[] = array(
				"description" => $row["description"],
				"input"	=> $this->getInputType($row["type"], $row["var"], $row["value"], ($row["options"] == "") ? null : $row["options"]),
				"var" => (Core::getLanguage()->exists($row["var"])) ? Core::getLanguage()->getItem($row["var"]) : $row["var"],
				"delete" => Link::get("config/deleteVariable/".$id."/".$row["var"], "[".Core::getLanguage()->getItem("Delete")."]"),
				"edit" => Link::get("config/editVariable/".$id."/".$row["var"], "[".Core::getLang()->get("Edit")."]")
			);
		}
		Core::getTPL()->addLoop("vars", $vars);
		return $this;
	}

	protected function getInputTypeSelect($type = "none")
	{
		$select = "";
		foreach($this->inputTypes as $key => $value)
		{
			$select .= createOption($key, $value, ($key == $type) ? 1 : 0);
		}
		return $select;
	}

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
				if($options === null) { return; }
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

	protected function saveVars()
	{
		$result = Core::getQuery()->select("config", array("var"), "", "groupid = '".Core::getRequest()->getGET("1")."' AND islisted = '1'", "sort_index ASC");
		while($row = Core::getDB()->fetch($result))
		{
			Core::getQuery()->update("config", array("value"), array(Core::getRequest()->getPOST($row["var"])), "var = '".$row["var"]."'");
		}
		Admin::rebuildCache("config");
		return $this;
	}

	protected function getConfigGroups()
	{
		if(is_null($this->configGroups))
		{
			$this->configGroups = array();
			$result = Core::getQuery()->select("configgroups", array("groupid", "groupname"), "", "", "groupname ASC");
			while($row = Core::getDB()->fetch($result))
			{
				$this->configGroups[] = $row;
			}
			Core::getDB()->free_result($result);
		}
		return $this->configGroups;
	}

	protected function getGroupSelect($groupid = 0)
	{
		$select = "";
		foreach($this->getConfigGroups() as $group)
		{
			$select .= createOption($group["groupid"], $group["groupname"], ($group["groupid"] == $groupid) ? 1 : 0);
		}
		return $select;
	}

	protected function addGroupAction()
	{
		if($this->isPost())
		{
			Core::getQuery()->insert("configgroups", "groupname", $this->getParam("groupname"));
		}
		return $this;
	}
}
?>