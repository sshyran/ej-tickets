<?php

class DbEditor
{
	private $postURL = null;
	private $dbObject = null;
	private $db = null;

	public function __construct( Db $dbObject )
	{
		$this->dbObject = $dbObject;
		$this->db = $dbObject->getDatabase();

		$this->postURL = $_SERVER["SCRIPT_NAME"];

		$scriptName = str_replace( ".php", "", strtolower( basename( $_SERVER["SCRIPT_NAME"] ) ) );
		$className = strtolower( get_class( $this ) );
		
		//if ( $scriptName == $className )
		//{
			$operation = "catalog";
			if( array_key_exists( "operation", $_REQUEST ) )
				$operation = $_REQUEST["operation"];
			
			switch( $operation )
			{
			case "new":
				$db = $this->db;
				$table = $dbObject->getTable();
				$schema = $dbObject->getSchema();

				$sql = sprintf( "INSERT INTO `%s` VALUES ()", $table );
				$db->query( $sql );
				$sql = sprintf( "SELECT * FROM %s WHERE %s = '%s'",
					$table, $schema['primaryKey'][0],
					$db->value( "SELECT last_insert_id()" ) );
				$row = $db->row( $sql );
				$_REQUEST['key'] = $this->getPrimaryKeyValueEncoded( $row );

			case "edit":
				$this->showEditor( $_REQUEST["key"] );
				break;

			case "save":
				foreach( $_REQUEST["records"] as $table => $row )
				{
					$dbObject->update( $row );
					#foreach( $row as $key => $data )
					#{
						#foreach( $data as $field => $value )
						#{
						#}
					#}
					#$key = $row
					#print "<pre>";
					#print_r($row);
				}

			default:
				$this->showCatalog();
			}
		//}
	}

	// Given a row, returns an array of identifying values
	private function getPrimaryKeyValueEncoded( $row )
	{
		$dbObject = $this->dbObject;

		$schema = $dbObject->getSchema();
		$retval = array();
		foreach( $schema["primaryKey"] as $key )
		{
			array_push($retval, $row[$key]);
			#$retval[$key] = $row[$key];
		}
		$retval = json_encode($retval);
		$retval = base64_encode($retval);
		$retval = str_replace("=", "", $retval);
		return $retval;
	}

	private function getPrimaryKeyValueEncodedAsWhereClause( $encoded )
	{
		$dbObject = $this->dbObject;
		$db = $this->db;

		$schema = $dbObject->getSchema();
		$values = json_decode( base64_decode($encoded), true );

		if( sizeof($schema["primaryKey"]) != sizeof($values) )
		{
			trigger_error("Array lengths don't match", E_USER_ERROR);
		}

		$where = "";
		for( $i=0; $i<sizeof($values); $i++ )
		{
			if( strlen($where) )
				$where .= " AND ";
			$where .= sprintf( "`%s` = '%s'",
				$schema["primaryKey"][$i],
				$db->quote($values[$i]) );
		}
		return $where;
	}

	// Takes an array of the form [ key1val, key2val ] in the order of
	// $this->schema["primaryKey"];
	public function showEditor( $keyArray = null )
	{
		$db = $this->db;
		$dbObject = $this->dbObject;
		$sql = sprintf( "SELECT * FROM `%s` WHERE %s;",
			$dbObject->getTable(),
			$this->getPrimaryKeyValueEncodedAsWhereClause( $keyArray ) );

		$row = $db->row( $sql );
		$table = $dbObject->getTable();
		$primaryKey = $this->getPrimaryKeyValueEncoded($row);
		printf("<form method='POST' action='%s'>", $this->postURL);
		print "<input name='operation' value='save' type='hidden'/>";
		print "<table cellspacing='10'>";
		print "<tr><th colspan='2'>Edit Record</th></tr>";
		print "<tr><th colspan='2'><small><i>$sql</i></small></th></tr>";
		foreach( $row as $key => $value )
		{
			print "<tr>";
			print "<th align='right'>$key: </th>";
			print "<td><input name='records[$table][$primaryKey][$key]' value='$value'/></td>";
			print "</tr>";
		}
		print "<tr>";
		print "<td></td>";
		print "<td><input type='submit' value='Save'/></td>";
		print "</tr>";
		print "</table>";
		print "</form>";
	}

	private function beautifyField( $field )
	{
		$words = split( "_", $field);
		$words = array_map("ucfirst", $words);
		$field = join(" ", $words);
		return $field;
	}

	public function showCatalog()
	{
		$db = $this->db;
		$dbObject = $this->dbObject;

		$schema = $dbObject->getSchema();

		print "<table>";

        $sqlFields = "";
        $sqlJoins = "";

		print "<tr>";
		foreach( $schema["fields"] as $field )
		{
			$printable = $this->beautifyField( $field );
			print "<th>$printable</th>";

            # Also build the SQL
            if( $sqlFields ) $sqlFields .= ", ";
            if( $sqlJoins ) $sqlJoins .= " ";
            if( ! array_key_exists( $field, $schema["foreignKeys"] ) )
            {
                $sqlFields .= $field;
            }
            else
            {
                $foreign = $schema["foreignKeys"][$field];
                $sqlJoins .= sprintf( "LEFT JOIN %s ON %s.%s = %s.%s",
                    $foreign["referenced_table_name"],
                    $foreign["table_name"], $foreign["column_name"],
                    $foreign["referenced_table_name"], $foreign["referenced_column_name"] );
                $sqlFields .= sprintf( "%s.label AS %s",
                    $foreign["referenced_table_name"], $field );
            }
		}
		print "</tr>";

        $sql = sprintf( "SELECT %s FROM %s %s",
            $sqlFields, $dbObject->getTable(), $sqlJoins );
        
		foreach( $db->rows( $sql ) as $row )
		{
			$editLink = sprintf('<a href="%s?operation=edit&key=%s">edit</a>',
				$this->postURL,
				$this->getPrimaryKeyValueEncoded($row));

			print "<tr>";
			foreach( $schema["fields"] as $field )
			{
				print "<td>" . $row[$field] . "</td>";
			}
			print "<td>$editLink</td>";
			print "</tr>";
		}

		print "</table>";

		$createLink = sprintf('<a href="%s?operation=new">New Record</a>', $this->postURL );
		print "<p style='margin: 2em 0.5em 1em 0.5em'>$createLink</p>";
	}
}

