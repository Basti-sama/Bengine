<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>{@pageTitle}</title>
<meta http-equiv="content-type" content="text/html; charset={@charset}" />
<meta http-equiv="expires" content="0" />
<meta http-equiv="pragma" content="no-cache" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Cache-control" content="no-cache" />
<meta http-equiv="Expires" content="-1" />

<link rel="shortcut icon" href="{@themePath}img/favicon.ico" type="image/x-icon" />

{if[Core::getUser()->get("theme") == ""]}
<link rel="stylesheet" type="text/css" href="{const}BASE_URL{/const}css/?f=layout.css,style.css"/>
{else}
<link rel="stylesheet" type="text/css" href="{@themePath}css/layout.css"/>
<link rel="stylesheet" type="text/css" href="{@themePath}css/style.css"/>
{/if}

<script type="text/javascript" src="{const}BASE_URL{/const}js/?f=lib/jquery.js,lib/jquery.countdown.js,main.js"></script>

<script type="text/javascript">
//<![CDATA[
$(function () {
{foreach[events]}
	$('#timer_{loop}eventid{/loop}').countdown({until: {loop}time_r{/loop}, compact: true, onExpiry: function() {
		$('#timer_{loop}eventid{/loop}').text('-');
	}});
{/foreach}
});
//]]>
</script>
</head>
<body>
{hook}HtmlBegin{/hook}
<table class="ntable">
	<tr>
		<th colspan="2">{lang}FLEET_ACTIVITIES{/lang}</th>
	</tr>
	{if[{var}num_rows{/var} <= 0]}<tr>
		<td>&nbsp;</td>
		<td>{lang}NO_MATCHES_FOUND{/lang}</td>
	</tr>{else}{foreach[events]}<tr>
		<td>
			<span id="timer_{loop}eventid{/loop}">{loop}time{/loop}</span>
		</td>
		<td><span class="{loop}class{/loop}">{loop}message{/loop}</span></td>
	</tr>{/foreach}{/if}
</table>
{hook}HtmlEnd{/hook}
</body>
</html>