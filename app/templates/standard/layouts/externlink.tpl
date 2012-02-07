<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>{@pageTitle}</title>
<meta http-equiv="content-type" content="text/html; charset={@charset}" />
<meta http-equiv="Cache-control" content="no-cache" />
<meta http-equiv="pragma" content="no-cache" />

<link rel="shortcut icon" href="{const}HTTP_HOST{/const}{const}REQUEST_DIR{/const}favicon.ico" type="image/x-icon" />
{if[{var=CSS_FILES} != ""]}<link rel="stylesheet" type="text/css" href="{const}BASE_URL{/const}css/?f={@CSS_FILES}"/>{/if}
{if[{var=JS_FILES} != ""]}<script type="text/javascript" src="{const}BASE_URL{/const}js/?f={@JS_FILES}"></script>{/if}
<meta http-equiv="refresh" content="1; URL={@link}" />

</head>
<body>
<div id="forward" class="centered"><a href="{@link}">{lang}EXTERN_LINK{/lang}<br />{@link}</a></div>
</body>
</html>