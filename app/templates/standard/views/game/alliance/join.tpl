<form method="post" action="{@formaction}">
<table class="ntable">
	<tr>
		<th>{lang}ALLIANCE_SEARCH{/lang}</th>
	</tr>
	<tr>
		<td><input type="text" name="searchitem" maxlength="30" value="{@searchitem}" /> <input type="submit" name="search" value="{lang}COMMIT{/lang}" class="button" /></td>
	</tr>
</table>
</form>
{if[count($this->getLoop("results")) > 0]}<table class="ntable">
	<tr>
		<th colspan="5">{lang}SEARCH_RESULTS{/lang}</th>
	</tr>
	<tr>
		<td>{lang}TAG{/lang}</td>
		<td>{lang}NAME{/lang}</td>
		<td>{lang}MEMBERS{/lang}</td>
		<td>{lang}POINTS{/lang}</td>
		<td>{lang}JOIN{/lang}</td>
	</tr>
	{foreach[results]}<tr>
		<td>{loop}tag{/loop}</td>
		<td>{loop}name{/loop}</td>
		<td>{loop}members{/loop}</td>
		<td>{loop}points{/loop}</td>
		<td>{loop}join{/loop}</td>
	</tr>{/foreach}
</table>{/if}