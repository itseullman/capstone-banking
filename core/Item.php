<?php

include_once(CORE_DIR . DIRECTORY_SEPARATOR . 'Oaks.php');

class Item {
	
	const TYPES = [
		'item_id' => [
			'INTEGER PRIMARY KEY AUTOINCREMENT',
			SQLITE3_INTEGER
		],
		'title' => [
			'VARCHAR(500)',
			SQLITE3_TEXT
		],
		'published_date' => [
			'VARCHAR(50)',
			SQLITE3_TEXT
		],
		'bib_text' => [
			'VARCHAR(500)',
			SQLITE3_TEXT
		],
		'origin_id' => [
			'INTEGER',
			SQLITE3_INTEGER
		],
		'document_number' => [
			'VARCHAR(128)',
			SQLITE3_TEXT
		],
		'location_id' => [
			'INTEGER',
			SQLITE3_INTEGER
		],
		'archive_number' => [
			'VARCHAR(128)',
			SQLITE3_TEXT
		],
		'comments' => [
			'VARCHAR(500)',
			SQLITE3_TEXT
		],
		'public_url' => [
			'VARCHAR(500)',
			SQLITE3_TEXT
		],
		'pdf_url' => [
			'VARCHAR(500)',
			SQLITE3_TEXT
		],
	];
	
	static public function Instance() {
		static $instance = null;
		if (is_null($instance)) {
			$instance = new Item();
		}
		return $instance;
	}
	
	private $db;
	
	private function __construct() {
		$this->db = db();
		if ($this->Count('category') === -1) {
			$this->InitCategory();
		}
		if ($this->Count('origin') === -1) {
			$this->Initorigin();
		}
		if ($this->Count('location') === -1) {
			$this->InitLocation();
		}
		if ($this->Count('author') === -1) {
			$this->InitAuthor();
		}
		if ($this->Count('item') === -1) {
			$this->InitItem();
		}
	}
	
	//public function Get($name) {
	//	$stmt = $this->db->prepare('SELECT meta_name, meta_value, meta_order FROM oaks_meta WHERE meta_name = :meta_name');
	//	$stmt->bindValue(':meta_name', $name, SQLITE3_TEXT);
	//	if( ($result = @$stmt->execute()) ) {
	//		return $result->fetchArray(SQLITE3_ASSOC);
	//	}
	//	return null;
	//}
	//
	///* Returns true or false, depending on whether the value was set. */
	//public function Set($name, $value, $ordering = 0) {
	//	$ordering = intval($ordering);
	//	$stmt = $this->db->prepare('INSERT INTO oaks_meta (meta_name, meta_value, meta_order) VALUES (:meta_name, :meta_value, :meta_order)');
	//	$stmt->bindValue(':meta_name', $name, SQLITE3_TEXT);
	//	$stmt->bindValue(':meta_value', $value, SQLITE3_TEXT);
	//	$stmt->bindValue(':meta_order', $ordering, SQLITE3_INTEGER);
	//
	//	if(!@$stmt->execute()) {
	//		$stmt = $this->db->prepare('UPDATE oaks_meta SET meta_value = :meta_value, meta_order = :meta_order WHERE  meta_name = :meta_name');
	//		$stmt->bindValue(':meta_name', $name, SQLITE3_TEXT);
	//		$stmt->bindValue(':meta_value', $value, SQLITE3_TEXT);
	//		$stmt->bindValue(':meta_order', $ordering, SQLITE3_INTEGER);
	//		return @$stmt->execute();
	//	}
	//	return false;
	//}
	
	public function GetCategories() {
		return $this->GetNameIdEntity('category');
	}
	
	public function GetOrigins() {
		return  $this->GetNameIdEntity('origin');
	}
	
	public function GetLocations() {
		return  $this->GetNameIdEntity('location');
	}
	
	public function GetAuthors() {
		return  $this->GetNameIdEntity('author');
	}
	
	private function GetNameIdEntity($table_name) {
		$data = [];
		switch ($table_name) {
			case 'category':
			case 'origin':
			case 'location':
			case 'author':
			$result = $this->db->query(sprintf('SELECT %1$s_id, %1$s_name, %1$s_order FROM %1$s', $table_name));
			if($result) {
				while($row = $result->fetchArray(SQLITE3_ASSOC)) {
					if ($row) {
						$data[] = $row;
					}
				}
			}
			
			default:
		}
		return $data;
	}
	
