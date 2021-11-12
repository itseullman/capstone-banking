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

$search_fields = ['w','x','y','z'];
$inputs = [];
foreach ($search_fields as $search_field) {
	$value = '';
	$type = '';
	if (isset($_REQUEST[$search_field])) {
		$value = htmlspecialchars($_REQUEST[$search_field]);
	}
	if (isset($_REQUEST[$search_field. '-type'])) {
		$type = htmlspecialchars($_REQUEST[$search_field . '-type']);
	}
	
	$selecteds = [];
	foreach(Item::VALID_SEARCH_TYPES as $vtype) {
		$selecteds[$vtype] = '';
	}
	
	if (isset($selecteds[$type])) {
		$selecteds[$type] = ' selected';
	}
	
	$inputs[$search_field] = sprintf('
	<input type="search" name="%s" value="%s" placeholder="Enter a search term">
	<select name="%s-type">
		<option value="title"%s>Title</option>
		<option value="comments"%s>Comments</option>
		<option value="document_number"%s>Doc./Archive #</option>
	</select>
	', $search_field, $value, $search_field, $selecteds['title'], $selecteds['comments'], $selecteds['document_number']);
}

$item = Item::Instance();

$cola = $item->GetCOLA();
$select_dropdowns = '';
foreach ($cola as $type => $select_data) {
	$options = '<option value="-1" selected>All</option>' . PHP_EOL;
	$select_size = min(10, count($select_data) + 1); 
	foreach ($select_data as $select_option) {
		$options .= sprintf('<option value="%d">%s</option>%s', $select_option[$type . '_id'], $select_option[$type . '_name'], PHP_EOL);
	}
	$select_label = Item::SELECT_LABELS[$type];
	$select_dropdowns .= <<<EOT
			<div>
				<button type="button" class="filter-button" data-display-$type-filter>$select_label</button>
				<div style="position: relative" class="hidden">
					<select multiple name="{$type}_id" size="$select_size" style="position: absolute">
	$options
					</select>
				</div>
			</div>
			
EOT;
}


$authors = $item->GetAuthors();
$author_options = '<option value="-1" selected>All</option>' . PHP_EOL;
foreach ($authors as $author) {
	$author_options .= sprintf('<option value="%d">%s</option>%s', $author['author_id'], $author['author_name'], PHP_EOL);
}


	// title, published_date, document_number, archive_number, authors, comments, bib_text, origin_name, categories, location_name, public_url, pdf_url
echo <<<EOT
<section id="page-archive">
	<div style="float: right; width: 200px;"><a href="./index.php?page=oaks">Search KSU OAKs Database</a></div>
	<h1>The Archive</h1>
	
	<div style="display: flex">
		
		<!--
			title, published_date, document_number, archive_number, authors, comments, bib_text, origin_name, categories, location_name, public_url, pdf_url -->
		<form method="get" action="./index.php?page=data&action=search">
		<p>Use any or all of the four search boxes to retrieve items from the database.</p>
			<div>
			{$inputs['w']}
			</div>
			<div>
			{$inputs['x']}
			</div>
			<div>
			{$inputs['y']}
			</div>
			<div>
			{$inputs['z']}
			</div>
			<div>
				<input type="submit" value="Search">
				<button type="button" id="search-all-results" title="Retrieve the entire database">All</button>
				<img class="hidden" src="./img/loading.gif">
				<button type="button" value="or" id="form-logic-button" style="float: right" title="The results of the four search terms above are either unioned or intersected.">Logical OR</button>
			</div>
		</form>
		<div id="archive-search-tools">
			<p>Filter retrieved items by column titles or row values.</p>
			<label>
				<span><input type="checkbox" name="display-field[title]" data-display-field="title"></span>
				<span>Title</span>
			</label>
			<label>
				<span><input type="checkbox" name="display-field[published_date]" data-display-field="published_date"></span>
				<span>Published Date</span>
			</label>
			<label>
				<span><input type="checkbox" name="display-field[document_number]" data-display-field="document_number"></span>
				<span>Document Number</span>
			</label>
			<label>
				<span><input type="checkbox" name="display-field[archive_number]" data-display-field="archive_number"></span>
				<span>Archive Number</span>
			</label>
			<label>
				<span><input type="checkbox" name="display-field[authors]" data-display-field="author"></span>
				<span>Authors</span>
			</label>
			<label>
				<span><input type="checkbox" name="display-field[comments]" data-display-field="comments"></span>
				<span>Comments</span>
			</label>
			<label>
				<span><input type="checkbox" name="display-field[bib_text]" data-display-field="bib_text"></span>
				<span>Bib. Text</span>
			</label>
			<label>
				<span><input type="checkbox" name="display-field[origin_name]" data-display-field="origin"></span>
				<span>Produced By</span>
			</label>
			<label>
				<span><input type="checkbox" name="display-field[categories]" data-display-field="category"></span>
				<span>Categories</span>
			</label>
			<label>
				<span><input type="checkbox" name="display-field[location_name]" data-display-field="location"></span>
				<span>Location</span>
			</label>
			<label>
				<span><input type="checkbox" name="display-field[public_url]" data-display-field="public_url"></span>
				<span>Public URL</span>
			</label>
			<label>
				<span><input type="checkbox" name="display-field[pdf_url]" data-display-field="pdf_url"></span>
				<span>PDF URL</span>
			</label>
			<div>
				<button type="button" class="filter-button" name="data-display-toggle" data-display-toggle="toggle">Invert</button>
				<button type="button" class="filter-button" name="data-display-toggle" data-display-toggle="on">All On</button>
				<button type="button" class="filter-button" name="data-display-toggle" data-display-toggle="off">All Off</button>
			</div>
			<div>
			</div>
$select_dropdowns
		</div>
	</div>
	<div class="ajax-response"></div>

	<table class="search-results-container">
		<thead>
			<tr>
				<th data-table-header="title">
				Title
				</th>
				<th data-table-header="published_date">
				Published Date
				</th>
				<th data-table-header="document_number">
				Document Number
				</th>
				<th data-table-header="archive_number">
				Archive Number
				</th>
				<th data-table-header="author">
				Author(s)
				</th>
				<th data-table-header="comments">
				Comments
				</th>
				<th data-table-header="bib_text">
				Bib. Text
				</th>
				<th data-table-header="origin">
				Produced By
				</th>
				<th data-table-header="category">
				Category
				</th>
				<th data-table-header="location">
				Location
				</th>
				<th data-table-header="public_url">
				Public URL
				</th>
				<th data-table-header="pdf_url">
				PDF URL
				</th>
			</tr>
		</thead>
	<tbody class="search-results-content"></tbody>
	</table>
</section>
<script src="./scripts/archive.js"></script>
EOT;

