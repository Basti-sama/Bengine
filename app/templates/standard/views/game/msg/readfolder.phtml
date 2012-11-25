<script type="text/javascript">
//<![CDATA[
function openWindow(url)
{
	win = window.open(url, "{lang}ASSAULT_REPORT{/lang}", "width=600,height=400,status=yes,scrollbars=yes,resizable=yes");
	win.focus();
}
//]]>
</script>
{@pagination}
<form method="post" action="{@formaction}">
<div class="clear"><input type="hidden" name="msgid[]" value="0"/></div>
<table class="ntable">
	<thead>
		<tr>
			<th><?php if($this->get("mode") != 2): ?>{lang}FROM{/lang}<?php else: ?>{lang}RECEIVER{/lang}<?php endif; ?></th>
			<th>{lang}SUBJECT{/lang}</th>
			<th>{lang}DATE{/lang}</th>
			<th>{lang}ACTION{/lang}</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="4" class="center">
				<?php if(count($this->getLoop("messages")) == 0): ?>
				{lang}NO_MATCHES_FOUND{/lang}
				<?php else: ?>
				<select name="deleteOption">
					<option value="1">{lang}DELETE_ALL_MARKED{/lang}</option>
					<option value="2">{lang}DELETE_ALL_NON_MARKED{/lang}</option>
					<option value="3">{lang}DELETE_ALL_SHOWN{/lang}</option>
					<option value="4">{lang}EMPTY_FOLDER{/lang}</option>
					<option value="5">{lang}REPORT_TO_MODERATOR{/lang}</option>
				</select>
				<input type="submit" name="delete" value="{lang}COMMIT{/lang}" class="button"/>
				<?php endif ?>
			</td>
		</tr>
		</tfoot>
	<tbody>
		<?php foreach($this->getLoop("messages") as $message): ?>
		<?php $reply = $message->get("reply_link") ?>
		<tr>
			<td><?php echo (empty($reply)) ? "" : $reply ?> <?php echo $message->get("sender") ?></td>
			<td><?php echo $message->get("subject") ?></td>
			<td><?php echo Date::timeToString(1, $message->get("time")) ?></td>
			<td>
				<input type="checkbox" name="msgid[]" value="<?php echo $message->get("msgid") ?>" />
			</td>
		</tr>
		<tr>
			<td colspan="3"><?php echo $message->get("message") ?></td>
			<td></td>
		</tr>
		<?php endforeach ?>
	</tbody>
</table>
</form>
<br/>
{@pagination}