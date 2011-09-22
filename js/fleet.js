/**
 * Fleet related JavaScript functions.
 * 
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll"
 * @version $Id: fleet.js 8 2010-10-17 20:55:04Z secretchampion $
 */

var outboundInterval = 0;
var returnInterval = 0;

function selectShips()
{
	for(i = 0; i < fleet.length; i++)
	{
		var quantity = quantities[fleet[i]];
		$('#ship_'+fleet[i]).val(quantity);
	}
}

function deselectShips()
{
	for(i = 0; i < fleet.length; i++)
	{
		$('#ship_'+fleet[i]).val(0);
	}
}

function getFlyTime(distance, maxspeed, speed)
{
	var time = Math.round((35000 / speed) * Math.sqrt(distance * 10 / maxspeed) + 10);
	if(gamespeed > 0)
	{
		time *= gamespeed;
		time = Math.round(time);
	}
	return time;
}

function getFlyConsumption(basicConsumption, distance, speed)
{	
	return Math.round(basicConsumption * distance / 35000 * ((speed / 10) + 1) * ((speed / 10) + 1)) + 1;
}

function getDistance(galaxy, system, pos)
{
	if(galaxy - oGalaxy != 0)
	{
		return Math.abs(galaxy - oGalaxy) * 20000;
	}
	else if(system - oSystem != 0)
	{
		return Math.abs(system - oSystem) * 5 * 19 + 2700;
	}
	else if(pos - oPos != 0)
	{
		return Math.abs(pos - oPos) * 5 + 1000;
	}
	return 5;
}

function arrivalTimer(timeId, time)
{
	var _now = new Date();
	_now.setTime(_now.getTime()+time);
	$(timeId).text(_now.toLocaleString());
}

function rebuild()
{
	window.clearInterval(outboundInterval);
	window.clearInterval(returnInterval);
	
	// Get vars
	var speed = $('#speed').val() / 10;
	var galaxy = $('#galaxy').val();
	var system = $('#system').val();
	var pos = $('#position').val();

	// Validate it
	if(speed < 0.1) { speed = 0.1; }
	else if(speed > 10) { speed = 10; }
	if(galaxy < 1) { galaxy = 1; }
	else if(galaxy > maxGalaxy) { galaxy = maxGalaxy; }
	if(system < 1) { system = 1; }
	else if(system > maxSystem) { system = maxSystem; }
	if(pos < 1) { pos = 1; }
	else if(pos > maxPos) { pos = maxPos; }
	
	// Calculations
	var distance = getDistance(galaxy, system, pos);
	var consumption = getFlyConsumption(basicConsumption, distance, speed);
	var time = getFlyTime(distance, maxspeed, speed);
	var outboundFlight = now.getTime() + time*1000;
	var returnFlight = now.getTime() + time*2000;
	
	// Write it
	$('#time').text(secToString(time));
	$('#fuel').text(fNumber(consumption));
	$('#distance').text(fNumber(distance));
	$('#capicity').text(fNumber(capicity - consumption));
	$('#speed').val(speed * 10);
	$('#galaxy').val(galaxy);
	$('#system').val(system);
	$('#position').val(pos);
	now.setTime(outboundFlight);
	$('#outbound-flight').text(now.toLocaleString());
	now.setTime(returnFlight);
	$('#return-flight').text(now.toLocaleString());
	
	outboundInterval = setInterval("arrivalTimer('#outbound-flight', "+time*1000+")", 1000);
	returnInterval = setInterval("arrivalTimer('#return-flight', "+(time*2000)+")", 1000);
	
	// Format
	if(capicity - consumption > 0)
	{ 
		$('#capacity').addClass('true');
		$('#fuel').addClass('true');
		$('#capacity').removeClass('false');
		$('#fuel').removeClass('false');
	}
	else
	{
		$('#capacity').removeClass('true');
		$('#fuel').removeClass('true');
		$('#capacity').addClass('false');
		$('#fuel').addClass('false');
	}
}

function fNumber(num)
{
	return number_format(num, 0, decPoint, thousandsSep);
}

function setAllResources()
{
	if(capacity < tMetal) { setMetal = capacity; }
	else { setMetal = tMetal; }
	setMaxRes('metal', setMetal);
	
	if(capacity < tSilicon) { setSilicon = capacity; }
	else { setSilicon = tSilicon; }
	setMaxRes('silicon', setSilicon);
	
	if(capacity < tHydrogen) { setHydrogen = capacity; }
	else { setHydrogen = tHydrogen; }
	setMaxRes('hydrogen', setHydrogen);
}

function setNoResources()
{
	setMinRes('metal');
	setMinRes('silicon');
	setMinRes('hydrogen');
}

function setMinRes(id)
{
	newVal = getValueFromId(id, true);
	if(id == "metal")
	{
		tMetal += newVal;
	}
	else if(id == "silicon")
	{
		tSilicon += newVal;
	}
	else
	{
		tHydrogen += newVal;
	}
	capacity += newVal;
	$('#'+id).val(0)
	setRest();
}

function setMaxRes(id, value)
{
	value = parseInt(value);
	obj = document.getElementById(id);
	if(value > capacity)
	{
		value = capacity;
		capacity = 0;
	}
	else { capacity -= value; }
	if(id == 'metal')
	{
		tMetal -= value;
	}
	else if(id == 'silicon')
	{
		tSilicon -= value;
	}
	else if(id == 'hydrogen')
	{
		tHydrogen -= value;
	}
	add = getValueFromId(id, true);
	obj.value = value + add;
	
	setRest();
}

function renewTransportRes()
{
	var inMetal = getValueFromId('metal', true);
	var inSilicon = getValueFromId('silicon', true);
	var inHydrogen = getValueFromId('hydrogen', true);
	
	tMetal = outMetal - inMetal;
	tSilicon = outSilicon - inSilicon;
	tHydrogen = outHydrogen - inHydrogen;
	capacity = sCapacity - inMetal - inSilicon - inHydrogen;
	
	setRest();
}

function setFormation(formation, galaxy, system, position, type)
{
	$("input[name=formation]").val(formation);
	setCoordinates(galaxy, system, position, type);
}

function setCoordinates(galaxy, system, position, type)
{
	$('#galaxy').val(galaxy);
	$('#system').val(system);
	$('#position').val(position);
	document.getElementById('targetType').selectedIndex = type;
	rebuild();
}

function getValueFromId(id, integer)
{
	ret = $('#'+id).val();
	if(integer)
	{
		ret = parseInt(ret);
		if(isNaN(ret)) { ret = 0; }
		return ret;
	}
	return ret;
}

function setRest()
{
	obj = $('#rest');
	obj.text(fNumber(capacity));
	if(capacity < 0)
	{
		obj.addClass('false');
		obj.removeClass('true');
	}
	else
	{
		obj.addClass('true');
		obj.removeClass('false');
	}
}