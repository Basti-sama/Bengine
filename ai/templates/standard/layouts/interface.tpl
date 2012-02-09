{include}"html_header"{/include}
<div id="top">
	<div id="topheader">
		<div class="link_a"><a href="javascript:location.reload(true);">{lang}Refresh{/lang}</a> | <a href="javascript:back();">{lang}Back{/lang}</a> | {link[Logout]}"auth/logout"{/link} | <a href="{const=APP_BASE_URL}">{lang}Page{/lang}</a></div>
	</div>
</div>
<div id="left">
	<div id="menu">
		<div class="mbg">
			<div class="menu_title">{lang}Main_Menu{/lang}</div>
			<ul>
			{foreach[menu]}
				<li>{loop}link{/loop}</li>
			{/foreach}
			</ul>
			{if[$this->templateVars["pluginCount"]]}<div class="menu_title">{lang}Plug_In_Management{/lang}</div>
			<ul>
			{foreach[plugins]}
				<li>{loop}link{/loop}</li>
			{/foreach}
			</ul>{/if}
		</div>
	</div>
</div>
<div id="content">
{include}$template{/include}
</div>
{include}"html_footer"{/include}