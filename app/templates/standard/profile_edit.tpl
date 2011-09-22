{@backLink}
<script type="text/javascript" src="{const=BASE_URL}js/lib/tiny_mce/tiny_mce_gzip.js"></script>
<form action="{@formaction}" method="post">
<input type="hidden" name="user_id" value="{@user_id}"/>
	<table class="ntable">
		<colgroup>
			<col width="20%"/>
			<col width="80%"/>
		</colgroup>
		<thead>
			<tr>
				<th colspan="2">{lang=EDIT_PROFILE}</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="2"><input type="submit" name="save" value="{lang=PROCEED}" class="button"/></td>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach($this->getLoop("profile") as $field): ?>
			<tr>
				<td><label for="<?php echo strtolower($field->getName()) ?>"><?php echo $field->getTranslatedName() ?></label></td>
				<td>
					<?php echo $field->getFieldObject()->getHtml() ?>
					<?php $_errors = $field->getFieldObject()->getErrors(); ?>
					<?php if(count($_errors) > 0): ?>
						<?php foreach($_errors as $_error): ?>
						<br/><?php echo $_error ?>
						<?php endforeach; ?>
					<?php endif; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</form>