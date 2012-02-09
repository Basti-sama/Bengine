<h1>{lang}Phrases_Groups{/lang}</h1>
<div class="draggable">
	<form method="post">
		<table class="ntable" cellpadding="4" cellspacing="0">
			<thead>
				<tr>
					<th colspan="2">{lang}Add_New_Group{/lang}</th>
				</tr>
			</thead>
			<tr>
				<td><label for="f_title">{lang}Group_Name{/lang}</label></td>
				<td><input type="text" name="title" maxlength="128" id="f_title" /></td>
			</tr>
			<tfoot>
				<tr>
					<td colspan="2"><input type="submit" name="add_group" value="{lang}Commit{/lang}" class="button" /></td>
				</tr>
			</tfoot>
		</table>
	</form>
</div>
<div class="draggable">
	<form method="post">
		<input type="hidden" name="delete[]" value="0" />
		<table class="ntable" cellpadding="4" cellspacing="0">
			<thead>
				<tr>
					<th colspan="3">{lang}Manage_Groups{/lang}</th>
				</tr>
			</thead>
			<tr>
				<td>{lang}Group{/lang}</td>
				<td>{lang}Show_Variables{/lang}</td>
				<td>{lang}Delete{/lang}</td>
			</tr>
			{foreach[groups]}<tr>
				<td><input type="hidden" name="groups[]" value="{loop}phrasegroupid{/loop}" /><input type="text" name="title_{loop}phrasegroupid{/loop}" value="{loop}title{/loop}" maxlength="128" /></td>
				<td>{loop}variables{/loop}</td>
				<td><input type="checkbox" name="delete[]" value="{loop}phrasegroupid{/loop}" /></td>
			</tr>{/foreach}
			<tfoot>
				<tr>
					<td colspan="3"><input type="submit" name="save_groups" value="{lang}Commit{/lang}" class="button" /></th>
				</tr>
			</tfoot>
		</table>
	</form>
</div>
<div id="right"><div class="link_b">{link[Back]}"language"{/link}</div></div>