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

$q2 = '';
if (isset($_REQUEST['q2'])) {
	$q2 = htmlspecialchars($_REQUEST['q2']);
}


echo <<<EOT
<section id="page-archive2">
	<h1 style="padding-bottom: 0">Search KSU OAKs Database</h1>
	<p style="padding-bottom: 20px; font-style: italic;">Follow <a href="https://oaks.kent.edu/kenneth-e-batcher-collection">this</a> link to view the "Kenneth E. Batcher Collection: Papers from the Parallel and Associative Computing Laboratory Collections" directly on the KSU OAKs website.</p>
	<form method="get" action="./index.php?page=data&action=oaks">
		<input type="search" value="$q2">
		<input type="submit" value="Search">
		<img class="hidden" src="./img/loading.gif">
	</form>
	
	<div class="ajax-error-response"></div>
	<br>
	<br>
</section>
<script src="./scripts/crypto-core.min.js"></script>
<script src="./scripts/crypto-md5.min.js"></script>
EOT;

