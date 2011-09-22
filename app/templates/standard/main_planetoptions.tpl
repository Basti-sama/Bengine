<form method="post" action="{@formaction}">
<table class="ntable">
	<thead><tr>
		<th colspan="2">{lang}PLANET_OPTIONS{/lang}</th>
	</tr></thead>
	<tfoot><tr>
		<td colspan="2" class="center"><input type="submit" name="changeplanetoptions" value="{lang}COMMIT{/lang}" class="button" /></td>
	</tr></tfoot>
	<tbody><tr>
		<td>{lang}POSITION{/lang}</td>
		<td>{@position}</td>
	</tr>
	<tr>
		<td>{lang}NEW_PLANET_NAME{/lang}</td>
		<td><input type="text" name="planetname" value="{@planetName}" maxlength="{config}MAX_USER_CHARS{/config}" /></td>
	</tr>
	<tr>
		<td>{lang}ABANDON_PLANET{/lang}</td>
		<td>
			<input type="checkbox" name="abandon" value="1" onclick="javascript:showHideId('password');" />
			<input type="password" name="password" id="password" maxlength="{config}MAX_PASSWORD_LENGTH{/config}" style="display: none;" class="pwInput" />
		</td>
	</tr></tbody>
</table>
</form>