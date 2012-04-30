<form method="post" action="{@formaction}">
<table class="ntable">
{if[Core::getUser()->get("umode")]}
	<tr>
		<th colspan="2">{lang}UMODE_PREFERENCES{/lang}</th>
	</tr>
	<tr>
		<td colspan="2" class="center">{if[$this->templateVars["can_disable_umode"]]}<input type="submit" name="disable_umode" value="{lang}DISABLE_UMODE{/lang}" class="button" />{else}{@umode_to}{/if}</td>
	</tr>
	<tr>
		<th colspan="2">{lang}ADVANCED_PREFERENCES{/lang}</th>
	</tr>
	<tr>
		<td><label for="del-acc">{lang}DELETE_ACCOUNT{/lang}</label></td>
		<td>
			<input type="checkbox" name="delete" id="del-acc" value="1"{if[Core::getUser()->get("delete") > 0]} checked="checked"{/if} />
			{if[Core::getUser()->get("delete") > 0]}<br/><span class="notavailable">{@delmessage}</span>{/if}
		</td>
	</tr>
	<tr>
		<td class="center" colspan="2"><input type="submit" name="update_deletion" value="{lang}COMMIT{/lang}" class="button" /></td>
	</tr>
{else}
	<tr>
		<th colspan="2">{lang}USER_DATA{/lang}</th>
	</tr>
	<tr>
		<td><label for="username">{lang}USERNAME{/lang}</label></td>
		<td><input type="text" name="username" id="username" maxlength="{config}MAX_USER_CHARS{/config}" value="{user}username{/user}" /></td>
	</tr>
	<tr>
		<td><label for="usertitle">{lang}USER_TITLE{/lang}</label></td>
		<td><input type="text" name="usertitle" id="usertitle" maxlength="{config}MAX_USER_CHARS{/config}" value="{user}usertitle{/user}" /></td>
	</tr>
	<tr>
		<td><label for="email">{lang}EMAIL{/lang}</label></td>
		<td><input type="text" name="email" id="email" maxlength="50" value="{user}email{/user}" /></td>
	</tr>
	<tr>
		<td><label for="new-pw">{lang}NEW_PASSWORD{/lang}</label></td>
		<td><input type="password" name="password" id="new-pw" maxlength="{config}MAX_PASSWORD_LENGTH{/config}" /></td>
	</tr>
	<tr>
		<th colspan="2">{lang}FEED_URLS{/lang}</th>
	</tr>
	<tr>
		<td colspan="2"><input type="checkbox" name="generate_key" id="feed-key" value="1"/><label for="feed-key">{lang}NEW_FEED_KEY{/lang}</label></td>
	</tr>
	<tr>
		<td colspan="2">{lang}FEED_RSS{/lang}: {@rss_feed_url}</td>
	</tr>
	<tr>
		<td colspan="2">{lang}FEED_ATOM{/lang}: {@atom_feed_url}</td>
	</tr>
	<tr>
		<th colspan="2">{lang}ADVANCED_PREFERENCES{/lang}</th>
	</tr>
	{if[Core::getDB()->num_rows($this->getLoop("langs")) > 1]}<tr>
		<td><label for="language">{lang}LANGUAGE{/lang}</label></td>
		<td><select name="language" id="language">{while[langs]}<option value="{loop}languageid{/loop}"{if[Core::getUser()->get("languageid") == $row["languageid"]]} selected="selected"{/if}>{loop}title{/loop}</option>{/while}</select></td>
	</tr>{/if}
	<tr>
		<td><label for="num-esps">{lang}ESPIONAGE_PROBES{/lang}</label></td>
		<td><input type="text" name="esps" id="num-esps" maxlength="2" value="{user}esps{/user}" /></td>
	</tr>
	{if[Core::getConfig()->get("USER_EDIT_IP_CHECK")]}
	<tr>
		<td><label for="ip-check">{lang}IP_CHECK_ACTIVATED{/lang}</label></td>
		<td><input type="checkbox" name="ipcheck" id="ip-check" value="1"{if[Core::getUser()->get("ipcheck")]} checked="checked"{/if} /></td>
	</tr>
	{/if}
	<tr>
		<td><label for="vacation">{lang}VACATION_MODE{/lang}</label></td>
		<td><input type="checkbox" name="umode" id="vacation" value="1" onclick="return confirm('{lang}VACATION_WARNING{/lang}');" /></td>
	</tr>
	<tr>
		<td><label for="del-acc">{lang}DELETE_ACCOUNT{/lang}</label></td>
		<td>
			<input type="checkbox" name="delete" id="del-acc" value="1"{if[Core::getUser()->get("delete") > 0]} checked="checked"{/if} />
			{if[Core::getUser()->get("delete") > 0]}<br/><span class="notavailable">{@delmessage}</span>{/if}
		</td>
	</tr>
	<tr>
		<th colspan="2">{lang}CUSTOM_LOOK{/lang}</th>
	</tr>
	{if[count($this->getLoop("templatePacks")) > 1]}<tr>
		<td><label for="template-pack">{lang}TEMPLATE_PACKAGE{/lang}</label></td>
		<td><select name="templatepackage" id="template-pack"><option value=""></option>{foreach[templatePacks]}<option value="{loop}package{/loop}"{if[$row["package"] == Core::getUser()->get("templatepackage")]} selected="selected"{/if}>{loop}package{/loop}</option>{/foreach}</select></td>
	</tr>{/if}
	<tr>
		<td><label for="theme">{lang}THEME{/lang}</label><br/><span class="small">{lang}THEME_HINT{/lang}</span></td>
		<td><input type="text" name="theme" id="theme" maxlength="{config}MAX_INPUT_LENGTH{/config}" value="{user}theme{/user}" /></td>
	</tr>
	<tr>
		<td><label for="js_interface">{lang}JS_INTERFACE{/lang}</label><br/><span class="small">{lang}JS_INTERFACE_DESCRIPTION{/lang}</span></td>
		<td><input type="text" name="js_interface" id="js_interface" maxlength="{config}MAX_INPUT_LENGTH{/config}" value="{user}js_interface{/user}" /></td>
	</tr>
	<tr>
		<td class="center" colspan="2"><input type="submit" name="saveuserdata" value="{lang}COMMIT{/lang}" class="button" /></td>
	</tr>
	<tr>
		<th colspan="2">{lang=SIGNATURE_GENERATOR}</th>
	</tr>
	<tr>
		<td class="center" colspan="2">{@goToSignature}</td>
	</tr>
{/if}</table>
</form>