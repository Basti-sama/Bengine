<?php
/**
 * Initialize language system.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Language.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Recipe_Language extends Recipe_Collection
{
	/**
	 * Language ID.
	 *
	 * @var mixed
	 */
	protected $langid = 0;

	/**
	 * Variable names.
	 *
	 * @var array
	 */
	protected $vars = array();

	/**
	 * Phrases groups in usage.
	 *
	 * @var array
	 */
	protected $grouplist = array();

	/**
	 * Particiluar options of current language.
	 *
	 * @var array
	 */
	protected $opts = array();

	/**
	 * Short language code. Indentified with two letters.
	 *
	 * @var string
	 */
	protected $langcode = "";

	/**
	 * Enable cache.
	 *
	 * @var boolean
	 */
	protected $cacheActive = true;

	/**
	 * Enables automatic cache rebuilding.
	 *
	 * @var boolean
	 */
	protected $autoCaching = false;

	/**
	 * Indicates if all variables were already cached.
	 *
	 * @var boolean
	 */
	protected $uncached = true;

	/**
	 * Holds dynamic variables.
	 *
	 * @var array
	 */
	protected $dynvars = array();

	/**
	 * Constructor.
	 *
	 * @param string	Shortcut of language package.
	 *
	 * @return void
	 */
	public function __construct($langid, $groups)
	{
		$this->langid = $langid;
		$this->getDefaultLang();
		$this->item = array();
		$this->vars = array();
		if($this->cacheActive) { $this->cacheActive = CACHE_ACTIVE; }
		$this->opts = $this->getOptions();
		$this->load($groups);
		return;
	}

	/**
	 * Set phrases groups.
	 *
	 * @param string	List of groups, separated by comma
	 *
	 * @return array	All language variables
	 */
	public function load($groups = array())
	{
		if(!is_array($groups))
		{
			$groups = Arr::trimArray(explode(",", $groups));
		}
		$this->grouplist = array_merge($this->grouplist, $groups);
		$this->grouplist = Arr::clean($this->grouplist);
		return $this->fetch();
	}

	/**
	 * Load language variables into an array.
	 *
	 * @return Recipe_Language
	 */
	protected function fetch()
	{
		Hook::event("LoadLanguageVarsStart", array($this));
		if($this->cacheActive)
		{
			$this->item = array_merge($this->item, Core::getCache()->getLanguageCache($this->opts["langcode"], $this->grouplist));
			$this->vars = array();
			foreach($this->item as $key => $val)
			{
				$this->item[$key] = $val;
				$this->vars[] = $key;
			}
		}
		else
		{
			$this->getFromDB();
		}
		Hook::event("LoadLanguageVarsClose", array($this));
		return $this;
	}

	/**
	 * Load language variables into array.
	 *
	 * @return Recipe_Language
	 */
	protected function getFromDB()
	{
		$whereclause = "";
		for($i = 0; $i < count($this->grouplist); $i++)
		{
			$whereclause .= "pg.title = '".$this->grouplist[$i]."' AND p.languageid = '".$this->langid."'";
			if($i < count($this->grouplist) - 1) { $whereclause .= " OR "; }
		}
		$select = array("p.title", "p.content");
		$result = Core::getQuery()->select("phrases AS p", $select, "LEFT JOIN ".PREFIX."phrasesgroups AS pg ON (pg.phrasegroupid = p.phrasegroupid)", $whereclause);
		while($row = Core::getDatabase()->fetch($result))
		{
		  	$compiler = new Recipe_Language_Compiler($row["content"], true);
		  	$row["content"] = $compiler->getPhrase();
		  	$compiler->shutdown();
			$this->setItem($row["title"], $row["content"]);
		}
		Core::getDatabase()->free_result($result);
		return $this;
	}

	/**
	 * Returns all var names.
	 *
	 * @return array	Var names
	 */
	public function getVarnames()
	{
		return $this->vars;
	}

	/**
	 * Counts array size.
	 *
	 * @return integer	The number of array elements
	 */
	public function getVarCount()
	{
		return sizeof($this->item);
	}

	/**
	 * Sets all language options.
	 *
	 * @return array	Options
	 */
	public function getOptions()
	{
		try {
			$row = Core::getDatabase()->query_unique("SELECT * FROM ".PREFIX."languages WHERE languageid = '".$this->langid."'");
		} catch(Exception $e) { $e->printError(); }
		return $row;
	}

	/**
	 * Detects default language.
	 *
	 * @return Recipe_Language
	 */
	public function getDefaultLang()
	{
		if(Str::length($this->langid) == 0)
		{
			$this->langcode = (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) ? substr($_SERVER["HTTP_ACCEPT_LANGUAGE"], 0, 2) : "";
			$this->langid = 0;
		}
		else if(!is_numeric($this->langid))
		{
			$this->langcode = $this->langid;
			$this->langid = 0;
		}
		$result = Core::getQuery()->select("languages", array("languageid"), "", "languageid = '".$this->langid."' OR langcode = '".$this->langcode."'", "", "1");
		if($row = Core::getDB()->fetch($result)) { $this->langid = $row["languageid"]; }
		else { $this->langid = Core::getOptions()->defaultlanguage; }
		Core::getDatabase()->free_result($result);
		return $this;
	}

	/**
	 * Returns item. If item does not exist, return item name.
	 *
	 * @param string	Variable name
	 *
	 * @return string	Value
	 */
	public function getItem($var)
	{
		Hook::event("GetLanguageItem", array($this, &$var));
		if($this->exists($var))
		{
			return $this->replaceWildCards($this->item[$var]);
		}
		// Try to cache language.
		if($this->cacheActive && $this->autoCaching && $this->uncached) { Core::getCache()->cacheLanguage($this->opts["langcode"]); }
		$this->uncached = false;
		return $var;
	}

	/**
	 * Alias to getItem().
	 *
	 * @param string	Variable name
	 *
	 * @return string	Value
	 */
	public function get($var)
	{
		return $this->getItem($var);
	}

	/**
	 * Set and fill item with content.
	 *
	 * @param string	Var name
	 * @param string	Value
	 *
	 * @return Recipe_Language
	 */
	public function setItem($var, $value)
	{
		if(!$this->exists($var))
		{
			array_push($this->vars, $var);
		}
		$this->item[$var] = $value;
		return $this;
	}

	/**
	 * Alias to setItem().
	 *
	 * @param string	Var name
	 * @param string	Value
	 *
	 * @return Language
	 */
	public function set($var, $value)
	{
		return $this->setItem($var, $value);
	}

	/**
	 * Assigns values to phrases variables.
	 *
	 * @param string	The variable name
	 * @param mixed		The value to assign
	 *
	 * @return Recipe_Language
	 */
	public function assign($variable, $value = null)
	{
		if(is_array($variable))
		{
			foreach($variable as $key => $val)
			{
				if(Str::length($key) > 0) { $this->assign($key, $val); }
			}
		}
		else if(is_string($variable) || is_numeric($variable))
		{
			if(Str::length($variable) > 0) { $this->dynvars[$variable] = $value; }
		}
		return $this;
	}

	/**
	 * Returns an phrases assignment.
	 *
	 * @param string	The variable name
	 *
	 * @return mixed
	 */
	public function getAssignment($variable)
	{
		return (isset($this->dynvars[$variable])) ? $this->dynvars[$variable] : "";
	}

	/**
	 * Replaces wildcards like {@assignment}.
	 *
	 * @param string	String to parse
	 *
	 * @return string	parsed string
	 */
	protected function replaceWildCards($content)
	{
		return preg_replace("/\{\@([^\"]+)}/siUe", '$this->getAssignment("$1")', $content);
	}

	/**
	 * Rebuilds the language cache for the current language.
	 *
	 * @param mixed		Indicates a special phrase group
	 *
	 * @return Recipe_Language
	 */
	public function rebuild($groups = null)
	{
		if(!is_null($groups))
		{
			Core::getCache()->cachePhraseGroup($groups, $this->opts["langcode"]);
			return;
		}
		Core::getCache()->cacheLanguage($this->opts["langcode"]);
		return $this;
	}

	/**
	 * Returns an option parameter for the language.
	 *
	 * @param string	Parameter name
	 *
	 * @return mixed	Parameter value
	 */
	public function getOpt($param)
	{
		return (isset($this->opts[$param])) ? $this->opts[$param] : $param;
	}
}
?>