<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="CONTENT-TYPE" content="text/html;charset=utf-8" />
	<meta name="generator" content="Recipe PHP5 Framework - Template Engine" />
	<script type="text/javascript" src="{const}BASE_URL{/const}js/?f=lib/jquery.js,lib/jquery-ui.js,lib/jquery-textarea.js"></script>
	{if[{var=CSS_FILES} != ""]}<link rel="stylesheet" type="text/css" href="{const}BASE_URL{/const}css/?f={@CSS_FILES}"/>{/if}
	{if[{var=JS_FILES} != ""]}<script type="text/javascript" src="{const}BASE_URL{/const}js/?f={@JS_FILES}"></script>{/if}
	<title>Recipe Admin Interface</title>
	<script type="text/javascript">
	//<![CDATA[
	$(function() {
		$('.draggable').draggable({ handle: 'th', snap: '#content h1:first, .draggable', snapMode: 'outer' });
		$("textarea").tabby();
	});

	// Define vars
	var url = "{const=BASE_URL}";
	//]]>
	</script>
</head>
<body>