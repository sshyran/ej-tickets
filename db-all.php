<?php
require_once("lib/prepend.php");

//$msg = "";
//if (array_key_exists("login", $_REQUEST) && array_key_exists("password", $_REQUEST)) {
	//$db = new Database();
	//$assignee = $db->row("SELECT * FROM assignee WHERE login = %login% AND password = %password%;", $_POST);
	//if ($assignee == false) {
		//$msg = "Invalid login credentials!";
	//} else {
		//$_SESSION["assignee"] = $assignee;
		//header("Location: working-home.php");
		//exit(0);
	//}
//}

$table = str_replace(
	array("db-", ".php"),
	array("", ""),
	basename($_SERVER["SCRIPT_NAME"]) );

print "<h1>Database Management</h1>";
print "<h2>" . ucfirst($table) . "</h2>";
$editor = new DbEditor( new Db( $table ) );

