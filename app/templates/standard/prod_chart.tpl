<table class="ntable" style="width: 100%;">
<thead><tr>
	<th>{lang}LEVEL{/lang}</th>
	<th>{lang}PRODUCTION{/lang}</th>
	<th>{lang}DIFFERENCE{/lang}</th>
</tr></thead>
<tbody>{foreach[chart]}<tr>
	<td><span class="{if[$row["level"] == {var}buildingLevel{/var}]}true{/if}">{loop}level{/loop}</span></td>
	<td>{loop}prod{/loop}</td>
	<td><span class="{if[$row["s_diffProd"] > 0]}true{else if[$row["s_diffProd"] < 0]}false{/if}">{loop}diffProd{/loop}</span></td>
</tr>{/foreach}</tbody>
</table>