<?php
/**
 * Displays share buttons in combat report.
 *
 * @package Bengine
 * @subpackage Share Button Plug-in
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: ShareButton.plugin.php 53 2011-08-13 16:05:23Z secretchampion $
 */

class Plugin_ShareButton extends Recipe_PluginAbstract
{
	/**
	 * Sets standard plug-in information.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->pluginName = "Share Button: Combat Report";
		$this->pluginVersion = "1.0";
		return;
	}

	/**
	 * Show share button code.
	 *
	 * @return string
	 */
	public function onFrontHtmlEnd()
	{
		switch(Core::getRequest()->getGET("controller"))
		{
			case "combat":
			case "alliance":
				return '<div style="margin: 1em 3em 1em 0; text-align: right;">
<a href="http://twitter.com/share" class="twitter-share-button" data-count="none">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script><br/>
<a name="fb_share" type="button_count" href="http://www.facebook.com/sharer.php">Teilen</a>
<script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"></script><br/>
<g:plusone size="tall"></g:plusone>
<script type="text/javascript">window.___gcfg={lang:"'.Core::getLang()->getOpt("code").'"};(function(){var po=document.createElement("script");po.type="text/javascript";po.async=true;po.src="https://apis.google.com/js/plusone.js";var s=document.getElementsByTagName("script")[0];s.parentNode.insertBefore(po,s);})();</script>
</div>';
			break;
		}
		return null;
	}
}

Hook::addHook("FrontHtmlEnd", new Plugin_ShareButton());
?>