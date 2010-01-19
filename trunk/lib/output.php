<?php

// Handling templating and high-level output control.

$smartypath = dirname( dirname( __FILE__ ) ) . "/var/smarty/Smarty.class.php";
require_once( $smartypath );

// Going to do this here so we have a global smarty variable in case we need it somewhere
$SMARTY = new Smarty();

// Override this variable switch templates!
$TEMPLATE = "default.tpl";

function shutdown_output()
{
    global $SMARTY;
    global $TEMPLATE;

    $smartydir = dirname( dirname( __FILE__ ) ) . "/smarty";

    $SMARTY->template_dir = $smartydir;
    $SMARTY->compile_dir = $smartydir.'/templates_c';
    $SMARTY->cache_dir = $smartydir.'/cache';
    $SMARTY->config_dir = $smartydir.'/configs';

    $str = ob_get_clean();

    $regex_head = '/<head[^>]*>(.*?)<\/head>/is';
    $regex_body = '/<body[^>]*>(.*?)<\/body>/is';

    $error = 2;

    $matches = array();
    preg_match( $regex_body, $str, $matches );
    if( sizeof( $matches ) > 1 )
    {
        $SMARTY->assign( 'body', $matches[1] );
        $error--;
    }
    
    $matches = array();
    preg_match( $regex_head, $str, $matches );
    if( sizeof( $matches ) > 1 )
    {
        $SMARTY->assign( 'head', $matches[1] );
        $error--;
    }

    if( ! $error )
    {
        print $SMARTY->fetch( $TEMPLATE );
    }
    else
    {
        print "Unable to find head or body tags in:<hr/>";
        print "<pre>"; print str_replace( "<", "&lt;", print_r($str, true) );
    }
}

ob_start();
register_shutdown_function( "shutdown_output" );

