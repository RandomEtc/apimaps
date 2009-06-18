<?php

    ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.'../lib');
    require_once 'init.php';
    require_once 'data.php';
    
    $C = new Context(DB_DSN, GUARDIAN_API_KEY, FLICKR_API_KEY, $_COOKIE['visitor']);
    $C->setCookie();
    
    list($response_format, $response_mime_type) = parse_format($_GET['format'], 'html');

    $woe_id = is_numeric($_GET['woe']) ? intval($_GET['woe']) : null;
    $woe_ids = isset($_GET['woes']) ? intvals($_GET['woes']) : null;
    $article_id = is_numeric($_GET['article']) ? intval($_GET['article']) : null;
    $article_ids = isset($_GET['articles']) ? intvals($_GET['articles']) : null;

    $count = is_numeric($_GET['count']) ? intval($_GET['count']) : null;
    $offset = is_numeric($_GET['offset']) ? intval($_GET['offset']) : 0;

    $js_callback = ($response_mime_type == 'text/javascript' && $_GET['callback']) ? sanitize_js_callback($_GET['callback']) : null;

    if($woe_id && $article_id) {
        $url = new Net_URL('http://'.get_domain_name().get_base_dir().'/point.php');
        $url->addQueryString('article', $article_id);
        $url->addQueryString('woe', $woe_id);
        $url->addQueryString('format', $response_format);
    
        header('Location: '.$url->getURL());
        exit();
    
    } elseif(($article_ids || $woe_ids) && ($article_id || $woe_id)) {
        header('Content-Type: text/plain');
        die_with_code(400, "It's not possible to specify both singular and plural article/WOE ID's.\n");
    
    } else {
        $points = get_points($C, compact('article_id', 'woe_id', 'article_ids', 'woe_ids', 'count', 'offset'));
        $total = get_points_total($C, compact('article_id', 'woe_id', 'article_ids', 'woe_ids'));
        $count = count($points);
    }
    
    $C->close();
    
    header("Content-Type: {$response_mime_type}; charset=UTF-8");
    
    switch($response_format)
    {
        case 'php':
            print serialize(compact('count', 'offset', 'total', 'points'));
            break;

        case 'json':
            echo json_encode(compact('count', 'offset', 'total', 'points'));
            break;

        case 'js':
            if(is_null($js_callback))
                die_with_code(400, "You must provide a javascript callback for format=js.");
        
            printf("%s(%s)\n", $js_callback, json_encode(compact('count', 'offset', 'total', 'points')));
            break;

        default:
            $sm =& get_smarty_instance();

            $sm->assign('total', $total);
            $sm->assign('count', $count);
            $sm->assign('offset', $offset);
            $sm->assign('points', $points);
        
            echo $sm->fetch("points.{$response_format}.tpl");
            break;
    }

?>
