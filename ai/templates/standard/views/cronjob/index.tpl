<h1>{lang}Cronjobs{/lang}</h1>
<div class="draggable">
	<form method="post">
		<table class="ntable" cellpadding="4" cellspacing="0">
			<thead>
				<tr>
					<th colspan="5">{lang}Add_Cronjob{/lang}</th>
				</tr>
			</thead>
			<tr>
				<td colspan="2"><label for="f_class">{lang}Script{/lang}</label></td>
				<td colspan="3"><input type="text" name="class" id="f_class" maxlength="128" /></td>
			</tr>
			<tr>
				<td><label for="f_minute">{lang}Minute{/lang}</label></td>
				<td><label for="f_hour">{lang}Hour{/lang}</label></td>
				<td><label for="f_weekday">{lang}Weekday{/lang}</label></td>
				<td><label for="f_day">{lang}Day{/lang}</label></td>
				<td><label for="f_month">{lang}Month{/lang}</label></td>
			</tr>
			<tr>
				<td valign="top"><select name="minute" id="f_minute" size="12">{@minute}</select></td>
				<td valign="top"><select name="hour" id="f_hour" size="12">{@hour}</select></td>
				<td valign="top"><select name="weekday[]" id="f_weekday" size="12" multiple="multiple">{@weekday}</select></td>
				<td valign="top"><select name="day[]" id="f_day" size="12" multiple="multiple">{@day}</select></td>
				<td valign="top"><select name="month[]" id="f_month" size="12" multiple="multiple">{@month}</select></td>
			</tr>
			<tfoot>
				<tr>
					<td colspan="5"><input type="submit" name="add_cronjob" value="{lang}Commit{/lang}" class="button" /></td>
				</tr>
			</tfoot>
		</table>
	</form>
</div>
<div class="draggable">
	<table class="ntable" cellpadding="4" cellspacing="0">
		<thead>
			<tr>
				<th colspan="5">{lang}Cronjobs{/lang}</th>
			</tr>
		</thead>
		<tr>
			<td>#</td>
			<td>{lang}Script{/lang}</td>
			<td>{lang}Execution_Time{/lang}</td>
			<td>{lang}Last_Execution{/lang}</td>
			<td></td>
		</tr>
		{foreach[crontabs]}<tr>
			<td>{loop}cronid{/loop}</td>
			<td>{loop}class{/loop}</td>
			<td>{loop}xtime{/loop}</td>
			<td>{loop}last{/loop}</td>
			<td>[{loop}exec{/loop}] [{loop}edit{/loop}] [{loop}delete{/loop}] [{loop}active{/loop}]</td>
		</tr>{/foreach}
	</table>
</div>