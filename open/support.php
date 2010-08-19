<?php
require_once( "../lib/prepend.php" );
?>

<html>
<head><title>Support</title></head>
<body>
<?php

PageContent::display( substr( basename( $_SERVER["SCRIPT_NAME"] ), 0, -4 ) );

?>
</body>
</html>
