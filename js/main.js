/**
 * Common JavaScript functions.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll"
 * @version $Id: main.js 38 2011-07-21 11:51:54Z secretchampion $
 */

function maxlength(field, max, counterid)
{
	len = field.value.length;
	if(len >= max)
	{
		var string = field.value;
		field.value = string.substring(0, max);
	}
	$('#'+counterid).text(len);
}

function checkNumberInput(field, min, max)
{
	if(isNaN(field.value) || field.value < min)
	{
		field.value = min;
		return;
	}
	if(field.value > max)
	{
		field.value = max;
		return;
	}
	field.value = Math.round(field.value);
}

function gotoPlanet(form, planetid)
{
	$('#planetid').val(planetid);
	$('#'+form).submit();
}

function secToString(secs)
{
	secs = Math.abs(secs);
	var parsed = parseInt(secs / 60 / 60 / 24) + "d " + leadingZeros((secs/60/60)%24) + ":" + leadingZeros((secs/60)%60) + ":" + leadingZeros(secs%60);
	parsed.toString();
	if(parseInt(secs / 60 / 60 / 24) == 0)
	{
		parsed = parsed.substring(3);
	}
	return parsed;
}

function leadingZeros(value)
{
	if(value < 10) { return "0" + parseInt(value); }
	else { return parseInt(value); }
}

function showHideId(id)
{
	obj = $("#"+id);
	if(obj.css("display") != "none")
	{
		obj.fadeOut(1000);
	}
	else
	{
		obj.fadeIn(1000);
	}
}

function setField(id, content)
{
	$('#'+id).val(content);
}

function setFromSelect(id, selectbox)
{
	val = selectbox.options[selectbox.selectedIndex].value;
	if(val != "none")
	{
		setField(id, val);
	}
}

function setProdTo0()
{
	for(i = 0; i < buildings.length; i++)
	{
		$('#factor_'+buildings[i]).val(0);
	}
}

function setProdTo100()
{
	for(i = 0; i < buildings.length; i++)
	{
		$('#factor_'+buildings[i]).val(100);
	}
}

function number_format(number, decimals, dec_point, thousands_sep)
{
	var exponent = "";
	var numberstr = number.toString();
	var eindex = numberstr.indexOf("e");
	if(eindex > -1)
	{
		exponent = numberstr.substring (eindex);
		number = parseFloat (numberstr.substring (0, eindex));
	}

	if(decimals != null)
	{
		var temp = Math.pow(10, decimals);
		number = Math.round(number * temp) / temp;
	}
	var sign = number < 0 ? "-" : "";
	var integer = (number > 0 ?
	Math.floor(number) : Math.abs (Math.ceil (number))).toString ();

	var fractional = number.toString().substring(integer.length + sign.length);
	dec_point = dec_point != null ? dec_point : ".";
	fractional = decimals != null && decimals > 0 || fractional.length > 1 ? (dec_point + fractional.substring (1)) : "";
	if(decimals != null && decimals > 0)
	{
		for(i = fractional.length - 1, z = decimals; i < z; ++i)
		{
			fractional += "0";
		}
	}

	thousands_sep = (thousands_sep != dec_point || fractional.length == 0) ? thousands_sep : null;
	if(thousands_sep != null && thousands_sep != "")
	{
		for(i = integer.length - 3; i > 0; i -= 3)
		integer = integer.substring (0 , i) + thousands_sep + integer.substring (i);
	}

	return sign + integer + fractional + exponent;
}

function setShell()
{
	metal = parseInt($('#unit_metal').val());
	silicon = parseInt($('#unit_silicon').val());
	$('#unit_shell').text((metal + silicon) / 10);
	return;
}

function displayAllyText(id)
{
	application = $('#ApplicationAllyText');
	intern = $('#InternAllyText');
	extern = $('#ExternAllyText');
	applicationTab = $('#ApplicationAllyText_Tab');
	internTab = $('#InternAllyText_Tab');
	externTab = $('#ExternAllyText_Tab');
	switch(id)
	{
		case 'ExternAllyText':
			application.hide();
			intern.hide();
			extern.fadeIn(500);
			externTab.addClass('active-tab');
			internTab.removeClass('active-tab');
			applicationTab.removeClass('active-tab');
		break;
		case 'InternAllyText':
			application.hide();
			intern.fadeIn(500);
			extern.hide();
			externTab.removeClass('active-tab');
			internTab.addClass('active-tab');
			applicationTab.removeClass('active-tab');
		break;
		case 'ApplicationAllyText':
			application.fadeIn(500);
			intern.hide();
			extern.hide();
			externTab.removeClass('active-tab');
			internTab.removeClass('active-tab');
			applicationTab.addClass('active-tab');
		break;
	}
	return;
}

function setHiddenValue(hiddenid, value, postname)
{
	setField(hiddenid, value);
	var input = document.createElement('input');
	input.setAttribute('type', 'hidden');
	input.setAttribute('name', postname);
	input.setAttribute('value', '1');
	obj = document.getElementById(postname);
	obj.appendChild(input);
	obj.submit();
}

function getDateFromSecs(secs)
{
	secs *= 1000;
	var date = new Date();
	var timestamp = date.getTime() + secs;
	date.setTime(timestamp);
	return date.getDate()+'.'+date.getMonth()+'.'+date.getYear()+' '+date.getHours()+':'+date.getMinutes()+':'.date.getSeconds();
}


function getParam(param)
{
	if(location.href.match(param+'='))
	{
		return location.href.toString().split(param+'=')[1].split('&')[0];
	}
	return '';
}

function pad(n, len)
{
	s = n.toString();
	if(s.length < len)
	{
		s = ('0000000000' + s).slice(-len);
	}
    return s;
}

$(document).ready(function() {
	$('.goto').click(function() {
		var planetid = ($(this).attr('rel')) ? $(this).attr('rel') : $(this).attr('lang');
		gotoPlanet('planetSelection', planetid);
	});
	if($.browser.msie && parseInt($.browser.version) <= 6)
	{
		$("#leftMenu").css("position", "absolute");
		var leftMenuTop = parseInt($("#leftMenu").css("top"));
	}
	$(window).scroll(function() {
		if($.browser.msie && parseInt($.browser.version) <= 6)
		{
			$("#leftMenu").css("top", $(window).scrollTop()+leftMenuTop+"px");
		}
	});
	$('a.external').bind('click',function(){window.open(this.href);return false;});
});