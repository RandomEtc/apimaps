<?xml version="1.0"?>
<rss version="2.0" xmlns:geo="http://www.w3.org/2003/01/geo/wgs84_pos#" xmlns:georss="http://www.georss.org/georss" xmlns:dc="http://purl.org/dc/elements/1.1/">
  <channel>

    <title></title>
    <description></description>
    <link>http://{$domain}{$base_dir}/data.html</link>
    <dc:publisher></dc:publisher>
    {assign var="created_timestamp" value=$points.0.created|@strtotime}
    <pubDate>{"D, d M Y H:i:s"|@gmdate:$created_timestamp} GMT</pubDate>
    {*
        Array
        (
            [article_id] => 345891271
            [woe_id] => 22661168
            [latitude] => 36.668
            [longitude] => -4.464
            [place_id] => JYeAQpqbA5qhIfDF2w
            [place_path] => /Spain/Andalusia/Guadalmar
            [place_type] => locality
            [place_name] => Guadalmar, Andalusia, Spain
            [created] => 2009-04-14 10:15:30
            [article] => Array
                (
                    [id] => 345891271
                    [title] => Spain's cherished beach bars face axe in environmental crackdown
                    [published] => 2009-04-14
                    [url] => http://www.guardian.co.uk/world/2009/apr/14/chiringuito-spain
                )
        
        )
    *}
    {foreach from=$points item="point"}
      <item>
        {assign var="created_timestamp" value=$point.created|@strtotime}
        <pubDate>{"D, d M Y H:i:s"|@gmdate:$created_timestamp} GMT</pubDate>
        <title>{$point.place_name|escape}: {$point.article.title|escape}</title>
        <description>{$point.place_name|escape}: {$point.article.title|escape}</description>
        <link>{$point.article.url|escape}</link>
        <geo:lat>{$point.latitude|escape}</geo:lat>
        <geo:long>{$point.longitude|escape}</geo:long>
        <georss:point>{$point.latitude|escape} {$point.longitude|escape}</georss:point>
        <guid isPermaLink="false">{$point.article_id|escape}+{$point.woe_id|escape}</guid>
      </item>
    {/foreach}

  </channel>
</rss>
