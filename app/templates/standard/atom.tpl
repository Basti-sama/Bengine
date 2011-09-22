<?php echo '<?xml version="1.0" encoding="'.$this->get("charset").'"?>'; ?>
<?php header('content-type: application/atom+xml'); ?>
<feed xmlns="http://www.w3.org/2005/Atom" xml:lang="de">
	<id>{const}BASE_URL{/const}</id>
	<title>{@pageTitle}</title>
	<link rel="self" type="application/atom+xml" href="{@selfUrl}"/>
	<link rel="alternate" type="text/html" href="{@alternateUrl}"/>
	<updated>{time}c{/time}</updated>
	<subtitle>{@title}</subtitle>
	<generator>Recipe PHP5 Framework - Template Engine</generator>
	{foreach[feed]}<entry>
		<id>{loop}link{/loop}</id>
		<title><![CDATA[{loop}title{/loop}]]></title>
		<link rel="alternate" type="text/html" href="{loop}link{/loop}"/>
		<updated>{loop}date_atom{/loop}</updated>
		<summary type="html"><![CDATA[{loop}text{/loop}]]></summary>
		<author><name>{loop=author}</name></author>
	</entry>{/foreach}
</feed>