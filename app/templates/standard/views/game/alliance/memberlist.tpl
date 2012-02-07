<form method="post" action="{@formaction}">
<table class="ntable">
	<thead><tr>
		<th colspan="{@colspan}">{lang}MEMBER_LIST{/lang} ({lang}TOTAL_MEMBERS{/lang}: {@totalmembers} / {@totalpoints})</th>
	</tr>
	<tr>
		<td>{lang}USERNAME{/lang}</td>
		<td></td>
		<td>{lang}RANK{/lang}</td>
		<td>{lang}POINTS{/lang}</td>
		<td>{lang}POSITION{/lang}</td>
		<td>{lang}JOIN_DATE{/lang}</td>
		{if[$this->get("can_see_onlie_state")]}<td></td>{/if}
		{if[$this->get("can_ban_member")]}<td></td>{/if}
	</tr></thead>
	{if[$this->get("can_manage")]}<tfoot><tr>
		<td colspan="{@colspan}" class="center"><input type="submit" name="changeMembers" value="{lang}PROCEED{/lang}" class="button" /></td>
	</tr></tfoot>{/if}
	<tbody>{foreach[members]}<tr>
		<td>{loop}username{/loop}</td>
		<td>{loop}message{/loop}</td>
		<td>{if[$this->get("founder") == $row["userid"] || !$this->get("can_manage")]}{loop}rank{/loop}{else}<select name="rank_{loop}userid{/loop}"><option value="0">{lang}NEWBIE{/lang}</option>{loop}rankselection{/loop}</select>{/if}</td>
		<td>{loop}points{/loop}</td>
		<td>{loop}position{/loop}</td>
		<td>{loop}joindate{/loop}</td>
		{if[$this->get("can_see_onlie_state")]}<td>{loop}online{/loop}</td>{/if}
		{if[$this->get("can_ban_member")]}<td>{if[$this->get("founder") != $row["userid"]]}<input type="image" src="{@themePath}img/error2.gif" name="kick_{loop}userid{/loop}" title="{lang}REMOVE{/lang}" alt="{lang}REMOVE{/lang}" />{/if}</td>{/if}
	</tr>{/foreach}</tbody>
</table>
</form>