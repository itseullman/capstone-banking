<?php

include_once(CORE_DIR . DIRECTORY_SEPARATOR . 'Oaks.php');
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


echo <<<EOT
<section id="page-archive">
	<h1>Search KSU OAKs Database</h1>
	<form method="get" action="./index.php?page=data&action=oaks">
		<input type="search" value="$q">
		<input type="submit" value="Search">
		<img class="hidden" src="./img/loading.gif">
	</form>
	
	<div class="ajax-response"></div>
</section>

<script src="./scripts/archive.js"></script>
EOT;

