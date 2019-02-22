<?php

/*
 * Make sure the class only exists once
 */
if ( !class_exists( 'DB' ) ) {
	
	/**
	 * Class for mysql
	 */
	class DB {
		/**
		 * Make sure variables are private!
		 */
		private $user;
		private $password;
		private $database;
		private $host;
		
		/**
		 * Declares the default constructor
		 * Upon creation, the object will have the following variables set with information
		 * obtained from the parameters defined by new
		 */
		public function __construct($user, $password, $database, $host = 'localhost') {
			$this->user = $user;
			$this->password = $password;
			$this->database = $database;
			$this->host = $host;
		}
	
		
		/**
		 * Functon will create and return the new mysqli object using the 
		 */
		protected function connect() {
			return new mysqli($this->host, $this->user, $this->password, $this->database);
		}
		
		/**
		 * Function runs the query from the parameters
		 * Parameters: $query
		 * Returns: an array with the results
		 */
		public function query($query) {
			$results = array();
			$db = $this->connect();
			mysqli_set_charset($db,"utf8");
			$result = $db->query($query);
			
			while ( $row = $result->fetch_object() ) {
				$results[] = $row;
			}
			
			return $results;
		}
		
		/**
		 * Function inserts data into a table
		 * Parameters
		 *	- $table: table name to insert into
		 *	- $data: is an array of data to insert
		 *	- $format: array that defines what type each element is (Ex: %d, %s, %f)
		 */
		public function insert($tabla, $data, $format) {
			
			// Check for $table or $data not set
			if ( empty( $tabla ) || empty( $data ) ) {
				return false;
			}
			
			// Connect to the database
			$db = $this->connect();
			
			// Cast $data and $format to arrays
			$tabla = (array) $tabla;
			$data = (array) $data;
			$format = (array) $format;
	
			// Build format string
			$format = implode('', $format); 
			$format = str_replace('%', '', $format);
			
			list( $fields, $placeholders, $values ) = $this->prep_query($data,$tabla);
			
			// Prepend $format onto $values
			array_unshift($values, $format); 
			// Prepary our query for binding
			$stmt = $db->prepare("INSERT INTO {$tabla[0]} ({$fields}) VALUES ({$placeholders})");
		
			// Dynamically bind values
			call_user_func_array( array( $stmt, 'bind_param'), $this->ref_values($values));
			
			// Execute the query
			$stmt->execute();
			
			// Check for successful insertion
			if ( $stmt->affected_rows ) {
				return true;
			}
			
			return false;
		}
		
		
		/**
		 * Update function
		 */
		public function update($table, $data, $format, $where, $where_format) {
			// Check for $table or $data not set
			if ( empty( $table ) || empty( $data ) ) {
				return false;
			}
			
			// Connect to the database
			$db = $this->connect();
			
			// Cast $data and $format to arrays
			$data = (array) $data;
			$format = (array) $format;
			
			// Build format array
			$format = implode('', $format); 
			$format = str_replace('%', '', $format);
			$where_format = implode('', $where_format); 
			$where_format = str_replace('%', '', $where_format);
			$format .= $where_format;
			
			list( $fields, $placeholders, $values ) = $this->prep_query($data, 'update');
			
			//Format where clause
			$where_clause = '';
			$where_values = '';
			$count = 0;
			
			foreach ( $where as $field => $value ) {
				if ( $count > 0 ) {
					$where_clause .= ' AND ';
				}
				
				$where_clause .= $field . '=?';
				$where_values[] = $value;
				
				$count++;
			}
			// Prepend $format onto $values
			array_unshift($values, $format);
			$values = array_merge($values, $where_values);
			// Prepary our query for binding
			$stmt = $db->prepare("UPDATE {$table} SET {$placeholders} WHERE {$where_clause}");
			
			// Dynamically bind values
			call_user_func_array( array( $stmt, 'bind_param'), $this->ref_values($values));
			
			// Execute the query
			$stmt->execute();
			
			// Check for successful insertion
			if ( $stmt->affected_rows ) {
				return true;
			}
			
			return false;
		}
		
		/**
		 * Function selects data, and returns it as an array
		 * Parameters:
		 *	- query: the statement that includes ? for a prepared statement
		 *	- data: the array of data that would fill those ? ex: array('434', '52')
		 *	- format: array that defines each data element's type
		 */
		public function select($query, $data, $format) {
			$results = array();
			// Connect to the database
			$db = $this->connect();
			mysqli_set_charset($db,"utf8");
			//Prepare our query for binding
			$stmt = $db->prepare($query);
			
			//Normalize format
			$format = implode('', $format); 
			$format = str_replace('%', '', $format);
			
			// Prepend $format onto $values
			array_unshift($data, $format);
			
			//Dynamically bind values
			call_user_func_array( array( $stmt, 'bind_param'), $this->ref_values($data));
			
			//Execute the query
			$stmt->execute();
			
			//Fetch results
			$result = $stmt->get_result();
			
			//Create results object
			while ($row = $result->fetch_object()) {
				$results[] = $row;
			}
			return $results;
		}
		
		/**
		 * Function returns the number of rows for a query
		 */
		public function row_count($query, $data, $format){
			// Connect to the database
			$db = $this->connect();
			
			//Prepare our query for binding
			$stmt = $db->prepare($query);
			
			//Normalize format
			$format = implode('', $format); 
			$format = str_replace('%', '', $format);
			
			// Prepend $format onto $values
			array_unshift($data, $format);
			
			//Dynamically bind values
			call_user_func_array( array( $stmt, 'bind_param'), $this->ref_values($data));
			
			//Execute the query
			$stmt->execute();
			
			//Fetch results
			$result = $stmt->get_result()->num_rows;
			
			return $result;
		}
		
		/**
		 * Function deletes a row, based on an element
		 * Parameters:
		 *	- table: string containing the table name
		 *	- id:	integer that contains the id
		 */
		public function delete($table, $id) {
			// Connect to the database
			$db = $this->connect();
			
			// Prepary our query for binding
			$stmt = $db->prepare("DELETE FROM {$table} WHERE ID = ?");
			
			// Dynamically bind values
			$stmt->bind_param('d', $id);
			
			// Execute the query
			$stmt->execute();
			
			// Check for successful insertion
			if ( $stmt->affected_rows ) {
				return true;
			}
		}
		
		/**
		 * Function to disconnect from mysql, if a connection exists
		 */
		public function disconnect(){
			$db->close();
		}
		
		private function prep_query($data,$tabla, $type='insert') {
			// Instantiate $fields and $placeholders for looping
			$fields = '';
			$placeholders = '';
			$values = array();
			
			// Loop through $data and build $fields, $placeholders, and $values			
			$count = 1;
			foreach ( $data as $field => $value ) {
				
				$fields .= $tabla[$count].",";
				$values[] = $value;
				
				if ( $type == 'update') {
					$placeholders .= $tabla[$count] . '=?,';
				} else {
					$placeholders .= '?,';
				}
				$count++;
			}
			
			// Normalize $fields and $placeholders for inserting
			$fields = substr($fields, 0, -1);
			$placeholders = substr($placeholders, 0, -1);
			
			return array( $fields, $placeholders, $values );
		}
		private function ref_values($array) {
			$refs = array();
			foreach ($array as $key => $value) {
				$refs[$key] = &$array[$key]; 
			}
			return $refs; 
		}
	}
}

?>