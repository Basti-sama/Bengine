{include}"ranking_index"{/include}

<table class="ntable">
	<colgroup>
		<col width="30"/>
		<col width="180"/>
		<col width="70"/>
		<col width="80"/>
		<col width="82"/>
		<col width="60"/>
	</colgroup>
	<thead><tr>
		<th>#</th>
		<th>{lang}PLAYER{/lang}</th>
		<th></th>
		<th>{lang}ALLIANCE{/lang}</th>
		<th>{lang}POINTS{/lang}</th>
		<th>{lang}POSITION{/lang}</th>
	</tr></thead>
	<tfoot><tr>
		<td colspan="6">
			<p class="legend"><cite><span>i</span> = {lang}LOWER_INACTIVE{/lang}</cite><cite><span>I</span> = {lang}UPPER_INACTIVE{/lang}</cite><cite><span class="banned">b</span> = {lang}BANNED{/lang}</cite><cite><span class="vacation-mode">v</span> = {lang}VACATION_MODE{/lang}</cite></p>
			<p class="legend"><cite><span class="ownPosition">{lang=ONESELF}</span></cite><cite><span class="alliance">{lang=ALLIANCE}</span></cite><cite><span class="friend">{lang=FRIEND}</span></cite>{foreach[relationTypes]}<cite><span class="{loop=css}">{loop=name}</span></cite>{/foreach}</p>
		</td>
	</tr></tfoot>
	<tbody>{foreach[ranking]}<tr>
		<td>{loop}rank{/loop}</td>
		<td>{loop}username{/loop}{if[$row["user_status"] != ""]} ({loop}user_status{/loop}){/if}</td>
		<td class="center">{if[$row["userid"] != Core::getUser()->get("userid")]}{perm[CAN_MODERATE_USER]}{loop}moderator{/loop} {/perm}{loop}message{/loop} {loop}buddyrequest{/loop}{/if}</td>
		<td>{loop}alliance{/loop}</td>
		<td>{loop}points{/loop}</td>
		<td>{loop}position{/loop}</td>
	</tr>{/foreach}</tbody>
</table>