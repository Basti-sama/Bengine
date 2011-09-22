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
	 * @param string	Template name
	 *
	 * @return void
	 */
	public function __construct($template)
	{
		$this->template = Str::reverse_strrchr(basename($template), '.', true);
		$this->template = Str::substring($this->template, 0, Str::length($this->template) - 1);
		$this->sourceTemplate = file_get_contents($template);
		$this->buildPatterns()->compile();
		try { parent::putCacheContent(Core::getCache()->getTemplatePath($this->template), $this->compiledTemplate->get()); }
		catch(Exception $e) { $e->printError(); }
		return;
	}

	/**
	 * Builds the compiling patterns.
	 *
	 * @return Recipe_Template_Compiler
	 */
	protected function buildPatterns()
	{
		$this->patterns["var"][] = "/\{var}([^\"]+)\{\/var}/siU";
		$this->patterns["var"][] = "/\{var=([^\"]+)\}/siU";
		$this->patterns["link"] = "/\{link\[([^\"]+)]}(.*)\{\/link}/siU";
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
		$this->patterns["include"][] = "/\{include}(.*)\{\/include}/siU";
		$this->patterns["include"][] = "/\{include=(.*)\}/siU";
		$this->patterns["image"] = "/\{image\[([^\"]+)]}([^\"]+)\{\/image}/siU";
		$this->patterns["assignment"] = "/\{\@([^\"]+)}/siU";
		$this->patterns["php_time"] = "/\{PHPTime}/siU";
		$this->patterns["sql_queries"] = "/\{SQLQueries}/siU";
		$this->patterns["time"][] = "/\{time}(.*)\{\/time}/siU";
		$this->patterns["time"][] = "/\{time=(.*)\}/siU";

		$this->patterns["if"] = "/\{if\[(.*)]}/siU";
		$this->patterns["endif"] = "/\{\/if}/siU";
		$this->patterns["else"] = "/\{else}/siU";
		$this->patterns["elseif"] = "/\{else if\[(.*)]}/siU";

		$this->patterns["loopvar"][] = "/\{loop}([^\"]+)\{\/loop}/siU";
		$this->patterns["loopvar"][] = "/\{loop=([^\"]+)\}/siU";
		$this->patterns["while"] = "/\{while\[([^\"]+)]}(.*)\{\/while}/siU";

		$this->patterns["foreach"] = "/\{foreach\[([^\"]+)]}(.*)\{\/foreach}/siU";
		$this->patterns["totalloopvars"] = "/\{\~count}/siU";

		$this->patterns["dphptags"] = "/\?><\?php/siU";
		$this->patterns["hooks"][] = "/\{hook}([^\"]+)\{\/hook}/siU";
		$this->patterns["hooks"][] = "/\{hook=([^\"]+)\}/siU";
		return $this;
	}

	/**
	 * Compiles source template code into PHP code.
	 *
	 * @return Recipe_Template_Compiler
	 */
	protected function compile()
	{
		$this->compiledTemplate = new String($this->sourceTemplate);

		// Compile variables
		$this	->compiledTemplate
				->regEx($this->patterns["var"], "\$this->get(\"$1\", false)")
				// Compile links {link[varname]}"index.php/".SID."/Main"{/link}
				->regEx($this->patterns["link"], "<?php echo Link::get(\\2, Core::getLanguage()->get(\"\\1\")); ?>")
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
				->regEx($this->patterns["permission"], "{if[Core::getUser()->ifPermissions(\"\\1\")]}\\2{/if}")
				// Compile includes {include}"templatename"{/include}
				->regEx($this->patterns["include"], "<?php \$this->includeTemplate(\\1); ?>")
				// Compile images {image[title]}path/pic.jpg{/image}
				->regEx($this->patterns["image"], "<?php echo Image::getImage(\"\\2\", Core::getLanguage()->getItem(\"\\1\")); ?>")
				// Compile generation times.
				->regEx($this->patterns["php_time"], "<?php echo Core::getTimer()->getTime(); ?>")
				->regEx($this->patterns["sql_queries"], "<?php echo Core::getDB()->getQueryNumber(); ?>")
				// Compile hook tags
				->regEx($this->patterns["hooks"], "<?php echo Hook::event(\"\\1\", array(\$this)); ?>")
				// Compile time designations.
				->regEx($this->patterns["time"], "<?php echo Date::timeToString(3, -1, \"\\1\", false); ?>");

		$this->compileIfTags()->compileLoops();

		$this	->compiledTemplate
				// Compile wildcards {@assignment}
				->regEx($this->patterns["assignment"], "<?php echo \$this->get(\"\\1\"); ?>")
				// Remove useless double php tags.
				->regEx($this->patterns["dphptags"], "");

		Hook::event("CompileTemplate", array($this, &$this->compiledTemplate));

		$this	->compiledTemplate
				->pop(parent::setCacheFileHeader("Template Cache File")."?>\r")
				->push("\r\r<?php // Cache-Generator finished ?>");
		return $this;
	}

	/**
	 * Compiles if-else tags into PHP code.
	 *
	 * @return Recipe_Template_Compiler
	 */
	protected function compileIfTags()
	{
		// Fetch complexes if else tags like {if[term]}print this{else if[term]}print that{else if[term]}print this [...]{else}or print this{/if}
		$this	->compiledTemplate
				->regEx($this->patterns["if"], "<?php if($1) { ?>")
				->regEx($this->patterns["endif"], "<?php } ?>")
				->regEx($this->patterns["else"], "<?php } else { ?>")
				->regEx($this->patterns["elseif"], "<?php } else if($1) { ?>");
		return $this;
	}

	/**
	 * Compiles loops {while[resource]}Print this{/while} or {foreach[array]}Print this{/while}.
	 *
	 * @return Recipe_Template_Compiler
	 */
	protected function compileLoops()
	{
		$this	->compiledTemplate
				// While loops (Specially for multiple database queries).
				->regEx($this->patterns["while"], "<?php while(\$row = Core::getDB()->fetch(\$this->getLoop(\"$1\"))){ ?> $2 <?php } ?>")
				// Foreach loops (Specially for arrays).
				->regEx($this->patterns["foreach"], "<?php \$count = count(\$this->getLoop(\"$1\")); foreach(\$this->getLoop(\"$1\") as \$key => \$row) { ?> $2 <?php } ?>")
				// Variables within loops {while[resource]}{loop}column1{/loop}{/while}
				->regEx($this->patterns["loopvar"], "<?php echo (isset(\$row[\"$1\"])) ? \$row[\"$1\"] : \"\"; ?>")
				// Total Number of array elements.
				->regEx($this->patterns["totalloopvars"], "<?php echo \$count; ?>");
		return $this;
	}
}
?>