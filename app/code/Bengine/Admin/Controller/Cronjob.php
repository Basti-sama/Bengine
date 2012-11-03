<?php
class Bengine_Admin_Controller_Cronjob extends Bengine_Admin_Controller_Abstract
{
	const CRONJOB_PATH = "Cronjob/";

	protected function init()
	{
		Core::getLanguage()->load("AI_Cronjobs");
		return parent::init();
	}

	protected function indexAction()
	{
		if($this->getParam("add_cronjob"))
		{
			$this->add($this->getParam("month"), $this->getParam("day"), $this->getParam("weekday"), $this->getParam("hour"), $this->getParam("minute"), $this->getParam("class"));
		}
		$minute = "";
		$hour = "";
		$weekday = "";
		$day = "";
		$month = "";
		$i = 0;
		while($i < 60)
		{
			$minute .= createOption($i, $i, 0);
			$i += 5;
		}
		for($i = 0; $i < 24; $i++)
		{
			$hour .= createOption($i, $i, 0);
		}
		$wds = array(1 => "Mon", 2 => "Tue", 3 => "Wed", 4 => "Thu", 5 => "Fri", 6 => "Sat", 7 => "Sun");
		for($i = 1; $i <= 7; $i++)
		{
			$weekday .= createOption($i, $wds[$i], 0);
		}
		for($i = 1; $i <= 31; $i++)
		{
			$day .= createOption($i, $i, 0);
		}
		for($i = 1; $i <= 12; $i++)
		{
			$month .= createOption($i, $i, 0);
		}
		Core::getTPL()->assign("minute", $minute);
		Core::getTPL()->assign("hour", $hour);
		Core::getTPL()->assign("weekday", $weekday);
		Core::getTPL()->assign("day", $day);
		Core::getTPL()->assign("month", $month);

		$tabs = array();
		$result = Core::getQuery()->select("cronjob", array("cronid", "class", "xtime", "last", "active"), "ORDER BY xtime ASC");
		while($row = Core::getDB()->fetch($result))
		{
			$id = $row["cronid"];
			$tabs[$id]["cronid"] = $row["cronid"];
			$tabs[$id]["class"] = $row["class"];
			$tabs[$id]["xtime"] = empty($row["xtime"]) ? Core::getLang()->get("NEVER") : date("Y-m-d H:i:s", $row["xtime"]);
			$tabs[$id]["last"] = empty($row["last"]) ? Core::getLang()->get("NEVER") : date("Y-m-d H:i:s", $row["last"]);
			$tabs[$id]["delete"] = Link::get("admin/cronjob/delete/".$id, Core::getLanguage()->getItem("Delete"));
			$tabs[$id]["exec"] = Link::get("admin/cronjob/execute/".$id, Core::getLanguage()->getItem("Execute"));
			$tabs[$id]["edit"] = Link::get("admin/cronjob/edit/".$id, Core::getLanguage()->getItem("Edit"));
			$tabs[$id]["active"] = ($row["active"]) ? Link::get("cadmin/ronjob/disable/".$id, "<span class=\"green\">On</span>", Core::getLanguage()->getItem("Disable")) : Link::get("admin/cronjob/enable/".$id, "<span class=\"red\">Off</span>", Core::getLanguage()->getItem("Enable"));
		}
		Core::getTPL()->addLoop("crontabs", $tabs);
		return $this;
	}

