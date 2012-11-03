<h1>{lang}Usergroup_Manager{/lang}</h1>
<div class="dragable">
	<form method="post">
		<table class="ntable" cellpadding="4" cellspacing="0">
			<thead>
				<tr>
					<th colspan="2">{lang}Add_New_User_Group{/lang}</th>
				</tr>
			</thead>
			<tr>
				<td><label for="f_title">{lang}User_Group{/lang}</label></td>
				<td><input type="text" name="grouptitle" maxlength="128" id="f_title" /></td>
			</tr>
			<tr>
				<td><label for="f_perm">{lang}Permission_Name{/lang}</label></td>
				<td><select name="permissions[]" id="f_perm" size="5" multiple="multiple"><option value="0">{lang}No_Permissions{/lang}</option>{while[perms]}<option value="{loop}permissionid{/loop}">{loop}permission{/loop}</option>{/while}</select></td>
			</tr>
			<tfoot>
				<tr>
					<td colspan="2"><input type="submit" name="add_usergroup" value="{lang}Commit{/lang}" class="button" /></td>
				</tr>
			</tfoot>
		</table>
	</form>
</div>
<div class="dragable" style="top: 400px;">
	<form method="post">
		<input type="hidden" name="delete[]" value="0" />
		<table class="ntable" cellpadding="4" cellspacing="0">
			<thead>
				<tr>
					<th colspan="3">{lang}Available_User_Groups{/lang}</th>
				</tr>
			</thead>
			<tr>
				<td>#</td>
				<td>{lang}User_Group{/lang}</td>
				<td>{lang}Delete{/lang}</td>
			</tr>
			{foreach[groups]}<tr>
				<td>{loop}usergroupid{/loop}</td>
				<td>[{loop}edit{/loop}] {loop}grouptitle{/loop}</td>
				<td>{if[$row["standard"] == 1]}{lang}Standard{/lang}{else}<input type="checkbox" name="delete[]" value="{loop}usergroupid{/loop}" />{/if}</td>
			</tr>{/foreach}
			<tfoot>
				<tr>
					<td colspan="3"><input type="submit" name="delete_usergroups" value="{lang}Commit{/lang}" class="button" /></td>
				</tr>
			</tfoot>
		</table>
	</form>
</div>