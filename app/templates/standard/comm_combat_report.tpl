<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>{lang}ASSAULT{/lang}: {@planetName}{config=TITLE_GLUE}{@pageTitle}</title>
<meta http-equiv="content-type" content="text/html; charset={@charset}" />

<link rel="shortcut icon" href="{const}HTTP_HOST.REQUEST_DIR{/const}favicon.ico" type="image/x-icon" />
{if[{var=CSS_FILES} != ""]}<link rel="stylesheet" type="text/css" href="{const}BASE_URL{/const}css/?f={@CSS_FILES}"/>{/if}
{if[{var=JS_FILES} != ""]}<script type="text/javascript" src="{const}BASE_URL{/const}js/?f={@JS_FILES}"></script>{/if}
</head>
<body>
{hook}FrontHtmlBegin{/hook}
{@report}
{hook}FrontHtmlEnd{/hook}
</body>
</html>