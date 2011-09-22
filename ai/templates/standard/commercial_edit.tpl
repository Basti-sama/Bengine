<h1>Ad-Manager</h1>
<div class="draggable">
	<form action="" method="post">
	<input type="hidden" name="ad_id" value="{@ad_id}"/>
		<table class="ntable" cellpadding="4" cellspacing="0">
			<thead>
				<tr>
					<th colspan="2">Werbung ändern</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><label for="f_name">Name</label></td>
					<td><input type="text" name="name" id="f_name" value="{@name}"/></td>
				</tr>
				<tr>
					<td><label for="f_position">Position</label></td>
					<td><input type="text" name="position" id="f_position" value="{@position}"/></td>
				</tr>
				<tr>
					<td><label for="f_max_views">Max. Anzeigen</label></td>
					<td><input type="text" name="max_views" id="f_max_views" value="{@max_views}"/></td>
				</tr>
				<tr>
					<td><label for="f_enabled">Aktiviert</label></td>
					<td><input type="checkbox" name="enabled" id="f_enabled" value="1"{if[{var=enabled}]} checked="checked"{/if}/></td>
				</tr>
				<tr>
					<td><label for="f_html_code">HTML</label></td>
					<td><textarea name="html_code" id="f_html_code" cols="50" rows="8">{@html_code}</textarea></td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="2"><input type="submit" name="edit_ad" value="{lang=Commit}" class="button"/></td>
				</tr>
			</tfoot>
		</table>
	</form>
</div>
<div id="right"><div class="link_b">{link[Back]}"commercial"{/link}</div></div>