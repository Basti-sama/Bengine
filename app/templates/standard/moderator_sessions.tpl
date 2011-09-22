<table class="ntable">
	<thead>
		<tr>
			<th colspan="4">Session Log</th>
		</tr>
		<tr>
			<td>User</td>
			<td>IP</td>
			<td>Date</td>
			<td>Agent</td>
		</tr>
	</thead>
	<tbody>
		<?php foreach($this->getLoop("sessionLog") as $session): ?>
		<tr>
			<td>
				<a href="<?php echo Link::url("game.php/".SID."/Moderator/Index/".$session->get("userid")) ?>">
					<?php echo $session->get("username") ?>
				</a>
			</td>
			<td>
				<a href="<?php echo Link::url("game.php/".SID."/Moderator/Sessions?ip=".$session->get("ipaddress")) ?>">
					<?php echo $session->get("ipaddress") ?>
				</a>
			</td>
			<td><?php echo Date::timeToString(1, $session->get("time")) ?></td>
			<td title="<?php echo $session->get("useragent") ?>">
				<?php $browserInfo = $session->getBrowserInfo() ?>
				Browser: <?php echo isset($browserInfo["parent"]) ? $browserInfo["parent"] : "Undefined" ?><br/>
				OS: <?php echo isset($browserInfo["platform"]) ? $browserInfo["platform"] : "Undefined" ?>
			</td>
		</tr>
		<?php endforeach ?>
	</tbody>
</table>