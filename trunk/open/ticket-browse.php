<?php
require_once("../lib/prepend.php");

$db = new Database();

$ticket = array();
if( isset( $_REQUEST["ticket"] ) )
{
	$ticket = $_REQUEST["ticket"];
}

?>
<html>
<head>
<title>Browse Tickets</title>
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
<h1>Browse Tickets</h1>

<?php
if( ! isset( $ticket["client_id"] ) )
{
	print '<form action="'.$_SERVER["SCRIPT_NAME"].'" method="GET">';
	print "<dl>";
	$client_role = $db->value("SELECT * FROM roles WHERE label = 'Client'");
	$sql = "SELECT user_id, label FROM users LEFT JOIN user_roles USING (user_id) WHERE role_id = '$client_role'";
	foreach ( $db->rows( $sql ) as $row ) {
		print "<dt>";
		printf( '<a href="%s?ticket[client_id]=%s">%s</a>',
			$_SERVER["SCRIPT_NAME"],
			$row["user_id"],
			$row["label"] );
		print "</dt>";
		print "<dd>";
		$sql = "SELECT COUNT(*) FROM ticket WHERE client_id = %user_id%";
		$count = $db->value( $sql, $row );
		printf( "%d ticket%s", 
			$count,
			$count==1 ? "" : "s" );
		print "</dd>";
	}
	print "</dl>";
	print "</form>";
}
else
{
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
