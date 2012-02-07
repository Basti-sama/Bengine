<table class="ntable">
	<thead><tr>
		<th colspan="5">{lang}ALLIANCE_DIPLOMACY_RELATIONSHIPS{/lang}</th>
	</tr>
	<tr>
		<th>#</th>
		<th>{lang}ALLIANCE{/lang}</th>
		<th>{lang}FOUNDER{/lang}</th>
		<th>{lang}ESTABLISHED{/lang}</th>
		<th>{lang}STATUS{/lang}</th>
	</tr></thead>
	{if[{var}applications{/var}]}<tfoot><tr>
		<td colspan="5" class="center">{@applications}</td>
	</tr></tfoot>{/if}
	<tbody>{foreach[relations]}<tr>
		<td>{loop}num{/loop}</td>
		<td>{loop}alliance{/loop}</td>
		<td>{loop}founder{/loop}</td>
		<td>{loop}time{/loop}</td>
		<td>{loop}status{/loop} {loop}determine{/loop}</td>
	</tr>{/foreach}
	{if[count($this->loopStack["relations"]) <= 0]}<tr>
	<td class="center" colspan="5">{lang}NO_MATCHES_FOUND{/lang}</td>
	</tr>{/if}</tbody>
</table>

<form method="post" action="{@formaction}">
<table class="ntable">
	<tr>
		<th colspan="2">{lang}APPLICATION_FOR_RELATIONSHIP{/lang}</th>
	</tr>
	<tr>
		<td><label for="tag">{lang}TAG{/lang}</label></td>
		<td><input type="text" name="tag" id="tag" maxlength="8" />{@nomatches}</td>
	</tr>
	<tr>
		<td><label for="status">{lang}STATUS{/lang}</label></td>
		<td>
			<select name="status" id="status">
			{foreach[statusList]}
				<option value="{loop=type_id}">{loop=trans}</option>
			{/foreach}
			</select>
		</td>
	</tr>
	<tr>
		<td><label for="message">{lang}MESSAGE{/lang}</label></td>
		<td>
			<textarea name="message" id="message" cols="35" rows="8" onkeyup="maxlength(this,{config}MAX_PM_LENGTH{/config},'counter')"></textarea><br />
			{lang}MAXIMUM{/lang} <span id="counter">0</span> / {@maxpmlength} {lang}CHARACTERS{/lang}<br />{@messageError}
		</td>
	</tr>
	<tr>
		<td class="center" colspan="2"><input type="submit" name="SendContract" value="{lang}COMMIT{/lang}" class="button" /></td>
	</tr>
</table>
</form>