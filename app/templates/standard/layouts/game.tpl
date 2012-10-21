<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>{@pageTitle}</title>
<meta http-equiv="content-type" content="text/html; charset={@charset}"/>
<meta http-equiv="Pragma" content="no-cache"/>
<meta http-equiv="Cache-control" content="no-cache"/>
<meta http-equiv="Expires" content="-1"/>

<link rel="shortcut icon" href="{@themePath}favicon.ico" type="image/x-icon"/>
<?php if($this->get("CSS_FILES") != "" && Core::getUser()->get("theme") == ""): ?>
<link rel="stylesheet" type="text/css" href="{const}BASE_URL{/const}css/?f={@CSS_FILES}&c=1"/>
<?php else: ?>
<?php foreach($this->htmlHead["css"] as $file): ?>
<link rel="stylesheet" type="text/css" href="{user}theme{/user}css/<?php echo $file ?>"/>
<?php endforeach ?>
<?php endif ?>
<?php if($this->get("JS_FILES")): ?><script type="text/javascript" src="{const}BASE_URL{/const}js/?f={@JS_FILES}&c=1"></script><?php endif ?>
<?php if($js_interface = Core::getUser()->get("js_interface")): ?>
<script type="text/javascript" src="<?php echo $js_interface ?>"></script>
<?php endif ?>
</head>
	<!--
		The source code of the browsergame engine is copyright protected by Sebastian Noll.
		This copyright notice must retain untouched.
		Website: http://bengine.de/
	-->
<body>
<div id="container">
{hook}HtmlBegin{/hook}
<form method="post" id="planetSelection" action="{@formaction}"><div style="display: none;"><input type="hidden" name="planetid" value="0" id="planetid" /></div></form>
<div id="topHeader">
	<?php $planet = Game::getPlanet() ?>
	<ul>
		<li>{@planetImageSmall}</li>
		<li class="header-planet-name"><b>{@currentPlanet}</b> {@currentCoords}</li>
		<li class="header-resource">
			<img src="{@themePath}img/met.gif" alt="{lang=METAL}" title="{lang=METAL}" width="42" height="22"/><br/>
			<span class="ressource">{lang}METAL{/lang}</span><br/>
			<span<?php if($planet->getData("metal") >= $planet->getStorage("metal")): ?> class="false"<?php endif ?>><?php echo fNumber($planet->getData("metal")) ?></span>
		</li>
		<li class="header-resource">
			<img src="{@themePath}img/silicon.gif" alt="{lang=SILICON}" title="{lang=SILICON}" width="42" height="22"/><br/>
			<span class="ressource">{lang}SILICON{/lang}</span><br/>
			<span<?php if($planet->getData("silicon") >= $planet->getStorage("silicon")): ?> class="false"<?php endif ?>><?php echo fNumber($planet->getData("silicon")) ?></span>
		</li>
		<li class="header-resource">
			<img src="{@themePath}img/hydrogen.gif" alt="{lang=HYDROGEN}" title="{lang=HYDROGEN}" width="42" height="22"/><br/>
			<span class="ressource">{lang}HYDROGEN{/lang}</span><br/>
			<span<?php if($planet->getData("hydrogen") >= $planet->getStorage("hydrogen")): ?> class="false"<?php endif ?>><?php echo fNumber($planet->getData("hydrogen")) ?></span>
		</li>
		<li class="header-resource">
			<img src="{@themePath}img/energy.gif" alt="{lang=ENERGY}" title="{lang=ENERGY}" width="42" height="22"/><br/>
			<span class="ressource">{lang}ENERGY{/lang}</span><br/>
			<span<?php if($planet->getConsumption("energy") >= $planet->getProd("energy")): ?> class="false"<?php endif ?> title="<?php echo fNumber($planet->getEnergy()) ?>"><?php echo fNumber($planet->getConsumption("energy")) ?>/<?php echo fNumber($planet->getProd("energy")) ?></span>
		</li>
	</ul>
</div>
<br class="clear" /><br class="clear" />
<div id="leftMenu">
	<ul>
		{foreach[navigation]}<li{loop}attributes{/loop}>{loop}link{/loop}</li>{/foreach}
		{hook}ListMenu{/hook}
	</ul>
</div>
<br class="clear" />
<div id="content">
{hook}ContentStarts{/hook}
{if[{var}delete{/var}]}<div class="warning">{@delete}</div>{/if}
{if[{var}umode{/var}]}<div class="info">{@umode}</div>{/if}
{if[{var}LOG{/var}]}{@LOG}{/if}
{include}$template{/include}
{hook}ContentEnds{/hook}
</div>
<div id="planets">
	<ul>
		{foreach[planetHeaderList]}
		<li>
			<a href="{loop}planetid{/loop}" class="goto{if[$row["planetid"] == Game::getPlanet()->getPlanetId()]} cur-planet{/if}">
				{loop}picture{/loop}<br />{loop}planetname{/loop}
			</a>
			{if[$row["moonid"] > 0]}
			<a href="{loop}moonid{/loop}" class="goto {if[$row["moonid"] == Game::getPlanet()->getPlanetId()]}cur-moon{else}moon-select{/if}">
				{loop}mpicture{/loop}<br />{loop}moon{/loop}
			</a>
			{/if}
		</li>
		{/foreach}
	</ul>
	<a href="#" id="edit-order"><span>{lang=EDIT_PLANET_SORTING}</span></a>
	<a href="{url}"game.php/".SID."/Preferences/savePlanetOrder"{/url}" id="save-order"><span>{lang=SAVE_PLANET_SORTING}</span></a>
</div>
{hook}HtmlEnd{/hook}
</div>
</body>
</html>