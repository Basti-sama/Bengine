<h1>{lang}Language_Manager{/lang}</h1>
<div class="draggable">
	<form method="post">
		<table class="ntable" cellpadding="4" cellspacing="0">
			<thead>
				<tr>
					<th colspan="2">{lang}Export_Language{/lang}</th>
				</tr>
				<tr>
					<td>{lang}Language{/lang}</td>
					<td>{lang}Export{/lang}</td>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="2">
						{lang=Destination_Folder} <input type="text" name="destination" value="var/cache/"/>
						<input type="submit" name="export" value="{lang=Start_Export}" class="button"/>
					</td>
				</tr>
			</tfoot>
			<tbody>
				{foreach[languages]}<tr>
					<td><label for="lang_{loop=langcode}">{loop=title}</label></td>
					<td><input type="radio" name="languageid" value="{loop=langcode}" id="lang_{loop=langcode}"/></td>
				</tr>{/foreach}
				<tr>
					<td><label for="lang_all">{lang=Export_All_Languages}</label></td>
					<td><input type="radio" name="languageid" value="all" id="lang_all"/></td>
				</tr>
			</tbody>
		</table>
	</form>
</div>
<div class="draggable">
	<form method="post" enctype="multipart/form-data">
		<table class="ntable" cellpadding="4" cellspacing="0">
			<thead>
				<tr>
					<th colspan="2">{lang=XML_Import}</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="2"><input type="submit" class="button" name="import" value="{lang=Import}"/></td>
				</tr>
			</tfoot>
			<tbody>
				<tr>
					<td><label for="import_file">{lang=Import_File}</label></td>
					<td><input type="file" name="import_file" id="import_file"/></td>
				</tr>
			</tbody>
		</table>
	</form>
</div>
<div id="right">
	<div class="link_b">
		{link[Back]}"language"{/link}
	</div>
</div>