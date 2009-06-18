<?php

    ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.'../lib');
    require_once 'init.php';
    require_once 'data.php';
    
    $C = new Context(DB_DSN, GUARDIAN_API_KEY, FLICKR_API_KEY, $_COOKIE['visitor']);
    $C->setCookie();
    
    $success_message = false;
    $error_message = false;
    
    $action = $_POST['action'] ? $_POST['action'] : null;
    $woe_id = is_numeric($_POST['woe']) ? intval($_POST['woe']) : null;
    $article_id = is_numeric($_POST['article']) ? intval($_POST['article']) : null;
    
    if(!$action || !$woe_id || !$article_id) {
        $error_message = 'Missing or bad action, WOE ID, or article ID.';
    
    } else {
        switch($action)
        {
            case 'add':
                $C->dbh->query('START TRANSACTION');
                $added = add_point($C, $article_id, $woe_id);
                $C->dbh->query('COMMIT');
                
                if($added) {
                    $success_message = "Article {$article_id} and place {$woe_id} are now connected.";

                    /*
                    $url = new Net_URL('http://'.get_domain_name().get_base_dir().'/point.php');
                    $url->addQueryString('article', $article_id);
                    $url->addQueryString('woe', $woe_id);
                
                    header('Location: '.$url->getURL());
                    */
                
                } else {
                    $error_message = "Article {$article_id} and place {$woe_id} are already connected.";
                }

                break;

            case 'remove':
                $C->dbh->query('START TRANSACTION');
                $removed = remove_point($C, $article_id, $woe_id);
                $C->dbh->query('COMMIT');
                
                if($removed) {
                    $success_message = "Article {$article_id} and place {$woe_id} are no longer connected.";
                
                } else {
                    $error_message = "Article {$article_id} and place {$woe_id} were not connected.";
                }

                break;

            default:
                $error_message = "I'm not sure what action '{$action}' means.";
                break;
        }
    }
    
    header("Content-Type: text/plain; charset=UTF-8");
    
    if($success_message) {
        echo "{$success_message}\n";

    } elseif($error_message) {
        header('HTTP/1.1 400');
        echo "{$error_message}\n";
    }

?>
