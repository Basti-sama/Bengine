<?php
/**
 * Research page. Shows research list.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Research.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_Game_Controller_Research extends Bengine_Game_Controller_Construction_Abstract
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
	 * @return Bengine_Game_Controller_Research
	 */
	protected function init()
	{
		// Get event
		$this->event = Game::getEH()->getResearchEvent();
		return parent::init();
	}

	/**
	 * Index action.
	 *
	 * @return Bengine_Game_Controller_Research
	 */
	protected function indexAction()
	{
		Core::getLanguage()->load(array("info", "buildings"));

		/* @var Bengine_Game_Model_Collection_Construction $collection */
		$collection = Application::getCollection("game/construction");
		$collection->addTypeFilter(self::RESEARCH_CONSTRUCTION_TYPE, Game::getPlanet()->getData("ismoon"))
			->addUserJoin(Core::getUser()->get("userid"))
			->addDisplayOrder();

		if(!Game::getPlanet()->getBuilding("RESEARCH_LAB") || !count($collection))
		{
			Logger::dieMessage("RESEARCH_LAB_REQUIRED");
		}
		Hook::event("ResearchLoaded", array($collection));
		Core::getTPL()->addLoop("constructions", $collection);
		Core::getTPL()->assign("event", $this->event);
		$canResearch = true;
		if(!Game::getEH()->canReasearch())
		{
			Logger::addMessage("RESEARCH_LAB_UPGRADING", "info");
			$canResearch = false;
		}
		Core::getTPL()->assign("canResearch", $canResearch);
		Core::getTPL()->addHTMLHeaderFile("lib/jquery.countdown.js", "js");
		return $this;
	}

	/**
	 * Check for sufficient resources and start research upgrade.
	 *
	 * @param integer $id    Building id to upgrade
	 * @throws Recipe_Exception_Generic
	 * @return Bengine_Game_Controller_Research
	 */
	protected function upgradeAction($id)
	{
		// Check events
		if($this->event != false || Core::getUser()->get("umode"))
		{
			$this->redirect("game/".SID."/Research");
		}

		// Check for requirements
		if(!Game::canBuild($id) || !Game::getPlanet()->getBuilding("RESEARCH_LAB"))
		{
			throw new Recipe_Exception_Generic("You do not fulfil the requirements to research this.");
		}

		// Check if research labor is not in progress
		if(!Game::getEH()->canReasearch())
		{
			throw new Recipe_Exception_Generic("Research labor in progress.");
		}

		/* @var Bengine_Game_Model_Construction $construction */
		$construction = Game::getModel("game/construction");
		$construction->load($id);
		if(!$construction->getId())
		{
			throw new Recipe_Exception_Generic("Unkown research :(");
		}
		if($construction->get("mode") != self::RESEARCH_CONSTRUCTION_TYPE)
		{
			throw new Recipe_Exception_Generic("Research not allowed.");
		}
		if(Game::getPlanet()->getData("ismoon") && !$construction->get("allow_on_moon"))
		{
			throw new Recipe_Exception_Generic("Research not allowed.");
		}
		Hook::event("UpgradeResearchFirst", array($construction));

		// Get required resources
		$level = Game::getResearch($id);
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
			Game::getEH()->addEvent(3, $time + TIME, Core::getUser()->get("curplanet"), Core::getUser()->get("userid"), null, $data);
			$this->redirect("game/".SID."/Research");
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
	 * @return Bengine_Game_Controller_Research
	 */
	protected function abortAction($id)
	{
		if(Core::getUser()->get("umode") || !$this->event)
		{
			$this->redirect("game/".SID."/Research");
		}
		$result = Core::getQuery()->select("construction", array("buildingid"), "", Core::getDB()->quoteInto("buildingid = ? AND mode = '2'", $id));
		if($row = $result->fetchRow())
		{
			$result->closeCursor();
			Hook::event("AbortResearch", array($this));
			Game::getEH()->removeEvent($this->event->get("eventid"));
			$this->redirect("game/".SID."/Research");
		}
		$result->closeCursor();
		return $this;
	}
}
?>