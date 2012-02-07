<table class="ntable">
	<tr>
		<th colspan="2">{lang}FLEET_SENT{/lang}</th>
	</tr>
	<tr>
		<td>{lang}MISSION{/lang}</td>
		<td>{@mission}</td>
	</tr>
	<tr>
		<td>{lang}DISTANCE{/lang}</td>
		<td>{@distance}</td>
	</tr>
	<tr>
		<td>{lang}SPEED{/lang}</td>
		<td>{@speed}</td>
	</tr>
	<tr>
		<td>{lang}FUEL{/lang}</td>
		<td>{@consume}</td>
	</tr>
	<tr>
		<td>{lang}START{/lang}</td>
		<td>{@start}</td>
	</tr>
	<tr>
		<td>{lang}TARGET{/lang}</td>
		<td>{@target}</td>
	</tr>
	<tr>
		<td>{lang}ARRIVAL{/lang}</td>
		<td>{@arrival}</td>
	</tr>
	{if[$this->templateVars["mode"] != 8 && $this->templateVars["mode"] != 6]}<tr>
		<td>{lang}RETURN{/lang}</td>
		<td>{@return}</td>
	</tr>{/if}
	<tr>
		<th colspan="2">{lang}FLEET{/lang}</th>
	</tr>
	{foreach[fleet]}<tr>
		<td>{loop}name{/loop}</td>
		<td>{loop}quantity{/loop}</td>
	</tr>{/foreach}
</table>