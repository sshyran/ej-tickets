<?php

class Db implements Iterator
{
	protected $table = null;
	private $schema = null;
	private $postURL = null;

	public function __construct( $table = null )
	{
		if( $table == null )
		{
			$this->table = strtolower( substr( get_class($this), 2 ) );
		}
		else
		{
			$this->table = $table;
		}

		$this->database = new Database();
	}

public function current() {
return current($this->a);
}
public function key() {
return key($this->a);
}
public function next() {
return next($this->a);
}
public function rewind() {
return reset($this->a);
}
public function valid() {
return (current($this->a) !== FALSE);
}


	private function setDatabase($database) { $this->database = $database; }
	public function getDatabase() { return $this->database; }
	private function setTable($t) { $this->table = $t; }
	public function getTable() { return $this->table; }

	public function getSchema()
	{
		if( $this->schema == null )
		{
            global $CONFIG;

			$schema = array();
			$schema["fields"]  = array();

			$db = $this->getDatabase();
			foreach( $db->rows("DESCRIBE " . $this->getTable()) as $row )
			{
				array_push($schema["fields"], $row["Field"]);

				if( $row["Key"] == "PRI" )
				{
					if( ! array_key_exists( "primaryKey", $schema ) )
					{
						$schema["primaryKey"] = array();
					}
					array_push( $schema["primaryKey"], $row["Field"] );
				}
			}

            $sql = sprintf( "SELECT 
                    table_name, column_name, referenced_table_name, referenced_column_name
                    FROM information_schema.KEY_COLUMN_USAGE
                    WHERE REFERENCED_TABLE_SCHEMA = '%s'
                    AND TABLE_NAME = '%s';",
                $CONFIG->getDbDatabase(),
                $this->getTable() );
            $schema["foreignKeys"] = array();
            foreach( $db->rows( $sql ) as $row )
            {
                $schema["foreignKeys"][$row["column_name"]] = $row;
            }

			$this->schema = $schema;
		}
		
		return $this->schema;
	}

	/* Array
	(
	    [WyIxIl0] => Array
		(
		    [assignee_id] => 1
		    [label] => Chris
		    [login] => chris
		    [password] => chris
		)

	) */
	public function update( $array )
	{
		// table data primary_key
		$database = $this->getDatabase();
		$table = $this->getTable();
		foreach( $array as $encoded => $row )
		{
			//$values = json_decode( base64_decode($encoded), true );
			$database->update( $table, $row );
		}
	}
	
    public function insert( $array )
	{
		$database = $this->getDatabase();
		$table = $this->getTable();
		foreach( $array as $encoded => $row )
		{
			$database->insert( $table, $row );
		}
	}
}
