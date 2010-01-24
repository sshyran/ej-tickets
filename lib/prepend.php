<?php

error_reporting(E_ALL);
define("ROOT_DIR", dirname( dirname( __FILE__ ) ) );
define("CONFIG_DIR", ROOT_DIR . "/config" );
define("LIB_DIR", ROOT_DIR . "/lib" );
set_include_path( get_include_path() 
	. ":" . ROOT_DIR );
session_start();

class Config {
	private $config = array(
		"DB_HOST" => "",
		"DB_LOGIN" => "",
		"DB_PASSWORD" => "",
		"DB_DATABASE" => ""
	);

	function __construct() {
		$this->loadConfig();
		$this->initializeDatabase();
	}

	private function loadConfig() {
		$config_name = CONFIG_DIR . "/default.php";
		$specific_name = CONFIG_DIR . "/" . $_SERVER["SERVER_NAME"] . ".php";
		if (file_exists($specific_name)) {
			$config_name = $specific_name;
		}
		require_once($config_name);
		$this->config = array_merge($this->config, $config);
	}
	private function initializeDatabase() {
		$link = mysql_connect( $this->config["DB_HOST"], $this->config["DB_LOGIN"], $this->config["DB_PASSWORD"]);
		if (!$link) {
			die("Could not connect: " . mysql_error());
		}
		if (! mysql_select_db($this->config["DB_DATABASE"])) {
			mysql_query("CREATE DATABASE " . $this->config["DB_DATABASE"] . ";");
			if (mysql_errno()) 
				die (mysql_error());
			mysql_select_db($this->config["DB_DATABASE"]);
		}
	}

	// Private so we protectt sensitive data ... ish ... not necessarily valid logic here....
	private function get( $var ) 
	{
		if( isset( $this->config[ $var ] ) )
		{
			return $this->config[ $var ];
		}
		return "";
	}

	public function getDbDatabase() { return $this->get( "DB_DATABASE" ); }
}

class Database {
	function q($str) {
		return str_replace(array("'", "\\"), array("''", "\\\\"), $str);
	}
	function quote($str) {
		return str_replace(array("'", "\\"), array("''", "\\\\"), $str);
	}

	function fill_fields($string, $data) {
		$matches = array();
		preg_match_all("/%[-a-zA-Z0-9_]+%/", $string, $matches);
		$searches = array();
		$replacements = array();
		foreach ($matches[0] as $match) {
			$key = str_replace("%", "", $match);
			if (!array_key_exists($key, $data)) {
				print "<pre>"; debug_print_backtrace(); print "</pre>";
				trigger_error("Couldn't find $key in field array!", E_USER_ERROR);
			}
			array_push($searches, $match);
			array_push($replacements, "'" . $this->q($data[$key]) . "'");
		}
		$string = str_replace($searches, $replacements, $string);
		return $string;
	}

