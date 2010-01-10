<?php
require_once("lib/prepend.php");

$msg = "";
if (array_key_exists("login", $_REQUEST) && array_key_exists("password", $_REQUEST)) {
	$db = new Database();
	$user = $db->row("SELECT * FROM users WHERE login = %login% AND password = %password%;", $_POST);
	if ($user == false) {
		$msg = "Invalid login credentials!";
	} else {
		$_SESSION["user"] = $user;
		header("Location: home.php");
		exit(0);
	}
}
?>
<html>
<head>
<title>Working Login</title>
</head>
<body>

<h1>Ticketing Login</h1>

<?php if (strlen($msg)) { ?>
<p><b><font color="red"><?php print $msg; ?></font></b></p>
<?php } ?>

<form action="<?php print $_SERVER["SCRIPT_NAME"]; ?>" method="POST">
<table>
<tr><th align="right">Login</th><td><input name="login" size="8"/></td></tr>
<tr><th align="right">Password</th><td><input name="password" type="password" size="8"/></td></tr>
<tr><th></th><td><input type="submit" value="OK"/></td></tr>
</table>
</form>

</body>
</html>
