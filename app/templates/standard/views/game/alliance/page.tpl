<form method="post" action="{@formaction}">
<table class="ntable">
	<tr>
		<th colspan="2">{lang}ALLIANCE{/lang}</th>
	</tr>
	{if[{var=logo} != ""]}<tr>
		<td colspan="2" class="center">{@logo}</td>
	</tr>{/if}
	<tr>
		<td style="width: 30%;">{lang}TAG{/lang}</td>
		<td>{@tag}</td>
	</tr>
	<tr>
		<td>{lang}NAME{/lang}</td>
		<td>{@name}</td>
	</tr>
	<tr>
		<td>{lang}MEMBER{/lang}</td>
		<td>{@memberNumber} {if[{var=showMember} || {var=CAN_SEE_MEMBERLIST} || {var=founder} == Core::getUser()->get("userid")]}({@memberList}){/if}</td>
	</tr>
	{if[({var=CAN_SEE_APPLICATIONS} || {var=founder} == Core::getUser()->get("userid")) && {var=appnumber} > 0]}<tr>
		<td>{lang}APPLICATIONS{/lang}</td>
		<td>{@applications}</td>
	</tr>{/if}
	{if[{var=aid} == Core::getUser()->get("aid")]}<tr>
		<td>{lang}RANK{/lang}</td>
		<td>{@rank} {@manage}</td>
	</tr>{/if}
	{if[{var=CAN_WRITE_GLOBAL_MAILS} || {var=founder} == Core::getUser()->get("userid")]}<tr>
		<td colspan="2" class="center">{link[WRITE_GLOBAL_MAIL]}"game/".SID."/Alliance/GlobalMail"{/link}</td>
	</tr>{/if}
	<tr>
		<td colspan="2">{@textextern}</td>
	</tr>
	{if[{var=homepage} != "" && ({var=showHomepage} || {var=aid} == Core::getUser()->get("aid"))]}<tr>
		<td>{lang}HOMEPAGE{/lang}</td>
		<td>{@homepage}</td>
	</tr>{/if}
	{if[{var=textintern} != "" && {var=aid} == Core::getUser()->get("aid")]}<tr>
		<th colspan="2">{lang}INTERN{/lang}</th>
	</tr>
	<tr>
		<td colspan="2">{@textintern}</td>
	</tr>{/if}
	{if[{var=aid} == Core::getUser()->get("aid") && {var=founder} != Core::getUser()->get("userid")]}<tr>
		<td colspan="2" class="center"><input name="leave" type="submit" value="{lang}LEAVE{/lang}" class="button"/></td>
	</tr>{/if}
	{if[!Core::getUser()->get("aid") && !{var=appInProgress}]}<tr>
		<td colspan="2" class="center"><input name="enter" type="button" value="{lang}JOIN{/lang}" class="button" onclick="javascript:location.href='{const=BASE_URL}game/{const=SID}/Alliance/Apply/{@aid}'"/></td>
	</tr>{/if}
	{if[{var=appInProgress}]}<tr>
		<td colspan="2" class="center">{lang}APPLICATION_IN_PROGRESS{/lang}</td>
	</tr>{/if}
</table>
</form>