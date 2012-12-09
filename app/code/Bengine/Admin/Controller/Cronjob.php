<?php
/**
 * Cronjob controller.
 *
 * @package Recipe PHP5 Admin Interface
 * @author Sebastian Noll
 * @copyright Copyright (c) 2012, Sebastian Noll
 * @license Proprietary
 */

class Bengine_Admin_Controller_Cronjob extends Bengine_Admin_Controller_Abstract
{
	/**
	 * @var string
	 */
	const CRONJOB_PATH = "Cronjob/";

	/**
	 * @return Bengine_Admin_Controller_Abstract
	 */
	protected function init()
	{
		Core::getLanguage()->load("AI_Cronjobs");
		return parent::init();
	}

	/**
	 * @return Bengine_Admin_Controller_Cronjob
	 */
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
		foreach($result->fetchAll() as $row)
		{
			$id = $row["cronid"];
			$tabs[$id]["cronid"] = $row["cronid"];
			$tabs[$id]["class"] = $row["class"];
			$tabs[$id]["xtime"] = empty($row["xtime"]) ? Core::getLang()->get("NEVER") : date("Y-m-d H:i:s", $row["xtime"]);
			$tabs[$id]["last"] = empty($row["last"]) ? Core::getLang()->get("NEVER") : date("Y-m-d H:i:s", $row["last"]);
			$tabs[$id]["delete"] = Link::get("admin/cronjob/delete/".$id, Core::getLanguage()->getItem("Delete"));
			$tabs[$id]["exec"] = Link::get("admin/cronjob/execute/".$id, Core::getLanguage()->getItem("Execute"));
			$tabs[$id]["edit"] = Link::get("admin/cronjob/edit/".$id, Core::getLanguage()->getItem("Edit"));
			$tabs[$id]["active"] = ($row["active"]) ? Link::get("admin/cronjob/disable/".$id, "<span class=\"green\">On</span>", Core::getLanguage()->getItem("Disable")) : Link::get("admin/cronjob/enable/".$id, "<span class=\"red\">Off</span>", Core::getLanguage()->getItem("Enable"));
		}
		Core::getTPL()->addLoop("crontabs", $tabs);
		return $this;
	}

	/**
	 * @param integer $cronid
	 * @return Bengine_Admin_Controller_Cronjob
	 */
	protected function editAction($cronid)
	{
		$result = Core::getQuery()->select("cronjob", array("class", "month", "day", "weekday", "hour", "minute"), "", "cronid = '".$cronid."'");
		if($row = $result->fetchRow())
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

	/**
	 * @param integer $cronid
	 * @param array $month
	 * @param array $day
	 * @param array $weekday
	 * @param integer $hour
	 * @param integer $minute
	 * @param string $class
	 * @return Bengine_Admin_Controller_Cronjob
	 */
	protected function save($cronid, $month, $day, $weekday, $hour, $minute, $class)
	{
		if(is_array($month) && is_array($day) && is_array($weekday) && !empty($class))
		{
			$month = implode(",", $month);
			$day = implode(",", $day);
			$weekday = implode(",", $weekday);
			$spec = array("class" => $class, "month" => $month, "day" => $day, "weekday" => $weekday, "hour" => $hour, "minute" => $minute, "active" => 0);
			Core::getQuery()->update("cronjob", $spec, "cronid = ?", array($cronid));
		}
		else
		{
			Logger::addMessage("InvalidInformation");
		}
		return $this;
	}

	/**
	 * @param array $month
	 * @param array $day
	 * @param array $weekday
	 * @param integer $hour
	 * @param integer $minute
	 * @param string $class
	 * @return Bengine_Admin_Controller_Cronjob
	 */
	protected function add($month, $day, $weekday, $hour, $minute, $class)
	{
		if(is_array($month) && is_array($day) && is_array($weekday) && !empty($class))
		{
			$month = implode(",", $month);
			$day = implode(",", $day);
			$weekday = implode(",", $weekday);
			$spec = array("class" => $class, "month" => $month, "day" => $day, "weekday" => $weekday, "hour" => $hour, "minute" => $minute, "xtime" => TIME, "last" => 0, "active" => 0);
			Core::getQuery()->insert("cronjob", $spec);
		}
		else
		{
			Logger::addMessage("InvalidInformation");
		}
		return $this;
	}

	/**
	 * @param integer $cronid
	 * @return Bengine_Admin_Controller_Cronjob
	 */
	protected function enableAction($cronid)
	{
		$result = Core::getQuery()->select("cronjob", array("month", "day", "weekday", "hour", "minute"), "", "cronid = '".$cronid."'");
		if($row = $result->fetchRow())
		{
			$row["xtime"] = TIME;
			$next = Core::getCron()->calcNextExeTime($row);
			Core::getQuery()->update("cronjob", array("xtime" => $next, "active" => 1), "cronid = ?", array($cronid));
		}
		$this->redirect("admin/cronjob");
		return $this;
	}

	/**
	 * @param integer $cronid
	 * @return Bengine_Admin_Controller_Cronjob
	 */
	protected function disableAction($cronid)
	{
		Core::getQuery()->update("cronjob", array("active" => 0), "cronid = ?", array($cronid));
		$this->redirect("admin/cronjob");
		return $this;
	}

	/**
	 * @param integer $cronid
	 * @return Bengine_Admin_Controller_Cronjob
	 */
	protected function deleteAction($cronid)
	{
		Core::getQuery()->delete("cronjob", "cronid = ?", null, null, array($cronid));
		$this->redirect("admin/cronjob");
		return $this;
	}

	/**
	 * @param integer $cronid
	 * @return Bengine_Admin_Controller_Cronjob
	 */
	protected function executeAction($cronid)
	{
		$result = Core::getQuery()->select("cronjob", array("cronid", "class", "month", "day", "weekday", "hour", "minute", "last"), "", "cronid = '".$cronid."'");
		if($row = $result->fetchRow())
		{
			$row["xtime"] = TIME;
			$next = Core::getCron()->calcNextExeTime($row);
			/* @var Recipe_CronjobAbstract $cronObj */
			$cronObj = new $row["class"]();
			$cronObj->execute($row["cronid"], TIME, $next);
		}
		$this->redirect("admin/cronjob");
		return $this;
	}
}
?>