<h1>{lang}Usergroup_Manager{/lang}</h1>
<div class="draggable">
	<form method="post">
		<input type="hidden" name="groupid" value="{request[get]}1{/request}" />
		<table class="ntable" style="width: auto;" cellpadding="4" cellspacing="0">
			<thead>
				<tr>
					<th colspan="2">{lang}Edit_User_Group{/lang}</th>
				</tr>
			</thead>
			<tr>
				<td><label for="f_title">{lang}User_Group{/lang}</label></td>
				<td><input type="text" name="grouptitle" maxlength="128" id="f_title" value="{@grouptitle}" /></td>
			</tr>
			<tr>
				<td><label for="f_perm">{lang}Permission_Name{/lang}</label></td>
				<td><select name="permissions[]" id="f_perm" size="5" multiple="multiple"><option value="0">{lang}No_Permissions{/lang}</option>{while[perms]}<option value="{loop}permissionid{/loop}"{if[in_array($row["permissionid"], $this->templateVars["perms"])]} selected="selected"{/if}>{loop}permission{/loop}</option>{/while}</select></td>
			</tr>
			<tfoot>
				<tr>
					<td colspan="2" style="text-align: center;"><input type="submit" name="save_usergroup" value="{lang}Commit{/lang}" class="button" /></td>
				</tr>
			</tfoot>
		</table>
	</form>
</div>

<div id="right"><div class="link_b">{link[Back]}"usergroups"{/link}</div></div>