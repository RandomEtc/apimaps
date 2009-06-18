<?php

    require_once 'Smarty/Smarty.class.php';

   /**
    * @return   Smarty  Locally-usable Smarty instance.
    */
    function get_smarty_instance()
    {
        $s = new Smarty();

        $s->compile_dir = join(DIRECTORY_SEPARATOR, array(dirname(__FILE__), '..', 'templates', 'cache'));
        $s->cache_dir = join(DIRECTORY_SEPARATOR, array(dirname(__FILE__), '..', 'templates', 'cache'));

        $s->template_dir = join(DIRECTORY_SEPARATOR, array(dirname(__FILE__), '..', 'templates'));
        $s->config_dir = join(DIRECTORY_SEPARATOR, array(dirname(__FILE__), '..', 'templates'));
        
        $s->assign('domain', get_domain_name());
        $s->assign('base_dir', get_base_dir());
        $s->assign('base_href', get_base_href());
        $s->assign('constants', get_defined_constants());
        $s->assign('request', array('get' => $_GET));
        
        return $s;
    }
    
   /**
    * @param    string  $format     "text", "xml", etc.
    * @param    string  $default    Default format
    * @return   array   Format, mime-type
    */
    function parse_format($format, $default)
    {
        $types = array('html' => 'text/html',
                       'text' => 'text/plain',
                       'php'  => 'application/php',
                       'atom' => 'application/atom+xml',
                       'rss'  => 'application/rss+xml',
                       'json' => 'text/json',
                    // 'xspf' => 'application/xspf+xml',
                       'xml'  => 'text/xml',
                    // 'jpg'  => 'image/jpeg',
                    // 'png'  => 'image/png',
                    // 'm3u'  => 'audio/x-mpegurl',
                       'js'   => 'text/javascript');

        $format = empty($format) ? $default : $format;
        
        return array($format, $types[$format]);
    }
    
    function sanitize_js_callback($callback)
    {
        return preg_replace('/[^\.\w]+/', '', $callback);
    }
    
    function get_domain_name()
    {
        if(php_sapi_name() == 'cli')
            return CLI_DOMAIN_NAME;
        
        return $_SERVER['SERVER_NAME'];
    }
    
    function get_base_dir()
    {
        if(php_sapi_name() == 'cli')
            return CLI_BASE_DIRECTORY;
        
        return rtrim(dirname($_SERVER['SCRIPT_NAME']), DIRECTORY_SEPARATOR);
    }
    
    function get_base_href()
    {
        if(php_sapi_name() == 'cli')
            return '';
        
        $query_pos = strpos($_SERVER['REQUEST_URI'], '?');
        
        return ($query_pos === false) ? $_SERVER['REQUEST_URI']
                                      : substr($_SERVER['REQUEST_URI'], 0, $query_pos);
    }
    
    function die_with_code($code, $message)
    {
        header("HTTP/1.1 {$code}");
        die($message);
    }

?>