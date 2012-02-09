<h1>{lang}Template_Editor{/lang}</h1>
	<div class="draggable">
		<table class="ntable" cellpadding="4" cellspacing="0">
			<thead>
				<tr>
					<th>{lang}Available_Templates{/lang}</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<strong>{lang}Package{/lang} "{@package}":</strong><br />
						{foreach[templates]}
						{loop}template{/loop}<br />
						{/foreach}
					</td>
				</tr>
			</tbody>
		</table>
	</div>
<div id="right">
	<div class="link_b">{link[Back]}"tpleditor"{/link}</div>
</div>