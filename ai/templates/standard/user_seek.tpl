<h1>{lang}User_Manager{/lang}</h1>
<div class="draggable">
	<form method="post">
		<table class="ntable" cellpadding="4" cellspacing="0">
			<thead>
				<tr>
					<th colspan="2">{lang}User_Search{/lang}</th>
				</tr>
			</thead>
			<tr>
				<td><label for="s_username">{lang}Username{/lang}</label></td>
				<td><input type="text" name="username" maxlength="128" id="s_username" value="{request[post]}username{/request}" /></td>
			</tr>
			<tr>
				<td><label for="s_email">{lang}E_Mail{/lang}</label></td>
				<td><input type="text" name="email" maxlength="128" id="s_email" value="{request[post]}email{/request}" /></td>
			</tr>
			<tfoot>
				<tr>
					<td colspan="2"><input type="submit" name="search_user" value="{lang}Go{/lang}" class="button" /></td>
				</tr>
			</tfoot>
		</table>
	</form>
</div>
<div class="draggable">
	{if[$this->templateVars["searched"]]}<form method="post">
		<table class="ntable" cellpadding="4" cellspacing="0">
			<thead>
				<tr>
					<th colspan="4">{lang}User_Search_Result{/lang}</th>
				</tr>
			</thead>
			<tr>
				<td class="tdbold">#</td>
				<td class="tdbold">{lang}Username{/lang}</td>
				<td class="tdbold">{lang}E_Mail{/lang}</td>
				<td class="tdbold">{lang}Delete{/lang}</td>
			</tr>
			{foreach[searchresult]}<tr>
				<td>{loop}userid{/loop}</td>
				<td>[{loop}edit{/loop}] {loop}username{/loop}</td>
				<td>{loop}email{/loop}</td>
				<td><input type="checkbox" name="delete[]" value="{loop}userid{/loop}" /></td>
			</tr>{/foreach}
			<tfoot>
				<tr>
					<td colspan="4">{if[count($this->loopStack["searchresult"]) > 0]}<input type="submit" name="delete_user" value="{lang}Commit{/lang}" class="button" />{else}{lang}No_Matches_Found{/lang}{/if}</td>
				</tr>
			</tfoot>
		</table>
	</form>{/if}
</div>
<div id="right"><div class="link_b">{link[Back]}"user"{/link}</div></div>