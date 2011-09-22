<table class="ntable">
	<thead><tr>
		<th colspan="5">{lang}ALLIANCE_DIPLOMACY_APPLICATIONS{/lang}</th>
	</tr>
	<tr>
		<th>{lang}ALLIANCE{/lang}</th>
		<th>{lang}APPLICATION_TIME{/lang}</th>
		<th>{lang}STATUS{/lang}</th>
		<th>{lang}APPLICATION_CONTENT{/lang}</th>
		<th>{lang}OPERATIONS{/lang}</th>
	</tr></thead>
	{if[count($this->loopStack["apps"]) <= 0]}<tfoot><tr>
		<td colspan="5">{lang}NO_MATCHES_FOUND{/lang}</td>
	</tr></tfoot>{/if}
	<tbody>{foreach[apps]}<tr>
		<td>{loop}ally{/loop}</td>
		<td>{loop}time{/loop}</td>
		<td>{loop}status{/loop}</td>
		<td>{loop}application{/loop}</td>
		<td>{loop}accept{/loop} {loop}refuse{/loop}</td>
	</tr>{/foreach}</tbody>
</table>
<div class="idiv">{link[BACK]}"game.php/".SID."/Alliance/Diplomacy"{/link}</div>