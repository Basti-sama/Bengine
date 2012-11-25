<?php
/**
 * Template compiler. Generates PHP code of an template.
 * Note: There is still no error reporting available.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Compiler.php 53 2011-08-13 16:05:23Z secretchampion $
 */

class Recipe_Template_Default_Compiler extends Recipe_Cache
{
	/**
	 * Source template code.
	 *
	 * @var string
	 */
	protected $sourceTemplate = "";

	/**
	 * Template file name.
	 *
	 * @var string
	 */
	protected $template = "";

	/**
	 * Regular expression patterns.
	 *
	 * @var array
	 */
	protected $patterns = array();

	/**
	 * The compiled template content.
	 *
	 * @var String
	 */
	protected $compiledTemplate = null;

	/**
	 * Constructor: Set up compiler.
	 *
	 * @param string $template	Template name
	 * @param string $type		Template type
	 *
	 * @return \Recipe_Template_Default_Compiler
	 */
	public function __construct($template, $type)
	{
		$this->template = $template;
		$filePath = Core::getTemplate()->getTemplatePath($template, $type);
		$this->sourceTemplate = file_get_contents($filePath);
		$this->buildPatterns()->compile();
		try {
			parent::putCacheContent(Core::getCache()->getTemplatePath($this->template, $type), $this->compiledTemplate->get());
		} catch(Recipe_Exception_Generic $e) {
			$e->printError();
		}
		return;
	}

	/**
	 * Builds the compiling patterns.
	 *
	 * @return Recipe_Template_Default_Compiler
	 */
	protected function buildPatterns()
	{
		$this->patterns["phrase"][] = "/\{lang}([^\"]+)\{\/lang}/siU";
		$this->patterns["phrase"][] = "/\{lang=([^\"]+)\}/siU";
		$this->patterns["config"][] = "/\{config}([^\"]+)\{\/config}/siU";
		$this->patterns["config"][] = "/\{config=([^\"]+)\}/siU";
		$this->patterns["user"][] = "/\{user}([^\"]+)\{\/user}/siU";
		$this->patterns["user"][] = "/\{user=([^\"]+)\}/siU";
		$this->patterns["request"] = "/\{request\[([^\"]+)\]\}([^\"]+)\{\/request\}/siU";
		$this->patterns["const"][] = "/\{const}([^\"]+)\{\/const}/siU";
		$this->patterns["const"][] = "/\{const=([^\"]+)\}/siU";
		$this->patterns["permission"] = "/\{perm\[([^\"]+)\]\}(.*)\{\/perm\}/siU";
		$this->patterns["assignment"] = "/\{\@([^\"]+)}/siU";
		$this->patterns["sql_queries"] = "/\{SQLQueries}/siU";
		$this->patterns["time"][] = "/\{time}(.*)\{\/time}/siU";
		$this->patterns["time"][] = "/\{time=(.*)\}/siU";

		$this->patterns["loopvar"][] = "/\{loop}([^\"]+)\{\/loop}/siU";
		$this->patterns["loopvar"][] = "/\{loop=([^\"]+)\}/siU";

		$this->patterns["dphptags"] = "/\?><\?php/siU";
		$this->patterns["hooks"][] = "/\{hook}([^\"]+)\{\/hook}/siU";
		$this->patterns["hooks"][] = "/\{hook=([^\"]+)\}/siU";
		return $this;
	}

	/**
	 * Compiles source template code into PHP code.
	 *
	 * @return Recipe_Template_Default_Compiler
	 */
	protected function compile()
	{
		$this->compiledTemplate = new String($this->sourceTemplate);

		// Compile variables
		$this	->compiledTemplate
				// Compile language variables {lang}varname{/lang}
				->regEx($this->patterns["phrase"], "<?php echo Core::getLanguage()->get(\"\\1\"); ?>")
				// Compile config variables {config}varname{/config}
				->regEx($this->patterns["config"], "<?php echo Core::getOptions()->get(\"\\1\"); ?>")
				// Compile session variables {user}varname{/user}
				->regEx($this->patterns["user"], "<?php echo Core::getUser()->get(\"\\1\"); ?>")
				// Compile request variables {request[get]}varname{/request}{request[post]}varname{/request}{request[cookie]}varname{/request}
				->regEx($this->patterns["request"], "<?php echo Core::getRequest()->get(\"\\1\", \"\\2\"); ?>")
				// Compile constants {const}CONSTANT{/const}
				->regEx($this->patterns["const"], "<?php echo \\1; ?>")
				// Parse permission expression {perm[CAN_READ_THIS]}print this{/perm}
				->regEx($this->patterns["permission"], "<?php if(Core::getUser()->ifPermissions(\"\\1\")): ?>\\2<?php endif; ?>")
				// Prints number of SQL queries
				->regEx($this->patterns["sql_queries"], "<?php echo Core::getDB()->getQueryNumber(); ?>")
				// Compile hook tags
				->regEx($this->patterns["hooks"], "<?php echo Hook::event(\"\\1\", array(\$this)); ?>")
				// Compile time designations.
				->regEx($this->patterns["time"], "<?php echo Date::timeToString(3, -1, \"\\1\", false); ?>")
				// Compile wildcards {@assignment}
				->regEx($this->patterns["assignment"], "<?php echo \$this->get(\"\\1\"); ?>")
				// Variables within loops {while[resource]}{loop}column1{/loop}{/while}
				->regEx($this->patterns["loopvar"], "<?php echo (isset(\$row[\"$1\"])) ? \$row[\"$1\"] : \"\"; ?>")
				// Remove useless double php tags.
				->regEx($this->patterns["dphptags"], "");

		Hook::event("CompileTemplate", array($this, &$this->compiledTemplate));

		$this	->compiledTemplate
				->pop(parent::setCacheFileHeader("Template Cache File")."?>\r")
				->push("\r\r<?php // Cache-Generator finished ?>");
		return $this;
	}
}
?>