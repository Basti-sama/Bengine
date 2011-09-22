<h1>{lang}Template_Editor{/lang}</h1>
<div class="draggable">
	<form method="post">
		<table class="ntable" cellpadding="4" cellspacing="0">
			<thead>
				<tr>
					<th colspan="2">{lang}Edit_Template{/lang}</th>
				</tr>
				<tr>
					<td colspan="2"><strong>{lang}Package{/lang} "{@package}"!</strong></td>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><label for="f_title">{lang}Template_Title{/lang}</label></td>
					<td><input type="text" name="title" id="f_title" maxlength="128" value="{@filename}" />{@extension}</td>
				</tr>
				<tr>
					<td colspan="2">
						<textarea name="content" cols="75" rows="30">{@contents}</textarea>
						<center><input type="submit" name="save_template" value="{lang}Commit{/lang}" class="button" /></center>
					</td>
				</tr>
			</tbody>
		</table>
	</form>
</div>
<script type="text/javascript">
//<![CDATA[
$('textarea').growfield({ auto: true, easing: true });
//]]>
</script>
<div id="right">
	<div class="link_b">{@backlink}</div>
</div>