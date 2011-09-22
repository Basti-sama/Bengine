<script type="text/javascript">
//<![CDATA[
	function sendFleet(mode, target)
	{
		var responseElem = $('#ajax-response');
		if(responseElem.css("visibility") == "hidden")
		{
			responseElem.css("visibility", "visible");
		}
		else
		{
			responseElem.html('{image[LOADING]}loading.gif{/image}');
		}
		url = "{const=BASE_URL}game.php/{const=SID}/Ajax_Fleet/" + mode + "/" + target;
		$.get(url, function(data) {
			responseElem.html(data);
		});
	}

	function openWindow(id)
	{
		win = window.open("{const=BASE_URL}game.php/{const=SID}/MonitorPlanet/Index/"+id, "", "width=600,height=400,status=yes,scrollbars=yes,resizable=yes");
		win.focus();
	}

	function galaxySubmit(type)
	{
		theForm = document.forms[1];
		theForm.submittype.value = type;
		theForm.submit();
	}

	document.onkeyup = function(event) {
		if(!event)
			event = window.event;
		if(event.target.nodeName == "HTML" || event.target.nodeName == "BODY")
		{
			var keyCode = event.which ? event.which : event.keyCode;
			if(keyCode == 37)
				galaxySubmit('prevsystem');
			else if(keyCode == 39)
				galaxySubmit('nextsystem');
		}
	};
//]]>
</script>
<form method="post" action="{const=BASE_URL}game.php/{const=SID}/Galaxy">
<input type="hidden" name="submittype" value="" />
<div class="idiv">
	<table class="ntable galaxy-browser">
		<tr>
			<th colspan="3">{lang}GALAXY{/lang}</th><th colspan="3">{lang}SYSTEM{/lang}</th>
		</tr>
		<tr>
			<td>
				<input type="button" name="prevgalaxy" value="&laquo;" class="button" onclick="galaxySubmit('prevgalaxy');" />
			</td>
			<td>
				<input type="text" name="galaxy" value="{@galaxy}" size="3" maxlength="2" class="center" onblur="checkNumberInput(this, 1, {config}GALAXYS{/config});" />
			</td>
			<td>
				<input type="button" name="nextgalaxy" value="&raquo;" class="button" onclick="galaxySubmit('nextgalaxy');" />
			</td>

			<td>
				<input type="button" name="prevsystem" value="&laquo;" class="button" onclick="galaxySubmit('prevsystem');" />
			</td>
			<td>
				<input type="text" name="system" value="{@system}" size="3" maxlength="3" class="center" onblur="checkNumberInput(this, 1, {config}SYSTEMS{/config});" />
			</td>
			<td>
				<input type="button" name="nextsystem" value="&raquo;" class="button" onclick="galaxySubmit('nextsystem');" />
			</td>
		</tr>
		<tr>
			<td colspan="6" class="center"><input type="submit" name="jump" value="{lang}COMMIT{/lang}" class="button" /></td>
		</tr>
	</table>
