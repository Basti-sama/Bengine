<h1>{lang}Permission_Manager{/lang}</h1>
<div class="draggable">
	<form method="post">
		<table class="ntable" cellpadding="4" cellspacing="0">
			<thead>
				<tr>
					<th colspan="2">{lang}New_Permission{/lang}</th>
				</tr>
			</thead>
			<tr>
				<td><label for="f_permission">{lang}Permission_Name{/lang}</label></td>
				<td><input type="text" name="permission" id="f_permission" maxlength="255" /></td>
			</tr>
			<tfoot>
				<tr>
					<td colspan="2"><input type="submit" name="add_permission" value="{lang}Commit{/lang}" class="button" /></td>
				</tr>
			</tfoot>
		</table>
	</form>
</div>
<div class="draggable">
	<form method="post">
		<input type="hidden" name="delete[]" value="0" />
		<input type="hidden" name="perm[]" value="0" />
		<table class="ntable" cellpadding="4" cellspacing="0">
			<thead>
				<tr>
					<th colspan="3">{lang}Available_Permissions{/lang}</th>
				</tr>
			</thead>
			<tr>
				<td>#</td>
				<td>{lang}Permission_Name{/lang}</td>
				<td>{lang}Delete{/lang}</td>
			</tr>
			{while[perms]}<tr>
				<td>{loop}permissionid{/loop}</td>
				<td><input type="hidden" name="perm[]" value="{loop}permissionid{/loop}" /><input type="text" name="perm_{loop}permissionid{/loop}" value="{loop}permission{/loop}" /></td>
				<td><input type="checkbox" name="delete[]" value="{loop}permissionid{/loop}" /></td>
			</tr>{/while}
			<tfoot>
				<tr>
					<td colspan="3"><input type="submit" name="update_permission" value="{lang}Commit{/lang}" class="button" /></td>
				</tr>
			</tfoot>
		</table>
	</form>
</div>