<h1>Ad-Manager</h1>
<div class="draggable">
	<form action="" method="post">
		<table class="ntable" cellpadding="4" cellspacing="0">
			<thead>
				<tr>
					<th colspan="2">Neue Werbung</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><label for="f_name">Name</label></td>
					<td><input type="text" name="name" id="f_name"/></td>
				</tr>
				<tr>
					<td><label for="f_position">Position</label></td>
					<td><input type="text" name="position" id="f_position"/></td>
				</tr>
				<tr>
					<td><label for="f_max_views">Max. Anzeigen</label></td>
					<td><input type="text" name="max_views" id="f_max_views"/></td>
				</tr>
				<tr>
					<td><label for="f_enabled">Aktiviert</label></td>
					<td><input type="checkbox" name="enabled" id="f_enabled" value="1" checked="checked"/></td>
				</tr>
				<tr>
					<td><label for="f_html_code">HTML</label></td>
					<td><textarea name="html_code" id="f_html_code" cols="50" rows="8"></textarea></td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="2"><input type="submit" name="add_ad" value="{lang=Commit}" class="button"/></td>
				</tr>
			</tfoot>
		</table>
	</form>
</div>
<div class="draggable">
	<form action="" method="post">
		<table class="ntable" cellpadding="4" cellspacing="0">
			<thead>
				<tr>
					<th colspan="6">Vorhandene Werbung</th>
				</tr>
				<tr>
					<td>#</td>
					<td>Position</td>
					<td>Name</td>
					<td>Anzeigen</td>
					<td>Max. Anzeigen</td>
					<td></td>
				</tr>
			</thead>
			<tbody>
				{foreach[ads]}<tr>
					<td>{loop=ad_id}</td>
					<td>{loop=position}</td>
					<td>{loop=name}</td>
					<td>{loop=views}</td>
					<td>{if[!$row["enabled"]]}Deaktiviert{else if[$row["max_views"] > 0]}{loop=max_views}{else}unbegrenzt{/if}</td>
					<td>[{loop=edit_link}] [{loop=reset_link}] <input type="checkbox" name="ads[]" value="{loop=ad_id}"/></td>
				</tr>{/foreach}
			</tbody>
			{if[$count > 0]}<tfoot>
				<tr>
					<td colspan="6"><input type="submit" value="{lang=Delete}" name="delete" class="button"/></td>
				</tr>
			</tfoot>{/if}
		</table>
	</form>
</div>