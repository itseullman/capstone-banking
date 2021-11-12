<?php

include_once(CORE_DIR . DIRECTORY_SEPARATOR . 'Oaks.php');

class Item {
	
	const SELECT_LABELS = [
		'category' => 'Categories',
		'origin' => 'Produced By',
		'location' => 'Location',
		'author' => 'Authors',
	];
	
	
	// title	published_date	bib_text	origin_id	document_number	location_id	archive_number	comments	public_url	pdf_url	author
	const TYPES = [
		'item_id' => [
			'create' => 'INTEGER PRIMARY KEY AUTOINCREMENT',
			'insert' => SQLITE3_INTEGER,
			'fk' => false,
		],
		'title' => [
			'create' => 'VARCHAR(500)',
			'insert' => SQLITE3_TEXT,
			'fk' => false,
		],
		'published_date' => [
			'create' => 'VARCHAR(50)',
			'insert' => SQLITE3_TEXT,
			'fk' => false,
		],
		'bib_text' => [
			'create' => 'VARCHAR(500)',
			'insert' => SQLITE3_TEXT,
			'fk' => false,
		],
		'origin_id' => [
			'create' => 'INTEGER',
			'insert' => SQLITE3_INTEGER,
			'fk' => 'origin',
		],
		'document_number' => [
			'create' => 'VARCHAR(128)',
			'insert' => SQLITE3_TEXT,
			'fk' => false,
		],
		'location_id' => [
			'create' => 'INTEGER',
			'insert' => SQLITE3_INTEGER,
			'fk' => 'location',
		],
		'archive_number' => [
			'create' => 'VARCHAR(128)',
			'insert' => SQLITE3_TEXT,
			'fk' => false,
		],
		'comments' => [
			'create' => 'VARCHAR(500)',
			'insert' => SQLITE3_TEXT,
			'fk' => false,
		],
		'public_url' => [
			'create' => 'VARCHAR(500)',
			'insert' => SQLITE3_TEXT,
			'fk' => false,
		],
		'pdf_url' => [
			'create' => 'VARCHAR(500)',
			'insert' => SQLITE3_TEXT,
			'fk' => false,
		],
		'author' => [
			'create' => false,
			'insert' => false,
			'fk' => false,
		],
		'category' => [
			'create' => false,
			'insert' => false,
			'fk' => false,
		],
	];
	
