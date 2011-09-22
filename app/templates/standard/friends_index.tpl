<form method="post" action="{@formaction}" id="accept">
<div>
	<input type="hidden" name="remove[]" value="0" />
	<input type="hidden" name="relid" id="relid" value="" />
</div>
<table class="ntable">
	<thead><tr>
		<th colspan="6">{lang}BUDDYLIST{/lang}</th>
	</tr>
	<tr>
		<th>{lang}USERNAME{/lang}</th>
		<th>{lang}POINTS{/lang}</th>
		<th>{lang}ALLIANCE{/lang}</th>
		<th>{lang}POSITION{/lang}</th>
		<th>{lang}STATUS{/lang}</th>
		<th></th>
	</tr></thead>
	<tfoot><tr>
		<td colspan="6">{if[count($this->loopStack["buddylist"]) > 0]}<input type="submit" name="delete" value="{lang}REMOVE{/lang}" class="button" />{else}{lang}NO_MATCHES_FOUND{/lang}{/if}</td>
	</tr></tfoot>
	<tbody>{foreach[buddylist]}<tr>
		<td>{loop}username{/loop}</td>
		<td>{loop}points{/loop}</td>
		<td>{loop}ally{/loop}</td>
		<td>{loop}position{/loop}</td>
		<td>
			{if[$row["f1"] == Core::getUser()->get("userid") && $row["accepted"] == 0]}{lang}WAITING{/lang}
			{else if[$row["accepted"] == 0]}<input type="button" onclick="javascript:setHiddenValue('relid', '{loop}relid{/loop}', 'accept');" value="{lang}ACCEPT{/lang}" class="button" />
			{else}{loop}status{/loop}{/if}</td>
		<td><input type="checkbox" name="remove[]" value="{loop}relid{/loop}" /></td>
	</tr>{/foreach}</tbody>
</table>
</form>