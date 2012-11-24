<?php
/**
 * Displays Google Analytics code on front pages.
 *
 * @package Bengine
 * @subpackage Google Analytics Plug-in
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: GoogleAnalytics.plugin.php 53 2011-08-13 16:05:23Z secretchampion $
 */

class Plugin_GoogleAnalytics extends Recipe_PluginAbstract
{
	/**
	 * Sets standard plug-in information.
	 *
	 * @return \Plugin_GoogleAnalytics
	 */
	public function __construct()
	{
		$this->pluginName = "Google Analytics Integration";
		$this->pluginVersion = "1.0";
	}

	/**
	 * Show Google Analytics code.
	 *
	 * @return string
	 */
	public function onFrontHtmlEnd()
	{
		if(Core::getConfig()->get("GOOGLE_ANALYTICS_ACCOUNT"))
		{
			return '<script type="text/javascript">
//<![CDATA[
var gaJsHost=(("https:"==document.location.protocol)?"https://ssl.":"http://www.");document.write(unescape("%3Cscript src=\'"+gaJsHost+"google-analytics.com/ga.js\' type=\'text/javascript\'%3E%3C/script%3E"));
//]]>
</script>
<script type="text/javascript">
//<![CDATA[
try{var pageTracker=_gat._getTracker("'.Core::getConfig()->get("GOOGLE_ANALYTICS_ACCOUNT").'");pageTracker._trackPageview();}catch(err){}
//]]>
</script>';
		}
		return null;
	}
}

Hook::addHook("FrontHtmlEnd", new Plugin_GoogleAnalytics());
?>