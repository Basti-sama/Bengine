<?php
/**
 * @package Recipe PHP Framework
 * Auto-generated cache file for:
 * Template Cache File
 * It is recommended to not modify anything here.
 */

?><!Doctype html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title><?php echo $this->get("page");  echo Core::getOptions()->get("TITLE_GLUE");  echo $this->get("pageTitle"); ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $this->get("charset"); ?>"/>
		<meta name="robots" content="index,follow"/>
		<meta http-equiv="Pragma" content="no-cache"/>
		<meta http-equiv="Cache-control" content="no-cache"/>
		<meta http-equiv="Expires" content="-1"/>
		<link rel="shortcut icon" href="<?php echo BASE_URL; ?>favicon.ico" type="image/x-icon"/>
		<?php if($this->get("CSS_FILES", false) != "") { ?><link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>css/?f=<?php echo $this->get("CSS_FILES"); ?>"/><?php } ?>
		<?php if($this->get("JS_FILES", false) != "") { ?><script type="text/javascript" src="<?php echo BASE_URL; ?>js/?f=<?php echo $this->get("JS_FILES"); ?>"></script><?php } ?>
	</head>
	<!--
		The source code of the browsergame engine is copyright protected by Sebastian Noll.
		This copyright notice must retain untouched.
		Website: http://web-union.de/bengine
	-->
	<body>
		<script type="text/javascript">
		//<![CDATA[
		// Define vars
		var userInvalid = '<?php echo $this->get("userCheck"); ?>';
		var emailInvalid = '<?php echo Core::getLanguage()->get("EMAIL_CHECK"); ?>';
		var passwordInvalid = '<?php echo $this->get("passwordCheck"); ?>';
		var valid = 'OK';
		var min_user_chars = <?php echo Core::getOptions()->get("MIN_USER_CHARS"); ?>;
		var max_user_cars = <?php echo Core::getOptions()->get("MAX_USER_CHARS"); ?>;
		var min_password_chars = <?php echo Core::getOptions()->get("MIN_PASSWORD_LENGTH"); ?>;
		var max_password_chars = <?php echo Core::getOptions()->get("MAX_PASSWORD_LENGTH"); ?>;
		var action = "<?php echo BASE_URL.LANG; ?>";
		var url = "<?php echo BASE_URL.LANG; ?>";

		<?php if($this->get("errorMsg", false) != "") { ?>$(document).ready(function() {
			$("#status-dialog").dialog({
				bgiframe: true,
				autoOpen: true,
				modal: true,
				buttons: {
					Ok: function() {
						$(this).dialog('close');
					}
				}
			});
		});<?php } ?>
		//]]>
		</script>
		<?php echo Hook::event("FrontHtmlBegin", array($this)); ?>
		<div id="container" class="<?php echo $this->get("containerClass"); ?>">
			<div id="content">
				<?php if($this->get("showDefaultCotnent", false)) { ?>
				<h1><?php echo Core::getLanguage()->get("WELCOME"); ?></h1>
				<p><?php echo Core::getLanguage()->get("GAME_DESCRIPTION"); ?></p>
				<?php } else { ?>
				<?php $this->includeTemplate($template); ?>
				<?php } ?>
			</div>
			<div id="navigation">
				<li><a href="#signup" class="signup"><?php echo Core::getLanguage()->get("SIGN_UP"); ?></a></li>
				<?php $count = count($this->getLoop("headerMenu")); foreach($this->getLoop("headerMenu") as $key => $row) { ?> <li><?php echo (isset($row["link"])) ? $row["link"] : ""; ?></li> <?php } ?>
			</div>
			<div id="signin">
				<h2>Login</h2>
				<div>
					<form action="" method="post" id="signin-form">
						<div class="input">
							<label for="username"><?php echo Core::getLanguage()->get("USERNAME"); ?></label>
							<input type="text" name="username" id="username" tabindex="1" maxlengt="<?php echo Core::getOptions()->get("MAX_USER_CHARS"); ?>" autofocus/>
						</div>
						<div class="input">
							<label for="password"><?php echo Core::getLanguage()->get("PASSWORD"); ?> (<?php echo Link::get(LANG."password", Core::getLanguage()->get("FORGOTTEN")); ?>)</label>
							<input type="password" name="password" id="password" tabindex="2" maxlengt="<?php echo Core::getOptions()->get("MAX_PASSWORD_LENGTH"); ?>"/>
						</div>
						<div class="input">
							<label for="universe"><?php echo Core::getLanguage()->get("UNIVERSE"); ?></label>
							<select name="universe" id="universe" tabindex="3">
								<?php echo $this->get("uniSelection"); ?>
							</select>
						</div>
						<div class="input">
							<input type="submit" class="ui-widget-header ui-corner-all" id="signin-btn" tabindex="4" value="<?php echo Core::getLanguage()->get("SIGN_IN"); ?>"/>
						</div>
					</form>
				</div>
				<p>
					<a href="#signup" class="signup">Jetzt registrieren!</a>
				</p>
				<div class="features">
					<ul>
						<li><?php echo Core::getLanguage()->get("BULLET_1"); ?></li>
						<li><?php echo Core::getLanguage()->get("BULLET_2"); ?></li>
						<li><?php echo Core::getLanguage()->get("BULLET_3"); ?></li>
					</ul>
				</div>
			</div>
			<div id="logo">
				<?php if(Core::getConfig()->get("GAME_LOGO")) { ?>
				<a href="<?php echo BASE_URL; ?>"><img src="<?php echo Core::getOptions()->get("GAME_LOGO"); ?>" title="<?php echo $this->get("pageTitle"); ?>" alt="<?php echo Core::getOptions()->get("pagetitle"); ?>"/></a>
				<?php } else { ?><span id="title"><?php echo $this->get("pageTitle"); ?></span><?php } ?>
			</div>
			<?php if($this->get("langCount", false) > 1) { ?><div id="language">
				<ul>
					<li class="first"></li>
				<?php while($row = Core::getDB()->fetch($this->getLoop("languages"))){ ?> 
					<li><a href="<?php echo BASE_URL;  echo (isset($row["langcode"])) ? $row["langcode"] : "";  echo $this->get("selfUrl"); ?>"><img src="<?php echo BASE_URL; ?>img/<?php echo (isset($row["langcode"])) ? $row["langcode"] : ""; ?>.gif" alt="<?php echo (isset($row["title"])) ? $row["title"] : ""; ?>" title="<?php echo (isset($row["title"])) ? $row["title"] : ""; ?>"/></a></li>
				 <?php } ?>
					<li class="last"></li>
				</ul>
			</div><?php } ?>
			<div id="legal">
				Powered by <a href="http://web-union.de/" class="external">Web-Union.de</a><br>
				Copyright &copy; <?php echo Date::timeToString(3, -1, "Y", false); ?>
			</div>
		</div>
		<?php echo Hook::event("FrontContentEnd", array($this)); ?>
		<div id="signup-dialog" title="<?php echo Core::getLanguage()->get("SIGN_UP"); ?>">
			<form method="post" action="">
			<fieldset>
				<ul>
					<li>
						<label for="su-universe"><?php echo Core::getLanguage()->get("UNIVERSE"); ?></label>
						<select name="su-universe" id="su-universe" tabindex="1" class="uni-selection"><?php echo $this->get("uniSelection"); ?></select>
					</li>
					<li>
						<label for="su-username"><?php echo Core::getLanguage()->get("USERNAME"); ?></label>
						<input type="text" name="su-username" id="su-username" class="text ui-widget-content ui-corner-all" maxlength="<?php echo Core::getOptions()->get("MAX_USER_CHARS"); ?>" />
					</li>
					<li>
						<label for="su-email"><?php echo Core::getLanguage()->get("EMAIL"); ?></label>
						<input type="text" name="su-email" id="su-email" value="" class="text ui-widget-content ui-corner-all" maxlength="50" />
					</li>
					<li>
						<label for="su-password"><?php echo Core::getLanguage()->get("PASSWORD"); ?></label>
						<input type="password" name="su-password" id="su-password" value="" class="text ui-widget-content ui-corner-all" maxlength="<?php echo Core::getOptions()->get("MAX_PASSWORD_LENGTH"); ?>" />
					</li>
					<li>
						<input type="button" id="signup-btn" class="ui-widget-header ui-corner-all" value="<?php echo Core::getLanguage()->get("SIGN_UP"); ?>"/>
					</li>
				</ul>

			</fieldset>
			</form>
			<div id="sign-live-check" class="field_warning" style="display: none;"></div>
			<div id="Ajax_Out" style="visibility:hidden;"><?php echo Image::getImage("comm-throbber.gif", Core::getLanguage()->getItem("LOADING")); ?></div>
		</div>
		<div id="status-dialog" title="<?php echo Core::getLanguage()->get("ERROR"); ?>">
			<p>
				<?php echo $this->get("errorMsg"); ?>
			</p>
		</div>
		<?php echo Hook::event("FrontHtmlEnd", array($this)); ?>
	</body>
</html><?php // Cache-Generator finished ?>