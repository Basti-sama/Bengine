<form method="post" action="{@formaction}">
<input type="hidden" name="name_id" value="{@name_id}" />
<input type="hidden" name="unitid" value="{@unitid}" />
<table class="ntable">
	<tr><th colspan="2">{lang}EDIT_UNIT{/lang}</th></tr>
	<tr>
		<td><label for="unit_name">{lang}CONSTRUCTION_NAME{/lang}</label></td>
		<td><input type="text" name="name" value="{@name}" maxlength="{config}MAX_INPUT_LENGTH{/config}" id="unit_name" /></td>
	</tr>
	<tr>
		<td><label for="unit_desc">{lang}DESCRIPTION{/lang}</label></td>
		<td><textarea name="desc" rows="5" cols="50" id="unit_desc">{@description}</textarea></td>
	</tr>
	<tr>
		<td><label for="unit_long_desc">{lang}FULL_DESCRIPTION{/lang}</label></td>
		<td><textarea name="full_desc" rows="5" cols="50" id="unit_long_desc">{@full_description}</textarea></td>
	</tr>
	<tr>
		<td><label for="unit_metal">{lang}BASIC_METAL{/lang}</label></td>
		<td><input type="text" name="basic_metal" value="{@basic_metal}" maxlength="{config}MAX_INPUT_LENGTH{/config}" id="unit_metal" onkeyup="javascript:setShell();" /></td>
	</tr>
	<tr>
		<td><label for="unit_silicon">{lang}BASIC_SILICON{/lang}</label></td>
		<td><input type="text" name="basic_silicon" value="{@basic_silicon}" maxlength="{config}MAX_INPUT_LENGTH{/config}" id="unit_silicon" onkeyup="javascript:setShell();" /></td>
	</tr>
	<tr>
		<td><label for="unit_hydrogen">{lang}BASIC_HYDROGEN{/lang}</label></td>
		<td><input type="text" name="basic_hydrogen" value="{@basic_hydrogen}" maxlength="{config}MAX_INPUT_LENGTH{/config}" id="unit_hydrogen" /></td>
	</tr>
	<tr>
		<td><label for="unit_energy">{lang}BASIC_ENERGY{/lang}</label></td>
		<td><input type="text" name="basic_energy" value="{@basic_energy}" maxlength="{config}MAX_INPUT_LENGTH{/config}" id="unit_energy" /></td>
	</tr>
	<tr>
		<td><label for="unit_capacity">{lang}CAPACITY{/lang}</label></td>
		<td><input type="text" name="capicity" value="{@capacity}" maxlength="{config}MAX_INPUT_LENGTH{/config}" id="unit_capacity" /></td>
	</tr>
	<tr>
		<td><label for="unit_consumption">{lang}CONSUMPTION{/lang}</label></td>
		<td><input type="text" name="consume" value="{@consume}" maxlength="{config}MAX_INPUT_LENGTH{/config}" id="unit_consumption" /></td>
	</tr>
	<tr>
		<td><label for="unit_speed">{lang}BASIC_SPEED{/lang}</label></td>
		<td><input type="text" name="speed" value="{@speed}" maxlength="{config}MAX_INPUT_LENGTH{/config}" id="unit_speed" /></td>
	</tr>
	<tr>
		<td><label for="unit_attack">{lang}ATTACK{/lang}</label></td>
		<td><input type="text" name="attack" value="{@attack}" maxlength="{config}MAX_INPUT_LENGTH{/config}" id="unit_attack" /></td>
	</tr>
	<tr>
		<td><label for="unit_shield">{lang}SHIELD{/lang}</label></td>
		<td><input type="text" name="shield" value="{@shield}" maxlength="{config}MAX_INPUT_LENGTH{/config}" id="unit_shield" /></td>
	</tr>
	<tr>
		<td>{lang}SHELL{/lang}</td>
		<td><span id="unit_shell">{@shell}</span></td>
	</tr>
	<tr>
		<td><label for="baisc_engine">{lang}BASIC_ENGINE{/lang}</label></td>
		<td><select name="baseEngine" id="baisc_engine"><option value="0"></option>{@baseEngine}</select></td>
	</tr>
	<tr>
		<td><label for="extented_engine">{lang}EXTENTED_ENGINE{/lang}</label></td>
		<td>
			<ul class="invi-list">
				<li><select name="extentedEngine" id="extented_engine"><option value="0"></option>{@extentedEngine}</select></li>
				<li><label for="ee_level">{lang}FROM_LEVEL{/lang}</label></li>
				<li><input type="text" name="extentedEngineLevel" value="{@extentedEngineLevel}" maxlength="2" id="ee_level" /></li>
				<li><label for="ee_speed">{lang}BASIC_SPEED{/lang}</label></li>
				<li><input type="text" name="extentedEngineSpeed" value="{@extentedEngineSpeed}" maxlength="{config}MAX_INPUT_LENGTH{/config}" id="ee_speed" /></li>
			</ul>
		</td>
	</tr>
	<tr>
		<td colspan="2" class="center"><input type="submit" name="saveunit" value="{lang}COMMIT{/lang}" class="button" /></td>
	</tr>
	<tr><th colspan="2">{lang}REQUIREMENTS{/lang}</th></tr>
	<tr>
		<td colspan="2">
			{foreach[requirements]}{loop}name{/loop}: {loop}level{/loop} {loop}delete{/loop}<br />{/foreach}
			<fieldset>
				<legend>{lang}ADD_REQUIREMENT{/lang}</legend>
				<ul>
					<li><label for="req-id">{lang}REQUIREMENT{/lang}</label><select name="needs" id="req-id">{foreach[constructions]}<option value="{loop}id{/loop}">{loop}name{/loop}</option>{/foreach}</select></li>
					<li><label for="req-level">{lang}LEVEL{/lang}</label><input type="text" value="1" name="level" id="req-level" maxlength="2" size="5" /></li>
					<li><input type="submit" name="addreq" value="{lang}COMMIT{/lang}" class="button" /></li>
				</ul>
			</fieldset>
		</td>
	</tr>
	<tr>
		<th colspan="2">{lang=RAPIDFIRE}</th>
	</tr>
	{foreach[rapidfire]}<tr>
		<td>
			<label for="rf_{loop=target}">{loop=name}</label>
		</td>
		<td>
			<input type="text" name="rf_{loop=target}" id="rf_{loop=target}" value="{loop=value}" maxlength="4" size="3" />&nbsp;
			<input type="checkbox" name="del_rf[]" id="del_rf_{loop=target}"  value="{loop=target}"/> <label for="del_rf_{loop=target}">{lang=DELETE}</label>
		</td>
	</tr>{/foreach}
	<tr>
		<td>
			<select name="rf_new" id="rf_new"><option value="0"></option>{@rfSelect}</select>
		</td>
		<td>
			<input name="rf_new_value" id="rf_new_value" value="0" maxlength="4" size="3"/>
		</td>
	</tr>
	<tr>
		<td colspan="2" class="center"><input type="submit" name="saveunit" value="{lang}COMMIT{/lang}" class="button" /></td>
	</tr>
</table>
</form>