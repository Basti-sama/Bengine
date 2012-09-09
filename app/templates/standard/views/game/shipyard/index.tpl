<form method="post" action="{@orderAction}" class="form-sec">
<table class="ntable">
	<thead>
		<tr>
			<th colspan="2">{@shipyard}</th><th>{lang}QUANTITY{/lang}</th>
		</tr>
		<?php if(Core::getConfig()->get("SCRAP_MERCHANT_RATE") > 0): ?>
		<tr>
			<td colspan="3" class="center"><a href="<?php echo Link::url("game.php/".SID."/Shipyard/Merchant") ?>">{lang=GO_TO_MERCHANT}</a></td>
		</tr>
		<?php endif ?>
	</thead>
	<?php if($this->get("canBuildUnits")): ?>
	<tfoot>
		<tr>
			<td colspan="3"><input type="submit" name="sendmission" value="{lang}COMMIT{/lang}" class="button" /></td>
		</tr>
	</tfoot>
	<?php endif ?>
	<tbody>
		<?php foreach($this->getLoop("units") as $unit): ?>
		<?php $id = $unit->get("buildingid") ?>
		<?php if(Bengine::canBuild($id)): ?>
		<tr>
			<td>
				<?php $image = Image::getImage("buildings/".$unit->get("name").".gif", $unit->getName(), 120, 120) ?>
				<?php echo Link::get("game.php/".SID."/Unit/Info/".$id, $image); ?>
			</td>
			<td style="vertical-align:top;">
				<?php echo $unit->getLinkName() ?>
				<?php if($unit->get("quantity") > 0): ?>(<?php echo sprintf(Core::getLang()->get("SHIPS_EXIST"), fNumber($unit->get("quantity"))) ?>)<?php endif ?>
				{perm[CAN_EDIT_CONSTRUCTIONS]}<?php echo $unit->getEditLink(); ?>{/perm}<br />
				<?php echo $unit->getDescription() ?><br/>
				{lang}REQUIRES{/lang}
				<?php if($metal = $unit->get("basic_metal")): ?>
				{lang=METAL}: <?php echo fNumber($metal) ?>
				<?php endif ?>
				<?php if($silicon = $unit->get("basic_silicon")): ?>
				{lang=SILICON}: <?php echo fNumber($silicon) ?>
				<?php endif ?>
				<?php if($hydrogen = $unit->get("basic_hydrogen")): ?>
				{lang=HYDROGEN}: <?php echo fNumber($hydrogen) ?>
				<?php endif ?>
				<?php if($energy = $unit->get("basic_energy")): ?>
				{lang=ENERGY}: <?php echo fNumber($energy) ?>
				<?php endif ?>
				<br/>
				{lang}PRODUCTION_TIME{/lang} <?php echo $unit->getProductionTime(true) ?>
			</td>
			<td>
				<?php if($id == 49 && Bengine::getEH()->hasSShieldDome() || $id == 50 && Bengine::getEH()->hasLShieldDome()): ?>
				{lang=ALREADY_BUILDED}
				<?php elseif($unit->get("quantity") > 0 && ($id == 49 || $id == 50)): ?>
				{lang=ALREADY_BUILDED}
				<?php elseif(($id == 51 || $id == 52) && !$this->get("canBuildRockets")): ?>
				{lang=ROCKET_STATION_FULL}
				<?php elseif($unit->hasResources() && $this->get("canBuildUnits") && !Core::getUser()->get("umode")): ?>
				<input type="text" name="<?php echo $id ?>" value="0" size="3" maxlength="4" class="center"/>
				<?php endif ?>
			</td>
		</tr>
		<?php endif ?>
		<?php endforeach ?>
	</tbody>
</table>
{if[$this->templateVars["hasEvent"]]}
<div class="idiv">
{lang}CURRENTLY_IN_WORK{/lang}<br />
{@currentWork} {@remainingTime}
</div>
<table class="ntable">
	<tr>
		<th>{lang}OUTSTANDING_MISSIONS{/lang}</th>
	</tr>
	<tr>
		<td class="center">
			<select name="shipyardevents" size="10" multiple="multiple" style="width: 300px;">
				{foreach[events]}<option value="{loop}mission{/loop}">{loop}quantity{/loop}x {loop}mission{/loop}</option>{/foreach}
			</select>
		</td>
	</tr>
</table>
{/if}
</form>