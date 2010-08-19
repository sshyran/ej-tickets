<?php
require_once("lib/prepend.php");

$db = new Database();

$ticket = array();
if( isset( $_REQUEST["ticket"] ) )
{
	$ticket = $_REQUEST["ticket"];
}

?>
<html>
<head>
<title>View Ticket</title>
<style>
dt
{
}
dd
{
	font-size: smaller;
	padding-bottom: 0.75em;
}
</style>
</head>
<body>
<h1>View Ticket</h1>

<?php
if( ! isset( $ticket["ticket_id"] ) )
{
	print '<form action="'.$_SERVER["SCRIPT_NAME"].'" method="GET">';
	print "Ticket ID: ";
	print "<input name='ticket[ticket_id]' size='4'/>";
	print "<input type='submit'/>";
	print "</form>";
}
else
{
	$ticket = $db->row( "SELECT * FROM ticket WHERE ticket_id = %ticket_id% ", $ticket );
	print "<table width='100%'>";
	print "<tr>";

	$owner = new Db( "users" );
	var_dump( $owner );
	printf( "<tr><td>Assigned</td><td>%s</td><td>Owner</td><td>%s</td></tr>", 
		$db->value( "SELECT label FROM users WHERE user_id = %assigned_id%", $ticket ),
		$db->value( "SELECT label FROM users WHERE user_id = %client_id%", $ticket ) );
	printf( "<tr><td>Status</td><td>%s</td><td>Project</td><td>%s</td></tr>", 
		$db->value( "SELECT label FROM status WHERE status_id = %status_id%", $ticket ),
		$db->value( "SELECT label FROM projects WHERE project_id = %project_id%", $ticket ) );
	print "</table>";

	print $ticket["description"];
	print "<pre>"; print_r($ticket);
	/*
	printf( "<h2>%s</h2>",
		$db->value( "SELECT label FROM users WHERE user_id = %client_id%", $ticket ) );
	
	$sql = "SELECT *, projects.label AS projects_label,
		users.label AS assigned_label,
		status.label AS status_label FROM ticket 
		LEFT JOIN projects USING (project_id)
		LEFT JOIN users ON ticket.assigned_id = users.user_id
		LEFT JOIN status USING (status_id)
		WHERE client_id = %client_id% 
		ORDER BY projects.label";
	$rows = $db->rows( $sql, $ticket );
	print "<table>";
	$header = -1;
	foreach( $rows as $row )
	{
		if( $header != $row["projects_label"] )
		{
			$header = $row["projects_label"];
			print "<tr><th colspan='2' align='left'>$header</th>";
			print "<th>Assigned</th>";
			print "<th>Status</th>";
			print "<th>Est.</th>";
			print "<th>Act.</th>";
			print "</tr>";
		}
		print "<tr>";
		print "<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>";
		print "<td><a href='ticket-view.php?ticket[ticket_id]=$row[ticket_id]'>$row[description]</a></td>";
		print "<td>$row[assigned_label]</td>";
		print "<td>$row[status_label]</td>";
		print "<td>$row[time_estimated]</td>";
		print "<td>$row[time_actual]</td>";
		print "</tr>";
	}
	print "</table>";
	*/
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
