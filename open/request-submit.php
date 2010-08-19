<?php
require_once( "../lib/prepend.php" );
?>

<html>
<head><title>Submit a Request</title></head>
<body>
<?php
if( sizeof( $_POST ) )
{
    $db = new Database;
    $key = base64_encode( rand() );
    $db->insert( "leads", array("label" => $key) );
    $lead_id = $db->value( "SELECT lead_id FROM leads WHERE label = '$key'" );

    $label = "";
    foreach( array( "company_name", "first_name", "last_name" ) as $field )
    {
        if( strlen( $label ) )
            $label .= " / ";
        if( array_key_exists( $field, $_REQUEST ) )
        {
            $label .= $_REQUEST[$field];
        }
    }
    $db->update( "leads", array( "lead_id" => $lead_id, "label" => $label ) );

    $db->insert( "lead_category_map", array( "lead_id"=>$lead_id, "category_id"=>$_REQUEST["lead_categories"] ) );

    unset( $_POST["x"] );
    unset( $_POST["y"] );
    foreach( $_POST as $label => $content )
    {
        if( strlen( $content ) )
        {
            $db->insert( "lead_fields", array( "lead_id" => $lead_id, "label" => $label, "content" => $content ) );
        }
    }
    PageContent::display( "request-thankyou" );
}
else
{
?>
    <?php PageContent::display( "request-submit" ); ?>
    <script language="javascript">
    FillField( 'lead_categories', GETparam( "category_id" ) );
    </script>
<?php
}
?>
</body>
</html>
