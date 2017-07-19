<?php

	/**
	* Independant Image Loader
	*/

	// Autoload composer
	require(realpath(__DIR__.'/../../../').'/autoload.php');

	// Find Gmaven Key
	//$key = include(__DIR__ . DIRECTORY_SEPARATOR . 'private/key.php');

	$key = "CweWeb:d7d4f40pf4de89863amzcbcd2e3o6bf2c08ee48m293ff432183bd13w05bc1bf188nee29";

	// Security
	$inputs = filter_input_array(INPUT_GET, [
			'm' => FILTER_SANITIZE_NUMBER_INT,
			'c' => FILTER_UNSAFE_RAW,
			's' => FILTER_UNSAFE_RAW,
		]
	);

	// Params
	$modified = $inputs['m'];
	$cdk      = $inputs['c'];
	$size     = $inputs['s'];

	// Find size
	switch($size){
		case 'thumb' :
			$resize = '150,150';
		break;

		case 'medium' :
			$resize = '800,600';
		break;

		default :
			$resize = '300,300';
		break;
	}

	// Set client
	$client = new \GuzzleHttp\Client(['base_uri' => 'https://www.gmaven.com/api/']);

	// Set response
	$response = $client->request('GET', 'data/content/entity/'.$cdk.'?resize='.$resize, [
		'headers' => [
			"gmaven.apiKey" => $key
		]
	]);

	// Get returned status code of request
	if($response->getStatusCode() == 200){

		// Pull contents
		$contents = $response->getBody()->getContents();
		$cached_for = 86400*1;
		
		// Set image Headers
		header("Content-Type: image/png");
		//header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $modified) . ' GMT');
		header("Content-Length: " . strlen($contents));
		header("Cache-control: max-age=".$cached_for);
		header("Expires: ".gmdate(DATE_RFC1123, time()+$cached_for));
		header("ETag: ".md5($cdk.$size));
		//header("Content-Disposition: filename=" . $name);
		ob_clean();
		print $contents;
	}
?>