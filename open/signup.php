<?php
require_once( "../lib/prepend.php" );
?>

<html>
<head><title>Buy Leads</title></head>
<body>
<?php

if( sizeof( $_POST ) )
{
    $errors = "";
    $db = new Database;
    $sql = "SELECT user_id FROM users WHERE login = %login%";
    if( strlen( $db->value( $sql, $_POST ) ) )
    {
        $errors .= "<li>That login is already taken!  Please try again!</li>";
    }

    if( strlen( $errors ) )
    {
        print "<div style='color: red; font-weight: bold'>$errors</div>";
        PageContent::display( substr( basename( $_SERVER["SCRIPT_NAME"] ), 0, -4 ) );
        
        print '<script language="javascript">';
        foreach( $_POST as $key=> $value )
        {
            print "FillField(\"$key\", \"$value\");\n";
        }
        print '</script>';
    }
    else
    {
        $data = array(
            "label" => sprintf( "%s %s (%s)", $_POST["first_name"], $_POST["last_name"], $_POST["company_name"] ),
            "login" => $_POST["login"],
            "password" => $_POST["password"] );
        $db->insert( "users", $data );
        $user_id = $db->value( "SELECT user_id FROM users WHERE login = %login%", $_POST );

        unset( $_POST["login"] );
        unset( $_POST["password"] );
        unset( $_POST["submit"] );
        foreach( $_POST as $key=> $value )
        {
            if( strlen( $value ) )
            {
                $data = array(
                    "user_id" => $user_id,
                    "label" => $key,
                    "content" => $value );
                $db->insert( "user_fields", $data );
            }
        }

        PageContent::display( "signup-thanks" );
    }
}
else
{
    PageContent::display( substr( basename( $_SERVER["SCRIPT_NAME"] ), 0, -4 ) );
}

?>
</body>
</html>
