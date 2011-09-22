<table class="itable">
{foreach[planetSelection]}
	{if[$row["counter"] % 2 != 0]}<tr>{/if}
		<td>{loop}planetname{/loop}<br />{loop}planetimage{/loop}<br />{loop}activity{/loop}</td>
	{if[$row["total"] == $row["counter"] && $row["counter"] % 2 != 0]}<td></td>{/if}
	{if[$row["counter"] % 2 == 0 || $row["total"] == $row["counter"]]}</tr>{/if}
{/foreach}
</table>