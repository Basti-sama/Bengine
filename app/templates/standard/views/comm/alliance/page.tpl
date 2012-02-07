<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>{lang}ALLIANCE_PAGE{/lang}: {@tag}{config=TITLE_GLUE}{@pageTitle}</title>
<meta http-equiv="content-type" content="text/html; charset={@charset}" />
<link rel="shortcut icon" href="{const}BASE_URL{/const}favicon.ico" type="image/x-icon"/>
{if[{var=CSS_FILES} != ""]}<link rel="stylesheet" type="text/css" href="{const}BASE_URL{/const}css/?f={@CSS_FILES}"/>{/if}
{if[{var=JS_FILES} != ""]}<script type="text/javascript" src="{const}BASE_URL{/const}js/?f={@JS_FILES}"></script>{/if}
</head>
<body>
	{hook}FrontHtmlBegin{/hook}
	<table class="ntable center-table">
		<colgroup>
			<col width="30%"/>
			<col width="70%"/>
		</colgroup>
		<tr>
			<th colspan="2">{lang}ALLIANCE{/lang}</th>
		</tr>
		{if[{var=logo} != ""]}<tr>
			<td colspan="2" class="center">{@logo}</td>
		</tr>{/if}
		<tr>
			<td>{lang}TAG{/lang}</td>
			<td>{@tag}</td>
		</tr>
		<tr>
			<td>{lang}NAME{/lang}</td>
			<td>{@name}</td>
		</tr>
		<tr>
			<td>{lang}MEMBER{/lang}</td>
			<td>{@member}</td>
		</tr>
		<tr>
			<td colspan="2">{@textextern}</td>
		</tr>
		{if[{var=homepage} != "" && {var=showHomepage}]}
		<tr>
			<td>{lang}HOMEPAGE{/lang}</td>
			<td>{@homepage}</td>
		</tr>
		{/if}
	</table>
	{hook}FrontHtmlEnd{/hook}
</body>
</html>