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
        
        $table = $dbObject->getTable();
        $schema = $dbObject->getSchema();

		//$scriptName = str_replace( ".php", "", strtolower( basename( $_SERVER["SCRIPT_NAME"] ) ) );
		//$className = strtolower( get_class( $this ) );
		//if ( $scriptName == $className )
		//{
			$operation = "catalog";
			if( array_key_exists( "operation", $_REQUEST ) )
				$operation = $_REQUEST["operation"];
			
			switch( $operation )
			{
			case "new":

                /*
				$sql = sprintf( "INSERT INTO `%s` VALUES ()", $table );
				$db->query( $sql );
				$sql = sprintf( "SELECT * FROM %s WHERE %s = '%s'",
					$table, $schema['primaryKey'][0],
					$db->value( "SELECT last_insert_id()" ) );
				$row = $db->row( $sql );
				$_REQUEST['key'] = $this->getPrimaryKeyValueEncoded( $row );
                */
				
				$this->showEditor();
                break;
			
            case "edit":
				$this->showEditor( $_REQUEST["key"] );
				break;

			case "delete":
				$this->deleteRecord( $_REQUEST["key"] );
				$this->showCatalog();
                break;

			case "save":
				foreach( $_REQUEST["records"] as $table => $row )
				{
                    $insert = false;
                    $primaryKeyFields = array();
                    foreach( $row as $encoded => $array )
                    {
                        if( $encoded == "new" )
                        {
                            $insert = true;
                            foreach( $schema["primaryKey"] as $primary )
                            {
                                unset( $row[$encoded][$primary] );
                            }
                        }
                    }
                    
                    if( $insert )
                    {
                        $dbObject->insert( $row );
                    }
                    else
                    {
                        $dbObject->update( $row );
                    }
				}
				$this->showCatalog();
                break;

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
	
    public function deleteRecord( $keyArray )
    {
        $sql = sprintf( "DELETE FROM %s WHERE %s",
            $this->dbObject->getTable(),
            $this->getPrimaryKeyValueEncodedAsWhereClause( $keyArray ) );
        $this->db->query( $sql );
        print "<p>Deleting records...";
        print "<br/><small><i>$sql</i></small>";
        print "</p>";
    }

	// Takes an array of the form [ key1val, key2val ] in the order of
	// $this->schema["primaryKey"];
	public function showEditor( $keyArray = null )
	{
		$db = $this->db;
		$dbObject = $this->dbObject;
        
		$schema = $dbObject->getSchema();
        list( $sqlFields, $sqlJoins ) = $this->buildJoins( $schema );

        //$sql = sprintf( "SELECT %s FROM %s %s WHERE %s",
            //$sqlFields, $dbObject->getTable(), $sqlJoins,
			//$this->getPrimaryKeyValueEncodedAsWhereClause( $keyArray ) );
		
        $table = $dbObject->getTable();

        $sql = "";
        $row = array();
        $primaryKey = "new";
        if( $keyArray )
        {
            $sql = sprintf( "SELECT * FROM `%s` WHERE %s;",
                $table,
                $this->getPrimaryKeyValueEncodedAsWhereClause( $keyArray ) );
            $row = $db->row( $sql );
            $primaryKey = $this->getPrimaryKeyValueEncoded($row);
        }
        else
        {
            foreach( $schema["fields"] as $field )
            {
                $value = "";
                if( in_array( $field, $schema["primaryKey"] ) )
                    $value = "new";
                $row[$field] = $value;
            }
        }

		printf("<form method='POST' action='%s'>", $this->postURL);
		print "<input name='operation' value='save' type='hidden'/>";
		print "<table cellspacing='10'>";
        if( $keyArray )
        {
            print "<tr><th colspan='2'>Edit Record</th></tr>";
            print "<tr><th colspan='2'><small><i>$sql</i></small></th></tr>";
        }
        else
        {
            print "<tr><th colspan='2'>New Record</th></tr>";
        }
		foreach( $row as $key => $value )
		{
            $name = "records[$table][$primaryKey][$key]";
			print "<tr>";
			print "<th align='right'>$key: </th>";
			print "<td>";
            if( array_key_exists( $key, $schema["foreignKeys"] ) )
            {
                $foreign = $schema["foreignKeys"][$key];
                $sql = sprintf( "SELECT %s AS id, label FROM %s ORDER BY label",
                    $foreign["referenced_column_name"], $foreign["referenced_table_name"] );
                print "<select name='$name'>";
                foreach( $db->rows( $sql ) as $entry )
                {
                    $selected = "";
                    if( $entry["id"] == $value )
                        $selected = "selected='selected'";
                    printf( "<option value='%d' $selected>%s</option>",
                        $entry["id"], $entry["label"] );
                }
                print "</select>";
            }
            else
            {
                print "<input name='$name' value='$value'/>";
            }
            print "</td>";
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

    public function buildJoins( &$schema )
    {
        // Returns array( "field1, field2", "LEFT JOIN z ON x.a = z.a LEFT JOIN ...." )
        $sqlFields = "";
        $sqlJoins = "";
		foreach( $schema["fields"] as $field )
        {
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
        return array( $sqlFields, $sqlJoins );
    }

	public function showCatalog()
	{
		$db = $this->db;
		$dbObject = $this->dbObject;

		$schema = $dbObject->getSchema();

        print "<script type='text/javascript'>
            function confirmDelete( url )
            {
                if( confirm( 'Are you sure you want to delete this record?' ) )
                {
                    window.location = url;
                }
            }
            </script>";
		print "<table cellpadding='4' cellspacing='0' >";

		print "<tr>";
		foreach( $schema["fields"] as $field )
		{
			$printable = $this->beautifyField( $field );
			print "<th>$printable</th>";
		}
		print "</tr>";
        
        list( $sqlFields, $sqlJoins ) = $this->buildJoins( $schema );

        $sql = sprintf( "SELECT %s FROM %s %s",
            $sqlFields, $dbObject->getTable(), $sqlJoins );
      
        $colora = "#eeeaee";
        $colorb = "#f2f3f2";
        $color = $colorb;
		foreach( $db->rows( $sql ) as $row )
		{
			$editLink = sprintf('<a href="%s?operation=edit&key=%s">edit</a>',
				$this->postURL,
				$this->getPrimaryKeyValueEncoded($row));
			
            $deleteLink = sprintf('<a href="javascript:confirmDelete(\'%s?operation=delete&key=%s\');">delete</a>',
				$this->postURL,
				$this->getPrimaryKeyValueEncoded($row));

            $color = $color == $colora ? $colorb : $colora;

			print "<tr>";
			foreach( $schema["fields"] as $field )
			{
				print "<td bgcolor='$color'>" . $row[$field] . "</td>";
			}
			print "<td>$editLink</td>";
			print "<td>$deleteLink</td>";
			print "</tr>";
		}

		print "</table>";

		$createLink = sprintf('<a href="%s?operation=new">New Record</a>', $this->postURL );
		print "<p style='margin: 2em 0.5em 1em 0.5em'>$createLink</p>";
	}
}

