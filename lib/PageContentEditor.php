<?php
require_once("../lib/prepend.php");
$_TEMPLATE = "admin.tpl";

$label = "none";
if( array_key_exists( "label", $_REQUEST ) )
{
    $label = $_REQUEST["label"];
}

print "<html><head><title>Manage Content</title></head><body>";

print "<center>";
print "<h1>Content Management</h1>";
print "<h2>Content block for <u>$label</u></h2>";

if( array_key_exists( "content", $_REQUEST ) )
{
    $db = new Database;
    $sql = "SELECT * FROM pages_content WHERE label = %label% ORDER BY version DESC limit 1";
    $row = $db->row( $sql, array( "label" => $_REQUEST["label"] ) );
    if( ! $row )
    {
        $db->insert( "pages_content", array( "label" => $_REQUEST["label"] ) );
        $row = $db->row( $sql, array( "label" => $_REQUEST["label"] ) );
    }
    unset( $row["page_id"] );
    $row["version"]++;
    $row["content"] = $_REQUEST["content"];
    $db->insert( "pages_content", $row );
    print "<h3>Changes were saved!</h3>";
}

$link = $_SERVER["HTTP_REFERER"];
if( array_key_exists( "link", $_REQUEST ) )
{
    $link = $_REQUEST["link_64"];
}
elseif( array_key_exists( "link_64", $_REQUEST ) )
{
    $link = base64_decode( $_REQUEST["link_64"] );
}
printf('<p><i><b>Content linked from:</b><br/><a href="%s">%s</a></i></p>', $link, $link );

?>

<script type="text/javascript" src="../js/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
tinyMCE.init({
// General options
mode : "textareas",
theme : "advanced",
plugins : "safari,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,imagemanager,filemanager",
 
// Theme options
theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertfile,insertimage",
theme_advanced_toolbar_location : "top",
theme_advanced_toolbar_align : "left",
theme_advanced_statusbar_location : "bottom",
theme_advanced_resizing : true,
 
// Example content CSS (should be your site CSS)
content_css : "css/example.css",
 
// Drop lists for link/image/media/template dialogs
template_external_list_url : "js/template_list.js",
external_link_list_url : "js/link_list.js",
external_image_list_url : "js/image_list.js",
media_external_list_url : "js/media_list.js",
 
// Replace values for the template plugin
template_replace_values : {
username : "Some User",
staffid : "991234"
}
});
</script>

<form method="post" action="<?php print $_SERVER["SCRIPT_NAME"]; ?>">
<input type='hidden' name='link_64' value='<?php print base64_encode( $link ) ?>' />
<input type='hidden' name='label' value="<?php print $label; ?>" />
<textarea name="content" style="width:100%; height: 30em;"><?php 
    print htmlspecialchars( PageContent::get( $label ) );
    ?></textarea>
<br/>
<input type="submit" value="Save Content"/>
</form>
</center>

<?php
print "</body></html>";
