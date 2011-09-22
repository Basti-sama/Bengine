var lastQuestFaction = 0;
var rewardCount = 1;

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

function closeOverlay()
{
	$("#layer, .overlay").hide();
}

function showExistingRewards()
{
	$("#layer").show();
	$("#existingRewards").show();
	return false;
}

function adjustSortIndexSelect()
{
	$("select[name=sort_index] option").hide();
	var faction = $("select[name=faction]").val();
	var questlevel = parseInt($("input[name=questlevel]").val(), 10);
	$("select[name=sort_index] option.faction_"+faction+".questlevel_"+questlevel).show();
	
	if($("select[name=sort_index] option[value="+$("select[name=sort_index]").val()+"]").css("display")=="none")
		$("select[name=sort_index]").val($("select[name=sort_index] option").filter(function(index) { return $(this).css("display") != "none"; }).first().val());
	
	if($("select[name=sort_index] option").filter(function(index) { return $(this).css("display") != "none"; }).length == 0) {
		$("select[name=sort_index] option[value=0]").show();
		$("select[name=sort_index]").val(0);
	}
}

function calcFlyTime()
{
	var distance = $("input[name=distance]").val();
	$("span#flytime").html(secToString(Math.round(3500 * Math.sqrt(distance * 10 / flySpeed) + 10)));
}

function showRewardDataPossibilities(select)
{
	var rewardType = $(select).val();
	var count = $(select).attr("name").match(/reward\[(\d+)\]/);
	count = count[1];
	$(select).siblings(":not(img.deleteReward)").remove();
	$.get(url+"quest/getRewardDataPossibilities/"+rewardType+"/"+count, function(data) {
		$(select).after(data);
	});
}

function addRewardInput(link)
{
	var text = $(link).parent().children(".template").html();
	rewardCount++;
	text = text.replace(/name="reward\[1\]/gm, 'name="reward['+rewardCount+']');
	$(link).before('<div class="rewardInput">'+text+'</div>');
	showRewardDataPossibilities($(link).parent().find("div.rewardInput:last select"));
	return false;
}

function deleteRewardInput(image)
{
	$(image).parent().remove();
	return false;
}

function switchNextDiv(image)
{
	var src = $(image).attr("src");
	if($(image).next().css("display") == "none")
	{
		$(image).next().show();
		$(image).attr("src", src.replace(/down.png/, "up.png"));
	}
	else
	{
		$(image).next().hide();
		$(image).attr("src", src.replace(/up.png/, "down.png"));
	}
}

function showQuestDataPossibilities()
{
	var questType = $("#createNewQuestForm select[name=type]").val();
	var questID = $("#createNewQuestForm input[name=questID]").val();
	$.get(url+"quest/getQuestDataPossibilities/"+questType+"/"+questID, function(data) {
		$("tr#createNewQuestData ~ tr").remove();
		$("tr#createNewQuestData").after(data);
	});
}

function addQuestShipInput()
{
	var text = $("form#createNewQuestForm table.shipInput tr.template").html();
	if($("form#createNewQuestForm table.shipInput tr.template").css("display") == "none")
	{
		$("form#createNewQuestForm table.shipInput tr.template").remove();
		$("form#createNewQuestForm table.shipInput").append('<tr class="template">'+text+'</tr>');
	}
	else
	{
		$("form#createNewQuestForm table.shipInput").append("<tr>"+text+"</tr>");
	}
	return false;
}

function addQuestResearchInput()
{
	var text = $("form#createNewQuestForm table.researchInput tr.template").html();
	if($("form#createNewQuestForm table.researchInput tr.template").css("display") == "none")
	{
		$("form#createNewQuestForm table.researchInput tr.template").remove();
		$("form#createNewQuestForm table.researchInput").append('<tr class="template">'+text+'</tr>');
	}
	else
	{
		$("form#createNewQuestForm table.researchInput").append("<tr>"+text+"</tr>");
	}
	return false;
}

function addQuestBuildingInput()
{
	var text = $("form#createNewQuestForm table.buildingInput tr.template").html();
	if($("form#createNewQuestForm table.buildingInput tr.template").css("display") == "none")
	{
		$("form#createNewQuestForm table.buildingInput tr.template").remove();
		$("form#createNewQuestForm table.buildingInput").append('<tr class="template">'+text+'</tr>');
	}
	else
	{
		$("form#createNewQuestForm table.buildingInput").append("<tr>"+text+"</tr>");
	}
	return false;
}

function addRewardShipInput(link)
{
	var text = $(link).next().html();
	$(link).parent().append('<div class="rewardShipInput">'+text+'</div>');
	return false;
}

function createNewReward()
{
	if($("form#createNewRewardForm input[name=name]").val() == "")
	{
		alert("No name specified!");
		return false;
	}
	
	var data = $("form#createNewRewardForm").serialize();
	$.post(url+"quest/createNewReward", data, function(data) {
		var result = data.split("|split|");
		$("select[name=reward]").append(result[1]);
		$("#existingRewards > table").append(result[2]);
		setReward(result[0]);
		return false;
	});
}

function deleteReward(link)
{
	var tableRow = $(link).parent().parent();
	var rewardID = tableRow.children("td:first-child").text();
	$.get(url+"quest/deleteReward/"+rewardID);
	tableRow.remove();
	$("select[name=reward] option[value="+rewardID+"]").remove();
	return false;
}

function deleteRow(button)
{
	var parent = $(button).parent();
	while(!parent.is("tr"))
	{
		parent = parent.parent();
	}
	if(parent.is(".template"))
		parent.hide();
	else
		parent.remove();
}

function descriptions_showQuests()
{
	$("div#factions").hide();
	$("div#rewards").hide();
	$("div#quests").show();
}


function descriptions_showFactions()
{
	$("div#rewards").hide();
	$("div#quests").hide();
	$("div#factions").show();
}

function descriptions_showRewards()
{
	$("div#quests").hide();
	$("div#factions").hide();
	$("div#rewards").show();
}