	function value() {
		# This function takes any number of arguments.
		# 0 is SQL statement
		# 1...x are fields to insert into the SQL statement
		# OR 2 args, 2nd is array of field->value...
		# RETURNS void (if no result) or first field from first row
		$args = func_get_args();
		$sql = array_shift($args);
		if (sizeof($args))
			$sql = $this->fill_fields($sql, array_shift($args));
		if (sizeof($args)) {
			trigger_error("Unfinished function...", E_USER_ERROR);
		}
		$result = mysql_query($sql);
		if (mysql_errno()) {
			debug_print_backtrace();
			trigger_error(mysql_error(), E_USER_ERROR);
		}

		if (mysql_num_rows($result) == 0) {
			mysql_free_result($result);
			return;
		} else {
			$row = mysql_fetch_row($result);
			mysql_free_result($result);
			return $row[0];
		}
	}
	function column() {
		### Copied
		$args = func_get_args();
		$sql = array_shift($args);
		if (sizeof($args))
			$sql = $this->fill_fields($sql, array_shift($args));
		if (sizeof($args))
			trigger_error("Unfinished function...", E_USER_ERROR);
		$retval = array();
		$result = mysql_query($sql);
		if (mysql_errno()) {
			debug_print_backtrace();
			trigger_error(mysql_error(), E_USER_ERROR);
		}
		while ($row = mysql_fetch_row($result)) {
			array_push($retval, $row[0]);
		}
		mysql_free_result($result);
		return $retval;
	}
	function row() {
		$args = func_get_args();
		$sql = array_shift($args);
		if (sizeof($args))
			$sql = $this->fill_fields($sql, array_shift($args));
		if (sizeof($args))
			trigger_error("Unfinished function...", E_USER_ERROR);
		$retval = array();
		$result = mysql_query($sql);
		if (mysql_errno()) {
			debug_print_backtrace();
			trigger_error(mysql_error(), E_USER_ERROR);
		}
		$retval = mysql_fetch_assoc($result);
		mysql_free_result($result);
		return $retval;
	}
	function rows() {
		$args = func_get_args();
		$sql = array_shift($args);
		if (sizeof($args))
			$sql = $this->fill_fields($sql, array_shift($args));
		if (sizeof($args))
			trigger_error("Unfinished function...", E_USER_ERROR);
		$retval = array();
		$result = mysql_query($sql);
		if (mysql_errno()) {
			debug_print_backtrace();
			trigger_error(mysql_error(), E_USER_ERROR);
		}
		while ($row = mysql_fetch_assoc($result)) {
			array_push($retval, $row);
		}
		mysql_free_result($result);
		return $retval;
	}
	function query() {
		$args = func_get_args();
		if (sizeof($args) > 1) {
			trigger_error("Unfinished function...", E_USER_ERROR);
		}
		mysql_query($args[0]);
		if (mysql_errno()) {
			debug_print_backtrace();
			trigger_error(mysql_error(), E_USER_ERROR);
		}
	}
	function insert($table, $data) {
		foreach (array_keys($data) as $key) {
			$data[$key] = $this->q($data[$key]);
		}
		$fields = "`" . join("`, `", array_keys($data)) . "`";
		$values = "'" . join("', '", array_values($data)) . "'";
		$sql = "INSERT INTO `$table` ($fields) VALUES ($values);";
		#print $sql;
		$result = mysql_query($sql);
		if (mysql_errno()) {
			print "<pre>"; debug_print_backtrace(); print "</pre>";
			trigger_error(mysql_error(), E_USER_ERROR);
		}
	}
	function update($table, $data, $primary_key=false) {
		global $CONFIG;
		$db_schema = $CONFIG->getDbDatabase();
		if ($primary_key == false) {
			$sql = "SELECT k.column_name FROM information_schema.table_constraints t JOIN information_schema.key_column_usage k USING(constraint_name,table_schema,table_name) WHERE t.constraint_type='PRIMARY KEY' AND t.table_schema='$db_schema' AND t.table_name='$table';";
			$primary_key = $this->value( $sql );
		}
		if (!array_key_exists($primary_key, $data))
			trigger_error("No key!", E_USER_ERROR);
		$sql = "";
		while (list($key, $value) = each($data)) {
			if ($key != $primary_key && ! is_numeric($key)) {
				if ($sql) $sql .= ", ";
				$sql .= "`$key` = '" . $this->q($value) . "'";
			}
		}
		$sql = "UPDATE `$table` SET $sql WHERE `$primary_key` = '" . $this->q($data[$primary_key]) . "' LIMIT 1";
		#print "<p>$sql</p>";
		$this->query($sql);
	}

	function mysql2timestamp($mysql) {
		$y = $m = $d = $h = $i = $s = 0;
		if (strpos($mysql, " ")) {
			list($date, $time) = split(" ", $mysql);
			list($y, $m, $d) = split("-", $date);
			list($h, $i, $s) = split(":", $time);
		} else {
			list($y, $m, $d) = split("-", $mysql);
		}
		return mktime($h, $i, $s, $m, $d, $y);
	}
}

$CONFIG = new Config();

require_once("lib/DatabaseSchema.php");
require_once("lib/orm/includer.php");
require_once("lib/output.php");

