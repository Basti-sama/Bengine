<script type="text/javascript">
//<![CDATA[
$(function () {
{foreach[fleetEvents]}
	$('#timer_{loop}eventid{/loop}').countdown({until: {loop}time_r{/loop}, description: '<span class="finish-time">{loop=time_finished}</span>', compact: true, onExpiry: function() {
		$('#timer_{loop}eventid{/loop}').text('-');
	}});
{/foreach}
$("#news").newsticker(9500);
});
//]]>
</script>

<table class="ntable main-header">
	<tr>
		<th class="news-row">{lang=NEWS_AND_INFO}</th>
		<th class="inbox-row">{lang=INBOX}</th>
	</tr>
	<tr>
		<td class="news-field">
			<ul id="news">
				<?php foreach($this->getLoop("news") as $i => $item): ?>
				<li<?php if($i == 0): ?> style="display: block;"<?php endif; ?>><b><?php echo $item->getTitle() ?></b> <?php echo $item->getText() ?></li>
				<?php endforeach ?>
			</ul>
		</td>
		<td class="center">{@newMessages}</td>
	</tr>
</table>


<table class="ntable">
	<tr>
		<th colspan="3">{lang}PLANET{/lang} "{@planetNameLink}" (<a href="{const=BASE_URL}game/{const=SID}/Profile">{user}username{/user}</a>)</th>
	</tr>
	<tr>
		<td>{lang}SERVER_TIME{/lang}</td>
		<td colspan="2"><span id="serverwatch">{@serverTime}</span></td>
	</tr>
	<tr><th colspan="3">{lang}EVENTS{/lang}</th></tr>
	{foreach[fleetEvents]}<tr>
		<td class="center"><span id="timer_{loop}eventid{/loop}">{loop}time{/loop}</span></td>
		<td colspan="2"><span class="{loop}class{/loop}">{loop}message{/loop}</span></td>
	</tr>{/foreach}
	<tr>
		<td class="center">{@moon}<br />{@moonImage}</td>
		<td class="center">{@planetImage}<br />{@planetAction}</td>
		<td>
			<p>
				<b>{lang=CURRENT_RESEARCH}</b><br/>
				<?php $research = $this->get("research") ?>
				<?php if($research): ?>
				<?php echo Link::get("game/".SID."/Research", Core::getLanguage()->get($research->getData("buildingname"))) ?><br/>
				<span id="research-countdown"><?php echo $research->getFormattedTimeLeft() ?></span><br/>
				<span class="finish-time"><?php echo $research->getFormattedTime() ?></span>
				<script type="text/javascript">
				//<![CDATA[
					$("#research-countdown").countdown({until: <?php echo $research->getTimeLeft() ?>, compact: true, onExpiry: function() {
						$("#research-countdown").text("-");
					}});
				//]]>
				</script>
				<?php else: ?>
				{lang=NONE}
				<?php endif ?>
			</p>
			<p>
				<b>{lang=CURRENT_SHIPYARD}</b><br/>
				<?php if(count($this->get("shipyardMissions"))): ?>
				<?php foreach($this->get("shipyardMissions") as $shipyard): ?>
				<?php echo $shipyard["mission"] ?> (<?php echo $shipyard["quantity"] ?>)
				<span class="finish-time"><?php echo Date::timeToString(1, $shipyard["time_finished"]) ?></span><br/>
				<?php endforeach ?>
				<?php else: ?>
				{lang=NONE}
				<?php endif ?>
			</p>
		</td>
	</tr>
	<tr>
		<td>{lang}PLANETDIAMETER{/lang}</td>
		<td colspan="2">{@planetDiameter} km ({@occupiedFields} / {@freeFields})</td>
	</tr>
	<tr>
		<td>{lang}TEMPERATURE{/lang}</td>
		<td colspan="2">{lang}APPROX{/lang} {@planetTemp} &deg;C</td>
	</tr>
	<tr>
		<td>{lang}POSITION{/lang}</td>
		<td colspan="2">{@planetPosition}</td>
	</tr>
	<tr>
		<td>{lang}POINTS{/lang}</td>
		<td colspan="2">{@points} ({lang}RANK_OF_USERS{/lang})</td>
	</tr>
</table>
{if[{var=popupContent}]}
<div id="layer"></div>
<div id="popup">{if[{var=popupTitle}]}<p id="popupHeading">{@popupTitle}</p>{/if}<div id="popupContent">{@popupContent}</div>
<a href="#" class="closePopup" onclick="return closePopup();">{lang}Close_Popup{/lang}</a></div>
<script type="text/javascript">
//<![CDATA[
function closePopup()
{
	$("div#layer").hide();
	$("div#popup").hide();
	return false;
}
//]]>
</script>
{/if}