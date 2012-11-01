<script type="text/javascript" src="{const=BASE_URL}js/lib/tiny_mce/tiny_mce_gzip.js"></script>
<script type="text/javascript">
//<![CDATA[
tinyMCE_GZ.init({
language: "{@langcode}",theme: "advanced",disk_cache : true,debug : false,
plugins: "safari,style,table,advhr,advimage,advlist,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,contextmenu,paste,fullscreen,noneditable,xhtmlxtras"
});
//]]>
</script>
<script type="text/javascript">
//<![CDATA[
tinyMCE.init({
language: "{@langcode}",forced_root_block: "div",theme: "advanced",skin : "alliancetext",mode: "exact",elements: "textextern,textintern",theme_advanced_toolbar_location: "top",theme_advanced_toolbar_align : "left",theme_advanced_disable: "anchor,styleselect",theme_advanced_statusbar_location: "bottom",theme_advanced_resizing: true,theme_advanced_resize_horizontal: false,width: 500,height: 370,relative_urls: false,remove_script_host: false,
theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,insertdate,inserttime,preview",
theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,forecolor,backcolor",
theme_advanced_buttons4 : "styleprops,cite,abbr,acronym,|,link,unlink,anchor,image,cleanup,code,fullscreen,|,charmap,emotions,iespell,media,advhr,|,sub,sup",
plugins: "safari,style,table,advhr,advimage,advlist,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,contextmenu,paste,fullscreen,noneditable,xhtmlxtras"
});

$(document).ready(function() {
	$('#counter3').text($('#applicationtext').val().length);
});
//]]>
</script>
<form method="post" action="{@formaction}">
<table class="ntable">
	<tr>
		<th colspan="2">{lang}ALLIANCE_MANAGEMENT{/lang}</th>
	</tr>
	<tr>
		<td><input type="text" name="tag" id="tag" value="{@tag}" maxlength="{config}MAX_CHARS_ALLY_TAG{/config}" /> <label for="tag">{lang}ALLIANCE_TAG{/lang}</label></td>
		<td><input type="submit" name="changetag" value="{lang}PROCEED{/lang}" class="button" /></td>
	</tr>
	<tr>
		<td><input type="text" name="name" id="name" value="{@name}" maxlength="{config}MAX_CHARS_ALLY_NAME{/config}" /> <label for="name">{lang}ALLIANCE_NAME{/lang}</label></td>
		<td><input type="submit" name="changename" value="{lang}PROCEED{/lang}" class="button" /></td>
	</tr>
	<tr>
		<td colspan="2" class="center">[ {link[DIPLOMACY]}"game/".SID."/Alliance/Diplomacy"{/link} ] [ {link[RIGHT_MANAGEMENT]}"game/".SID."/Alliance/RightManagement"{/link} ]</td>
	</tr>
</table>
</form>
<form method="post" action="{@formaction}">
<table class="ntable">
	<tr>
		<th><a onclick="javascript:displayAllyText('ExternAllyText');" class="tab active-tab" id="ExternAllyText_Tab">{lang}EXTERN_ALLIANCE_TEXT{/lang}</a><a onclick="javascript:displayAllyText('InternAllyText');" class="tab" id="InternAllyText_Tab">{lang}INTERN_ALLIANCE_TEXT{/lang}</a><a onclick="javascript:displayAllyText('ApplicationAllyText');" class="tab" id="ApplicationAllyText_Tab">{lang}APPLICATION_TEXT{/lang}</a></th>
	</tr>
	<tr>
		<td>
			<div id="ExternAllyText">
				<textarea name="textextern" id="textextern" cols="75" rows="15" class="center">{@textextern}</textarea>
			</div>
			<div id="InternAllyText" style="display: none;">
				<textarea name="textintern" id="textintern" cols="75" rows="15" class="center">{@textintern}</textarea>
			</div>
			<div id="ApplicationAllyText" style="display: none;">
				<textarea name="applicationtext" id="applicationtext" cols="75" rows="15" class="center" onkeyup="maxlength(this,{config}MAX_APPLICATION_TEXT_LENGTH{/config},'counter3');">{@applicationtext}</textarea><br/>
				{lang}MAXIMUM{/lang} <span id="counter3">0</span> / {@maxapplicationtext} {lang}CHARACTERS{/lang}
			</div>
			<br/>{@externerr}{@internerr}{@apperr}
		</td>
	</tr>
	<tr>
		<td class="center"><input type="reset" class="button" /><input type="submit" name="changetext" value="{lang}PROCEED{/lang}" class="button" /></td>
	</tr>
