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

echo <<<EOT
<section id="page-archive">
	<h1>The Archive</h1>
	<div><br>
		Min Year: $min_year<br>
		Max Year: $max_year<br>
	</div>
	<form method="get" action="./index.php?page=data&action=oaks">
		<input type="search">
		<input type="submit" value="Search">
		<img class="hidden" src="./img/loading.gif">
	</form>
	<div class="ajax-response"></div>
</section>
<script src="./scripts/archive.js"></script>
EOT;

