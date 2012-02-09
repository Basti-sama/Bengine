<h1>{lang}Edit_Phrases{/lang}</h1>
<div class="draggable">
	<form method="post">
		<table class="ntable" cellpadding="4" cellspacing="0">
			<thead>
				<tr>
					<th>{lang}Operations{/lang}</th>
					<th>{lang}Phrase_Content{/lang}</th>
				</tr>
			</thead>
			{foreach[vars]}<tr>
				<td>
					<input type="hidden" name="phraseid[]" value="{loop}phraseid{/loop}" />
					<label for="title_{loop}phraseid{/loop}">{lang}Phrase_Name{/lang}</label><br />
					<input type="text" name="title_{loop}phraseid{/loop}" id="title_{loop}phraseid{/loop}" value="{loop}title{/loop}" /><br />
					<label for="language_{loop}phraseid{/loop}">{lang}Language{/lang}</label><br />
					<select name="language_{loop}phraseid{/loop}" id="language_{loop}phraseid{/loop}">{loop}lang{/loop}</select><br />
					<label for="phrasegroup_{loop}phraseid{/loop}">{lang}Phrase_Group{/lang}</label><br />
					<select name="phrasegroup_{loop}phraseid{/loop}" id="phrasegroup_{loop}phraseid{/loop}">{loop}groups{/loop}</select><br />
					{loop}delete{/loop}
				</td>
				<td>
					<textarea name="content_{loop}phraseid{/loop}" cols="50" rows="7">{loop}content{/loop}</textarea>
				</td>
			</tr>{/foreach}
			<tfoot>
				<tr>
					<td colspan="2"><input type="submit" name="save_phrases" value="{lang}Commit{/lang}" class="button" /></td>
				</tr>
			</tfoot>
		</table>
	</form>
</div>
<div id="right"><div class="link_b">{link[Back]}"language/phrasesgroups"{/link}</div></div>