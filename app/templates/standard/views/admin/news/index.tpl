<h1>{lang}News{/lang}</h1>
<div class="draggable">
	<form method="post">
	{if[{var=languageCount} == 1]}
	<input type="hidden" name="language_id" value="{@languages}"/>
	{/if}
		<table class="ntable" cellpadding="4" cellspacing="0">
			<thead>
				<tr>
					<th colspan="2">{lang}Add_News{/lang}</th>
				</tr>
			</thead>
			<tr>
				<td><label for="f_title">{lang}Title{/lang}</label></td>
				<td><input type="text" name="title" id="f_title"/></td>
			</tr>
			<tr>
				<td><label for="f_text">{lang}Text{/lang}</label></td>
				<td><textarea cols="75" rows="15" name="text" id="f_text"></textarea></td>
			</tr>
			{if[{var=languageCount} > 1]}
			<tr>
				<td><label for="f_language">{lang}Language{/lang}</label></td>
				<td>
					<select name="language_id" id="f_language">
						{@languages}
					</select>
				</td>
			</tr>
			{/if}
			<tfoot>
				<tr>
					<td colspan="2"><input type="submit" name="add" value="{lang}Commit{/lang}" class="button"/></td>
				</tr>
			</tfoot>
		</table>
	</form>
</div>
<div class="draggable">
	<table class="ntable" cellpadding="4" cellspacing="0">
		<colgroup>
			<col/>
			<col/>
			<col/>
			<col/>
			<col/>
			<col width="50"/>
		</colgroup>
		<thead>
			<tr>
				<th colspan="6">{lang}News{/lang}</th>
			</tr>
		</thead>
		<tr>
			<td>#</td>
			<td>{lang}Title{/lang}</td>
			<td>{lang}Text{/lang}</td>
			<td>{lang}Time{/lang}</td>
			<td></td>
			<td></td>
		</tr>
		{foreach[news]}<tr>
			<td>{loop}news_id{/loop}</td>
			<td><b>{loop}title{/loop}</b></td>
			<td>{loop}text{/loop}</td>
			<td>{loop}time{/loop}</td>
			<td>[{loop}edit{/loop}] [{loop}delete{/loop}] [{if[$row["enabled"]]}{loop}disable{/loop}{else}{loop}enable{/loop}{/if}]</td>
			<td>{loop=down} {loop=up}</td>
		</tr>{/foreach}
	</table>
</div>