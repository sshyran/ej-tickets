<?php
require_once( "../lib/prepend.php" );
?>

<html>
<head><title>Login</title></head>
<body>
<?php

if( ! array_key_exists("login", $_REQUEST) )
{
    ?>
    <form action="<?php print $_SERVER["SCRIPT_NAME"]; ?>" method="post">
        <table cellspacing="10"><tr><td colspan="2">
            <table cellspacing="0" cellpadding="0" border="0"><tr><td><!-- img style="padding: 0em 0.65em 0em 0.5em" src="../images/sprite-man.gif" border="0"/ --></td>
            <td><b>Ticketing Login</b></td></tr></table>
        </td></tr><tr><td>
        <input style="padding: 3px; width: 210px" name="login" title="Login" value="Enter Your Name" onFocus="if(this.value='Enter Your Name')this.value='';"/>
        </td></tr><tr><td>
        <input style="padding: 3px; width: 210px" name="password" type="password" title="Password"/>
        </td><td>
        <input type="submit" name="submit" value="Login"/>
        </td></tr><tr><td>
            <table cellspacing="0" cellpadding="0" border="0"><tr><td><input name="rememberMyPassword" type="checkbox" id="rememberMyPassword"/></td>
            <td><label for="rememberMyPassword">&nbsp;Remember My Password</label></td></tr></table>
        </td></tr></table>
    </form>
    <?php
    #PageContent::display( "login-invalid" );
}
else
{

    $db = new Database();

    $user_id = $db->value("SELECT user_id FROM users WHERE login = %login% AND password = %password%", $_REQUEST );
    if( ! $user_id )
    {
        PageContent::display( "login-invalid", "<p>Sorry, invalid login.  Please try again.</p>" );
    }
    else
    {
        if( array_key_exists( "type", $_POST ) )
        {
            $role_id = $db->value("SELECT role_id FROM roles WHERE label = %type%", $_POST);
            if( $user_id && $role_id )
            {
                $db->insert( "user_roles", array( "user_id" => $user_id, "role_id" => $role_id ) );
            }
        }

        $rows = $db->rows( "SELECT user_id, login, role_id, roles.label AS role FROM users 
            RIGHT JOIN user_roles USING(user_id)
            LEFT JOIN roles USING(role_id)
            WHERE login = %login% AND password = %password%", $_REQUEST );

        if( sizeof( $rows ) )
        {
            PageContent::display( "login-thanks" );

            $auth = array();
            $auth["roles"] = array();
            print "<ul>";
            foreach( $rows as $row )
            {
                $auth["user_id"] = $row["user_id"];
                $auth["login"] = $row["login"];
                $auth["roles"][$row["role"]] = $row["role_id"];
                printf('<li>Go to your <a href="home-%s.php">%s homepage</a>.</li>',
                    strtolower( $row["role"] ), strtolower( $row["role"] ) );
            }
            print "</ul>";
            $_SESSION["auth"] = $auth;
        }
        else
        {
            // no roles for the user, so we are going to ASK
            print "
            <table width='100%'>
            <tr><td>
            <h1>Login as <big>CLIENT</big></h1>
            <form action='$_SERVER[SCRIPT_NAME]' method='post'>
            <p>Do you wish to submit leads?</p>
            <input type='submit' value='Continue as CLIENT'/>
            <input type='hidden' name='login' value=\"$_REQUEST[login]\"/>
            <input type='hidden' name='password' value=\"$_REQUEST[password]\"/>
            <input type='hidden' name='type' value=\"Client\">
            </form>
            </td><td valign='top'>
            <form action='$_SERVER[SCRIPT_NAME]' method='post'>
            <h1>Login as <big>VENDOR</big></h1>
            <p>Do you wish to collect leads?</p>
            <input type='submit' value='Continue as VENDOR'/>
            <input type='hidden' name='login' value=\"$_REQUEST[login]\"/>
            <input type='hidden' name='password' value=\"$_REQUEST[password]\"/>
            <input type='hidden' name='type' value=\"Vendor\">
            </form>
            </td></tr></table>";
        }
    }
}

?>
</body>
</html>