</div>
</form>
<div id="ajax-response" class="idiv">{image[LOADING]}loading.gif{/image}</div>
<table class="ntable">
	<thead>
		<tr>
			<th colspan="8">{lang}SUNSYSTEM{/lang} {@galaxy}:{@system}</th>
		</tr>
		<tr>
			<th>#</th>
			<th>{lang}PLANET{/lang}</th>
			<th>{lang}NAME{/lang}</th>
			<th>{lang}MOON{/lang}</th>
			<th>{lang}TF{/lang}</th>
			<th>{lang}USER{/lang}</th>
			<th>{lang}ALLIANCE{/lang}</th>
			<th>{lang}ACTIONS{/lang}</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="8">
				<p class="legend"><cite><span>i</span> = {lang}LOWER_INACTIVE{/lang}</cite><cite><span>I</span> = {lang}UPPER_INACTIVE{/lang}</cite><cite><span class="banned">b</span> = {lang}BANNED{/lang}</cite><cite><span class="strong-player">s</span> = {lang}STRONG_PLAYER{/lang}</cite><cite><span class="weak-player">n</span> = {lang}NEWBIE{/lang}</cite><cite><span class="vacation-mode">v</span> = {lang}VACATION_MODE{/lang}</cite></p>
				<p class="legend"><cite><span class="ownPosition">{lang=ONESELF}</span></cite><cite><span class="alliance">{lang=ALLIANCE}</span></cite><cite><span class="friend">{lang=FRIEND}</span></cite><cite><span class="enemy">{lang=ENEMY}</span></cite><cite><span class="confederation">{lang=CONFEDERATION}</span></cite><cite><span class="trade-union">{lang=TRADE_UNION}</span></cite><cite><span class="protection">{lang=PROTECTION_ALLIANCE}</span></cite></p>
			</td>
		</tr>
	</tfoot>
	<tbody>
		{foreach[sunsystem]}<tr>
			<?php $actionsUrl = BASE_URL."game.php/".SID."/Mission/Index/".$this->get("galaxy")."/".$this->get("system")."/".$row["systempos"] ?>
			<td class="center">{loop}systempos{/loop}</td>
			<td class="center">{loop}picture{/loop}</td>
			<td class="center">{loop}planetname{/loop} {loop}activity{/loop}</td>
			<td class="center">
				{if[$row["moonid"] != ""]}
				<script type="text/javascript">//<![CDATA[
					var actions = '{if[$row["userid"] != Core::getUser()->get("userid") && $row["userid"]]}<a href="javascript:void(0);" onclick="sendFleet(&quot;espionage&quot;, {loop=moonid})">{lang=SPY}</a> | <a href="<?php echo $actionsUrl."/moon/attack" ?>">{lang=ATTACK}</a>{else}<a href="<?php echo $actionsUrl."/moon/position" ?>">{lang=STATIONATE}</a>{/if} | <a href="<?php echo $actionsUrl."/moon/transport" ?>">{lang=TRANSPORT}</a>';
					var moon_{loop}systempos{/loop} = '<table class="ttable"><tr><td rowspan="3">{@moon}</td><th colspan="2">{lang}FEATURES{/lang}</th></tr><tr><td>{lang}SIZE{/lang}:</td><td>{loop}moonsize{/loop}km</td></tr><tr><td>{lang}TEMPERATURE{/lang}:</td><td>{loop}moontemp{/loop} &deg;C</td></tr>{loop}moonrocket{/loop}</table>'+actions;
				//]]></script>
				<a href="{const=BASE_URL}game.php/{const=SID}/Mission/Index/{@galaxy}/{@system}/{loop=systempos}/moon" onmouseover="Tip(moon_{loop}systempos{/loop}, TITLE, '{loop}moon{/loop}', FADEIN, 300, FADEOUT, 300, STICKY, 1, CLOSEBTN, true);" onmouseout="UnTip();">{loop}moonpicture{/loop}</a>
				{/if}
			</td>
			<td class="center">
				{if[$row["metal"] || $row["silicon"]]}
				<script type="text/javascript">//<![CDATA[
					var debris_{loop}systempos{/loop} = '<table class="ttable"><tr><td rowspan="3">{@debris}</td><th colspan="2">{lang}RESOURCES{/lang}</th></tr><tr><td>{lang}METAL{/lang}:</td><td>{loop}metal{/loop}</td></tr><tr><td>{lang}SILICON{/lang}:</td><td>{loop}silicon{/loop}</td></tr></table>';
				//]]></script>
				<a href="{const=BASE_URL}game.php/{const=SID}/Mission/Index/{@galaxy}/{@system}/{loop=systempos}/tf/recycling" onmouseover="Tip(debris_{loop}systempos{/loop}, TITLE, '{lang}DEBRIS{/lang}', FADEIN, 300, FADEOUT, 300, STICKY, 1, CLOSEBTN, true);" onmouseout="UnTip();">{loop}debris{/loop}</a>
				{/if}
			</td>
			<td class="center normal">{loop}username{/loop} {if[!empty($row["user_status_long"])]}({loop}user_status_long{/loop}){/if}{if[!empty($row["userid"])]}<br /><span class="galaxysub">#{loop}rank{/loop} / {loop}points{/loop}</span>{/if}</td>
			<td class="center">
				{if[!empty($row["alliance"])]}
				<script type="text/javascript">//<![CDATA[
					var ally_{loop}systempos{/loop} = '<table class="ttable"><tr><th>{loop}allydesc{/loop}</th></tr><tr><td>{loop}allypage{/loop}</td></tr>{loop}homepage{/loop}{loop}memberlist{/loop}</table>';
				//]]></script>
				<a href="javascript:void(0);" onmouseover="Tip(ally_{loop}systempos{/loop}, TITLE, '{lang}ALLIANCE{/lang}', FADEIN, 300, FADEOUT, 300, STICKY, 1, CLOSEBTN, true);" onmouseout="UnTip();">{loop}alliance{/loop}</a>{if[!empty($row["userid"])]}<br /><span class="galaxysub">#{loop}alliance_rank{/loop}</span>{/if}
				{/if}
			</td>
			<td class="center">{if[$row["userid"] != Core::getUser()->get("userid") && $row["userid"]]}<span class="pointer" onclick="sendFleet('espionage', {loop}planetid{/loop});">{@sendesp}</span> {loop}message{/loop} {loop}buddyrequest{/loop} {loop}rocketattack{/loop} {if[{var}canMonitorActivity{/var}]}<span onclick="openWindow({loop}planetid{/loop});" class="pointer">{@monitorfleet}</span>{/if}{/if}</td>
		</tr>{/foreach}
	</tbody>
</table>
<script type="text/javascript" src="{const=BASE_URL}js/?f=lib/wz_tooltip.js"></script>