<h1>{lang}Template_Editor{/lang}</h1>
<div class="draggable">
	<form method="post">
		<table class="ntable" cellpadding="4" cellspacing="0">
			<thead>
				<tr>
					<th>{lang}Template_Packages{/lang}</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<strong>{lang}Application_Templates{/lang}</strong><br />
						{foreach[app]}
						{loop}package{/loop}<br />
						{/foreach}<br />
						<strong>{lang}AI_Templates{/lang}</strong><br />
						{foreach[ai]}
						{loop}package{/loop}<br />
						{/foreach}
					</td>
				</tr>
			</tbody>
		</table>
	</form>
</div>