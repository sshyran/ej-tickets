<?php
require_once("../lib/prepend.php");
$_TEMPLATE = "admin.tpl";
?>
<html>
<head><title>Admin</title></head>
<body>

<h1>Administrative Scripts</h1>

<ul>
<li><a href="db-lead_categories.php">Lead Categories</a></li>
<li><a href="db-roles.php">Login Roles</a></li>
<br/>
<li><a href="db-users.php">Users</a></li>
<li><a href="db-user_roles.php">User Roles</a></li>
<br/>
<li><a href="db-leads.php">Leads</a></li>
<li><a href="db-lead_fields.php">Lead Fields</a></li>
<li><a href="lead-assignment.php">Lead Assignment</a></li>
<li><a href="lead-assignment-report.php">Lead Assignment Report</a></li>
</ul>

</body>
</html>
