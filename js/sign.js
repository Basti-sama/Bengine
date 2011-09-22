/**
 * Common JavaScript functions.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll"
 * @version $Id: sign.js 25 2011-06-10 15:23:32Z secretchampion $
 */

$(document).ready(function() {
	var uni = $("#su-universe");
	var username = $("#su-username");
	var password = $("#su-password");
	var email = $("#su-email");
	var validation = $("#sign-live-check");
	var throbber = "";
	
	function hideLiveCheck()
	{
		validation.hide();
		return true;
	}
	
	function showLiveCheck(text, error)
	{
		validation.fadeIn(1000);
		validation.html(text);
		if(error)
		{
			validation.removeClass("field_success");
			validation.addClass("field_warning");
		}
		else
		{
			validation.removeClass("field_warning");
			validation.addClass("field_success");
		}
		return;
	}
	
	function checkUser()
	{
		var len = username.val().length;
		if(len < min_user_chars || len > max_user_cars)
		{
			showLiveCheck(userInvalid, true);
			validation.text(userInvalid);
			validation.addClass("field_warning");
			return false;
		}
		else
		{
			showLiveCheck(valid, false);
		}
		return true;
	}
	
	function checkEmail()
	{
		var regexp = /^(\w+(?:\.\w+)*)@((?:\w+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
		var result = regexp.test(email.val());
		if(!result)
		{
			showLiveCheck(emailInvalid, true);
			return false;
		}
		else
		{
			showLiveCheck(valid, false);
		}
		return true;
	}
	
	function checkPassword()
	{
		var len = password.val().length;
		if(len < min_password_chars || len > max_password_chars)
		{
			showLiveCheck(passwordInvalid, true);
			return false;
		}
		else
		{
			showLiveCheck(valid, false);
		}
		return true;
	}
	
	var signIn = $("#signin-form");
	signIn.submit(function() {
		
		if($("#universe").val() != null && $("#universe").val() != "")
		{
			action = $("#universe").val();
		}
		
		signIn.attr("action", action);
		return true;
	});

	
	username.keyup(checkUser);
	username.focus(checkUser);
	username.blur(hideLiveCheck);
	password.keyup(checkPassword);
	password.focus(checkPassword);
	password.blur(hideLiveCheck);
	email.keyup(checkEmail);
	email.focus(checkEmail);
	email.blur(hideLiveCheck);
	
	$("#signup-btn").click(function() {
		if(throbber == "")
		{
			throbber = $("#Ajax_Out").html();
			$("#Ajax_Out").css("visibility", "visible");
		}
		$("#Ajax_Out").html(throbber);
		$.post(url+"signup/checkuser", { universe: uni.val(), username: username.val(), password: password.val(), email: email.val() }, function(data) {
			$("#Ajax_Out").html(data);
		});
		return false;
	});
	$(".signup").bind("click", function() {
		$("#signup-dialog").dialog("open");
		return false;
	});
	$("#signup-dialog").dialog({
		bgiframe: true,
		autoOpen: false,
		modal: true,
		width: 400
	});
	$("a.external").bind("click",function(){window.open(this.href);return false;});
});