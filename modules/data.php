<?php


function get_oaks_data() {
	$result = file_get_contents(OAKS_WWW, false, stream_context_create(['http' => ['method'  => 'GET']]));
	$result = json_decode($result, true);
	return $result;
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
	case 'all':
	echo "case 'all' not implemented yet.";
	break;
	
	case 'table':
	case_data_table();
	break;
	
	default:
	echo "case 'default' not implemented yet.";
	break;
}




function case_data_table() {
	$data = get_oaks_data();
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





















