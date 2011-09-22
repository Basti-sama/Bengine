<?php
/**
 * Generates espionage report.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Report.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_Espionage_Report extends Bengine_Planet
{
	/**
	 * Buildings, research, fleet and defense data of planet.
	 *
	 * @var array
	 */
	protected $research = array(), $fleet = array(), $defense = array();

	/**
	 * Number of probes.
	 *
	 * @var integer
	 */
	protected $probes = 0;

	/**
	 * Espionage tech of the commiting user.
	 *
	 * @var integer
	 */
	protected $espTech = 0;

	/**
	 * Number of block that will be shown in report.
	 *
	 * @var integer
	 */
	protected $blocks = 1;

	/**
	 * Final HTML string of report.
	 *
	 * @var string
	 */
	protected $espReport = "";

	/**
	 * Chance that probes will be detected.
	 *
	 * @var integer
	 */
	protected $chance = 0;

	/**
	 * If espionage probes were destroyed.
	 *
	 * @var boolean
	 */
	protected $probesLost = false;

	/**
	 * Galaxy link of the target planet.
	 *
	 * @var string
	 */
	protected $position = "";

	/**
	 * Target username
	 *
	 * @var string
	 */
	protected $targetName = "";

	/**
	 * Sets essentail data for the espionage report.
	 *
	 * @param integer	Target planet id
	 * @param integer	Committing user
	 * @param integer	Target user id
	 * @param string	Traget user name
	 * @param integer	Number of espionage probes
	 *
	 * @return void
	 */
	public function __construct($planetid, $userid, $targetUser, $targetName, $probes)
	{
		parent::__construct($planetid, $targetUser, false);
		$this->getProduction();
		$this->addProd();
		$this->userid = $userid;
		$this->targetName = $targetName;
		$this->probes = $probes;
		Hook::event("EspionageReportStart", array($this));
		Core::getLanguage()->load(array("info" ,"EspionageReport"));
		$this->building = array();
		$this->position = getCoordLink($this->getData("galaxy"), $this->getData("system"), $this->getData("position"), true);
		$this->loadEspData()
			->generateReport()
			->sendESPR();
		return;
	}

	/**
	 * Saves the espionage report.
	 *
	 * @return Bengine_Espionage_Report
	 */
	protected function sendESPR()
	{
		Core::getQuery()->insertInto("message", array("mode" => 4, "time" => TIME, "sender" => null, "receiver" => $this->userid, "message" => $this->espReport, "subject" => Core::getLanguage()->getItem("ESP_REPORT_SUBJECT"), "read" => 0));
		return $this;
	}

	/**
	 * Loads all needed data to calculate report data.
	 *
	 * @return Bengine_Espionage_Report
	 */
	protected function loadEspData()
	{
		// Load research
		$result = Core::getQuery()->select("research2user", "level", "", "userid = '".$this->userid."' AND buildingid = '13'");
		$row = Core::getDB()->fetch($result);
		Core::getDB()->free_result($result);
		$this->espTech = (int) $row["level"];
		$result = Core::getQuery()->select("research2user r2u", array("r2u.buildingid", "r2u.level", "b.name"), "LEFT JOIN ".PREFIX."construction b ON (b.buildingid = r2u.buildingid)", "r2u.userid = '".$this->getData("userid")."'", "b.display_order ASC, b.buildingid ASC");
		while($row = Core::getDB()->fetch($result))
		{
			$this->research[$row["buildingid"]]["level"] = (int) $row["level"];
			$this->research[$row["buildingid"]]["name"] = Core::getLanguage()->getItem($row["name"]);
		}
		Core::getDB()->free_result($result);
		Hook::event("EspionageReportDataLoaded", array($this));

		// Get the blocks, wich will be viewed.
		if(!isset($this->research[13]["level"]) || !$this->research[13]["level"]) { $tEspTech = 0; }
		else { $tEspTech = $this->research[13]["level"]; }
		$x = ($this->espTech - $tEspTech);
		$y = ($tEspTech - $this->espTech);
		if($tEspTech > $this->espTech)
		{
			$this->blocks = ($this->probes - pow($x, 2));
		}
		if($this->espTech > $tEspTech)
		{
			$this->blocks = ($this->probes + pow($y, 2));
		}
		if($tEspTech == $this->espTech)
		{
			$this->blocks = $this->probes;
		}

		$units = 0;
		$result = Core::getQuery()->select("unit2shipyard u2s", array("u2s.unitid", "u2s.quantity", "b.name"), "LEFT JOIN ".PREFIX."construction b ON (b.buildingid = u2s.unitid)", "u2s.planetid = '".$this->planetid."' AND b.mode = '3'", "b.display_order ASC, b.buildingid ASC");
		while($row = Core::getDB()->fetch($result))
		{
			$units += $row["quantity"];
			$this->fleet[$row["unitid"]]["quantity"] = $row["quantity"];
			$this->fleet[$row["unitid"]]["name"] = Core::getLanguage()->getItem($row["name"]);
		}
		Core::getDB()->free_result($result);
		$this->loadHoldingFleet();
		if($this->blocks > 2)
		{
			$result = Core::getQuery()->select("unit2shipyard u2s", array("u2s.unitid", "u2s.quantity", "b.name"), "LEFT JOIN ".PREFIX."construction b ON (b.buildingid = u2s.unitid)", "u2s.planetid = '".$this->planetid."' AND b.mode = '4'", "b.display_order ASC, b.buildingid ASC");
			while($row = Core::getDB()->fetch($result))
			{
				$this->defense[$row["unitid"]]["quantity"] = $row["quantity"];
				$this->defense[$row["unitid"]]["name"] = Core::getLanguage()->getItem($row["name"]);
			}
			Core::getDB()->free_result($result);
			if($this->blocks > 3)
			{
				$result = Core::getQuery()->select("building2planet b2p", array("b2p.buildingid", "b2p.level", "b.name"), "LEFT JOIN ".PREFIX."construction b ON (b.buildingid = b2p.buildingid)", "b2p.planetid = '".$this->planetid."' AND (b.mode = '1' OR b.mode = '5')", "b.display_order ASC, b.buildingid ASC");
				while($row = Core::getDB()->fetch($result))
				{
					$this->building[$row["buildingid"]]["level"] = $row["level"];
					$this->building[$row["buildingid"]]["name"] = Core::getLanguage()->getItem($row["name"]);
				}
				Core::getDB()->free_result($result);
			}
		}

		// Get espionage defense chance.
		$this->chance = ($this->probes / 4) * ($tEspTech / $this->espTech) * (sqrt($units) * sqrt($this->probes));
		$this->chance = ($this->chance > 100) ? 100 : round($this->chance, 0);
		if(mt_rand(0, 100) < $this->chance)
		{
			$this->probesLost = true;
		}
		return $this;
	}

	/**
	 * Generates the HTML code for the report.
	 *
	 * @return Bengine_Espionage_Report
	 */
	protected function generateReport()
	{
		$rHeadline = Core::getLanguage()->getItem("ESP_REPORT_RESSOURCE_HEADLINE");
		$rHeadline = sprintf($rHeadline, $this->getData("planetname"), $this->position, $this->targetName);
		$this->espReport  = "<table class=\"ntable\" style=\"width: 400px;\">";
		$this->espReport .= "<tr><th colspan=\"4\">".$rHeadline."</th></tr>";
		$this->espReport .= "<tr><td>".Core::getLanguage()->getItem("ESP_REPORT_METAL")."</td><td>".fNumber($this->getData("metal"))."</td><td>".Core::getLanguage()->getItem("ESP_REPORT_SILICON")."</td><td>".fNumber($this->getData("silicon"))."</td></tr>";
		$this->espReport .= "<tr><td>".Core::getLanguage()->getItem("ESP_REPORT_HYDROGEN")."</td><td>".fNumber($this->getData("hydrogen"))."</td><td>".Core::getLanguage()->getItem("ESP_REPORT_ENERGY")."</td><td>".fNumber($this->getEnergy())."</td></tr>";
		if($this->blocks > 1)
		{
			// Set fleet
			$this->espReport .= "<tr><th colspan=\"4\">".Core::getLanguage()->getItem("ESP_REPORT_FLEET")."</th></tr>";
			$i = 0;
			if(count($this->fleet) > 0)
			{
				foreach($this->fleet as $fleet)
				{
					if($i % 2 == 0) { $this->espReport .= "<tr>"; }
					$this->espReport .= "<td>".$fleet["name"]."</td><td>".$fleet["quantity"]."</td>";
					if(count($this->fleet) == $i + 1 && $i % 2 == 0)
					{
						$this->espReport .= "<td></td><td></td></tr>";
					}
					if($i % 2 == 1) { $this->espReport .= "</tr>"; }
					$i++;
				}
			}

			// Set defense
			if($this->blocks > 2)
			{
				$this->espReport .= "<tr><th colspan=\"4\">".Core::getLanguage()->getItem("ESP_REPORT_DEFENSE")."</th></tr>";
				$i = 0;
				if(count($this->defense) > 0)
				{
					foreach($this->defense as $def)
					{
						if($i % 2 == 0) { $this->espReport .= "<tr>"; }
						$this->espReport .= "<td>".$def["name"]."</td><td>".$def["quantity"]."</td>";
						if(count($this->defense) == $i + 1 && $i % 2 == 0)
						{
							$this->espReport .= "<td></td><td></td></tr>";
						}
						if($i % 2 == 1) { $this->espReport .= "</tr>"; }
						$i++;
					}
				}

				// Set buildings
				if($this->blocks > 3)
				{
					$this->espReport .= "<tr><th colspan=\"4\">".Core::getLanguage()->getItem("ESP_REPORT_BUILDINGS")."</th></tr>";
					$i = 0;
					if(count($this->building) > 0)
					{
						foreach($this->building as $b)
						{
							if($i % 2 == 0) { $this->espReport .= "<tr>"; }
							$this->espReport .= "<td>".$b["name"]."</td><td>".$b["level"]."</td>";
							if(count($this->building) == $i + 1 && $i % 2 == 0)
							{
								$this->espReport .= "<td></td><td></td></tr>";
							}
							if($i % 2 == 1) { $this->espReport .= "</tr>"; }
							$i++;
						}
					}

					// Set research
					if($this->blocks > 4)
					{
						$this->espReport .= "<tr><th colspan=\"4\">".Core::getLanguage()->getItem("ESP_REPORT_RESEARCH")."</th></tr>";
						$i = 0;
						if(count($this->research) > 0)
						{
							foreach($this->research as $r)
							{
								if($i % 2 == 0) { $this->espReport .= "<tr>"; }
								$this->espReport .= "<td>".$r["name"]."</td><td>".$r["level"]."</td>";
								if(count($this->research) == $i + 1 && $i % 2 == 0)
								{
									$this->espReport .= "<td></td><td></td></tr>";
								}
								if($i % 2 == 1) { $this->espReport .= "</tr>"; }
								$i++;
							}
						}
					}
				}
			}
		}
		$this->espReport .= "<tr><td colspan=\"4\" style=\"text-align: center;\">".sprintf(Core::getLanguage()->getItem("ESP_DEFENDING_CHANCE"), round($this->chance))."</td></tr>";
		if($this->probesLost)
		{
			$this->espReport .= "<tr><td colspan=\"4\" style=\"text-align: center; font-weight: bold;\" class=\"false\">".Core::getLanguage()->getItem("ESP_PROBES_DESTROYED")."</td></tr>";
		}

		$_baseUrl = "game.php/%SID%/Mission/Index/".$this->getData("galaxy")."/".$this->getData("system")."/".$this->getData("position")."/";
		$baseUrl = $_baseUrl.($this->getData("ismoon") ? "moon" : "planet")."/";
		$attackLink = Link::get($baseUrl."attack", Core::getLanguage()->getItem("ATTACK"));
		$transportLink = Link::get($baseUrl."transport", Core::getLanguage()->getItem("TRANSPORT"));
		$espionageLink = Link::get($baseUrl."espionage", Core::getLanguage()->getItem("SPY"));
		$recyclingLink = Link::get($_baseUrl."tf", Core::getLanguage()->getItem("RECYCLING"));
		$this->espReport .= "<tr><td colspan=\"4\" style=\"text-align: center;\">".$attackLink." | ".$transportLink." | ".$espionageLink." | ".$recyclingLink."</td></tr>";
		$this->espReport .= "</table>";
		Hook::event("EspionageReportGenerator", array($this));
		return $this;
	}

	/**
	 * Loads the holding fleets.
	 *
	 * @return Bengine_Espionage_Report
	 */
	protected function loadHoldingFleet()
	{
		$result = Core::getQuery()->select("events", array("data"), "", "mode = '17' AND destination = '".$this->planetid."'");
		while($row = Core::getDB()->fetch($result))
		{
			$row["data"] = unserialize($row["data"]);
			foreach($row["data"]["ships"] as $ship)
			{
				if(isset($this->fleet[$ship["id"]]))
				{
					$this->fleet[$ship["id"]]["quantity"] += $ship["quantity"];
				}
				else
				{
					$this->fleet[$ship["id"]]["name"] = Core::getLanguage()->getItem($ship["name"]);
					$this->fleet[$ship["id"]]["quantity"] = $ship["quantity"];
				}
			}
		}
		Core::getDB()->free_result($result);
		return $this;
	}

	/**
	 * Returns the name of the planet.
	 *
	 * @return string
	 */
	public function getPlanetname()
	{
		return $this->getData("planetname");
	}

	/**
	 * Returns the chance to be discovered.
	 *
	 * @return integer
	 */
	public function getChance()
	{
		return $this->chance;
	}

	/**
	 * Returns the lost probes.
	 *
	 * @return boolean
	 */
	public function getProbesLost()
	{
		return $this->probesLost;
	}

	/**
	 * Returns the target user id.
	 *
	 * @return integer
	 */
	public function getTargetUserId()
	{
		return $this->getData("userid");
	}
}
?>