<?php
require_once( "../lib/prepend.php" );

$_TEMPLATE = "blank.tpl";
?>
<html>
<head><title>DB Export</title>
</head>
<body>
<p>Database Exporter</p>

<?php
var_dump($CONFIG);
print $CONFIG->getExportCmd();
?>

</body>
</html>
