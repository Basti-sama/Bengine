<script type="text/javascript" src="{const=BASE_URL}js/lib/tiny_mce/tiny_mce_gzip.js"></script>
<script type="text/javascript">
//<![CDATA[
tinyMCE_GZ.init({
language: "{@langcode}",theme: "advanced",disk_cache : true,debug : false,
plugins: "style,table,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,contextmenu,paste,fullscreen,noneditable,xhtmlxtras"
});
//]]>
</script>
<script type="text/javascript">
//<![CDATA[
$(document).ready(function() {
	$('#counter').text($('#application').val().length);
});
//]]>
</script>
<form method="post" action="{@formaction}">
<div><input type="hidden" name="aid" value="{@aid}" /></div>
<table class="ntable">
	<tr>
		<th>{lang}APPLICATION{/lang} {@alliance}</th>
	</tr>
	<tr>
		<td>
			<textarea cols="60" rows="10" class="center" name="application" id="application" onkeyup="maxlength(this,{config}MAX_APPLICATION_TEXT_LENGTH{/config},'counter');">{@applicationtext}</textarea><br/>
			{lang}MAXIMUM{/lang} <span id="counter">0</span> / {@maxapplicationtext} {lang}CHARACTERS{/lang}
		</td>
	</tr>
	<tr>
		<td class="center"><input type="submit" name="apply" value="{lang}COMMIT{/lang}" class="button"/></td>
	</tr>
</table>
</form>