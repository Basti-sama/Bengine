<script type="text/javascript">
//<![CDATA[
$(document).ready(function() {
	$("#pw-send").click(function() {
		var url = "{const}BASE_URL.LANG{/const}password/request";
		$.post(url, { universe: $("#pw-universe").val(), username: $("#pw-username").val(), email: $("#pw-email").val() }, function(data) {
			$('#Ajax_Out_Pw').html(data);
		});
	});
});
//]]>
</script>

<fieldset>
	<legend>{lang}PASSWORD_FORGOTTEN{/lang}</legend>
	<form method="post" action="" id="lostpw">
	<ul>
		<li>
			<select name="universe" id="pw-universe" class="uni-selection">{@uniSelection}</select>
			<label for="pw-universe">{lang}UNIVERSE{/lang}</label>
		</li>
		<li><input type="text" name="username" id="pw-username" class="sign-input" /> <label for="pw-username">{lang}USERNAME{/lang}</label></li>
		<li><input type="text" name="email" id="pw-email" class="sign-input" /> <label for="pw-email">{lang}EMAIL{/lang}</label></li>
		<li>
			<input type="button" id="pw-send" value="{lang}REQUEST_DATA{/lang}" class="sign-btn" />
		</li>
	</ul>
	</form>
	<div id="Ajax_Out_Pw"></div>
</fieldset>
<h3>{lang}LOST_PW_HINT_1{/lang}</h3>
{lang}LOST_PW_HINT_2{/lang}