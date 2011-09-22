<h1>{lang}CMS_Manager{/lang}</h1>
<div class="draggable">
	<form method="post" action="{@formaction}">
		<input type="hidden" name="delete[]" value="0" />
		<table class="ntable" cellpadding="4" cellspacing="0">
			<thead>
				<tr>
					<th colspan="5">{lang}Available_Pages{/lang}</th>
				</tr>
			</thead>
			<tr>
				<td>{lang}Displayorder{/lang}</td>
				<td>{lang}Title{/lang}</td>
				<td>{lang}Position{/lang}</td>
				<td></td>
				<td>{lang}Delete{/lang}</td>
			</tr>
			{foreach[pages]}<tr>
				<td><input type="hidden" name="pages[]" value="{loop}pageid{/loop}" /><input type="text" name="displayorder_{loop}pageid{/loop}" value="{loop}displayorder{/loop}" /></td>
				<td><input type="text" name="title_{loop}pageid{/loop}" value="{loop}title{/loop}" /></td>
				<td><select name="position_{loop}pageid{/loop}" size="1">
					{var=position}
					<option value="h" {if[$row["position"] == "h"]} selected="selected" {/if}>{lang}header{/lang}</option>
					</select></td>
				<td>[{loop}edit_link{/loop}]</td>
				<td><input type="checkbox" name="delete[]" value="{loop}pageid{/loop}" />
			</tr>{/foreach}
			<tfoot>
				<tr>
					<td colspan="5"><input type="submit" name="update_pages" value="{lang}Commit{/lang}" class="button" /></td>
				</tr>
			</tfoot>
		</table>
	</form>
</div>
<div id="right">
	<div class="link_b">
		{link[New_Page]}"cms/newPage"{/link}
	</div>
</div>