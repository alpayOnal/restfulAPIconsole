<?php
require 'vendor/autoload.php';

use Guzzle\Http\Client;
use Guzzle\Http\Message;
use Guzzle\Http\Message\RequestFactory;

class Router
{

	public $host;

	public $path;

	public $method;

	public $version;

	public $headers;

	public $postFields;

	public function __construct()
	{
		error_reporting(E_ALL);
		ini_set("display_errors", 1); 

		$this->r = $_REQUEST;
		
		$this->createParams();
		$this->version = $this->r['version']; //http version
		$this->method = $this->r['method']; 
	}

	public function createParams()
	{
		$this->path = $this->createPath();
		$this->headers = $this->createHeaders();
		$this->postFields = $this->createPostFields();
	}
	
	public function createPath()
	{
		$patht = explode('/',$this->r['path']);
		$path = '';
		if (isset($patht)){
			foreach($patht as $p){
				 $path.= '/'.rawurlencode($p);
			}
		}

		return mb_substr($path,1);
	}

	public  function createHeaders(){

		if (isset($this->r['headerFieldNs'])){
			
			$r = $this->r;	
			$headers = array();
			
			foreach($r['headerFieldNs'] as $i=>$n){
				if($n=='host')
					$this->host = $r['headerFieldVs'][$i];
							
				$headers[$r['headerFieldNs'][$i]] = $r['headerFieldVs'][$i];

			}

			return $headers;
		}

		return false;
	}
	
	public function createPostFields()
	{
		if (isset($this->r['postFieldNs'])){
			
			$r = $this->r;	
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
					$pcount = 0;

				$postFields[$r['postFieldNs'][$i]] = $r['postFieldVs'][$i];

			}

			return $postFields;
		}

		return false;
	}

	public function run()
	{
		$client = new Client();
		$requestFactory = new RequestFactory();
		$request = $requestFactory->fromMessage(
			$this->method .' '.$this->path.' '.$this->version."\r\n" .
			"Host: ".$this->host." \r\n\r\n"
		);

		if (isset($this->postFields) && !in_array($this->method,array('GET','HEAD','TRACE','OPTIONS')))
			$request->addPostFields($this->postFields);

		if (isset($this->headers['host']))
			unset($this->headers['host']);

		$request->addHeaders($this->headers);
		$request->setClient($client);
		$response = $request->send();

		echo $response->getBody();
	}
}

$router = new Router();
$router->run();
