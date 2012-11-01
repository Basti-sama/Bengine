<table class="ntable">
	<colgroup>
		<col width="50%"/>
		<col width="30%"/>
		<col width="20%"/>
	</colgroup>
	<thead>
		<tr>
			<th>{lang}FOLDER{/lang}</th>
			<th>{lang}NUMBER{/lang}</th>
			<th>{lang=STORAGE}</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="3" class="center"><a href="{const=BASE_URL}game/{const=SID}/MSG/DeleteAll" onclick="return confirm('{lang=CONFIRM_DELETE_ALL}')">{lang=DELETE_ALL}</a> {link[CREATE_NEW_MESSAGE]}"game/".SID."/MSG/Write"{/link}</td>
		</tr>
	</tfoot>
	<tbody>{foreach[folders]}
		<tr>
			<td>{loop=image} {loop=label}</td>
			<td>{loop=messages} {loop=newMessages}</td>
			<td>{loop=size}</td>
		</tr>
	{/foreach}</tbody>
</table>