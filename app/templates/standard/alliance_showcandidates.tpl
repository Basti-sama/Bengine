<form method="post" action="{@formaction}">
<input type="hidden" name="users[]" value="0" />
<table class="ntable">
	<tr>
		<th colspan="4">{@candidates}</th>
	</tr>
	<tr>
		<th>{lang}CANDIDATE{/lang}</th>
		<th>{lang}POINTS{/lang}</th>
		<th>{lang}POSITION{/lang}</th>
		<th></th>
	</tr>
	{foreach[applications]}<tr>
		<td class="center">{loop}username{/loop} {loop}message{/loop}</td>
		<td width="30%" class="center">{loop}points{/loop}</td>
		<td width="30%" class="center">{loop}position{/loop}</td>
		<td width="1%" class="center"><input type="checkbox" name="users[]" value="{loop}userid{/loop}" /></td>
	</tr>
	<tr>
		<td colspan="3"><h1 class="underline">{lang}APPLICATION{/lang}</h1>{loop}apptext{/loop}</td>
		<td class="center">{loop}date{/loop}</td>
	</tr>{/foreach}
	<tr>
		<td colspan="4" class="center">{if[count($this->getLoop("applications")) == 0]}{lang}NO_MATCHES_FOUND{/lang}{else}{lang}MARKED{/lang} <input type="submit" name="receipt" value="{lang}RECEIPT{/lang}" class="button" /> <input type="submit" name="refuse" value="{lang}REFUSE{/lang}" class="button" />{/if}</td>
	</tr>
</table>
</form>