<?php

isAjax(true);

function get_oaks_data($search_term) {
	return file_get_contents(OAKS_WWW . '/' . urlencode($search_term), false, stream_context_create(['http' => ['method'  => 'GET']]));
}


/*
  object(stdClass)#1 (7) {
    ["id"]=>
    string(5) "16621"
    ["title"]=>
    string(83) "A scalable associative processor with applications in database and image processing"
    ["author"]=>
    string(44) "Hong Wang;Lei Xie;Meiduo Wu;Robert A. Walker"
    ["date"]=>
    string(7) "2004-04"
    ["public_url"]=>
    string(32) "https://oaks.kent.edu/node/16621"
    ["pdf_url"]=>
    string(0) ""
    ["search_api_excerpt"]=>
    string(0) ""
  }
*/

switch (REQUEST_ACTION) {
	case 'sq3':
	case_sq3_test();
	break;
	
	case 'table':
	case_data_table();
	break;
	
	case 'oaks':
	case_oaks();
	break;
	
	default:
	echo "case 'default' not implemented yet.";
	break;
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
	echo get_oaks_data($q);
}



function case_sq3_test() {
	$db = new SQLite3(DB_DIR . DIRECTORY_SEPARATOR . 'batcher.db');
	
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
	$data = json_decode(get_oaks_data(), true);
	$output = '';
	if (is_array($data)) {
		foreach ($data as $item) {
			$output .= <<<EOT
	<tr>
		<td>
		{$item['title']}
		</td>
		<td>
		{$item['author']}
		</td>
		<td>
		{$item['date']}
		</td>
		<td>
		{$item['public_url']}
		</td>
		<td>
		{$item['pdf_url']}
		</td>
	</tr>
EOT;
		}
	}
	
	echo <<<EOT
<p>{{data-test1}}</p>
<p>{{data-test2}}</p>
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





















