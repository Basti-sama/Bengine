{include}"game/search/index"{/include}
<table class="ntable">
	<thead><tr>
		<th>{lang}USERNAME{/lang}</th>
		<th></th>
		<th>{lang}POINTS{/lang}</th>
		<th>{lang}ALLIANCE{/lang}</th>
		<th>{lang}PLANET{/lang}</th>
		<th>{lang}POSITION{/lang}</th>
	</tr></thead>
	<tbody>{foreach[result]}<tr>
		<td>{loop}username{/loop}{if[$row["user_status_long"] != ""]} ({loop}user_status_long{/loop}){/if}</td>
		<td class="center">{if[$row["userid"] != Core::getUser()->get("userid")]}{perm[CAN_MODERATE_USER]}{loop}moderator{/loop} {/perm}{loop}message{/loop} {loop}buddyrequest{/loop}{/if}</td>
		<td>{loop}points{/loop}</td>
		<td>{loop}alliance{/loop}</td>
		<td>{loop}planetname{/loop}</td>
		<td>{loop}position{/loop}</td>
	</tr>{/foreach}
	{if[$count <= 0]}<tr>
		<td colspan="6" class="center">{lang}NO_MATCHES_FOUND{/lang}</td>
	</tr>{/if}</tbody>
</table>