<?php
require_once("../lib/prepend.php");
?>
<html>
<head>
<title>Ticket System</title>
</head>
<body>

<h1>Ticket System</h1>

<p> <input type="button" onClick="window.location = 'ticket-create.php';" value="Submit New Ticket"/> </p>
<p> <input type="button" onClick="window.location = 'ticket-browse.php';" value="Browse Tickets"/> </p>

<?php
//$assignee = new DbAssignee();
//$assignee->showCatalog();
?>

</body>
</html>