	protected function editAction($cronid)
	{
		$result = Core::getQuery()->select("cronjob", array("class", "month", "day", "weekday", "hour", "minute"), "", "cronid = '".$cronid."'");
		if($row = Core::getDB()->fetch($result))
		{
			if($this->getParam("edit_cronjob"))
			{
				$this->save($cronid, $this->getParam("month"), $this->getParam("day"), $this->getParam("weekday"), $this->getParam("hour"), $this->getParam("minute"), $this->getParam("class"));
			}
			$selMin = explode(",", $row["minute"]);
			$selHour = explode(",", $row["hour"]);
			$selWD = explode(",", $row["weekday"]);
			$selDay = explode(",", $row["day"]);
			$selMon = explode(",", $row["month"]);
			$minute = "";
			$hour = "";
			$weekday = "";
			$day = "";
			$month = "";
			$i = 0;
			while($i < 60)
			{
				$minute .= createOption($i, $i, (in_array($i, $selMin)) ? 1 : 0);
				$i += 5;
			}
			for($i = 0; $i < 24; $i++)
			{
				$hour .= createOption($i, $i, (in_array($i, $selHour)) ? 1 : 0);
			}
			$wds = array(1 => "Mon", 2 => "Tue", 3 => "Wed", 4 => "Thu", 5 => "Fri", 6 => "Sat", 7 => "Sun");
			for($i = 1; $i <= 7; $i++)
			{
				$weekday .= createOption($i, $wds[$i], (in_array($i, $selWD)) ? 1 : 0);
			}
			for($i = 1; $i <= 31; $i++)
			{
				$day .= createOption($i, $i, (in_array($i, $selDay)) ? 1 : 0);
			}
			for($i = 1; $i <= 12; $i++)
			{
				$month .= createOption($i, $i, (in_array($i, $selMon)) ? 1 : 0);
			}
			Core::getTPL()->assign("minute", $minute);
			Core::getTPL()->assign("hour", $hour);
			Core::getTPL()->assign("weekday", $weekday);
			Core::getTPL()->assign("day", $day);
			Core::getTPL()->assign("month", $month);
			Core::getTPL()->assign("class", $row["class"]);
		}
		return $this;
	}

	protected function save($cronid, $month, $day, $weekday, $hour, $minute, $class)
	{
		if(is_array($month) && is_array($day) && is_array($weekday) && !empty($class))
		{
			$month = implode(",", $month);
			$day = implode(",", $day);
			$weekday = implode(",", $weekday);
			$atts = array("class", "month", "day", "weekday", "hour", "minute", "active");
			$vals = array($class, $month, $day, $weekday, $hour, $minute, 0);
			Core::getQuery()->update("cronjob", $atts, $vals, "cronid = '".$cronid."'");
		}
		else
		{
			Logger::addMessage("InvalidInformation");
		}
		return $this;
	}

	protected function add($month, $day, $weekday, $hour, $minute, $class)
	{
		if(is_array($month) && is_array($day) && is_array($weekday) && !empty($class))
		{
			$month = implode(",", $month);
			$day = implode(",", $day);
			$weekday = implode(",", $weekday);
			$atts = array("class", "month", "day", "weekday", "hour", "minute", "xtime", "last", "active");
			$vals = array($class, $month, $day, $weekday, $hour, $minute, TIME, 0, 0);
			Core::getQuery()->insert("cronjob", $atts, $vals);
		}
		else
		{
			Logger::addMessage("InvalidInformation");
		}
		return $this;
	}

	protected function enableAction($cronid)
	{
		$result = Core::getQuery()->select("cronjob", array("month", "day", "weekday", "hour", "minute"), "", "cronid = '".$cronid."'");
		if($row = Core::getDB()->fetch($result))
		{
			$row["xtime"] = TIME;
			$next = Core::getCron()->calcNextExeTime($row);
			Core::getQuery()->update("cronjob", array("xtime", "active"), array($next, 1), "cronid = '".$cronid."'");
		}
		$this->redirect("cronjob");
		return $this;
	}

	protected function disableAction($cronid)
	{
		Core::getQuery()->update("cronjob", array("active"), array(0), "cronid = '".$cronid."'");
		$this->redirect("cronjob");
		return $this;
	}

	protected function deleteAction($cronid)
	{
		Core::getQuery()->delete("cronjob", "cronid = '".$cronid."'");
		$this->redirect("cronjob");
		return $this;
	}

	protected function executeAction($cronid)
	{
		$result = Core::getQuery()->select("cronjob", array("cronid", "class", "month", "day", "weekday", "hour", "minute", "last"), "", "cronid = '".$cronid."'");
		if($row = Core::getDB()->fetch($result))
		{
			$row["xtime"] = TIME;
			$next = Core::getCron()->calcNextExeTime($row);
			$cronObj = new $row["class"]();
			$cronObj->execute($row["cronid"], TIME, $next);
		}
		$this->redirect("cronjob");
		return $this;
	}
}
?>