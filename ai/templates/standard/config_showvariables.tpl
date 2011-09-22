<h1>{lang}Configuration{/lang}</h1>
<div class="draggable">
<form method="post" action="{@faction}">
<table class="ntable" cellpadding="4" cellspacing="0">
	<thead>
		<tr>
			<th>{lang}Manage_Config_Vars{/lang}</th>
		</tr>
	</thead>
	{foreach[vars]}<tr>
		<td><label for="config-{loop}var{/loop}"><b>{loop}var{/loop}</b></label>
		<div>{loop}description{/loop}</div>
		<div class="config-input-field">{loop}input{/loop}</div>
		<div class="closediv">{loop}delete{/loop} {loop}edit{/loop}</div></td>
	</tr>{/foreach}
	<tr>
		<td style="text-align: center;">
			<input type="submit" name="save_vars" value="{lang}Commit{/lang}" class="button" />
		</td>
	</tr>
</table>
</form>
</div>
<div id="right"><div class="link_b">{link[Back]}"config"{/link}</div></div>