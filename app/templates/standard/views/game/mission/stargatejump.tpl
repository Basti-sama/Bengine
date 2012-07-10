<form method="post" action="{@formaction}" class="form-sec">
<table class="ntable">
	<thead><tr>
		<th colspan="2">{lang}STAR_GATE_JUMP{/lang}</th>
	</tr></thead>
	{if[count($this->loopStack["moons"]) > 0]}<tfoot><tr>
		<td colspan="2"><input type="submit" name="execjump" value="{lang}EXECUTE_JUMP{/lang}" class="button" /></td>
	</tr></tfoot>{/if}
	<tbody><tr>
		<td><label for="moonid">{lang}SELECT_TARGET_MOON{/lang}</label></td>
		<td>
			<select name="moonid" id="moonid">{foreach[moons]}<option value="{loop}planetid{/loop}">{loop}planetname{/loop} [{loop}galaxy{/loop}:{loop}system{/loop}:{loop}position{/loop}]</option>{/foreach}</select>
		</td>
	</tr></tbody>
</table>
</form>