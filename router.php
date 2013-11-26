<?php
require 'vendor/autoload.php';
error_reporting(E_ALL);
ini_set("display_errors", 1); 

$r = $_REQUEST;

$patht = explode('/',$r['path']);
$path = '';
if (isset($patht))
	foreach($patht as $p){
		 $path.= '/'.rawurlencode($p);
	}

$path = mb_substr($path,1);
$version = $r['version'];
$method = $r['method'];

if (isset($r['headerFieldNs'])){
	
	$headers = array();
	
	foreach($r['headerFieldNs'] as $i=>$n){
		if($n=='host')
			$host = $r['headerFieldVs'][$i];
					
		$headers[] = $n.':'.$r['headerFieldVs'][$i];
		
		$dataFields[$r['headerFieldNs'][$i]] = $r['headerFieldVs'][$i];
	}	
}


if (isset($r['postFieldNs'])){
	
	$postFields = array();
	$pname = '';
	foreach($r['postFieldNs'] as $i=>$n){
		
		if (strstr($n,'[]')){

			if ($pname!=str_replace('[]','',$n))
				$pcount = 0;

			$pname = str_replace('[]','',$n);
			$r['postFieldNs'][$i] = $pname.'['.$pcount.']';
			$pcount++;
		}else
			$pcount =0;

		$postFields[$r['postFieldNs'][$i]] = $r['postFieldVs'][$i];
	}
}

use Guzzle\Http\Client;
use Guzzle\Http\Message;
use Guzzle\Http\Message\RequestFactory;

$client = new Client();
$requestFactory = new RequestFactory();
$request = $requestFactory->fromMessage(
	$method .' '.$path.' '.$version."\r\n" .
	"Host: ".$host." \r\n\r\n"
);

// method kontrolü yapılmalı. GET HEAD TRACE OPTIONS 
if (isset($postFields))
	$client->addPostFields($postFields);

if (isset($dataFields['host']))
	unset($dataFields['host']);

$request->addHeaders($dataFields);
$request->setClient($client);
$response = $request->send();

echo $response->getBody();
?>
