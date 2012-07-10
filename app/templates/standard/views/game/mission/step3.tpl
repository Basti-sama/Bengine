<script type="text/javascript">
//<![CDATA[
var outMetal = {@metal};
var outSilicon = {@silicon};
var outHydrogen = {@hydrogen};
var sCapacity = {@capacity};
var tMetal = {@metal};
var tSilicon = {@silicon};
var tHydrogen = {@hydrogen};
var capacity = {@capacity};
var decPoint = '{lang}DECIMAL_POINT{/lang}';
var thousandsSep = '{lang}THOUSANDS_SEPERATOR{/lang}';
//]]>
</script>
<form method="post" action="{@formaction}" class="form-sec">
<table class="ntable">
	<tr>
		<th colspan="4">{@targetName}</th>
	</tr>
	<tr>
		<th>{lang}MISSION{/lang}</th>
		<th colspan="3">{lang}RESOURCES{/lang}</th>
	</tr>
	<tr>
		<td rowspan="5">
			<?php if(count($this->getLoop("missions"))): ?>
			<?php foreach($this->getLoop("missions") as $key => $row): ?>
				<?php if($key == 19): ?>
				<?php foreach($row as $mission): ?>
					<input type="radio" name="mode" id="mode_<?php echo $mission["mode"]; ?>" value="<?php echo $mission["mode"]; ?>" /> <label for="mode_<?php echo $mission["mode"]; ?>"><?php echo $mission["mission"]; ?></label><br />
				<?php endforeach; ?>
				<?php else: ?>
				<input type="radio" name="mode" id="mode_{loop}mode{/loop}" value="{loop}mode{/loop}"<?php if($row["selected"] || count($this->getLoop("missions"))==1): ?> checked="checked"<?php endif ?>/> <label for="mode_{loop}mode{/loop}">{loop}mission{/loop}</label><br />
				<?php endif ?>
			<?php endforeach ?>
			<?php else: ?>
			{lang}NO_MISSIONS_AVAILABLE{/lang}
			<?php endif ?>
		</td>
		<td>{lang}METAL{/lang}</td>
		<td class="center"><a href="javascript:void();" onclick="javascript:setMaxRes('metal', tMetal);">{lang}MAX{/lang}</a> | <a href="#" onclick="javascript:setMinRes('metal');">{lang}MIN{/lang}</a></td>
		<td><input type="text" value="{@preMetal}" name="metal" size="8" maxlength="10" id="metal" onkeyup="javascript:renewTransportRes();" /></td>
	</tr>
	<tr>
		<td>{lang}SILICON{/lang}</td>
		<td class="center"><a href="javascript:void();" onclick="javascript:setMaxRes('silicon', tSilicon);">{lang}MAX{/lang}</a> | <a href="#" onclick="javascript:setMinRes('silicon');">{lang}MIN{/lang}</a></td>
		<td><input type="text" value="{@preSilicon}" name="silicon" size="8" maxlength="10" id="silicon" onkeyup="javascript:renewTransportRes();" /></td>
	</tr>
	<tr>
		<td>{lang}HYDROGEN{/lang}</td>
		<td class="center"><a href="javascript:void();" onclick="javascript:setMaxRes('hydrogen', tHydrogen);">{lang}MAX{/lang}</a> | <a href="#" onclick="javascript:setMinRes('hydrogen');">{lang}MIN{/lang}</a></td>
		<td><input type="text" value="{@preHydrogen}" name="hydrogen" size="8" maxlength="10" id="hydrogen" onkeyup="javascript:renewTransportRes();" /></td>
	</tr>
	<tr>
		<td>{lang}CAPICITY{/lang}</td>
		<td colspan="2" class="center"><span id="rest" class="available">{@rest}</span></td>
	</tr>
	<tr>
		<td colspan="3" class="center"><a href="javascript:void();" onclick="javascript:setAllResources();">{lang}ALL_RESOURCES{/lang}</a> | <a href="#" onclick="javascript:setNoResources();">{lang}NO_RESOURCES{/lang}</a></td>
	</tr>
	{if[{var=showHoldingTime}]}
	<tr>
		<th colspan="4">{lang=HOLDING_TIME}</th>
	</tr>
	<tr>
		<td colspan="4" class="center">
			<input type="text" name="holdingtime" id="holdingtime" value="1" size="2" maxlength="2" onblur="checkNumberInput(this, 0, 24);" class="center" /> <label for="holdingtime">{lang=HOURS}</label>
		</td>
	</tr>{/if}
	<tr>
		<td colspan="4" class="center">{if[count($this->loopStack["missions"]) > 0]}<input type="submit" name="step4" value="{lang}NEXT{/lang}" class="button" />{/if}</td>
	</tr>
</table>
</form>
<script type="text/javascript">
//<![CDATA[
renewTransportRes();
//]]>
</script>