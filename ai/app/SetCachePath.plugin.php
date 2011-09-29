<?php
/**
 * Set cache path plugin for AI.
 *
 * @package Recipe PHP5 Admin Interface
 * @author Sebastian Noll
 * @copyright Copyright (c) 2011, Sebastian Noll
 * @license Proprietary
 */

class SetCachePathPlugin extends Recipe_PluginAbstract
{
	/**
	 * Sets the cache pathes for AI.
	 *
	 * @param Recipe_Cache $cache
	 * @return void
	 */
	public function onCacheConstruct(Recipe_Cache $cache)
	{
		$cache->setSessionCacheDir("ai/sessions/");
		$cache->setTemplateCacheDir("ai/templates/");
	}
}
?>