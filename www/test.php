<?php

    ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.'../lib');
    require_once 'init.php';
    require_once 'data.php';
    
    $C = new Context(DB_DSN, GUARDIAN_API_KEY, FLICKR_API_KEY);
    
    header("Content-Type: text/plain; charset=UTF-8");
    //print_r(guardian_article_search($C, 'Oakland'));
    //print_r(flickr_place_find($C, 'Oakland'));
    print_r(flickr_place_info($C, 3534));

?>
