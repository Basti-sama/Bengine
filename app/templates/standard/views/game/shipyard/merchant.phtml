<form action="{const=BASE_URL}game/{const=SID}/Shipyard/Change" method="post">
	<table class="ntable">
		<thead>
			<tr>
				<th colspan="4">{lang=SCRAP_MERCHANT}</th>
			</tr>
			<tr>
				<td colspan="4">{lang=SCRAP_MERCHANT_DESCRIPTION}</td>
			</tr>
			<tr>
				<th>{lang=SHIPYARD}</th>
				<th>{lang=QUANTITY}</th>
				<th></th>
				<th>{lang=PRICE}</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($this->get("units") as $unit): ?>
			<tr>
				<td>
					<?php echo $unit->getLinkName() ?>
				</td>
				<td class="right">
					<?php echo $unit->getFormattedQty() ?>
					(<a href="#" onclick="merchantSetValue(<?php echo $unit->getUnitid() ?>, <?php echo $unit->getQty() ?>, <?php echo $unit->get("basic_metal") ?>, <?php echo $unit->get("basic_silicon") ?>, <?php echo $unit->get("basic_hydrogen") ?>);return false;">max</a> | <a href="#" onclick="merchantSetValue(<?php echo $unit->getUnitid() ?>, 0, <?php echo $unit->get("basic_metal") ?>, <?php echo $unit->get("basic_silicon") ?>, <?php echo $unit->get("basic_hydrogen") ?>);return false;">min</a>)
				</td>
				<td class="center">
					<input type="text" name="unit[<?php echo $unit->getUnitid() ?>]" value="0" class="center" size="3" maxlength="4" id="ship-<?php echo $unit->getUnitid() ?>" onkeyup="assembleRes(this.value, <?php echo $unit->getUnitid() ?>, <?php echo $unit->get("basic_metal") ?>, <?php echo $unit->get("basic_silicon") ?>, <?php echo $unit->get("basic_hydrogen") ?>)"/>
				</td>
				<td>
					{lang=METAL}: <span class="true" id="metal-<?php echo $unit->getUnitid() ?>">0</span><br/>
					{lang=SILICON}: <span class="true" id="silicon-<?php echo $unit->getUnitid() ?>">0</span><br/>
					{lang=HYDROGEN}: <span class="true" id="hydrogen-<?php echo $unit->getUnitid() ?>">0</span>
				</td>
			</tr>
			<?php endforeach ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="4">
					<?php if(count($this->get("units"))): ?>
					<input type="submit" name="sell" value="{lang=SELL}" class="button"/>
					<?php else: ?>
					{lang=NO_MATCHES_FOUND}
					<?php endif ?>
				</td>
			</tr>
		</tfoot>
	</table>
</form>

<script type="text/javascript">
//<![CDATA[
var rate = {config=SCRAP_MERCHANT_RATE};
function assembleRes(qty, id, metal, silicon, hydrogen)
{
	qty = Math.abs(parseInt(qty));
	metal *= qty*rate;
	silicon *= qty*rate;
	hydrogen *= qty*rate;
	$("#metal-"+id).text(number_format(metal, 0, "{lang=DECIMAL_POINT}", "{lang=THOUSANDS_SEPERATOR}"));
	$("#silicon-"+id).text(number_format(silicon, 0, "{lang=DECIMAL_POINT}", "{lang=THOUSANDS_SEPERATOR}"));
	$("#hydrogen-"+id).text(number_format(hydrogen, 0, "{lang=DECIMAL_POINT}", "{lang=THOUSANDS_SEPERATOR}"));
}

function merchantSetValue(id, value, metal, silicon, hydrogen)
{
	$("#ship-"+id).val(value);
	assembleRes(value, id, metal, silicon, hydrogen);
}
//]]>
</script>