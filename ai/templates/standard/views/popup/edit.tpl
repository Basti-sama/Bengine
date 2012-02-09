<h1>{lang}Popups{/lang}</h1>
<div class="draggable">
	<form method="post">
	{if[{var=languageCount} == 1]}
	<input type="hidden" name="language_id" value="{@languages}"/>
	{/if}
		<table class="ntable" cellpadding="4" cellspacing="0">
			<thead>
				<tr>
					<th colspan="2">{lang}Edit_Popups{/lang}</th>
				</tr>
			</thead>
			<tr>
				<td><label for="f_title">{lang}Title{/lang}</label></td>
				<td><input type="text" name="title" id="f_title" value="{@title}"/></td>
			</tr>
			<tr>
				<td><label for="f_content">{lang}Content{/lang}</label></td>
				<td><textarea cols="75" rows="15" name="content" id="f_content">{@content}</textarea><br />
					<a href="#" onclick="return showPopup();">{lang}Show{/lang}</a></td>
			</tr>
			{if[{var=languageCount} > 1]}
			<tr>
				<td><label for="f_language">{lang}Language{/lang}</label></td>
				<td>
					<select name="language_id" id="f_language">
						{@languages}
					</select>
				</td>
			</tr>
			{/if}
			<tr>
				<td><label for="f_logins">{lang}Logins{/lang}</label></td>
				<td><input type="text" name="logins" id="f_logins" value="{@logins}"/></td>
			</tr>
			<tfoot>
				<tr>
					<td colspan="2"><input type="submit" name="save" value="{lang}Commit{/lang}" class="button"/></td>
				</tr>
			</tfoot>
		</table>
	</form>
</div>
<div id="right"><div class="link_b">{link[Back]}"news"{/link}</div></div>

<div id="layer"></div>
<div id="popup"><p id="popupHeading"></p><div id="popupContent"></div>
<a href="#" class="closePopup" onclick="return closePopup();">{lang}Close_Popup{/lang}</a></div>
<script type="text/javascript">
//<![CDATA[
function showPopup()
{
	$("div#popup > p#popupHeading").empty().append($("input[name=title]").val());
	$("div#popup > div#popupContent").empty().append(nl2br($("textarea[name=content]").val()));
	$("div#layer").show();
	$("div#popup").show();
	return false;
}

function closePopup()
{
	$("div#layer").hide();
	$("div#popup").hide();
	return false;
}

function nl2br(str)
{
	if(typeof(str)=="string") return str.replace(/(\r\n)|(\n\r)|\r|\n/g,"<br />");
	else return str;
}
//]]>
</script>