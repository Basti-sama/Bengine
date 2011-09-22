<!Doctype html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>{@page}{config=TITLE_GLUE}{@pageTitle}</title>
		<meta http-equiv="Content-Type" content="text/html; charset={@charset}"/>
		<meta name="robots" content="index,follow"/>
		<meta http-equiv="Pragma" content="no-cache"/>
		<meta http-equiv="Cache-control" content="no-cache"/>
		<meta http-equiv="Expires" content="-1"/>
		<link rel="shortcut icon" href="{const}BASE_URL{/const}favicon.ico" type="image/x-icon"/>
		{if[{var=CSS_FILES} != ""]}<link rel="stylesheet" type="text/css" href="{const}BASE_URL{/const}css/?f={@CSS_FILES}"/>{/if}
		{if[{var=JS_FILES} != ""]}<script type="text/javascript" src="{const}BASE_URL{/const}js/?f={@JS_FILES}"></script>{/if}
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
		var userInvalid = '{@userCheck}';
		var emailInvalid = '{lang}EMAIL_CHECK{/lang}';
		var passwordInvalid = '{@passwordCheck}';
		var valid = 'OK';
		var min_user_chars = {config}MIN_USER_CHARS{/config};
		var max_user_cars = {config}MAX_USER_CHARS{/config};
		var min_password_chars = {config}MIN_PASSWORD_LENGTH{/config};
		var max_password_chars = {config}MAX_PASSWORD_LENGTH{/config};
		var action = "{const=BASE_URL.LANG}";
		var url = "{const=BASE_URL.LANG}";

		{if[{var=errorMsg} != ""]}$(document).ready(function() {
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
		});{/if}
		//]]>
		</script>
		{hook}FrontHtmlBegin{/hook}
		<div id="container" class="{@containerClass}">
			<div id="content">
				{if[{var=showDefaultCotnent}]}
				<h1>{lang}WELCOME{/lang}</h1>
				<p>{lang}GAME_DESCRIPTION{/lang}</p>
				{else}
				{include}$template{/include}
				{/if}
			</div>
			<div id="navigation">
				<li><a href="#signup" class="signup">{lang=SIGN_UP}</a></li>
				{foreach[headerMenu]}<li>{loop}link{/loop}</li>{/foreach}
			</div>
			<div id="signin">
				<h2>Login</h2>
				<div>
					<form action="" method="post" id="signin-form">
						<div class="input">
							<label for="username">{lang}USERNAME{/lang}</label>
							<input type="text" name="username" id="username" tabindex="1" maxlengt="{config=MAX_USER_CHARS}" autofocus/>
						</div>
						<div class="input">
							<label for="password">{lang}PASSWORD{/lang} ({link[FORGOTTEN]}LANG."password"{/link})</label>
							<input type="password" name="password" id="password" tabindex="2" maxlengt="{config=MAX_PASSWORD_LENGTH}"/>
						</div>
						<div class="input">
							<label for="universe">{lang}UNIVERSE{/lang}</label>
							<select name="universe" id="universe" tabindex="3">
								{@uniSelection}
							</select>
						</div>
						<div class="input">
							<input type="submit" class="ui-widget-header ui-corner-all" id="signin-btn" tabindex="4" value="{lang=SIGN_IN}"/>
						</div>
					</form>
				</div>
				<p>
					<a href="#signup" class="signup">Jetzt registrieren!</a>
				</p>
				<div class="features">
					<ul>
						<li>{lang=BULLET_1}</li>
						<li>{lang=BULLET_2}</li>
						<li>{lang=BULLET_3}</li>
					</ul>
				</div>
			</div>
			<div id="logo">
				{if[Core::getConfig()->get("GAME_LOGO")]}
				<a href="{const=BASE_URL}"><img src="{config=GAME_LOGO}" title="{@pageTitle}" alt="{config=pagetitle}"/></a>
				{else}<span id="title">{@pageTitle}</span>{/if}
			</div>
			{if[{var=langCount} > 1]}<div id="language">
				<ul>
					<li class="first"></li>
				{while[languages]}
					<li><a href="{const=BASE_URL}{loop=langcode}{@selfUrl}"><img src="{const=BASE_URL}img/{loop=langcode}.gif" alt="{loop=title}" title="{loop=title}"/></a></li>
				{/while}
					<li class="last"></li>
				</ul>
			</div>{/if}
			<div id="legal">
				Powered by <a href="http://web-union.de/" class="external">Web-Union.de</a><br>
				Copyright &copy; {time=Y}
			</div>
		</div>
		{hook}FrontContentEnd{/hook}
		<div id="signup-dialog" title="{lang=SIGN_UP}">
			<form method="post" action="">
			<fieldset>
				<ul>
					<li>
						<label for="su-universe">{lang}UNIVERSE{/lang}</label>
						<select name="su-universe" id="su-universe" tabindex="1" class="uni-selection">{@uniSelection}</select>
					</li>
					<li>
						<label for="su-username">{lang}USERNAME{/lang}</label>
						<input type="text" name="su-username" id="su-username" class="text ui-widget-content ui-corner-all" maxlength="{config}MAX_USER_CHARS{/config}" />
					</li>
					<li>
						<label for="su-email">{lang}EMAIL{/lang}</label>
						<input type="text" name="su-email" id="su-email" value="" class="text ui-widget-content ui-corner-all" maxlength="50" />
					</li>
					<li>
						<label for="su-password">{lang}PASSWORD{/lang}</label>
						<input type="password" name="su-password" id="su-password" value="" class="text ui-widget-content ui-corner-all" maxlength="{config}MAX_PASSWORD_LENGTH{/config}" />
					</li>
					<li>
						<input type="button" id="signup-btn" class="ui-widget-header ui-corner-all" value="{lang=SIGN_UP}"/>
					</li>
				</ul>

			</fieldset>
			</form>
			<div id="sign-live-check" class="field_warning" style="display: none;"></div>
			<div id="Ajax_Out" style="visibility:hidden;">{image[LOADING]}comm-throbber.gif{/image}</div>
		</div>
		<div id="status-dialog" title="{lang=ERROR}">
			<p>
				{@errorMsg}
			</p>
		</div>
		{hook}FrontHtmlEnd{/hook}
	</body>
</html>