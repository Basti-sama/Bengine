<?php
/**
 * Abstract class for Plugins. Should be implemented by every plug in.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2011, Sebastian Noll
 * @license Proprietary
 * @version $Id$
 */

abstract class Recipe_PluginAbstract
{
	/**
	 * Plug in name.
	 *
	 * @var string
	 */
	protected $pluginName = "";

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	protected $pluginVersion = "";

	/**
	 * Return plug in name.
	 *
	 * @return string
	 */
	public function getPluginName()
	{
		return $this->pluginName;
	}

	/**
	 * Return plug in version.
	 *
	 * @return string
	 */
	public function getPluginVersion()
	{
		return $this->pluginVersion;
	}
}
?>