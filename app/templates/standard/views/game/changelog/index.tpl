<table class="ntable">
	<thead><tr>
		<th>{lang}RELEASE{/lang}</th>
		<th>{lang}CHANGES{/lang}</th>
	</tr></thead>
	{if[{var}latestRevision{/var} > BENGINE_REVISION]}{perm[CAN_MODERATE_USER]}<tfoot><tr>
		<td class="pointer" colspan="2" onclick="window.open('{const}HTTP_HOST.REQUEST_DIR{/const}refdir.php?url=http://sourceforge.net/projects/bengine');"><span class="external">{lang}AVAILABLE_VERSION{/lang} {@latestVersion}</span></td>
	</tr></tfoot>{/perm}{/if}
	<tbody>
	{hook}showChangeLog{/hook}
	{foreach[release]}<tr>
			<td>{loop}version{/loop}</td>
			<td><pre>{loop}changes{/loop}</pre></td>
		</tr>{/foreach}
	</tbody>
</table>