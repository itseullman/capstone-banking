<?php


include_once(CORE_DIR . DIRECTORY_SEPARATOR . 'Oaks.php');


		/*(
			[id] => 16621
			[title] => A scalable associative processor with applications in database and image processing
			[author] => Hong Wang;Lei Xie;Meiduo Wu;Robert A. Walker
			[date] => 2004-04
			[public_url] => https://oaks.kent.edu/node/16621
			[pdf_url] => 
			[search_api_excerpt] => 
		)*/

switch (REQUEST_ACTION) {
	// View strings (or intialize the strings table)
	case 'sq3_strings':
	case_sq3_strings();
	break;
	
	// View Oaks meta-data (or intialize the meta_data table)
	case 'sq3_oaks':
	case_sq3_oaks();
	break;
	
	case 'sq3_query':
	case_sq3_query();
	break;
	
	case 'table':
	case_data_table();
	break;
	
	case 'oaks':
	isAjax(true);
	case_oaks();
	break;
	
	case 'search':
	isAjax(true);
	case_search();
	break;
	
	
	default:
	echo "case 'default' not implemented yet.";
	break;
}


function case_search() {
	
	
	
	$valid_types = [
		'title',
		'author',
		'comments',
		'document_number',
	];
	
	include_once(CORE_DIR . DIRECTORY_SEPARATOR . 'Item.php');
	
	$search_fields = ['w','x','y','z'];
	$searches = [];
	foreach ($search_fields as $search_field) {
		if (isset($_REQUEST[$search_field])) {
			$value = trim($_REQUEST[$search_field]);
			if (strlen($value) > 0) {
				$type = 'title';
				if (isset($_REQUEST[$search_field. '-type']) && in_array($_REQUEST[$search_field. '-type'], Item::VALID_SEARCH_TYPES)) {
					$type = $_REQUEST[$search_field . '-type'];
				}
				$searches[] = [
					'value' => $value,
					'type' => $type,
				];
			}
		}
	}
	
	if (!isset($_REQUEST['logic']) or $_REQUEST['logic'] != 'and') {
		$logic = 'or';
	} else {
		$logic = 'and';
	}
	
	$item = Item::Instance();
	echo json_encode(['items' => $item->GetItems($searches, $logic)]);
}


function case_oaks() {
	if (!isset($_REQUEST['q'])) {
		echo json_encode(['error' => 'A search term is required.']);
		return;
	}
	$q = trim($_REQUEST['q']);
	if (strlen($q) < 3) {
		echo json_encode(['error' => 'The search string must be at least three characters long.']);
		return;
	}
	echo json_encode(OakSearch::Instance()($q));
}



function case_sq3_query() {
	$query = null;
	$results = [];
	if (isset($_REQUEST['query'])) {
		$db = db();
		$data = $db->query($_REQUEST['query']);
		while ($row = $data->fetchArray()) {
			$results[] = implode(",\t", $row);
		}
	}
	$results = implode('<br>', $results);
	
echo <<<EOT
<form method="POST" action="">
<input type="text" name="query" style="width: 400px">
<input type="submit" name="Execute">
</form>
<div>
$results
</div>
EOT;
}



function case_sq3_oaks() {
	$db = db();
	
	//$db->exec('DROP TABLE IF EXISTS oaks_meta');
	//$db->exec('CREATE TABLE oaks_meta (meta_name VARCHAR(50), meta_value VARCHAR(200), meta_order INT, UNIQUE(meta_name))');
	//$result = @$db->exec("INSERT INTO oaks_meta (meta_name, meta_value, meta_order) VALUES ('meta_name', 'meta_value', 0)");
	//var_dump($result);
	
	$result = @$db->query('SELECT meta_name, meta_value, meta_order FROM oaks_meta');
	var_dump($result->fetchArray(SQLITE3_ASSOC));
}


function case_sq3_strings() {
	$db = db();
	
	/*
	$db->exec('DROP TABLE IF EXISTS strings');
	$db->exec('CREATE TABLE strings (tag_name VARCHAR(50), text VARCHAR(2000), lang VARCHAR(20))');
	$db->exec("INSERT INTO strings (tag_name, text, lang) VALUES ('data-test1', 'This is the first test of the language system.', 'en-US')");
	*/
	
	//$db->exec("INSERT INTO strings (tag_name, text, lang) VALUES ('data-test2', 'This string, and the one above it where pulled from the database.', 'en-US')");
	$result = $db->query('SELECT tag_name, text, lang FROM strings');
	var_dump($result->fetchArray(SQLITE3_ASSOC));
}



function case_data_table() {
	
	/*(
		[id] => 16621
		[title] => A scalable associative processor with applications in database and image processing
		[author] => Hong Wang;Lei Xie;Meiduo Wu;Robert A. Walker
		[date] => 2004-04
		[public_url] => https://oaks.kent.edu/node/16621
		[pdf_url] => 
		[search_api_excerpt] => 
	)*/
	
	$oaks = OakSearch::Instance();
	$data = $oaks();
	
	$output = '';
	foreach ($data as $item) {
		$output .= <<<EOT
	<tr>
		<td>
		{$item->title}
		</td>
		<td>
		{$item->author}
		</td>
		<td>
		{$item->date_formatted}
		</td>
		<td>
		{$item->public_url}
		</td>
		<td>
		{$item->pdf_url}
		</td>
	</tr>

EOT;
	}
	
	$meta = OakMeta::Instance();
	$min_year = $meta->Get('year-min');
	$max_year = $meta->Get('year-max');

	if (!is_array($min_year)) {
		$min_year = "Not set yet";
	} else {
		$min_year = $min_year['meta_value'];
	}
	if (!is_array($max_year)) {
		$max_year = "Not set yet";
	} else {
		$max_year = $max_year['meta_value'];
	}
	
	
	echo <<<EOT
<p>{{data-test1}}</p>
<p>{{data-test2}}</p>
<div><br>
Min year: $min_year<br>
Max year: $max_year<br>
</div>
<table>
	<tr>
		<th>
		Title
		</th>
		<th>
		Author
		</th>
		<th>
		Date
		</th>
		<th>
		Public Url
		</th>
		<th>
		PDF Url
		</th>
	</tr>
$output
</table>
EOT;
}





















