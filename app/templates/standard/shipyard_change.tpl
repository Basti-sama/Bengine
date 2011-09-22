<form action="{@formaction}" method="post">
	<table class="ntable">
		<thead>
			<tr>
				<th>{lang=SCRAP_MERCHANT}</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<?php foreach($this->get("units") as $id => $qty): ?>
					<input type="hidden" name="unit[<?php echo $id ?>]" value="<?php echo $qty ?>"/>
					<?php endforeach ?>
					{lang=SCRAP_MERCHANT_CHANGE}
				</td>
			</tr>
		</tbody>
		<tfoot>
			<tr>
				<td>
					<button type="submit" name="verify" value="yes" class="button">Ja</button>
					<button type="submit" name="verify" value="no" class="button">Nein</button>
				</td>
			</tr>
		</tfoot>
	</table>
</form>