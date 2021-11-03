<?php

include_once(CORE_DIR . DIRECTORY_SEPARATOR . 'Oaks.php');
include_once(CORE_DIR . DIRECTORY_SEPARATOR . 'Item.php');
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

$q = '';
if (isset($_REQUEST['q'])) {
	$q = htmlspecialchars($_REQUEST['q']);
}

$item = Item::Instance();
$datasets = [
	'category' => $item->GetCategories(),
	'origin' => $item->GetOrigins(),
	'location' => $item->GetLocations(),
	'author' => $item->GetAuthors(),
];

$lists = [
	'category' => '',
	'origin' => '',
	'location' => '',
	'author' => '',
];

foreach ($datasets as $key => $dataset) {
	foreach ($dataset as $data) {
		$lists[$key] .= sprintf('<span>%s</span><br>%s', $data[$key . '_name'], PHP_EOL);
	}
}

$item_list = '';
foreach ($item->GetItems() as $row_num => $record) {
	if ($row_num === 0) {
		continue;
	}
	
	// title, published_date, bib_text, origin_id, document_number, location_id, archive_number, comments, public_url, pdf_url
	$item_list .= <<<EOT
	<tr>
		<td>
		{$record['title']}
		</td>
		<td>
		{$record['published_date']}
		</td>
		<td>
		{$record['document_number']}
		</td>
		<td>
		{$record['authors']}
		</td>
		<td>
		{$record['comments']}
		</td>
		<td>
		{$record['bib_text']}
		</td>
	</tr>
	
EOT;
}

echo <<<EOT
<section id="page-archive">
	<h1>The Archive</h1>
	<div><br>
		Min Year: $min_year<br>
		Max Year: $max_year<br>
	</div>
	<form method="get" action="./index.php?page=data&action=oaks">
		<input type="search" value="$q">
		<input type="submit" value="Search">
		<img class="hidden" src="./img/loading.gif">
	</form>
	<div class="ajax-response"></div>
</section>
<div id="page-archive-category">
<h3>Category List</h3>
{$lists['category']}
</div>
<div id="page-archive-origin">
<h3>Origin List</h3>
{$lists['origin']}
</div>
<div id="page-archive-location">
<h3>Location List</h3>
{$lists['location']}
</div>
<div id="page-archive-author">
<h3>Author List</h3>
{$lists['author']}
</div>

<table>
	<tr>
		<th>
		Title
		</th>
		<th>
		Published Date
		</th>
		<th>
		Document Number
		</th>
		<th>
		Author(s)
		</th>
		<th>
		Comments
		</th>
		<th>
		Bib. Text
		</th>
	</tr>
$item_list
</table>
<script src="./scripts/archive.js"></script>
EOT;

