<?php
error_reporting(E_ALL);
class Blobber implements ArrayAccess {
    private $container = array();
    public function __construct() {
        $this->container = array(
            "leadCategoryList" => true,
            "latestBlogEntries" => true,
            "sessionAuthLogin" => true,
            "sessionCurrentUserName" => true,
            "dropdownLeadCategories" => true,
            "leadsDisplay8" => true,
            "yourBudget" => true
        );
    }
    public function offsetSet($offset, $value) {
        // Can be used to pass arguments to the blob generator
        $this->container[$offset] = $value;
    }
    public function offsetExists($offset) {
        return isset($this->container[$offset]);
    }
    public function offsetUnset($offset) {
        unset($this->container[$offset]);
    }
    public function offsetGet($offset) {
        if( ! isset( $this->container[$offset] ) )
        {
            return null;
        }

        $s = ""; // return this
        $db = new Database;
        switch( $offset ) 
        {
        case "yourBudget":  
            $b = "$0.00";
            if( array_key_exists( "auth", $_SESSION ) )
            {
                $value = $db->value( "SELECT budget FROM budgets WHERE user_id = %user_id% ORDER BY created DESC LIMIT 1", $_SESSION["auth"] );
                if( $value )
                {
                    $b = "$" . sprintf("%0.2f", ($value/100) );
                }
            }
            $s .= $b;
            break;
        case "leadsDisplay8":
            $sql = "SELECT * FROM leads 
                LEFT JOIN lead_category_map USING( lead_id )
                LEFT JOIN lead_categories USING( category_id )
                ORDER BY leads.created DESC LIMIT 8";
            $rows = $db->rows( $sql );
            $s .= "<table width='100%'><tr>";
            $i = 0;
            shuffle( $rows );
            foreach( $rows as $lead )
            {
                $fields = array();
                $sql = "SELECT label, content FROM lead_fields WHERE lead_id = '$lead[lead_id]'";
                foreach( $db->rows( $sql ) as $row )
                {
                    $fields[$row["label"]] = $row["content"];
                }
                $title = preg_replace("/ [^ ]*$/", "", substr($fields["project_description"],0,30) ) . "...";
                $body = preg_replace("/ [^ ]*$/", "", substr($fields["project_description"],0,100) ) 
                    . " &nbsp;&nbsp;(" . date("n/j/y", strtotime( $lead["created"] ) ) . ")";

                $s .= "<td width='50%' valign='top'>";
                $s .= "<div style='line-height: 150%; padding-top: 1.5em; padding-right: 0.8em'>";
                $s .= "<b>$title</b>";

                $s .= "<div style='font-size: smaller'>$body</div>";
                $s .= "<div style='float: right; font-size: smaller;'><a href='signup.php'>View Lead</a></div>";
                $s .= "</div>";
                $s .= "</td>";
                if( ((++$i) % 2) == 0 )
                    $s .= "</tr><tr>";
            }
            $s .= "</tr></table>";
            break;
        case "dropdownLeadCategories":
            $sql = "SELECT * FROM lead_categories ORDER BY sequence";
            $s .= "<select name='lead_categories'>";
            $i = 0;
            foreach( $db->rows( $sql ) as $row )
            {
                $s .= "<option value='$row[category_id]'>$row[label]</option>";
            }
            $s .= "</select>";
            break;
        case "leadCategoryList":
            $sql = "SELECT * FROM lead_categories";
            $s .= "<table width='100%'><tr>";
            $i = 0;
            foreach( $db->rows( $sql ) as $row )
            {
                $s .= "<td>";
                $s .= "<h2 style='margin: 0px; padding: 0px'>$row[label]</h2>"
                    . "<a href='request-submit.php?category_id=$row[category_id]'>Category " . ++$i . "</a>";
                $s .= "</td>";
            }
            $s .= "</tr></table>";
            break;
        case "latestBlogEntries":
            $s .= "<br/><img src='../images/temp-latestBlogEntries.gif'/>";
            break;
        case "sessionAuthLogin":
            $s = "[unknown user]";
            if( array_key_exists( "auth", $_SESSION ) )
            {
                $s = $_SESSION["auth"]["login"];
            }
            break;
        case "sessionCurrentUserName":
            $s = "[unknown user]";
            if( array_key_exists( "auth", $_SESSION ) )
            {
                $s = $db->value( "SELECT label FROM users WHERE user_id = %user_id%", $_SESSION["auth"] );
            }
            break;
        default:
            $s = "Unknown blob: $offset";
        }
        return $s;
    }
}

class PageContent
{
    public static function get( $str, $default=null )
    {
        $db = new Database();

        $sql = "SELECT content FROM pages_content WHERE label = %label% ORDER BY version DESC limit 1";
        $content = $db->value( $sql, array( "label" => $str ) );

        if( ! $content )
        {
	    if( $default )
            {
	        $content = $default;
	    }
	    else
	    {
                $content = "<b><font color='red'><small>No content block for '$str' exists yet!</small></font></b>";
	    }
        }

        return $content;
    }

    public static function display( $str, $default=null )
    {
        $editLink = sprintf( '<a class="pageContentEditLink" href="../lib/PageContentEditor.php?label=%s">edit</a>',
            urlencode( $str ) );
        print $editLink;

        $out = self::get( $str, $default );
        //if( strpos( $out, "{" ) !== FALSE )
        //{
            //global $SMARTY;
//
//            $SMARTY->assign( 'blobLeadCategoryList', self::generate( "blobLeadCategoryList" ) );
            //$SMARTY->assign( 'blob', new Blobber );
        //}
        print $out;
    }
}

$DATABASESCHEMA["pages_content"] =
    "CREATE TABLE pages_content (
        page_id INT NOT NULL AUTO_INCREMENT,
        label TINYTEXT DEFAULT '',
        version INT DEFAULT 1,
        content TEXT DEFAULT '',
        PRIMARY KEY(page_id)
        );";