	const VALID_SEARCH_TYPES = 	[
		'title',
		'comments',
		'document_number',
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
	
	public function GetCategories($orderby = 'name') {
		return $this->GetNameIdEntity('category', $orderby);
	}
	
	public function GetOrigins($orderby = 'name') {
		return  $this->GetNameIdEntity('origin', $orderby);
	}
	
	public function GetLocations($orderby = 'name') {
		return  $this->GetNameIdEntity('location', $orderby);
	}
	
	public function GetAuthors($orderby = 'name') {
		return  $this->GetNameIdEntity('author', $orderby);
	}
	
	public function GetCOLA() {
		return [
			'category' => $this->GetCategories(),
			'origin' => $this->GetOrigins(),
			'location' => $this->GetLocations(),
			'author' => $this->GetAuthors(),
		];
	}
	
	public function GetNameIdEntity($table_name, $orderby = 'name') {
		$data = [];
		$order_clause = '';
		
		switch ($orderby) {
			case 'id':
			$order_clause = 'ORDER BY %s_id';
			break;
			
			case 'order':
			$order_clause = 'ORDER BY %s_order';
			break;
			
			case 'name':
			default:
			$order_clause = 'ORDER BY %s_name';
			break;
		}
		
		switch ($table_name) {
			case 'category':
			case 'origin':
			case 'location':
			case 'author':
			$order_clause = sprintf($order_clause, $table_name);
			$result = $this->db->query(sprintf('SELECT %1$s_id, %1$s_name, %1$s_order FROM %1$s %2$s', $table_name, $order_clause));
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
	
	public function GetItems($searches = [], $logic = 'or') {
		if ($logic != 'and') {
			$logic = 'or';
		}
		
		$where = [];
		if (count($searches) > 0) {
			foreach($searches as $search) {
				if (in_array($search['type'], self::VALID_SEARCH_TYPES)) {
					$where[] = sprintf('item.%s LIKE("%%%s%%")', $search['type'], SQLite3::escapeString($search['value']));
				}
			}
		}
		
		if (count($where) == 0) {
			$where = '';
		} else {
			$where = 'WHERE ' . implode(sprintf(' %s ', $logic), $where);
		}
		
		
		$data = []; // GROUP_CONCAT(author.author_name)
		$query = sprintf('SELECT item.title, item.published_date, item.bib_text,
			item.origin_id, item.document_number, item.location_id, item.archive_number,
			item.comments, item.public_url, item.pdf_url, GROUP_CONCAT(author.author_name, ", ") as authors,
			GROUP_CONCAT(category.category_name, ", ") as categories, origin_name, location_name FROM item
			LEFT JOIN author_item on item.item_id = author_item.item_id
			LEFT JOIN author on author_item.author_id = author.author_id
			LEFT JOIN category_item on item.item_id = category_item.item_id
			LEFT JOIN category on category_item.category_id = category.category_id
			LEFT JOIN origin on item.origin_id = origin.origin_id
			LEFT JOIN location on item.location_id = location.location_id
			%s
			GROUP BY item.item_id', $where);
		$result = $this->db->query($query);
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
	
	private function InitItem() {
		echo "<h3>Initializing Item</h3>";
		$data = explode("\n", file_get_contents(DATA_DIR . DIRECTORY_SEPARATOR . 'item.csv'));
		$headers = array_map('trim', explode("\t", array_shift($data)));
		
		$oakSearch = OakSearch::Instance();
		$oakResults = $oakSearch();
		foreach ($oakResults as $oakResult) {
			$data[] = $oakResult->ToItem();
		}
		
		$authors = [];
		$categories = [];
		$origins = [];
		$locations = [];
		$item_id = 1;
		foreach ($data as $key => &$row) {
			if (!is_array($row)) {
				$row = explode("\t", $row);
			}
			$row_values = array_map('trim', $row);
			while(count($row_values) < count($headers)) {
				$row_values[] = '';
			}
			$row = array_combine($headers, $row_values);
			if (strlen($row['title']) == 0) {
				unset($data[$key]);
				continue;
			}
			$row['item_id'] = $item_id;
			$item_id++;
			if (!isset($row['author']) or strlen($row['author']) == 0) {
				$row['author'] = [];
			} else {
				$row['author'] = array_map('trim', explode(';', ucwords($row['author'])));
			}
			foreach ($row['author'] as $author) {
				if (strlen($author) == 0) {
					continue;
				}
				$authors[$author] = $author;
			}
			if (!isset($row['category']) or strlen($row['category']) == 0) {
				$row['category'] = [];
			} else {
				$row['category'] = array_map('trim', explode(';', ucwords($row['category'])));
			}
			foreach ($row['category'] as $category) {
				if (strlen($category) == 0) {
					continue;
				}
				$categories[$category] = $category;
			}
			if (!isset($row['origin_id'])) {
				$row['origin_id'] = '';
			} else if (!empty($row['origin_id'])) {
				$origins[$row['origin_id']] = $row['origin_id'];
			}
			if (!isset($row['location_id'])) {
				$row['location_id'] = '';
			} else if (!empty($row['location_id'])) {
				$locations[$row['location_id']] = $row['location_id'];
			}
		}
		unset($row);
		
		// item_id should not be specified in
		// the item.csv boostrap file.
		$headers[] = 'item_id';
		
		$this->InitNameIdEntity('author', $authors);
		$this->InitNameIdEntity('category', $categories);
		$this->InitNameIdEntity('origin', $origins);
		$this->InitNameIdEntity('location', $locations);
		
		$entityData = [
			'category' => $this->GetNameIdEntity('category'),
			'origin' => $this->GetNameIdEntity('origin'),
			'location' => $this->GetNameIdEntity('location'),
			'author' => $this->GetNameIdEntity('author'),
			'category_item' => [],
			'author_item' => [],
		];
		
		$offset = 0;
		$length = 25;
		$drop_and_create = true;
		while($offset < count($data)) {
			$this->InitItem_(array_slice($data, $offset, $length), $headers, $drop_and_create, $entityData);
			$drop_and_create = false;
			$offset += $length;
		}
		
		$this->InitJunctionIdEntity('category', $entityData['category_item']);
		$this->InitJunctionIdEntity('author', $entityData['author_item']);
		
	}
	
	private function InitItem_($data, &$headers, $drop_and_create, &$entityData) {
		static
			$header_template = [],
			$create_template = [],
			$item_columns = [];
		
		$table_name = 'item';
		// after the first time we call InitItem,
		// $header_template will be made into a string
		// we reuse that data in subsequent invocations
		// of this method.
		if (!is_string($header_template)) {
			foreach ($headers as $header) {
				if (!isset(self::TYPES[$header])) {
					throw new Exception('The format of item.csv is incorrect.');
				} else if (self::TYPES[$header]['create'] === false) {
					continue;
				}
				$header_template[] = sprintf(':item_%s_%%1$d', $header);
				$create_template[] = sprintf('%s %s', $header, self::TYPES[$header]['create']);
				$item_columns[] = $header;
			}
			$header_template = sprintf('(%s)', implode(', ', $header_template));
			$create_template = sprintf('(%s)', implode(', ', $create_template));
			$item_columns = implode(', ', $item_columns);
		}
		
		$field_data = [];
		$placeholders = [];
		foreach ($data as $row_num => $row) {
			$placeholders[] = sprintf($header_template, $row_num);
			foreach ($row as $key => $field) {
				$type_data = self::TYPES[$key];
				if ($key == 'item_id') {
					continue;
				} else if ($type_data['create'] === false) {
					if (count($field) > 0) {
						foreach ($field as $field_item) {
							foreach ($entityData[$key] as $entity_row) {
								if ($entity_row[$key . '_name'] == $field_item) {
									$entityData[$key . '_item'][$row['item_id']] = $entity_row[$key . '_id'];
									break;
								}
							}
						}
						
					}
					continue;
				} else if (is_string($type_data['fk'])) {
					$fk = $type_data['fk'];
					switch (null) {
						default:
						// ride the foreach loop until we find a match.
						// If no match, then we null out $field.
						foreach ($entityData[$fk] as $entityItem) {
							if ($entityItem[$fk . '_name'] == $field) {
								$field = $entityItem[$fk . '_id'];
								break 2;
							}
						}
						$field = null;
					}
				}
				$field_data[sprintf('item_%s_%d', $key, $row_num)] = [
					'value' => $field,
					'key' =>$key,
				];
			}
		}
		
		if ($drop_and_create) {
			$this->db->exec('DROP TABLE IF EXISTS item');
			$this->db->exec(sprintf('CREATE TABLE item %s', $create_template));
		}
		$prepare_template = sprintf('INSERT INTO item (%s) VALUES %s', $item_columns, implode(', ', $placeholders));
		$stmt = $this->db->prepare($prepare_template);
		foreach ($field_data as $placeholder => $datum) {
			$stmt->bindValue($placeholder, $datum['value'], self::TYPES[$datum['key']]['insert']);
		}
		$stmt->execute();
	}
	
	private function InitNameIdEntity($table_name, $data) {
		$texts = [];
		$values = [];
		$placeholders = [];
		
		$this->db->exec(sprintf('DROP TABLE IF EXISTS %s', $table_name));
		$this->db->exec(sprintf('CREATE TABLE %1$s (%1$s_id INTEGER PRIMARY KEY AUTOINCREMENT, %1$s_name VARCHAR(200), %1$s_order INTEGER, UNIQUE(%1$s_name))', $table_name));
		
		if (count($data) < 1) {
			return;
		}
		
		$count = 1;
		foreach ($data as $datum) {
			$placeholders[] = sprintf('(:%2$s_name_%1$d, :%2$s_order_%1$d)', $count, $table_name);
			$texts[sprintf('%s_name_%d', $table_name, $count)] = $datum;
			$values[sprintf('%s_order_%d', $table_name, $count)] = 0;
			$count++;
		}
		
		$stmt = $this->db->prepare(sprintf('INSERT INTO %1$s (%1$s_name, %1$s_order) VALUES %2$s', $table_name, implode(', ', $placeholders)));
		foreach ($texts as $key => $datum) {
			$stmt->bindValue($key, $datum, SQLITE3_TEXT);
		}
		foreach ($values as $key => $datum) {
			$stmt->bindValue($key, $datum, SQLITE3_INTEGER);
		}
		$result = $stmt->execute();
	}
	
	private function InitJunctionIdEntity($part_name, $data) {
		
		$this->db->exec(sprintf('DROP TABLE IF EXISTS %s_item', $part_name));
		$this->db->exec(sprintf('CREATE TABLE %1$s_item (%1$s_id INTEGER, item_id INTEGER, UNIQUE(%1$s_id, item_id))', $part_name));
		
		$values = [];
		$placeholders = [];
		$count = 1;
		foreach ($data as $item_id => $datum) {
			$placeholders[] = sprintf('(:%1$s_id_%2$d, :item_id_%2$d)', $part_name, $count);
			$values[sprintf('%s_id_%d', $part_name, $count)] = $datum;
			$values[sprintf('item_id_%d', $count)] = $item_id;
			$count++;
		}
		
		
		$offset1 = 0;
		$length1 = 25;
		$offset2 = 0;
		$length2 = 50;
		while($offset1 < count($placeholders)) {
			$placeholders_ = array_slice($placeholders, $offset1, $length1);
			$values_ = array_slice($values, $offset2, $length2);
			$offset1 += $length1;
			$offset2 += $length2;
		
			
			$stmt = $this->db->prepare(sprintf('INSERT INTO %s_item (%s_id, item_id) VALUES %s', $part_name, $part_name, implode(', ', $placeholders_)));
			foreach ($values_ as $key => $datum) {
				$stmt->bindValue($key, $datum, SQLITE3_INTEGER);
			}
			$result = $stmt->execute();
		}
	}
	
}

