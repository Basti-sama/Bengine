<table class="ntable">
	<tr>
		<th>{@buildingName}</th>
	</tr>
	<tr>
		<td>{@buildingImage}{@buildingDesc}{perm[CAN_EDIT_CONSTRUCTIONS]}<div class="right">{@edit}</div>{/perm}</td>
	</tr>
	{if[{var}chartType{/var}]}<tr>
		<td>{include}{var}chartType{/var}{/include}</td>
	</tr>{/if}
</table>
{if[{var}demolish{/var}]}<table class="ntable center">
	<tr>
		<th>{lang}DEMOLISH{/lang} {@buildingName} {lang}LEVEL{/lang} {@buildingLevel}</th>
	</tr>
	<tr>
		<td>{lang}REQUIRES{/lang} {@metal} {@silicon} {@hydrogen}<br />{lang}PRODUCTION_TIME{/lang} {@dimolishTime}</td>
	</tr>
	<?php if($this->get("showLink")): ?>
	<tr>
		<td>{@demolishNow}</td>
	</tr>
	<?php endif; ?>
</table>{/if}