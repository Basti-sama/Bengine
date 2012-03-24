<?php
/**
 * Research page. Shows research list.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Research.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_Page_Research extends Bengine_Page_Construction_Abstract
{
	/**
	 * Type ID for research.
	 *
	 * @var integer
	 */
	const RESEARCH_CONSTRUCTION_TYPE = 2;

	/**
	 * Current research event.
	 *
	 * @var mixed
	 */
	protected $event = false;

	/**
	 * Displays list of available researches.
	 *
	 * @return Bengine_Page_Research
	 */
	protected function init()
	{
		// Get event
		$this->event = Bengine::getEH()->getResearchEvent();
		return parent::init();
	}

	/**
	 * Index action.
	 *
	 * @return Bengine_Page_Research
	 */
	protected function indexAction()
	{
		Core::getLanguage()->load(array("info", "buildings"));

		/* @var Bengine_Model_Collection_Construction $collection */
		$collection = Application::getCollection("construction");
		$collection->addTypeFilter(self::RESEARCH_CONSTRUCTION_TYPE, Bengine::getPlanet()->getData("ismoon"))
			->addUserJoin(Core::getUser()->get("userid"))
			->addDisplayOrder();

		if(!Bengine::getPlanet()->getBuilding("RESEARCH_LAB") || !count($collection))
		{
			Logger::dieMessage("RESEARCH_LAB_REQUIRED");
		}
		Hook::event("ResearchLoaded", array($collection));
		Core::getTPL()->addLoop("constructions", $collection);
		Core::getTPL()->assign("event", $this->event);
		Core::getTPL()->assign("canResearch", Bengine::getEH()->canReasearch());
		Core::getTPL()->addHTMLHeaderFile("lib/jquery.countdown.js", "js");
		return $this;
	}

	/**
	 * Check for sufficient resources and start research upgrade.
	 *
	 * @param integer $id	Building id to upgrade
	 *
	 * @return Bengine_Page_Research
	 */
	protected function upgradeAction($id)
	{
		// Check events
		if($this->event != false || Core::getUser()->get("umode"))
		{
			$this->redirect("game.php/".SID."/Research");
		}

		// Check for requirements
		if(!Bengine::canBuild($id) || !Bengine::getPlanet()->getBuilding("RESEARCH_LAB"))
		{
			throw new Recipe_Exception_Generic("You does not fulfil the requirements to research this.");
		}

		// Check if research labor is not in progress
		if(!Bengine::getEH()->canReasearch())
		{
			throw new Recipe_Exception_Generic("Research labor in progress.");
		}

		/* @var Bengine_Model_Construction $construction */
		$construction = Bengine::getModel("construction");
		$construction->load($id);
		if(!$construction->getId())
		{
			throw new Recipe_Exception_Generic("Unkown research :(");
		}
		if($construction->get("mode") != self::RESEARCH_CONSTRUCTION_TYPE)
		{
			throw new Exception("Research not allowed.");
		}
		if(Bengine::getPlanet()->getData("ismoon") && !$construction->get("allow_on_moon"))
		{
			throw new Exception("Research not allowed.");
		}
		Hook::event("UpgradeResearchFirst", array($construction));

		// Get required resources
		$level = Bengine::getResearch($id);
		if($level > 0)
			$level = $level + 1;
		else
			$level = 1;
		$this->setRequieredResources($level, $construction);

		// Check resources
		if($this->checkResources())
		{
			$data["metal"] = $this->requiredMetal;
			$data["silicon"] = $this->requiredSilicon;
			$data["hydrogen"] = $this->requiredHydrogen;
			$data["energy"] = $this->requiredEnergy;
			$time = getBuildTime($data["metal"], $data["silicon"], self::RESEARCH_CONSTRUCTION_TYPE);
			$data["level"] = $level;
			$data["buildingid"] = $id;
			$data["buildingname"] = $construction->get("name");
			Hook::event("UpgradeResearchLast", array($construction, &$data, &$time));
			Bengine::getEH()->addEvent(3, $time + TIME, Core::getUser()->get("curplanet"), Core::getUser()->get("userid"), null, $data);
			$this->redirect("game.php/".SID."/Research");
		}
		else
		{
			Logger::dieMessage("INSUFFICIENT_RESOURCES");
		}
		return $this;
	}

	/**
	 * Aborts the current research event.
	 *
	 * @param integer $id	Building id
	 *
	 * @return Bengine_Page_Research
	 */
	protected function abortAction($id)
	{
		if(Core::getUser()->get("umode"))
		{
			$this->redirect("game.php/".SID."/Research");
		}
		$result = Core::getQuery()->select("construction", array("buildingid"), "", "buildingid = '".$id."' AND mode = '2'");
		if($row = Core::getDB()->fetch($result))
		{
			Core::getDB()->free_result($result);
			Hook::event("AbortResearch", array($this));
			Bengine::getEH()->removeEvent($this->event->get("eventid"));
			$this->redirect("game.php/".SID."/Research");
		}
		Core::getDB()->free_result($result);
		return $this;
	}
}
?>