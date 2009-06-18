<?php

    require_once 'DB.php';
    require_once 'PEAR.php';
    require_once 'JSON.php';
    require_once 'HTTP/Request.php';
    require_once 'Net/URL.php';
    require_once 'output.php';
    
   /**
    * Some server context here: DB connection, memcache, etc.
    */
    class Context
    {
        function Context($db_dsn, $guardian_key, $flickr_key, $visitor_id)
        {
            $this->dbh =& DB::connect($db_dsn);
            $this->guardian_key = $guardian_key;
            $this->flickr_key = $flickr_key;
            
            $this->dbh->query('START TRANSACTION');

            $visitor = get_visitor($this, $visitor_id);
            
            if(!$visitor)
                $visitor = add_visitor($this);

            $this->dbh->query('COMMIT');

            $this->visitor_id = $visitor['id'];
        }
        
        function setCookie()
        {
            setcookie('visitor', $this->visitor_id, time() + 86400 * 30, get_base_dir(), get_domain_name());
        }
        
        function close()
        {
            $this->dbh->disconnect();
        }
    }
    
    if(!function_exists('json_encode'))
    {
        function json_encode($value)
        {
            $json = new Services_JSON();
            return $json->encode($value);
        }
    }
    
    if(!function_exists('json_decode'))
    {
        function json_decode($value)
        {
            $json = new Services_JSON();
            return $json->decode($value);
        }
    }
    
    function intvals($str)
    {
            return preg_match('/^\d+(,\d+)*$/', $str)
                ? array_map('intval', explode(',', $str))
                : null;
    }
    
    function generate_id()
    {
        $chars = 'qwrtypsdfghjklzxcvbnm0123456789';
        $id = '';
        
        while(strlen($id) < 16)
            $id .= substr($chars, rand(0, strlen($chars) - 1), 1);

        return $id;
    }
    
    function get_visitor(&$C, $visitor_id)
    {
        $q = sprintf("SELECT id
                      FROM visitors
                      WHERE id = %s",
                     $C->dbh->quoteSmart($visitor_id));
        
        $res = $C->dbh->query($q);
    
        if(PEAR::isError($res))
            die("DB Error: ".$q);
        
        if($visitor = $res->fetchRow(DB_FETCHMODE_ASSOC))
            return $visitor;
        
        return false;
    }
    
    function get_article(&$C, $article_id)
    {
        $q = sprintf("SELECT id, title, published, url
                      FROM articles
                      WHERE id = %d",
                     $article_id);
        
        $res = $C->dbh->query($q);
    
        if(PEAR::isError($res))
            die("DB Error: ".$q);
        
        if($article = $res->fetchRow(DB_FETCHMODE_ASSOC))
        {
            $article['id'] = intval($article['id']);
            return $article;
        }
        
        return false;
    }
    
    function get_points(&$C, $args)
    {
        $count = is_numeric($args['count']) ? intval($args['count']) : 1000;
        $offset = is_numeric($args['offset']) ? intval($args['offset']) : 0;

        $woe_id = is_int($args['woe_id']) ? $args['woe_id'] : null;
        $woe_ids = is_array($args['woe_ids']) ? $args['woe_ids'] : null;
        $article_id = is_int($args['article_id']) ? $args['article_id'] : null;
        $article_ids = is_array($args['article_ids']) ? $args['article_ids'] : null;
        
        $where = array('1');
        $order = array();
        
        if($woe_id)
            $where[] = sprintf('p.woe_id = %d', $woe_id);

        if($woe_ids)
            $where[] = sprintf('p.woe_id IN (%s)', join(', ', $woe_ids));

        if($article_id)
            $where[] = sprintf('p.article_id = %d', $article_id);

        if($article_ids)
            $where[] = sprintf('p.article_id IN (%s)', join(', ', $article_ids));

        // default sort
        $order[] = 'p.created DESC';
        
        $where_clause = join(' AND ', $where);
        $order_clause = join(', ', $order);
        
        $q = sprintf("SELECT p.article_id, p.woe_id,
                             p.latitude, p.longitude,
                             p.place_id, p.place_path, p.place_type, p.place_name,
                             p.created
                      FROM points AS p
                      WHERE {$where_clause}
                      ORDER BY {$order_clause}
                      LIMIT {$count} OFFSET {$offset}");
        
        $res = $C->dbh->query($q);
    
        if(PEAR::isError($res))
            die("DB Error: ".$q);
        
        $points = array();
    
        while($point = $res->fetchRow(DB_FETCHMODE_ASSOC))
        {
            $point['article'] = get_article($C, $point['article_id']);
            $point['article_id'] = intval($point['article_id']);
            $point['woe_id'] = intval($point['woe_id']);
            $points[] = $point;
        }
        
        return $points;
    }
    
    function get_points_total(&$C, $args)
    {
        $woe_id = is_int($args['woe_id']) ? $args['woe_id'] : null;
        $woe_ids = is_array($args['woe_ids']) ? $args['woe_ids'] : null;
        $article_id = is_int($args['article_id']) ? $args['article_id'] : null;
        $article_ids = is_array($args['article_ids']) ? $args['article_ids'] : null;
        
        $where = array('1');
        
        if($woe_id)
            $where[] = sprintf('p.woe_id = %d', $woe_id);

        if($woe_ids)
            $where[] = sprintf('p.woe_id IN (%s)', join(', ', $woe_ids));

        if($article_id)
            $where[] = sprintf('p.article_id = %d', $article_id);

        if($article_ids)
            $where[] = sprintf('p.article_id IN (%s)', join(', ', $article_ids));

        $where_clause = join(' AND ', $where);
        
        $q = sprintf("SELECT COUNT(*) AS points
                      FROM points AS p
                      WHERE {$where_clause}");
        
        $res = $C->dbh->query($q);
    
        if(PEAR::isError($res))
            die("DB Error: ".$q);
        
        if($count = $res->fetchRow(DB_FETCHMODE_ASSOC))
            return intval($count['points']);

        return false;
    }
    
    function add_log(&$C, $message)
    {
        $q = sprintf("INSERT INTO log
                      SET visitor_id = %s, remote_addr = %s, message = %s",
                     $C->dbh->quoteSmart($C->visitor_id),
                     $C->dbh->quoteSmart($_SERVER['REMOTE_ADDR']),
                     $C->dbh->quoteSmart($message));

        $res = $C->dbh->query($q);
        
        if(PEAR::isError($res)) 
            die_with_code(500, "{$res->message}\n{$q}\n");

        return true;
    }
    
    function add_visitor(&$C)
    {
        while(true)
        {
            $visitor_id = generate_id();
            
            $q = sprintf('INSERT INTO visitors
                          SET id = %s',
                         $C->dbh->quoteSmart($visitor_id));

            error_log(preg_replace('/\s+/', ' ', $q));
    
            $res = $C->dbh->query($q);
            
            if(PEAR::isError($res)) 
            {
                if($res->getCode() == DB_ERROR_ALREADY_EXISTS)
                    continue;
    
                die_with_code(500, "{$res->message}\n{$q}\n");
            }
            
            return get_visitor($C, $visitor_id);
        }
    }
    
    function add_article(&$C, $article_id)
    {
        $article = guardian_article_info($C, $article_id);
        
        if($article === false)
            return false;
        
        $q = sprintf("INSERT INTO articles
                      SET id = %d, point_count = 0",
                     $article_id);

        $res = $C->dbh->query($q);
        
        $already_exists = false;
            
        if(PEAR::isError($res)) 
            if($res->getCode() == DB_ERROR_ALREADY_EXISTS) {
                $already_exists = true;
            
            } else {
                die_with_code(500, "{$res->message}\n{$q}\n");
            }

        if(!$already_exists)
            add_log($C, "Added article:{$article_id}");
        
        $q = sprintf("UPDATE articles
                      SET title = %s, published = %s, url = %s
                      WHERE id = %d",
                     
                     $C->dbh->quoteSmart($article->headline),
                     $C->dbh->quoteSmart(preg_replace('/^(\d\d\d\d-\d\d-\d\d)T\d\d:\d\d:\d\d$/', '\1', $article->publicationDate)),
                     $C->dbh->quoteSmart($article->webUrl),

                     $article_id);

        $res = $C->dbh->query($q);
        
        if(PEAR::isError($res)) 
            die_with_code(500, "{$res->message}\n{$q}\n");

        return true;
    }
    
    function add_point(&$C, $article_id, $woe_id)
    {
        $added_article = add_article($C, $article_id);
        
        if(!$added_article)
            die_with_code(400, "Article {$article_id} does not exist?\n");
        
        $place = flickr_place_info($C, $woe_id);
        
        $q = sprintf("INSERT INTO points
                      SET article_id = %d, woe_id = %d,
                          latitude = %f, longitude = %f,
                          place_id = %s, place_path = %s,
                          place_type = %s, place_name = %s,
                          visitor_id = %s, remote_addr = %s",

                     $article_id, $place->woeid,

                     $place->latitude, $place->longitude,

                     $C->dbh->quoteSmart($place->place_id),
                     $C->dbh->quoteSmart($place->place_url),
                     $C->dbh->quoteSmart($place->place_type),
                     $C->dbh->quoteSmart($place->name),

                     $C->dbh->quoteSmart($C->visitor_id),
                     $C->dbh->quoteSmart($_SERVER['REMOTE_ADDR']));

        $res = $C->dbh->query($q);
        
        if(PEAR::isError($res)) 
        {
            // it already exists, so bail out
            if($res->getCode() == DB_ERROR_ALREADY_EXISTS)
                return false;

            die_with_code(500, "{$res->message}\n{$q}\n");
        }
        
        $q = sprintf("UPDATE articles
                      SET point_count = point_count + 1
                      WHERE id = %d",
                     $article_id);

        $res = $C->dbh->query($q);
        
        if(PEAR::isError($res)) 
            die_with_code(500, "{$res->message}\n{$q}\n");

        add_log($C, "Added point article:{$article_id}, woe:{$woe_id}");

        return true;
    }
    
    function remove_point(&$C, $article_id, $woe_id)
    {
        $q = sprintf("DELETE FROM points
                      WHERE article_id = %d
                        AND woe_id = %d",
                     $article_id,
                     $woe_id);

        $res = $C->dbh->query($q);
        
        if(PEAR::isError($res)) 
            die_with_code(500, "{$res->message}\n{$q}\n");
        
        $res = $C->dbh->query('SELECT ROW_COUNT() AS count');

        if(PEAR::isError($res)) 
            die_with_code(500, "{$res->message}\n{$q}\n");

        $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
        
        if($row['count'] <= 0)
            return false;

        $q = sprintf("UPDATE articles
                      SET point_count = point_count - %d
                      WHERE id = %d",
                     $row['count'],
                     $article_id);

        $res = $C->dbh->query($q);
        
        if(PEAR::isError($res)) 
            die_with_code(500, "{$res->message}\n{$q}\n");

        add_log($C, "Removed point article:{$article_id}, woe:{$woe_id}");

        return true;
    }
    
    function guardian_article_search(&$C, $q)
    {
        $req = new HTTP_Request('http://api.guardianapis.com/content/search?format=json');
        $req->addQueryString('api_key', $C->guardian_key);
        $req->addQueryString('q', $q);
        
        $res = $req->sendRequest();
        
        if(PEAR::isError($res))
            die_with_code(500, "{$res->message}\n");
        
        if($req->getResponseCode() == 200)
            if($response = json_decode($req->getResponseBody()))
                return $response; // array of articles
        
        return false;
    }
    
    function guardian_article_info(&$C, $article_id)
    {
        $req = new HTTP_Request(sprintf('http://api.guardianapis.com/content/item/%d?format=json', $article_id));
        $req->addQueryString('api_key', $C->guardian_key);
        
        $res = $req->sendRequest();
        
        if(PEAR::isError($res))
            die_with_code(500, "{$res->message}\n");
        
        if($req->getResponseCode() == 200)
            if($response = json_decode($req->getResponseBody()))
                if($response->content)
                    return $response->content; // dictionary of article stuff
        
        return false;
    }
    
    function flickr_place_find(&$C, $q)
    {
        $req = new HTTP_Request('http://api.flickr.com/services/rest/?format=json&nojsoncallback=1&method=flickr.places.find');
        $req->addQueryString('api_key', $C->flickr_key);
        $req->addQueryString('query', $q);
        
        $res = $req->sendRequest();
        
        if(PEAR::isError($res))
            die_with_code(500, "{$res->message}\n");
        
        if($req->getResponseCode() == 200)
            if($response = json_decode($req->getResponseBody()))
                if($response->stat == 'ok')
                    return $response->places->place; // array of places
        
        return false;
    }
    
    function flickr_place_info(&$C, $woe_id)
    {
        $req = new HTTP_Request('http://api.flickr.com/services/rest/?format=json&nojsoncallback=1&method=flickr.places.getInfo');
        $req->addQueryString('api_key', $C->flickr_key);
        $req->addQueryString('woe_id', $woe_id);
        
        $res = $req->sendRequest();
        
        if(PEAR::isError($res))
            die_with_code(500, "{$res->message}\n");
        
        if($req->getResponseCode() == 200)
            if($response = json_decode($req->getResponseBody()))
                if($response->stat == 'ok')
                    return $response->place; // one place
        
        return false;
    }

?>