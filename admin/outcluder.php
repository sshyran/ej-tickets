<?php

function do_outcluder_work()
{
    if( array_key_exists( "REBUILD", $_GET ) )
    {
        $db = new Database();
        $roles = array( "Administrator", "Vendor", "Client" );
        foreach( $roles as $role )
        {
            $value = $db->value( "SELECT COUNT(*) FROM roles WHERE label = %role%", array( "role" => $role ) );
            if( ! $value )
            {
                $db->query( "INSERT INTO roles (label) VALUES ('$role')");
            }
        }
    }
}
do_outcluder_work();

#require_once("DbAssignee.php");
