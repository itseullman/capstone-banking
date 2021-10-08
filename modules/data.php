<?php

// create curl resource
$ch = curl_init();

// set url
curl_setopt($ch, CURLOPT_URL, OAKS_WWW);

//return the transfer as a string
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

// $output contains the output string
$data = curl_exec($ch);

// close curl resource to free up system resources
curl_close($ch);     

switch (REQUEST_ACTION) {
	case 'all':
	echo "case 'all' not implemented yet.";
	break;
	
	case 'table':
	echo $data;
	break;
	
	default:
	echo "case 'default' not implemented yet.";
	break;
}