</table>
<table class="ntable">
	<tr>
		<th colspan="2">{lang}MENU_PREFERENCES{/lang}</th>
	</tr>
	<tr>
		<td><label for="logo">{lang}LOGO{/lang}</label><br />{@logoerr}</td>
		<td><input type="text" name="logo" id="logo" value="{@logo}" maxlength="100" /></td>
	</tr>
	<tr>
		<td><label for="hp">{lang}HOMEPAGE{/lang}</label><br />{@hperr}</td>
		<td><input type="text" name="homepage" id="hp" value="{@homepage}" maxlength="100" /><input type="checkbox" name="showhomepage" value="1" id="showhp"{@showhp} /><label for="showhp">{lang}VISIBLE_TO_ALL{/lang}</label></td>
	</tr>
	<tr>
		<td><label for="memberlist">{lang}MEMBERLIST_SORT{/lang}</label></td>
		<td><select name="memberlistsort" id="memberlist"><option value="1"{@bypoinst}>{lang}BY_POINTS{/lang}</option><option value="2"{@byname}>{lang}BY_NAME{/lang}</option></select><input type="checkbox" name="showmember" value="1" id="showmember"{@showmember} /><label for="showmember">{lang}VISIBLE_TO_ALL{/lang}</label></td>
	</tr>
	<tr>
		<td><label for="apps">{lang}ENABLE_APPLICATIONS{/lang}</label></td>
		<td><input type="checkbox" name="open" id="apps" value="1"{@open} /></td>
	</tr>
	{if[$this->get("founder") == Core::getUser()->get("userid")]}<tr>
		<td><label for="founder">{lang}FOUNDER_NAME{/lang}</label></td>
		<td><input type="text" name="foundername" id="founder" value="{@foundername}" maxlength="{config}MAX_CHARS_ALLY_NAME{/config}" /></td>
	</tr>{/if}
	<tr>
		<td>{lang=PUBLIC_ALLIANCE_LINK}</td>
		<td><a href="{const=BASE_URL}{@langcode}/alliance/page/{@tag}" target="_blank">{const=BASE_URL}{@langcode}/alliance/page/{@tag}</a></td>
	</tr>
	<tr>
		<td colspan="2" class="center"><input type="submit" name="changeprefs" value="{lang}PROCEED{/lang}" class="button" /></td>
	</tr>
</table>
{if[$this->get("founder") != Core::getUser()->get("userid")]}<input type="hidden" name="foundername" value="{@foundername}" />{/if}
</form>
{if[$this->get("founder") == Core::getUser()->get("userid")]}
{if[$this->get("referfounder") != ""]}<form method="post" action="{@formaction}">
<table class="ntable">
	<tr>
		<th>{lang}REFER_FOUNDER_STATUS{/lang}</th>
	</tr>
	<tr>
		<td class="center"><select name="userid">{@referfounder}</select><input type="submit" name="referfounder" value="{lang}COMMIT{/lang}" onclick="return confirm('{lang}CONFIRM_PROCEEDING{/lang}');" class="button" /></td>
	</tr>
</table>
</form>{/if}
<form method="post" action="{@formaction}">
<table class="ntable">
	<tr>
		<th>{lang}ABANDON_ALLIANCE{/lang}</th>
	</tr>
	<tr>
		<td class="center"><input type="submit" name="abandonally" value="{lang}COMMIT{/lang}" onclick="return confirm('{lang}CONFIRM_PROCEEDING{/lang}');" class="button" /></td>
	</tr>
</table>
</form>
{/if}