<?php
/**
 * Shows infos about an unit.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Unit.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_Game_Controller_Unit extends Bengine_Game_Controller_Abstract
{
	/**
	 * Holds a list of all available engines.
	 *
	 * @var array
	 */
	protected $engines = array();

	/**
	 * The Id of the unit.
	 *
	 * @var integer
	 */
	protected $id = 0;

	/**
	 * Constructor: Shows informations about an unit.
	 *
	 * @return Bengine_Game_Controller_Unit
	 */
	protected function init()
	{
		Core::getLanguage()->load(array("Administrator", "UnitInfo", "buildings", "info"));

		$this->id = $this->getParam("1");
		return parent::init();
	}

	/**
	 * Shows all unit information.
	 *
	 * @param integer $id
	 * @throws Recipe_Exception_Generic
	 * @return Bengine_Game_Controller_Unit
	 */
	protected function infoAction($id)
	{
		// Bengine_Game_Common unit data
		$select = array("c.name", "c.mode", "c.basic_metal", "c.basic_silicon", "c.basic_hydrogen", "ds.capicity", "ds.speed", "ds.consume", "ds.attack", "ds.shield");
		$join = "LEFT JOIN ".PREFIX."ship_datasheet ds ON (ds.unitid = c.buildingid)";
		$result = Core::getQuery()->select("construction c", $select, $join, "c.buildingid = '".$id."'");
		if($row = $result->fetchRow())
		{
			$result->closeCursor();
			Core::getLanguage()->load("info,UnitInfo");
			Hook::event("ShowUnitInfo", array(&$row));
			Core::getTPL()->assign("mode", $row["mode"]);
			Core::getTPL()->assign("structure", fNumber($row["basic_metal"] + $row["basic_silicon"]));
			Core::getTPL()->assign("shield", fNumber($row["shield"]));
			Core::getTPL()->assign("attack", fNumber($row["attack"]));
			Core::getTPL()->assign("shell", fNumber(($row["basic_metal"] + $row["basic_silicon"]) / 10));
			Core::getTPL()->assign("capacity", fNumber($row["capicity"]));
			Core::getTPL()->assign("speed", fNumber($row["speed"]));
			Core::getTPL()->assign("consume", fNumber($row["consume"]));
			Core::getTPL()->assign("name", Core::getLanguage()->getItem($row["name"]));
			Core::getTPL()->assign("description", Core::getLanguage()->getItem($row["name"]."_FULL_DESC"));
			Core::getTPL()->assign("pic", Image::getImage("buildings/".$row["name"].".gif", Core::getLanguage()->getItem($row["name"]), null, null, "leftImage"));
			Core::getTPL()->assign("edit", Link::get("game/".SID."/Unit/Edit/".$id, "[".Core::getLanguage()->getItem("EDIT")."]"));

			// Rapidfire
			$i = 0; $rf = array();
			$_result = Core::getQuery()->select("rapidfire rf", array("rf.target", "rf.value", "c.name"), "LEFT JOIN ".PREFIX."construction c ON (c.buildingid = rf.target)", "rf.unitid = '".$id."'");
			foreach($_result->fetchAll() as $_row)
			{
				Hook::event("ShowUnitRapidfire", array($row, &$_row));
				$name = Link::get("game/".SID."/Unit/Info/".$_row["target"], Core::getLanguage()->getItem($_row["name"]));
				$rf[$i]["rapidfire"] = sprintf(Core::getLanguage()->getItem("RAPIDFIRE_TO"), $name);
				$rf[$i]["value"] = "<span class=\"available\">".fNumber($_row["value"])."</span>";
				$i++;
			}
			$_result->closeCursor();
			$_result = Core::getQuery()->select("rapidfire rf", array("rf.unitid", "rf.value", "c.name"), "LEFT JOIN ".PREFIX."construction c ON (c.buildingid = rf.unitid)", "rf.target = '".$id."'");
			foreach($_result->fetchAll() as $_row)
			{
				Hook::event("ShowUnitRapidfire", array($row, &$_row));
				$name = Link::get("game/".SID."/Unit/Info/".$_row["unitid"], Core::getLanguage()->getItem($_row["name"]));
				$rf[$i]["rapidfire"] = sprintf(Core::getLanguage()->getItem("RAPIDFIRE_FROM"), $name);
				$rf[$i]["value"] = "<span class=\"notavailable\">".fNumber($_row["value"])."</span>";
				$i++;
			}
			$_result->closeCursor();
			Core::getTPL()->addLoop("rapidfire", $rf);

			$engines = array();
			$_result = Core::getQuery()->select(
				"ship2engine s2e",
				array("s2e.base_speed", "s2e.base", "s2e.level", "e.engineid", "factor", "c.name"),
				"LEFT JOIN ".PREFIX."engine e ON (e.engineid = s2e.engineid) ".
				"LEFT JOIN ".PREFIX."construction c ON (c.buildingid = e.engineid)",
				"s2e.unitid = '{$id}'",
				"s2e.level ASC"
			);
			foreach($_result->fetchAll() as $_row)
			{
				$_row["name"] = Link::get("game/".SID."/Constructions/Info/".$_row["engineid"], Core::getLanguage()->get($_row["name"]));
				$_row["base_speed"] = fNumber($_row["base_speed"]);
				$engines[] = $_row;
			}
			$_result->closeCursor();
			Core::getTPL()->addLoop("engines", $engines);
		}
		else
		{
			$result->closeCursor();
			throw new Recipe_Exception_Generic("Unkown unit. You'd better don't mess with the URL.");
		}
		return $this;
	}


	/**
	 * Adds a new requirement.
	 *
	 * @param integer $level	Level
	 * @param integer $needs	Required construction
	 *
	 * @return Bengine_Game_Controller_Unit
	 */
	protected function addRequirement($level, $needs)
	{
		if(!is_numeric($level) || $level < 0) { $level = 1; }
		Core::getQuery()->insert("requirements", array("buildingid" => $this->id, "needs" => $needs, "level" => $level));
		Core::getCache()->flushObject("requirements");
		return $this;
	}

	/**
	 * Shows the editing form.
	 *
	 * @return Bengine_Game_Controller_Unit
	 */
	protected function editAction()
	{
		Core::getUser()->checkPermissions("CAN_EDIT_CONSTRUCTIONS");
		if($this->isPost())
		{
			if($this->getParam("saveunit"))
			{
				$this->saveConstruction(
					$this->id, $this->getParam("name_id"), $this->getParam("name"), $this->getParam("allow_on_moon"), $this->getParam("desc"), $this->getParam("full_desc"),
					$this->getParam("basic_metal"), $this->getParam("basic_silicon"), $this->getParam("basic_hydrogen"), $this->getParam("basic_energy"),
					$this->getParam("capicity"), $this->getParam("speed"), $this->getParam("consume"), $this->getParam("attack"), $this->getParam("shield"),
					$this->getParam("baseEngine"), $this->getParam("extentedEngine"), $this->getParam("extentedEngineLevel"), $this->getParam("extentedEngineSpeed"),
					$this->getParam("del_rf"), $this->getParam("rf_new"), $this->getParam("rf_new_value")
				);
			}
			if($this->getParam("addreq"))
			{
				$this->addRequirement($this->getParam("level"), $this->getParam("needs"));
			}
		}
		$languageid = Core::getLang()->getOpt("languageid");
		$select = array("c.name AS name_id", "c.allow_on_moon", "p.content AS name", "c.basic_metal", "c.basic_silicon", "c.basic_hydrogen", "c.basic_energy", "sds.unitid", "sds.capicity AS capacity", "sds.speed", "sds.consume", "sds.attack", "sds.shield");
		$joins  = "LEFT JOIN ".PREFIX."phrases p ON (p.title = c.name)";
		$joins .= "LEFT JOIN ".PREFIX."ship_datasheet sds ON (sds.unitid = c.buildingid)";
		$result = Core::getQuery()->select("construction c", $select, $joins, "c.buildingid = '".$this->id."' AND p.languageid = '".$languageid."'");
		if($row = $result->fetchRow())
		{
			$result->closeCursor();
			Hook::event("EditUnitDataLoaded", array(&$row));
			Core::getTPL()->assign($row);
			Core::getTPL()->assign("shell", fNumber(($row["basic_metal"] + $row["basic_silicon"]) / 10));
			$result = Core::getQuery()->select("phrases", "content", "", "languageid = '".$languageid."' AND title = '".$row["name_id"]."_DESC'");
			$_row = $result->fetchRow();
			$result->closeCursor();
			Core::getTPL()->assign("description", Str::replace("<br />", "", $_row["content"]));
			$result = Core::getQuery()->select("phrases", "content", "", "languageid = '".$languageid."' AND title = '".$row["name_id"]."_FULL_DESC'");
			$_row = $result->fetchRow();
			$result->closeCursor();
			Core::getTPL()->assign("full_description", Str::replace("<br />", "", $_row["content"]));

			// Engine selection
			Core::getTPL()->assign("extentedEngine", $this->getEnginesList(0));
			Core::getTPL()->assign("extentedEngineLevel", 0);
			Core::getTPL()->assign("extentedEngineSpeed", "");
			$engines = array();
			$result = Core::getQuery()->select("ship2engine s2e", array("s2e.engineid", "s2e.level", "s2e.base_speed", "s2e.base"), "", "s2e.unitid = '".$this->id."'");
			foreach($result->fetchAll() as $_row)
			{
				if($_row["base"] == 1)
				{
					Core::getTPL()->assign("baseEngine", $this->getEnginesList($_row["engineid"]));
				}
				else
				{
					Core::getTPL()->assign("extentedEngine", $this->getEnginesList($_row["engineid"]));
					Core::getTPL()->assign("extentedEngineLevel", $_row["level"]);
					Core::getTPL()->assign("extentedEngineSpeed", $_row["base_speed"]);
				}
			}

			$req = array(); $i = 0;
			$result = Core::getQuery()->select("requirements r", array("r.requirementid", "r.needs", "r.level", "p.content"), "LEFT JOIN ".PREFIX."construction b ON (b.buildingid = r.needs) LEFT JOIN ".PREFIX."phrases p ON (p.title = b.name)", "r.buildingid = '".$this->id."' AND p.languageid = '".$languageid."'");
			foreach($result->fetchAll() as $row)
			{
				$req[$i]["delete"] = Link::get("game/".SID."/Unit/DeleteRequirement/".$this->id."/".$row["requirementid"], "[".Core::getLanguage()->getItem("DELETE")."]");
				$req[$i]["name"] = Link::get("game/".SID."/Unit/Edit/".$row["needs"], $row["content"]);
				$req[$i]["level"] = $row["level"];
				$i++;
			}
			$result->closeCursor();
			Core::getTPL()->addLoop("requirements", $req);

			$const = array(); $i = 0;
			$result = Core::getQuery()->select("construction b", array("b.buildingid", "p.content"), "LEFT JOIN ".PREFIX."phrases p ON (p.title = b.name)", "(b.mode = '1' OR b.mode = '2' OR b.mode = '5') AND p.languageid = '".$languageid."'", "p.content ASC");
			foreach($result->fetchAll() as $row)
			{
				$const[$i]["name"] = $row["content"];
				$const[$i]["id"] = $row["buildingid"];
				$i++;
			}
			$result->closeCursor();
			Core::getTPL()->addLoop("constructions", $const);
			Core::getTPL()->addLoop("rapidfire", $this->getRapidFire());
			Core::getTPL()->assign("rfSelect", $this->getShipSelect());
		}
		return $this;
	}

	/**
	 * Deletes requirements.
	 *
	 * @param integer $unitid	Requirement id to delete
	 * @param integer $delete
	 *
	 * @return Bengine_Game_Controller_Unit
	 */
	protected function deleteRequirementAction($unitid, $delete)
	{
		Core::getUser()->checkPermissions("CAN_EDIT_CONSTRUCTIONS");
		Core::getQuery()->delete("requirements", "requirementid = ?", null, null, array($delete));
		Core::getCache()->flushObject("requirements");
		$this->redirect("game/".SID."/Unit/Edit/".$unitid);
		return $this;
	}

	/**
	 * Saves the entered data.
	 *
	 * @param integer $unitid
	 * @param string $nameId
	 * @param string $name
	 * @param integer $allowOnMoon
	 * @param string $desc
	 * @param string $fullDesc
	 * @param integer $basicMetal
	 * @param integer $basicSilicon
	 * @param integer $basicHydrogen
	 * @param integer $basicEnergy
	 * @param integer $capacity
	 * @param integer $speed
	 * @param integer $consumption
	 * @param integer $attack
	 * @param integer $shield
	 * @param integer $baseEngine
	 * @param integer $extentedEngine
	 * @param integer $extentedEngineLevel
	 * @param integer $extentedEngineSpeed
	 * @param integer $rfDelete
	 * @param integer $rfNew
	 * @param integer $rfNewValue
	 *
	 * @return Bengine_Game_Controller_Unit
	 */
	protected function saveConstruction(
		$unitid, $nameId, $name, $allowOnMoon, $desc, $fullDesc,
		$basicMetal, $basicSilicon, $basicHydrogen, $basicEnergy,
		$capacity, $speed, $consumption, $attack, $shield,
		$baseEngine, $extentedEngine, $extentedEngineLevel, $extentedEngineSpeed,
		$rfDelete, $rfNew, $rfNewValue
	)
	{
		Hook::event("EditUnitSave");
		$languageid = Core::getLang()->getOpt("languageid");
		$spec = array(
			"allow_on_moon" => $allowOnMoon,
			"basic_metal" => $basicMetal,
			"basic_silicon" => $basicSilicon,
			"basic_hydrogen" => $basicHydrogen,
			"basic_energy" => $basicEnergy
		);
		Core::getQuery()->update("construction", $spec, "name = ?", array($nameId));
		$spec = array(
			"capicity" => $capacity,
			"speed" => $speed,
			"consume" => $consumption,
			"attack" => $attack,
			"shield" => $shield
		);
		Core::getQuery()->update("ship_datasheet", $spec, "unitid = ?", array($unitid));
		Core::getQuery()->update("ship2engine", array("engineid" => $baseEngine), "unitid = ? AND base = ?", array($unitid, 1));
		Core::getQuery()->delete("ship2engine", "unitid = ? AND base = ?", null, null, array($unitid, 0));
		if($extentedEngineLevel > 0)
		{
			Core::getQuery()->insert("ship2engine", array("engineid" => $extentedEngine, "unitid" => $unitid, "level" => $extentedEngineLevel, "base_speed" => $extentedEngineSpeed, "base" => 0));
		}

		if(Str::length($name) > 0)
		{
			$result = Core::getQuery()->select("phrases", "phraseid", "", "title = '".$nameId."'");
			if($result->rowCount() > 0)
			{
				Core::getQuery()->update("phrases", array("content" => convertSpecialChars($name)), "title = ?", array($nameId));
			}
			else
			{
				Core::getQuery()->insert("phrases", array("languageid" => $languageid, "phrasegroupid" => 4, "title" => $nameId, "content" => convertSpecialChars($name)));
			}
			$result->closeCursor();
		}
		if(Str::length($desc) > 0)
		{
			$result = Core::getQuery()->select("phrases", "phraseid", "", "title = '".$nameId."_DESC'");
			if($result->rowCount() > 0)
			{
				Core::getQuery()->update("phrases", array("content" => convertSpecialChars($desc)), "title = ?", array($nameId."_DESC"));
			}
			else
			{
				Core::getQuery()->insert("phrases", array("languageid" => $languageid, "phrasegroupid" => 4, "title" => $nameId."_DESC", "content" => convertSpecialChars($desc)));
			}
			$result->closeCursor();
		}
		if(Str::length($fullDesc) > 0)
		{
			$result = Core::getQuery()->select("phrases", "phraseid", "", "title = '".$nameId."_FULL_DESC'");
			if($result->rowCount() > 0)
			{
				Core::getQuery()->update("phrases", array("content" => convertSpecialChars($fullDesc)), "title = ?", array($nameId."_FULL_DESC"));
			}
			else
			{
				Core::getQuery()->insert("phrases", array("languageid" => $languageid, "phrasegroupid" => 4, "title" => $nameId."_FULL_DESC", "content" => convertSpecialChars($fullDesc)));
			}
			$result->closeCursor();
		}
		Core::getLang()->rebuild("info");

		// Rapidfire
		$result = Core::getQuery()->select("rapidfire", array("target", "value"), "", "unitid = '".$this->id."'");
		foreach($result->fetchAll() as $row)
		{
			if(is_array($rfDelete) && in_array($row["target"], $rfDelete))
			{
				Core::getQuery()->delete("rapidfire", "unitid = ? AND target = ?", null, null, array($this->id, $row["target"]));
			}
			else if(Core::getRequest()->getPOST("rf_".$row["target"]) != $row["value"])
			{
				Core::getQuery()->update("rapidfire", array("value" => Core::getRequest()->getPOST("rf_".$row["target"])), "unitid = ? AND target = ?", array($this->id, $row["target"]));
			}
		}
		if($rfNew > 0 && $rfNewValue > 0)
		{
			Core::getQuery()->delete("rapidfire", "unitid = ? AND target = ?", null, null, array($this->id, $rfNew));
			Core::getQuery()->insert("rapidfire", array("unitid" => $this->id, "target" => $rfNew, "value" => $rfNewValue));
		}

		return $this;
	}

	/**
	 * Generates the HTML for the engine list.
	 *
	 * @param integer $engineid	Selected engine
	 *
	 * @return string	HTML code (only option-tags)
	 */
	protected function getEnginesList($engineid)
	{
		if(count($this->engines) <= 0)
		{
			$joins  = "LEFT JOIN ".PREFIX."construction c ON (c.buildingid = e.engineid)";
			$joins .= "LEFT JOIN ".PREFIX."phrases p ON (p.title = c.name)";
			$result = Core::getQuery()->select("engine e", array("e.engineid", "p.content AS name"), $joins, "p.languageid = '".Core::getLang()->getOpt("languageid")."'", "c.display_order ASC, c.buildingid ASC");
			foreach($result->fetchAll() as $row)
			{
				$this->engines[] = $row;
			}
		}
		$select = "";
		foreach($this->engines as $engine)
		{
			if($engine["engineid"] == $engineid) { $s = 1; }
			else { $s = 0; }
			$select .= createOption($engine["engineid"], $engine["name"], $s);
		}
		return $select;
	}

	/**
	 * Returns the rapidfire of the unit.
	 *
	 * @return array
	 */
	protected function getRapidFire()
	{
		$rf = array();
		$sel = array("r.target", "r.value", "p.content AS name");
		$joins  = "LEFT JOIN ".PREFIX."construction b ON (b.buildingid = r.target) ";
		$joins .= "LEFT JOIN ".PREFIX."phrases p ON (b.name = p.title)";
		$result = Core::getQuery()->select("rapidfire r", $sel, $joins, "r.unitid = '".$this->id."' AND p.languageid = '".Core::getUser()->get("languageid")."'");
		foreach($result->fetchAll() as $row)
		{
			$rf[] = $row;
		}
		return $rf;
	}

	/**
	 * Fetches all ships returns them as an option list.
	 *
	 * @param integer $unit	Pre-selected ship
	 *
	 * @return string	Options
	 */
	protected function getShipSelect($unit = 0)
	{
		$ret = "";
		$sel = array("b.buildingid", "p.content AS name");
		$join = "LEFT JOIN ".PREFIX."phrases p ON (b.name = p.title)";
		$result = Core::getQuery()->select("construction b", $sel, $join, "(b.mode = '3' OR b.mode = '4') AND p.languageid = '".Core::getUser()->get("languageid")."'");
		foreach($result->fetchAll() as $row)
		{
			$ret .= createOption($row["buildingid"], $row["name"], ($row["buildingid"] == $unit) ? 1 : 0);
		}
		return $ret;
	}
}
?>