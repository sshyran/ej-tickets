<?php
require_once("../lib/prepend.php");

$db = new Database();

$ticket = array();
if( isset( $_REQUEST["ticket"] ) )
{
	$ticket = $_REQUEST["ticket"];
}

if( sizeof( $ticket ) > 2 )
{
	if( strlen( $ticket["project-new"] ) )
	{
		// If a new project is specified, create it, and insert into data array
		$sql = "SELECT project_id FROM projects WHERE user_id = %client_id% AND label = %project-new%";
		$project_id = $db->value( $sql, $ticket );
		if( $project_id === NULL ) 
		{
			$db->insert( "projects", array(
				"user_id" => $ticket["client_id"],
				"label" => $ticket["project-new"] ) );
		}
		$ticket["project_id"] = $db->value( $sql, $ticket );
	}

	$data = array();
    print "<pre>"; print_r($ticket);
	foreach (array("client_id", "project_id", "status_id", "assigned_id") as $key) {
		$data[$key] = $ticket[$key];
	}
	foreach ($_REQUEST["descriptions"] as $description) {
		if (strlen($description)) {
			$data["description"] = $description;
			#print "<pre>"; print_r($data);
			$db->insert("ticket", $data);
		}
	}
}
?>
<html>
<head>
<title>New Ticket</title>
</head>
<body>
<h1>Create New Ticket</h1>

<?php

if( ! isset( $ticket["client_id"] ) )
{
	print '<form action="'.$_SERVER["SCRIPT_NAME"].'" method="GET">';
	print "<table>";
	print "<tr><th align='right'>Client</th><td>";
	print "<select name='ticket[client_id]'>";
	print "<option value=''></option>";
	$client_role = $db->value("SELECT * FROM roles WHERE label = 'Client'");
	$sql = "SELECT user_id, label FROM users LEFT JOIN user_roles USING (user_id) WHERE role_id = '$client_role'";
	foreach ($db->rows( $sql ) as $row) {
		print "<option value='$row[user_id]'>$row[label]</option>";
	}
	print "</select>";
	print "</td></tr>";
	print "<tr><th></th><td><input type='submit' value='OK'/></td></tr>";
	print "</table>";
	print "</form>";
}
else
{
	print '<form action="'.$_SERVER["SCRIPT_NAME"].'" method="GET">';


	print "<table>";
	print "<tr><th align='right'>Client</th><td>";
	printf("<input type='hidden' name='%s' value='%s' />",
		"ticket[client_id]", $ticket["client_id"]);
	$client_name = $db->value("SELECT label FROM users WHERE user_id = %client_id%", $ticket);
	print $client_name;
	print "</td></tr>";
	
	print "<tr><th align='right'>Project</th><td>";
	print "<select name='ticket[project_id]'>";
	print "<option value=''></option>";
	foreach ($db->rows("SELECT project_id, label FROM projects WHERE user_id = %client_id%", $ticket) as $row) {
		print "<option value='$row[project_id]'>$row[label]</option>";
	}
	print "</select>";
	print "<input name='ticket[project-new]'/>";
	print "</td></tr>";
	
	print "<tr><th align='right'>Status</th><td>";
	print "<select name='ticket[status_id]'>";
	print "<option value='1'>Open</option>";
	#print "<input name='status' value='Open' type='hidden'/>";
	#print "<option value=''></option>";
	#foreach ($db->rows("SELECT DISTINCT project FROM ticket WHEclient_id, label FROM client") as $row) {
		#print "<option value='$row[client_id]'>$row[label]</option>";
	#}
	print "</select>";
	print "</td></tr>";
	
	print "<tr><th align='right'>Assigned</th><td>";
	print "<select name='ticket[assigned_id]'>";
	print "<option value=''></option>";
	$client_role = $db->value("SELECT * FROM roles WHERE label = 'Programmer'");
	#print "<input name='status' value='Open' type='hidden'/>";
	#print "<option value=''></option>";
	$sql = "SELECT user_id, label FROM users LEFT JOIN user_roles USING (user_id) WHERE role_id = '$client_role'";
	foreach ($db->rows( $sql ) as $row) {
		print "<option value='$row[user_id]'>$row[label]</option>";
	}
	print "</select>";
	print "</td></tr>";

	print "<tr><th align='right'>Ticket 1</th><td><textarea rows='3' cols='80' name='descriptions[]'></textarea></td></tr>";
	print "<tr><th align='right'>2</th><td><textarea rows='3' cols='80' name='descriptions[]'></textarea></td></tr>";
	print "<tr><th align='right'>3</th><td><textarea rows='3' cols='80' name='descriptions[]'></textarea></td></tr>";
	print "<tr><th align='right'>4</th><td><textarea rows='3' cols='80' name='descriptions[]'></textarea></td></tr>";
	print "<tr><th align='right'>5</th><td><textarea rows='3' cols='80' name='descriptions[]'></textarea></td></tr>";
	print "<tr><th align='right'>6</th><td><textarea rows='3' cols='80' name='descriptions[]'></textarea></td></tr>";
	print "<tr><th align='right'>7</th><td><textarea rows='3' cols='80' name='descriptions[]'></textarea></td></tr>";
	print "<tr><th align='right'>8</th><td><textarea rows='3' cols='80' name='descriptions[]'></textarea></td></tr>";
	print "<tr><th align='right'>9</th><td><textarea rows='3' cols='80' name='descriptions[]'></textarea></td></tr>";
	print "<tr><th align='right'>10</th><td><textarea rows='3' cols='80' name='descriptions[]'></textarea></td></tr>";
	print "<tr><th></th><td><input type='submit' value='OK'/></td></tr>";
	print "</table>";
	print "</form>";
}

/*
mysql> desc ticket;
+----------------+--------------+------+-----+---------+----------------+
| Field          | Type         | Null | Key | Default | Extra          |
+----------------+--------------+------+-----+---------+----------------+
| ticket_id      | int(11)      | NO   | PRI | NULL    | auto_increment | 
| client_id      | int(11)      | NO   |     | NULL    |                | 
| project        | tinytext     | YES  |     | NULL    |                | 
| status         | int(11)      | NO   |     | NULL    |                | 
| assigned       | int(11)      | NO   |     | NULL    |                | 
| time_estimated | decimal(5,2) | YES  |     | NULL    |                | 
| time_actual    | decimal(5,2) | YES  |     | NULL    |                | 
+----------------+--------------+------+-----+---------+----------------+
*/
?>

</body>
</html>
