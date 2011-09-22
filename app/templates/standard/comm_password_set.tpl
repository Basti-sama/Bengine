<script type="text/javascript">
//<![CDATA[
$(document).ready(function() {
	$("#pws-send").click(function() {
		var url = "{const}BASE_URL.LANG{/const}password/change";
		$.post(url, { universe: $("#pws-universe").val(), key: $("#key").val(), userid: $("#userid").val(), password: $("#pws-password").val() }, function(data) {
			$('#Ajax_Out_Pw').html(data);
		});
	});
});
//]]>
</script>

<fieldset>
	<form method="post" action="">

	<input type="hidden" name="key" value="{request[get]}key{/request}" id="key" />
	<input type="hidden" name="userid" value="{request[get]}user{/request}" id="userid" />
	<legend>{lang}CHANGE_PASSWORD{/lang}</legend>
	<ul>
		<li>
			<select name="universe" id="pws-universe" class="uni-selection">{@uniSelection}</select>
			<label for="pws-universe">{lang}UNIVERSE{/lang}</label>
		</li>
		<li><input type="text" name="password" id="pws-password" class="sign-input" maxlength="{config=MAX_PASSWORD_LENGTH}"/> <label for="pws-password">{lang}PASSWORD{/lang}</label></li>
		<li>
			<input type="button" id="pws-send" value="{lang}COMMIT{/lang}" class="sign-btn"/>
		</li>
	</ul>
	</form>
	<div id="Ajax_Out_Pw"></div>
</fieldset>