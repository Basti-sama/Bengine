<script type="text/javascript">
//<![CDATA[
$(function() {
	$("input[name=step2], input[name=stargatejump]").click(function() {
		var ships = 0;
		$(".ships").each(function() {
			ships += parseInt($(this).val());
		});
		if(ships <= 0)
		{
			$(".dyn-message").remove();
			$("#content").prepend('<div class="error dyn-message" style="display:none;">{lang=NO_SHIPS_SELECTED}</div>');
			$(".dyn-message").fadeIn("slow");
			return false;
		}
	});
});
var fleet = new Array();
var quantities = new Array();
var n = 0;
<?php foreach($this->getLoop("fleet") as $unit): ?>
<?php if($unit->getSpeed() > 0): ?>
fleet[n] = <?php echo $unit->getId() ?>;
n++;
quantities[<?php echo $unit->getId() ?>] = <?php echo $unit->getQty() ?>;
<?php endif; ?>
<?php endforeach; ?>
//]]>
</script>

<?php if(count($this->getLoop("missions")) > 0): ?>
<table class="ntable">
	<thead>
		<tr>
			<th colspan="7">{lang}RUNNING_MISSIONS{/lang}</th>
		</tr>
		<tr>
			<th>{lang}MISSION{/lang}</th>
			<th>{lang}QUANTITY{/lang}</th>
			<th>{lang}START{/lang}</th>
			<th>{lang}ARRIVAL{/lang}</th>
			<th>{lang}TARGET{/lang}</th>
			<th>{lang}RETURN{/lang}</th>
			<th>{lang}ORDER{/lang}</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->getLoop("missions") as $event): ?>
		<tr>
			<td><strong><?php echo $event->getModeName() ?></strong><?php if($event->getOrgMode()): ?><br/><?php echo $event->getOrgModeName(); ?><?php endif; ?></td>
			<td><strong><?php echo $event->getFormattedFleetQuantity() ?></strong> (<?php echo $event->getFleetString() ?>)</td>
			<td><?php echo $event->getPlanetCoords() ?></td>
			<td><?php echo $event->getFormattedTime() ?></td>
			<td><?php echo $event->getDestinationCoords() ?></td>
			<td><?php echo $event->getReturnTime() ?></td>
			<td>
				<?php if($event->getCode() != "return" && $event->getCode() != "missileAttack"): ?>
				<form method="post" action="{@formaction}" class="form-sec">
					<input type="hidden" name="retreat" value="1"/>
					<input type="hidden" name="id" value="<?php echo $event->getEventid() ?>"/>
					<input type="submit" value="{lang}RETREAT{/lang}" class="button"/>
				</form>
				<?php if($event->getMode() == 10 || $event->getMode() == 12): ?>
				<form method="post" action="{@formaction}" class="form-sec">
					<input type="hidden" name="formation" value="1"/>
					<input type="hidden" name="id" value="<?php echo $event->getEventid() ?>"/>
					<input type="submit" value="{lang}FORMATION{/lang}" class="button"/>
				</form>
				<?php endif; ?>
				<?php endif; ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
<br /><br />
<?php endif; ?>
<form method="post" action="{@formaction}" class="form-sec">
<div style="display: none;">
	<input type="hidden" name="galaxy" value="{request[get]}1{/request}"/>
	<input type="hidden" name="system" value="{request[get]}2{/request}"/>
	<input type="hidden" name="position" value="{request[get]}3{/request}"/>
	<input type="hidden" name="targetType" value="{request[get]}4{/request}"/>
	<input type="hidden" name="code" value="{request[get]}5{/request}"/>
</div>
<table class="ntable">
	<thead>
		<tr>
			<th colspan="5">{lang}NEW_MISSION{/lang}</th>
		</tr>
		<tr>
			<th>{lang}SHIP_NAME{/lang}</th>
			<th>{lang}SPEED_CAPICITY{/lang}</th>
			<th>{lang}WAITING{/lang}</th>
			<th></th>
			<th>{lang}SELECTION{/lang}</th>
		</tr>
	</thead>
	<?php if(count($this->getLoop("fleet")) > 0): ?>
	<tfoot>
		<tr>
			<td colspan="2" class="center"><a href="javascript:void(0);" onclick="selectShips();">{lang}ALL_SHIPS{/lang}</a></td>
			<td colspan="3" class="center"><a href="javascript:void(0);" onclick="deselectShips();">{lang}NO_SHIPS{/lang}</a></td>
		</tr>
		<tr>
			<td colspan="5" class="center">
				{if[{var}canSendFleet{/var}]}
				<input type="hidden" name="step2" value="1"/>
				<input type="submit" value="{lang}NEXT{/lang}" class="button"/>
				{if[Bengine::getPlanet()->getBuilding("STAR_GATE") > 0]}
				<input type="hidden" name="stargatejump" value="1"/>
				<input type="submit" value="{lang}STAR_GATE_JUMP{/lang}" class="button"/>
				{/if}
				{else}
				{lang}NO_FREE_FLEET_SLOTS{/lang}
				{/if}
				<div class="right">
					{lang=FLEET_SLOTS} <?php echo count($this->getLoop("missions")) ?> / <?php echo Bengine::getResearch(14) + 1 ?>
					<?php if(Core::getOptions()->get("ATTACKING_STOPPAGE")): ?>
					<b class="false">{lang=ATTACKING_STOPPAGE_ENABLED}</b>
					<?php endif ?>
				</div>
			</td>
		</tr>
	</tfoot>
	<tbody>
		<?php foreach($this->getLoop("fleet") as $unit): ?>
		<tr>
			<td><?php echo $unit->getName() ?></td>
			<td><?php echo $unit->getFormattedSpeed() ?> / <?php echo $unit->getFormattedCapacity() ?></td>
			<td class="center"><?php echo $unit->getFormattedQty() ?></td>
			<?php if($unit->getSpeed() > 0): ?>
			<td class="center"><a href="javascript:void(0);" onclick="setField('ship_<?php echo $unit->getId() ?>', '<?php echo $unit->getQty() ?>');">{lang}MAX{/lang}</a> | <a href="javascript:void(0);" onclick="setField('ship_<?php echo $unit->getId() ?>', '0');">{lang}MIN{/lang}</a></td>
			<td><input type="text" name="<?php echo $unit->getId() ?>" value="0" size="8" maxlength="8" id="ship_<?php echo $unit->getId() ?>" class="center ships"/></td>
			<?php else: ?>
			<td></td>
			<td></td>
			<?php endif; ?>
		</tr>
	<?php endforeach; ?>
	</tbody>
	<?php else: ?>
	<tfoot>
		<tr>
			<td colspan="5" class="center">{lang}NO_SHIPS_REDEADY{/lang}</td>
		</tr>
	</tfoot>
	<tbody>
		<tr>
			<td class="center">-</td>
			<td class="center">-</td>
			<td class="center">-</td>
			<td class="center">-</td>
			<td class="center">-</td>
		</tr>
	</tbody>
	<?php endif; ?>
</table>
</form>