	public function GetItems() {
		$data = [];
		$result = $this->db->query('SELECT title, published_date, bib_text, origin_id, document_number,
			location_id, archive_number, comments, public_url, pdf_url FROM item');
		if($result) {
			while($row = $result->fetchArray(SQLITE3_ASSOC)) {
				if ($row) {
					$data[] = $row;
				}
			}
		}
		return $data;
	}
	
	public function Count($table_name) {
		// A value of -1 indicates an error.
		// Perhaps the query failed or
		// no matching table was found
		// in the switch block.
		$row_count = -1;
		$result = false;
		switch ($table_name) {
			case 'category':
			case 'origin':
			case 'location':
			case 'author':
			case 'item':
			$result = @$this->db->query(sprintf('select count(*) as row_count from %s', $table_name));
		}
		if ($result) {
			$row = $result->fetchArray(SQLITE3_ASSOC);
			if ($row) {
				$row_count = $row['row_count'];
			}
		}
		return $row_count;
	}
	
	private function InitCategory() {
		echo "<h3>Initializing category</h3>";
		$this->InitNameIdEntity('category');
	}
	
	private function Initorigin() {
		echo "<h3>Initializing origin</h3>";
		$this->InitNameIdEntity('origin');
	}
	
	private function InitLocation() {
		echo "<h3>Initializing location</h3>";
		$this->InitNameIdEntity('location');
	}
	
	private function InitAuthor() {
		echo "<h3>Initializing author</h3>";
		$this->InitNameIdEntity('author');
	}
	
	private function InitItem() {
		echo "<h3>Initializing Item</h3>";
		$table_name = 'item';
		$data = explode("\n", file_get_contents(DATA_DIR . DIRECTORY_SEPARATOR . $table_name . '.csv'));
		$offset = 0;
		$length = 25;
		$drop_and_create = true;
		$headers = array_map('trim', explode("\t", array_shift($data)));
		
		$oakSearch = OakSearch::Instance();
		$oakResults = $oakSearch();
		foreach ($oakResults as $oakResult) {
			$data[] = array_values($oakResult->ToItem());
		}
		
		while($offset < count($data)) {
			$this->InitItem_(array_slice($data, $offset, $length), $headers, $drop_and_create);
			$drop_and_create = false;
			$offset += $length;
		}
	}
	
	private function InitItem_($data, $headers, $drop_and_create) {
		$table_name = 'item';
		$header_template = [];
		$create_template = [];
		$attribute_list = '';
		foreach ($headers as $key => $header) {
			$header_template[] = sprintf(':%s_%s_%%1$d', $table_name, $header);
			if (!isset(self::TYPES[$header])) {
				throw new Exception('The format of item.csv is incorrect.');
			}
			$create_template[] = sprintf('%s %s', $header, self::TYPES[$header][0]);
		}
		$header_template = sprintf('(%s)', implode(', ', $header_template));
		$create_template = sprintf('(%s)', implode(', ', $create_template));
		$field_data = [];
		$placeholders = [];
		foreach ($data as $row_num => $row) {
			if (!is_array($row)) {
				$row = array_map('trim', explode("\t", $row));
			}
			$placeholders[] = sprintf($header_template, $row_num);
			foreach ($row as $key => $field) {
				$field_data[sprintf('%s_%s_%d', $table_name, $headers[$key], $row_num)] = $field;
			}
		}
		
		if ($drop_and_create) {
			$this->db->exec(sprintf('DROP TABLE IF EXISTS %s', $table_name));
			$this->db->exec(sprintf('CREATE TABLE %s %s', $table_name, $create_template));
		}
		$prepare_template = sprintf('INSERT INTO %s (%s) VALUES %s', $table_name,
			implode(', ', $headers), implode(', ', $placeholders));
		$stmt = $this->db->prepare($prepare_template);
		foreach ($field_data as $key => $datum) {
			if ($key === sprintf('%s_%s_id_%d', $table_name, $table_name, $key)) {
				$stmt->bindValue($key, $datum, SQLITE3_INTEGER);
			} else {
				$stmt->bindValue($key, $datum, SQLITE3_TEXT);
			}
		}
		$stmt->execute();
	}
	
	private function InitNameIdEntity($table_name) {
		switch ($table_name) {
			case 'category':
			case 'origin':
			case 'location':
			case 'author':
			$data = explode("\n", file_get_contents(DATA_DIR . DIRECTORY_SEPARATOR . $table_name . '.csv'));
			// We don't need the headers. We put
			// them in the .csv file to document
			// what the fields are.
			$headers = array_shift($data);
			$texts = [];
			$values = [];
			$placeholders = [];
			foreach ($data as $key => $datum) {
				$datum = explode(',', $datum);
				if (!isset($datum[1])) {
					$datum[1] = 0; // default sort order
				}
				$placeholders[] = sprintf('(:%2$s_name_%1$d, :%2$s_order_%1$d)', $key, $table_name);
				$texts[sprintf('%s_name_%d', $table_name, $key)] = $datum[0];
				$values[sprintf('%s_order_%d', $table_name, $key)] = $datum[1];
			}
			
			$this->db->exec(sprintf('DROP TABLE IF EXISTS %s', $table_name));
			$this->db->exec(sprintf('CREATE TABLE %1$s (%1$s_id INTEGER PRIMARY KEY AUTOINCREMENT, %1$s_name VARCHAR(200), %1$s_order INT, UNIQUE(%1$s_name))', $table_name));
			$stmt = $this->db->prepare(sprintf('INSERT INTO %1$s (%1$s_name, %1$s_order) VALUES %2$s', $table_name, implode(', ', $placeholders)));
			foreach ($texts as $key => $datum) {
				$stmt->bindValue($key, $datum, SQLITE3_TEXT);
			}
			foreach ($values as $key => $datum) {
				$stmt->bindValue($key, $datum, SQLITE3_INTEGER);
			}
			$stmt->execute();
			
			default:
		}

	}
	
}

