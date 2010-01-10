<?php
function action_db_schema() { # don't clutter the namespace
	$db = new Database();

	$tables = array(
	"site_preferences" =>
		"CREATE TABLE site_preferences (
		preference_name TINYTEXT NULL,
		preference_value TEXT NULL
		) ENGINE = MYISAM;",
	"local_preferences" =>
		"CREATE TABLE local_preferences (
		preference_name TINYTEXT NULL,
		preference_value TEXT NULL
		) ENGINE = MYISAM;",
	"ticket_log" =>
		"CREATE TABLE ticket_log
		(
		ticket_log_id INT NOT NULL AUTO_INCREMENT,
		ticket_id INT NOT NULL,
		date TIMESTAMP,
		old TEXT NULL,
		new TEXT NULL,
		note TEXT NULL,
		PRIMARY KEY(ticket_log_id)
		);",
	"ticket" => 
		"CREATE TABLE ticket (
		ticket_id INT NOT NULL AUTO_INCREMENT,
		client_id INT NOT NULL,
		project_id TINYTEXT DEFAULT '',
		status_id INT NOT NULL,
		assigned_id INT NOT NULL,
		description TEXT DEFAULT '',
		time_estimated DECIMAL(5,2),
		time_actual DECIMAL(5,2),
		PRIMARY KEY(ticket_id)
		);",
	"status" => 
		"CREATE TABLE status (
		status_id INT NOT NULL AUTO_INCREMENT,
		label TINYTEXT DEFAULT '',
		PRIMARY KEY(status_id)
		);",
	"users" => 
		"CREATE TABLE users (
		user_id INT NOT NULL AUTO_INCREMENT,
		label TINYTEXT DEFAULT '',
		login TINYTEXT DEFAULT '',
		password TINYTEXT DEFAULT '',
		PRIMARY KEY(user_id)
		);",
	"roles" => 
		"CREATE TABLE roles (
		role_id INT NOT NULL AUTO_INCREMENT,
		label TINYTEXT DEFAULT '',
		PRIMARY KEY(role_id)
		);",
	"user_roles" => 
		"CREATE TABLE user_roles (
		user_role_id INT NOT NULL AUTO_INCREMENT,
		user_id INT NOT NULL,
		role_id INT NOT NULL,
		PRIMARY KEY(user_role_id)
		);",
	"assignments" =>
		"CREATE TABLE assignments (
		assignment_id INT NOT NULL AUTO_INCREMENT,
		user_id INT NOT NULL,
		PRIMARY KEY(assignment_id)
		);",
	"projects" =>
		"CREATE TABLE projects (
		project_id INT NOT NULL AUTO_INCREMENT,
		user_id INT NOT NULL,
		label TINYTEXT DEFAULT '',
		PRIMARY KEY(project_id)
		);",
		
	);

	$existing_tables = $db->column("SHOW TABLES;");
	while (list($table, $sql) = each($tables)) {
		if (!in_array($table, $existing_tables)) {
			#print "<pre>$sql</pre>";
			$db->query($sql);
		}
	}
	foreach (array_keys($tables) as $table) {
		$sql = $tables[$table];
		### we're going to assume that all non-existant tables have been created now...
		$existing_sql = $db->value("SELECT preference_value FROM site_preferences WHERE preference_name = 'table-sql-$table'");
		if ($existing_sql != $sql) {
			### This should pretty much never happen, and we probably 
			### want an administrative lockout, but if it does happen,
			### I don't care about design, I want a nice big warning.
			print "<h1>ALERT: Recreating table `$table`!</h1>";
			$tmptable = "${table}_tmp_" . rand(100000, 900000);
			$sqltmp = "RENAME TABLE `$table` TO `$tmptable`;";
			$db->query($sqltmp);
			$sqltmp = $sql;
			$db->query($sqltmp);
			#$sqltmp = "INSERT INTO `$table` SELECT * FROM `$tmptable`;";
			#print $sqltmp;
			#db_query($sqltmp);
		
			# Grab all data from old table
			$result = mysql_query("SELECT * FROM $tmptable");
			if (mysql_errno()) {
				debug_print_backtrace();
				trigger_error(mysql_error(), E_USER_ERROR);
			}
			# Discover columns common to both tables
			$columns = array();
			$a = array();
			foreach ($db->rows("DESCRIBE `$table`") as $desc)
				array_push($a, $desc["Field"]);
			$b = array();
			foreach ($db->rows("DESCRIBE `$tmptable`") as $desc)
				array_push($b, $desc["Field"]);
			$columns = array_intersect($a, $b);
			# Copy data...
			if (mysql_num_rows($result)) {
				print "<h2>Copying records ";
				while ($row = mysql_fetch_array($result)) {
					print " .";
					$fields = array();
					$values = array();
					foreach ($columns as $col) {
						array_push($fields, "`" . $col . "`");
						array_push($values, "'" . $db->q($row[$col]) . "'");
					}
					$insert = "INSERT INTO `$table` (" . join(", ", $fields) . ") VALUES (" . join(", ", $values) . ");";
					$db->query($insert);
				}
				print " done</h2>";
			}
			mysql_free_result($result);

			# Drop the old (table
			$sqltmp = "DROP TABLE `$tmptable`;";
			$db->query($sqltmp);

			# Update code records
			$sqltmp = "DELETE FROM site_preferences WHERE preference_name = 'table-sql-$table'";
			$db->query($sqltmp);
			$sqltmp = "INSERT INTO site_preferences (preference_name, preference_value) VALUES ('table-sql-$table', '" . $db->q($sql) . "');";
			$db->query($sqltmp);
		}
	}

	### Now here we re-create the table indexes

	$indexes = array(
		#"visits" => array("record_timestamp", "inventory_record_id", "contract_num(4)", "visit_date",
			#"contract_record_id_service", "contract_record_id_testing"),
		#"customers" => array("record_timestamp", "last_name(5)"),
		#"inventory" => array("record_timestamp", "shf_state(2)", "shf_city(6)", "customer_record_id(6)"),
		#"contracts" => array("record_timestamp", "contract_num(7)", "next_renewal",
			 #"visits_completed(2)", "num_visits(2)")
		);
	foreach (array_keys($indexes) as $table) {
		$sql = "SHOW INDEX FROM `$table`";
		$created_indexes = array();
		foreach ($db->rows($sql) as $row) {
			//pprint($row);
			if ($row["Non_unique"] == "1") {
				array_push($created_indexes, $row["Column_name"]);
			}
		}
		foreach ($indexes[$table] as $index) {
			if (! in_array(preg_replace("/\\(.*$/", "", $index), $created_indexes)) {
				$sql = "ALTER TABLE `$table` ADD INDEX ( $index )";

				print "<p>Adding index on $table.$index...</p>";
				$db->query($sql);
			}
		}
		#print "<hr>Indexes in $table";
		#print_r($created_indexes);
	}
}
action_db_schema();
?>
