<?php

function do_outcluder_work()
{
    if( array_key_exists( "REBUILD", $_GET ) )
    {
        $db = new Database();
        $roles = array( "Administrator", "Client", "Programmer" );
        foreach( $roles as $role )
        {
            $value = $db->value( "SELECT COUNT(*) FROM roles WHERE label = %role%", array( "role" => $role ) );
            if( ! $value )
            {
                $db->query( "INSERT INTO roles (label) VALUES ('$role')");
            }
        }
        $set = array( "Open", "Approved", "Working", "Testing", "Closed" );
        foreach( $set as $word )
        {
            $value = $db->value( "SELECT COUNT(*) FROM status WHERE label = %word%", array( "word" => $word ) );
            if( ! $value )
            {
                $db->query( "INSERT INTO status (label) VALUES ('$word')");
            }
        }
    }
}
do_outcluder_work();

#require_once("DbAssignee.php");
