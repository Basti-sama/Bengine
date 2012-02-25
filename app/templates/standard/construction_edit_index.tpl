<form method="post" action="{@formaction}">
<input type="hidden" name="name_id" value="{@name_id}" />
<table class="ntable">
	<tr><th colspan="2">{lang}EDIT_CONSTRUCTION{/lang}</th></tr>
	<tr>
		<td><label for="construction_name">{lang}CONSTRUCTION_NAME{/lang}</label></td>
		<td><input type="text" name="name" value="{@name}" maxlength="{config}MAX_INPUT_LENGTH{/config}" id="construction_name" /></td>
	</tr>
	<tr>
		<td><label for="construction_desc">{lang}DESCRIPTION{/lang}</label></td>
		<td><textarea name="desc" rows="5" cols="50" id="construction_desc">{@description}</textarea></td>
	</tr>
	<tr>
		<td><label for="construction_long_desc">{lang}FULL_DESCRIPTION{/lang}</label></td>
		<td><textarea name="full_desc" rows="5" cols="50" id="construction_long_desc">{@full_description}</textarea></td>
	</tr>
	<tr>
		<td><label for="construction_basic_metal">{lang}BASIC_METAL{/lang}</label></td>
		<td><input type="text" name="basic_metal" value="{@basic_metal}" maxlength="{config}MAX_INPUT_LENGTH{/config}" id="construction_basic_metal" /></td>
	</tr>
	<tr>
		<td><label for="construction_basic_silicon">{lang}BASIC_SILICON{/lang}</label></td>
		<td><input type="text" name="basic_silicon" value="{@basic_silicon}" maxlength="{config}MAX_INPUT_LENGTH{/config}" id="construction_basic_silicon" /></td>
	</tr>
	<tr>
		<td><label for="construction_basic_hydrogen">{lang}BASIC_HYDROGEN{/lang}</label></td>
		<td><input type="text" name="basic_hydrogen" value="{@basic_hydrogen}" maxlength="{config}MAX_INPUT_LENGTH{/config}" id="construction_basic_hydrogen" /></td>
	</tr>
	<tr>
		<td><label for="construction_basic_energy">{lang}BASIC_ENERGY{/lang}</label></td>
		<td><input type="text" name="basic_energy" value="{@basic_energy}" maxlength="{config}MAX_INPUT_LENGTH{/config}" id="construction_basic_energy" /></td>
	</tr>
	<tr>
		<td><label for="construction_prod">{lang}PRODUCTION{/lang}</label></td>
		<td><select name="prod_what"><option value=""></option>{@prodWhat}</select>&nbsp;<input type="text" name="prod" value="{@prod}" maxlength="{config}MAX_INPUT_LENGTH{/config}" id="construction_prod" /></td>
	</tr>
	<tr>
		<td><label for="construction_consumption">{lang}CONSUMPTION{/lang}</label></td>
		<td><select name="cons_what"><option value=""></option>{@consWhat}</select>&nbsp;<input type="text" name="consumption" value="{@consumption}" maxlength="{config}MAX_INPUT_LENGTH{/config}" id="construction_consumption" /></td>
	</tr>
	<tr>
		<td><label for="construction_charge_metal">{lang}CHARGE_METAL{/lang}</label></td>
		<td><input type="text" name="charge_metal" value="{@charge_metal}" maxlength="{config}MAX_INPUT_LENGTH{/config}" id="construction_charge_metal" /></td>
	</tr>
	<tr>
		<td><label for="construction_charge_silicon">{lang}CHARGE_SILICON{/lang}</label></td>
		<td><input type="text" name="charge_silicon" value="{@charge_silicon}" maxlength="{config}MAX_INPUT_LENGTH{/config}" id="construction_charge_silicon" /></td>
	</tr>
	<tr>
		<td><label for="construction_charge_hydrogen">{lang}CHARGE_HYDROGEN{/lang}</label></td>
		<td><input type="text" name="charge_hydrogen" value="{@charge_hydrogen}" maxlength="{config}MAX_INPUT_LENGTH{/config}" id="construction_charge_hydrogen" /></td>
	</tr>
	<tr>
		<td><label for="construction_charge_energy">{lang}CHARGE_ENERGY{/lang}</label></td>
		<td><input type="text" name="charge_energy" value="{@charge_energy}" maxlength="{config}MAX_INPUT_LENGTH{/config}" id="construction_charge_energy" /></td>
	</tr>
	<tr>
		<td><label for="construction_special">{lang}SPECIAL{/lang}</label></td>
		<td><input type="text" name="special" value="{@special}" maxlength="{config}MAX_INPUT_LENGTH{/config}" id="construction_special" /></td>
	</tr>
	<tr>
		<td colspan="2" class="center"><input type="submit" name="saveconstruction" value="{lang}COMMIT{/lang}" class="button" /></td>
	</tr>
	<tr><th colspan="2">{lang}REQUIREMENTS{/lang}</th></tr>
	<tr>
		<td colspan="2">
			{foreach[requirements]}{loop}name{/loop}: {loop}level{/loop} {loop}delete{/loop}<br />{/foreach}
			<h4></h4>
			<form action="{@formaction}" method="post">
			<fieldset>
				<legend>{lang}ADD_REQUIREMENT{/lang}</legend>
				<ul>
					<li><label for="req-id">{lang}REQUIREMENT{/lang}</label><select name="needs" id="req-id">{foreach[constructions]}<option value="{loop}id{/loop}">{loop}name{/loop}</option>{/foreach}</select></li>
					<li><label for="req-level">{lang}LEVEL{/lang}</label><input type="text" value="1" name="level" id="req-level" maxlength="2" size="5" /></li>
					<li><label for="req-hidden">{lang}HIDE_IN_TECHTREE{/lang}</label><input type="checkbox" name="hidden" id="req-hidden" value="1"/></li>
					<li><input type="submit" name="addreq" value="{lang}COMMIT{/lang}" class="button" /></li>
				</ul>
			</fieldset>
		</td>
	</tr>
</table>
</form>