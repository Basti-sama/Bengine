<table class="ntable">
	<tr>
		<th>{lang=SIGNATURE_GENERATOR}</th>
	</tr>
	<tr>
		<td>
			<label>{lang=PREVIEW}:</label><br/>
			<img src="{@imgUrl}" alt="{lang=SIGNATURE_PREVIEW_IMAGE}" title="{lang=SIGNATURE_PREVIEW_IMAGE}" width="468" height="60"/>
			<p>{lang=SIGNATURE_DESCRIPTION}</p>
		</td>
	</tr>
	<tr>
		<td>
			<label for="bbcode">{lang=BB_CODE}:</label><br/>
			<textarea cols="60" rows="5" readonly="readonly" id="bbcode" class="select-all">[URL={const=BASE_URL}][IMG]{@imgUrl}[/IMG][/URL]</textarea>
		</td>
	</tr>
	<tr>
		<td>
			<label for="htmlcode">{lang=HTML_CODE}:</label><br/>
			<textarea cols="60" rows="5" readonly="readonly" id="htmlcode" class="select-all">&lt;a href=&quot;{const=BASE_URL}&quot; target=&quot;_blank&quot;&gt;&lt;img src=&quot;{@imgUrl}&quot; alt=&quot;{config=pagetitle}&quot; title=&quot;{config=pagetitle}&quot; width=&quot;468&quot; height=&quot;60&quot;/&gt;&lt;/a&gt;</textarea>
		</td>
	</tr>
	<tr>
		<td>
			<label for="directlink">{lang=DIRECT_LINK}:</label><br/>
			<input type="text" value="{@imgUrl}" size="63" readonly="readonly" id="directlink" class="select-all"/>
		</td>
	</tr>
</table>

<script type="text/javascript">
//<![CDATA[
$(".select-all").bind("click", function() {
	this.focus();
	this.select();
});
//]]>
</script>