<?php
require_once("../lib/prepend.php");

$table = str_replace(
	array("db-", ".php"),
	array("", ""),
	basename($_SERVER["SCRIPT_NAME"]) );

$_TEMPLATE = "admin.tpl";

print "<html><head><title>DB Management</title></head>";
print "<body>";
print "<h1>Database Management</h1>";
print "<h2>" . join(" ", array_map("ucfirst", explode("_", $table))) . "</h2>";
$editor = new DbEditor( new Db( $table ) );
print "</body></html>";

