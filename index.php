<?php
// 
################################
# DEFINE SOME GLOBAL CONSTANTS #
#------------------------------#

/*
	Defined constants:
	
		Misc:
	TIMEZONE
	REQUEST_PAGE
	REQUEST_ACTION
	
		Url constants:
	BASE_WWW
	CSS_WWW
	JS_WWW
	IMG_WWW
	OAKS_WWW
	
		Directory constants:
	BASE_DIR
	CSS_DIR
	JS_DIR
	IMG_DIR
	VIEW_DIR
	MODULE_DIR
	CORE_DIR
	DB_DIR
	DATA_DIR
*/
	
	
// Define TIMEZONE
define('TIMEZONE', 'America/New_York');

// Define REQUEST_SCHEME
if ( (! empty($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https') ||
	(! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ||
	(! empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ) {
	define('REQUEST_SCHEME', 'https');
} else {
	define('REQUEST_SCHEME', 'http');
}

// Define BASE_WWW
if (isset($_SERVER['SERVER_NAME'])) {
	define('BASE_WWW', sprintf('%s://%s', REQUEST_SCHEME, $_SERVER['SERVER_NAME']));
} else {
	define('BASE_WWW', '');
}
// Define CSS_WWW
define('CSS_WWW', BASE_WWW . '/css');

// Define JS_WWW
define('JS_WWW', BASE_WWW . '/scripts');

// Define IMG_WWW
define('IMG_WWW', BASE_WWW . '/img');

// Define OAKS_WWW
define('OAKS_WWW', 'https://oaks.kent.edu/api/v1/collections/14349');



// Define BASE_DIR
define('BASE_DIR', getcwd());

// Define CSS_DIR
define('CSS_DIR', BASE_DIR . DIRECTORY_SEPARATOR . 'css');

// Define JS_DIR
define('JS_DIR', BASE_DIR . DIRECTORY_SEPARATOR . 'scripts');

// Define IMG_DIR
define('IMG_DIR', BASE_DIR . DIRECTORY_SEPARATOR . 'img');

// Define VIEW_DIR
define('VIEW_DIR', BASE_DIR . DIRECTORY_SEPARATOR . 'views');

// Define MODULE_DIR
define('MODULE_DIR', BASE_DIR . DIRECTORY_SEPARATOR . 'modules');

// Define CORE_DIR
define('CORE_DIR', BASE_DIR . DIRECTORY_SEPARATOR . 'core');

// Define DB_DIR
define('DB_DIR', BASE_DIR . DIRECTORY_SEPARATOR . 'db');

// Define DATA_DIR
define('DATA_DIR', BASE_DIR . DIRECTORY_SEPARATOR . 'data');

#-------------------------------------#
# END OF DEFINE SOME GLOBAL CONSTANTS #
#######################################


/*
	The db connection is created the first time
	this function is called. Afterwords, the
	db connection is simply returned.
*/
function db() {
	static $db = null;
	if (is_null($db)) {
		$db = new SQLite3(DB_DIR . DIRECTORY_SEPARATOR . 'batcher.db');
	}
	return $db;
}

/* If isAjax() returns true, then no headers and footers will be
	added to the page. And an appropriate header for an
	ajax/json response is added.*/
function isAjax($foo = null) {
	static $flag = false;
	if (!is_null($foo) and is_bool($foo)) {
		$flag = $foo;
	} 
	return $flag;
}

/*
	Normally, $page should = REQUEST_PAGE
	If using some other value, make sure to
	secure it against SQL injection.
*/
function stringReplacements($page, $string) {
	//$db = db();
	//// Need two percent signs inside of sprintf because % is a control
	//// character in sprintf. The first % escapes the second one.
	////
	//// Note that REQUEST_PAGE is safe to inject into the query. The logic of how REQUEST_PAGE
	//// is defined limits the possible values to known and predifined possibilities.
	//$result = $db->query(sprintf('SELECT tag_name, text FROM strings WHERE lang = "en-US" and tag_name LIKE ("%s-%%")', REQUEST_PAGE));
	//if ($result instanceof SQLite3Result) {
	//	$search = [];
	//	$replace = [];
	//	while ($item = $result->fetchArray(SQLITE3_ASSOC)) {
	//		$search[] = sprintf('{{%s}}', $item['tag_name']);
	//		$replace[] = $item['text'];
	//	}
	//	$string = str_replace(
	//		$search,
	//		$replace, 
	//		$string
	//	);
	//}
	return $string;
}

function debug($var) {
	$c = function($stmt) { return $stmt; };
	return <<<EOT
<pre>{$c(print_r($var, true))}</pre>
EOT;
}



// basename ensures that people can't sumbit something like
// page = ../../password.txt
// because that would become: page = password.txt
$page = (isset($_REQUEST['page']) && is_string($_REQUEST['page'])) ? basename($_REQUEST['page']) : '';

// We cannot define REQUEST_PAGE yet because we need to test
// for the existence of some files first. And the value of REQUEST_PAGE
// will reflect the actual page which is loaded.
// define("REQUEST_PAGE", $page);


// Grab the requested action, if there is one.
$action = (isset($_REQUEST['action']) && is_string($_REQUEST['action'])) ? $_REQUEST['action'] : '';
define("REQUEST_ACTION", $action);



// Start a buffer to capture the output which
// will be injected into the body (i.e., {{main-body}})
// of the main.html template.
ob_start();

// Look for a $page corresponding to a .php module:
if (strlen($page) > 0 && file_exists(MODULE_DIR . DIRECTORY_SEPARATOR . $page . '.php')) {
	
	define("REQUEST_PAGE", $page);
	require_once(MODULE_DIR . DIRECTORY_SEPARATOR . $page . '.php');
	
// Look for a $page corresponding to an .html template:
} else {
	
	// The $page must not be empty 
	// and we're allowed to load the main template!
	if (strlen($page) === 0 || $page === 'main') {
		$page === 'home';
	}
	
	$template = '';
	if (file_exists(VIEW_DIR . DIRECTORY_SEPARATOR . $page . '.html')) {
		define("REQUEST_PAGE", $page);
		$template = file_get_contents(VIEW_DIR . DIRECTORY_SEPARATOR . $page . '.html');
	} else {
		define("REQUEST_PAGE", 'home');
		$template = file_get_contents(VIEW_DIR . DIRECTORY_SEPARATOR . 'home.html');
	}
	
	
	// Now output the template so that the output buffer
	// can grab a hold of it. Later we will insert
	// that output into the main.html template.
	echo $template;
	
}


// Grab the html template (or php module output)
$output = ob_get_clean();

if (isAjax()) {
	header('Content-type: application/json');
	echo $output;
} else {
	
	$output = stringReplacements(REQUEST_PAGE, $output);
	
	// If we need to output HTML Headers, this is the place to do it,
	// since at this point, no output has been sent to the browser

	echo str_replace(
		[
			'{{main-body}}', 														// search
			'{{curr_year}}',
		], 														// search
		// Get the contents of the buffer,
		// and wipe the buffer clean.	
		[
			$output, 																// replace
			date('Y'),
		],
		file_get_contents(VIEW_DIR . DIRECTORY_SEPARATOR . 'main.html')		// context
	);
}












