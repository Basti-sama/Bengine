/**
 * Common JavaScript functions.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll"
 * @version $Id: sign.js 25 2011-06-10 15:23:32Z secretchampion $
 */

$(document).ready(function() {
	var uni = $("#registerUniverse");
	var username = $("#registerUsername");
	var password = $("#registerPassword");
	var email = $("#registerEmail");
	var validation = $("#register-live-check");
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
			validation.removeClass("alert-success");
			validation.addClass("alert-warning");
		}
		else
		{
			validation.removeClass("alert-warning");
			validation.addClass("alert-success");
		}
	}

	function checkUser()
	{
		var len = username.val().length;
		if(len < min_user_chars || len > max_user_cars)
		{
			showLiveCheck(userInvalid, true);
			validation.text(userInvalid);
			validation.addClass("alert-warning");
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
		var regexp = /[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}/i;
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
			signIn.attr("action", $("#universe").val());
		}
		return true;
	});

	username.keyup(checkUser);
	username.focus(checkUser);
	//username.blur(hideLiveCheck);
	password.keyup(checkPassword);
	password.focus(checkPassword);
	//password.blur(hideLiveCheck);
	email.keyup(checkEmail);
	email.focus(checkEmail);
	//email.blur(hideLiveCheck);

	$("#register-form").submit(function() {
		hideLiveCheck();
		if(throbber == "")
		{
			throbber = $("#Ajax_Out").html();
			$("#Ajax_Out").show();
		}
		$("#Ajax_Out").html(throbber);
		$.post(this.action+"signup/checkuser", { universe: uni.val(), username: username.val(), password: password.val(), email: email.val() }, function(data) {
			$("#Ajax_Out").html(data);
		});
		return false;
	});

	$("#statusDialog").modal("show");
});