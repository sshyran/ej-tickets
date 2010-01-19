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

    $matches = array();
    preg_match( $regex_body, $str, $matches );
    $SMARTY->assign( 'body', $matches[1] );
    
    $matches = array();
    preg_match( $regex_head, $str, $matches );
    $SMARTY->assign( 'head', $matches[1] );

    print $SMARTY->fetch( $TEMPLATE );
}

ob_start();
register_shutdown_function( "shutdown_output" );

