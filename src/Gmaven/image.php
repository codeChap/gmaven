<?php

  /**
   * Independant Image Loader
   */

  // Autoload composer
  require(realpath(__DIR__.'/../../../../').'/autoload.php');

  // Find Gmaven Key
  $key = include(__DIR__ . DIRECTORY_SEPARATOR . 'private.php');

  // Security
  $inputs = filter_input_array(INPUT_GET, array(
      'm' => FILTER_SANITIZE_NUMBER_INT,
      'c' => FILTER_UNSAFE_RAW,
      's' => FILTER_UNSAFE_RAW,
    )
  );

  // Params
  $modified = $inputs['m'];
  $cdk      = $inputs['c'];
  $size     = $inputs['s'];

  // Find size
  switch($size){
    
    case 'medium' :
    $resize = '800,600';
    break;
    
    default :
    $resize = '300,300';
    break;
  }

  // Set client
  $client = new \GuzzleHttp\Client(
    array(
      'base_uri' => 'https://www.gmaven.com/api/'
    )
  );

  // Set response
  $response = $client->request('GET', 'data/content/entity/'.$cdk.'?resize='.$resize, [
    'headers' => [
      "gmaven.apiKey" => $key
    ]
  ]);

  // Get returned status code of request
  if($response->getStatusCode()){

    // Pull contents
    $contents = $response->getBody()->getContents();
    $cached_for = 86400*1;
    
    // Set image Headers
    header("Content-Type: image/png");
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $modified) . ' GMT');
    header("Content-Length: " . strlen($contents));
    header("Cache-control: max-age=".$cached_for);
    header("Expires: ".gmdate(DATE_RFC1123, time()+$cached_for));
    header("ETag: ".md5($cdk.$size));
    //header("Content-Disposition: filename=" . $name);
    ob_clean();
    print $contents;
  }
?>