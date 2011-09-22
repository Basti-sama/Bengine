<script type="text/javascript" src="{const=BASE_URL}js/lib/tiny_mce/tiny_mce_gzip.js"></script>
<script type="text/javascript">
//<![CDATA[
tinyMCE_GZ.init({
language: "{@langcode}",theme: "advanced",plugins: "emotions",disk_cache : true,debug : false
});
//]]>
</script>
<script type="text/javascript">
//<![CDATA[
tinyMCE.init({
language: "{@langcode}",forced_root_block: "div",skin : "message",mode: "exact",elements: "message",theme: "advanced",theme_advanced_toolbar_location: "top",theme_advanced_toolbar_align : "left",theme_advanced_disable: "anchor,styleselect",width: 436,height: 250,plugins: "emotions",theme_advanced_buttons1_add: "forecolor,backcolor",theme_advanced_buttons3_add: "emotions",relative_urls: false,remove_script_host: false
});
//]]>
</script>
<form method="post" action="{@sendAction}">
<table class="ntable">
	<thead><tr>
		<th colspan="2">{lang}NEW_MESSAGE{/lang}</th>
	</tr></thead>
	<tbody><tr>
		<td><label for="receiver">{lang}RECEIVER{/lang}</label></td>
		<td><input type="text" name="receiver" id="receiver" value="{@receiver}" maxlength="{config}MAX_USER_CHARS{/config}" /><br />{@userError}</td>
	</tr>
	<tr>
		<td><label for="subject">{lang}SUBJECT{/lang}</label></td>
		<td><input type="text" name="subject" id="subject" value="{@subject}" maxlength="50" /><br />{@subjectError}</td>
	</tr>
	<tr>
		<td><label for="message">{lang}MESSAGE{/lang}</label></td>
		<td>
			<textarea name="message" id="message" cols="35" rows="8"></textarea>
			<br/>{@messageError}
		</td>
	</tr></tbody>
	<tfoot><tr>
		<td colspan="2"><input type="submit" name="send" value="{lang}COMMIT{/lang}" class="button" /></td>
	</tr></tfoot>
</table>
</form>