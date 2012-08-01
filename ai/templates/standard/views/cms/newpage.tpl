<h1>{lang}CMS_Manager{/lang}</h1>
<div class="draggable">
	<form method="post" action="{@formaction}">
		<table class="ntable" cellpadding="4" cellspacing="0">
			<thead>
				<tr>
					<th colspan="2">{lang}New_Page{/lang}</th>
				</tr>
			</thead>
			<tr>
				<td><label for="f_title">{lang}Title{/lang}</label></td>
				<td><input type="text" name="title" id="f_title" /></td>
			</tr>
			<tr>
				<td><label for="f_language">{lang}Language{/lang}</label></td>
				<td><select name="languageid" id="f_language">{while[langselection]}<option value="{loop}languageid{/loop}">{loop}title{/loop}</option>{/while}</select></td>
			</tr>
			<tr>
				<td><label for="f_position">{lang}Position{/lang}</label></td>
				<td><select name="position" id="f_position" size="1">
					<option value="h">{lang}header{/lang}</option></select></td>
			</tr>
			<tr>
				<td><label for="f_displayorder">{lang}Displayorder{/lang}</label></td>
				<td><input type="text" name="displayorder" id="f_displayorder" /></td>
			</tr>
			<tr>
				<td><label for="f_label">{lang}Label{/lang}</label></td>
				<td><input type="text" name="label" id="f_label" /></td>
			</tr>
			<tr>
				<td><label for="f_link">{lang}Link{/lang}</label></td>
				<td><input type="text" name="link" id="f_link" /></td>
			</tr>
			<tr>
				<td><label for="f_content">{lang}Content{/lang}</label></td>
				<td><textarea name="content" cols="70" rows="3"></textarea></td>
			</tr>
			<tfoot>
				<tr>
					<td colspan="2"><input type="submit" name="add_page" value="{lang}Commit{/lang}" class="button" /></td>
				</tr>
			</tfoot>
		</table>
	</form>
</div>
<script type="text/javascript">
//<![CDATA[
$('textarea').growfield({ auto: true, easing: true });
//]]>
</script>
<div id="right"><div class="link_b">{link[Back]}"cms"{/link}</div></div>