<form method="post" action="{@formaction}">
<table class="ntable">
	<thead><tr>
		<th colspan="8">{lang}SET_RANK_RIGHTS{/lang}</th>
	</tr>
	<tr>
		<td>{lang}RANK_NAME{/lang}</td>
		<td class="center">{image[CAN_MANAGE]}CAN_MANAGE.png{/image}</td>
		<td class="center">{image[CAN_SEE_APPLICATIONS]}CAN_SEE_APPLICATIONS.png{/image}</td>
		<td class="center">{image[CAN_SEE_MEMBERLIST]}CAN_SEE_MEMBERLIST.png{/image}</td>
		<td class="center">{image[CAN_SEE_ONLINE_STATE]}CAN_SEE_ONLINE_STATE.png{/image}</td>
		<td class="center">{image[CAN_BAN_MEMBER]}CAN_BAN_MEMBER.png{/image}</td>
		<td class="center">{image[CAN_WRITE_GLOBAL_MAILS]}CAN_WRITE_GLOBAL_MAILS.png{/image}</td>
		<td></td>
	</tr></thead>
	{if[$this->templateVars["num"] > 0]}
	<tfoot><tr>
		<td class="center" colspan="8"><input type="submit" name="changerights" value="{lang}PROCEED{/lang}" class="button" /></td>
	</tr></tfoot>
	<tbody>{while[ranks]}<tr>
		<td>{loop}name{/loop}</td>
		<td class="center"><input type="checkbox" name="CAN_MANAGE_{loop}rankid{/loop}" value="1"{if[$row["CAN_MANAGE"]]} checked="checked"{/if} /></td>
		<td class="center"><input type="checkbox" name="CAN_SEE_APPLICATIONS_{loop}rankid{/loop}" value="1"{if[$row["CAN_SEE_APPLICATIONS"]]} checked="checked"{/if} /></td>
		<td class="center"><input type="checkbox" name="CAN_SEE_MEMBERLIST_{loop}rankid{/loop}" value="1"{if[$row["CAN_SEE_MEMBERLIST"]]} checked="checked"{/if} /></td>
		<td class="center"><input type="checkbox" name="CAN_SEE_ONLINE_STATE_{loop}rankid{/loop}" value="1"{if[$row["CAN_SEE_ONLINE_STATE"]]} checked="checked"{/if} /></td>
		<td class="center"><input type="checkbox" name="CAN_BAN_MEMBER_{loop}rankid{/loop}" value="1"{if[$row["CAN_BAN_MEMBER"]]} checked="checked"{/if} /></td>
		<td class="center"><input type="checkbox" name="CAN_WRITE_GLOBAL_MAILS_{loop}rankid{/loop}" value="1"{if[$row["CAN_WRITE_GLOBAL_MAILS"]]} checked="checked"{/if} /></td>
		<td class="center"><input type="image" src="{@themePath}img/error2.gif" name="delete_{loop}rankid{/loop}" title="{lang}REMOVE{/lang}" alt="{lang}REMOVE{/lang}" /></td>
	</tr>{/while}</tbody>
	{else}<tr>
		<td class="center" colspan="8">{lang}NO_MATCHES_FOUND{/lang}</td>
	</tr>{/if}
</table>
</form>
<form method="post" action="{@formaction}">
<table class="ntable">
	<tr>
		<th>{lang}CREATE_NEW_RANK{/lang}</th>
	</tr>
	<tr>
		<td><label for="name">{lang}RANK_NAME{/lang}:</label> <input type="text" name="name" id="name" maxlength="30" /> <input type="submit" name="createrank" value="{lang}PROCEED{/lang}" class="button" /></td>
	</tr>
</table>
</form>
<table class="ntable">
	<tr>
		<th colspan="2">{lang}CREATE_NEW_RANK{/lang}</th>
	</tr>
	<tr>
		<td class="center">{image[CAN_MANAGE]}CAN_MANAGE.png{/image}</td>
		<td>{lang}CAN_MANAGE{/lang}</td>
	</tr>
	<tr>
		<td class="center">{image[CAN_SEE_APPLICATIONS]}CAN_SEE_APPLICATIONS.png{/image}</td>
		<td>{lang}CAN_SEE_APPLICATIONS{/lang}</td>
	</tr>
	<tr>
		<td class="center">{image[CAN_SEE_MEMBERLIST]}CAN_SEE_MEMBERLIST.png{/image}</td>
		<td>{lang}CAN_SEE_MEMBERLIST{/lang}</td>
	</tr>
	<tr>
		<td class="center">{image[CAN_SEE_ONLINE_STATE]}CAN_SEE_ONLINE_STATE.png{/image}</td>
		<td>{lang}CAN_SEE_ONLINE_STATE{/lang}</td>
	</tr>
	<tr>
		<td class="center">{image[CAN_BAN_MEMBER]}CAN_BAN_MEMBER.png{/image}</td>
		<td>{lang}CAN_BAN_MEMBER{/lang}</td>
	</tr>
	<tr>
		<td class="center">{image[CAN_WRITE_GLOBAL_MAILS]}CAN_WRITE_GLOBAL_MAILS.png{/image}</td>
		<td>{lang}CAN_WRITE_GLOBAL_MAILS{/lang}</td>
	</tr>
</table>
