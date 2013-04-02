<?php
/**
 * Displays random commercials on the page.
 *
 * @package Bengine
 * @subpackage Google Analytics Plug-in
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Commercials.plugin.php 53 2011-08-13 16:05:23Z secretchampion $
 */

class Plugin_Commercials extends Recipe_PluginAbstract
{
	/**
	 * Holds the ads.
	 *
	 * @var array
	 */
	protected $ads = array();

	/**
	 * Ads loaded?
	 *
	 * @var boolean
	 */
	protected $loaded = false;

	/**
	 * Instance of this class.
	 *
	 * @var Plugin_Commercials
	 */
	protected static $instance = null;

	/**
	 * Defines basic plug in information.
	 *
	 * @return Plugin_Commercials
	 */
	public function __construct()
	{
		$this->pluginName = "Commercials";
		$this->pluginVersion = "0.0.1";
		return $this;
	}

	/**
	 * Loads the ads.
	 *
	 * @return Plugin_Commercials
	 */
	public function loadAds()
	{
		if(!$this->loaded)
		{
			$result = Core::getQuery()->select("ad", array("ad_id", "position", "html_code", "views"), "", "(max_views <= views OR max_views = 0 OR max_views IS NULL) AND enabled = 1");
			foreach($result->fetchAll() as $row)
			{
				$this->ads[$row["position"]][] = $row;
			}
			$this->loaded = true;
		}
		return $this;
	}

	/**
	 * Displays the ads.
	 *
	 * @param string $event	Event mehtod that has been called
	 * @param mixed $args	Arguments [ignored]
	 *
	 * @return Plugin_Commercials
	 */
	public function __call($event, $args = null)
	{
		if(Core::getConfig()->get("COMMERCIALS_ENABLED") && !Core::getUser()->ifPermissions("HIDE_ADS"))
		{
			$this->loadAds();
			$event = Str::substring($event, 2);
			if(isset($this->ads[$event]))
			{
				$index = array_rand($this->ads[$event]);
				$ad = $this->ads[$event][$index];
				if(Core::getConfig()->get("COUNT_AD_VIEWS"))
				{
					Core::getQuery()->update("ad", array("views" => $ad["views"]+1), "ad_id = ?", array($ad["ad_id"]));
				}
				return htmlspecialchars_decode($ad["html_code"]);
			}
		}
		return null;
	}

	/**
	 * Returns all ads.
	 *
	 * @return array
	 */
	public function getAds()
	{
		$this->loadAds();
		return $this->ads;
	}

	/**
	 * Adds an event to all position names.
	 *
	 * @param Recipe_Template_Adapter_Default $engine
	 * @param string $template
	 * @param boolean $noLayout
	 *
	 * @return Plugin_Commercials
	 */
	public function onTemplatePreDisplay(Recipe_Template_Adapter_Default $engine, $template, $noLayout)
	{
		if(!$noLayout && Core::getConfig()->get("COMMERCIALS_ENABLED"))
		{
			$this->loadAds();
			foreach($this->ads as $position => $none)
			{
				Hook::addHook($position, $this);
			}
		}
		return $this;
	}
}

Hook::addHook("TemplatePreDisplay", new Plugin_Commercials());
?>