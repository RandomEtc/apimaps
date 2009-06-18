<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title>Untitled</title>
</head>
<body>
    <ul>
        {foreach from=$points item="point"}
            <li>
                <a href="points.php?article={$point.article_id|escape}">{$point.article.title|escape}</a>
                is in <a href="points.php?woe={$point.woe_id|escape}">{$point.place_name|escape}</a>
            </li>
        {/foreach}
    </ul>
</body>
</html>
