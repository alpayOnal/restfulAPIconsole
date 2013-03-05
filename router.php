<?php
class quickCurl
{
	public static function run($url,$path,$reqtype="POST",
		$httpv="HTTP/1.1",$headers=false,$data=null){ 
		
		if (!function_exists('curl_init')){
			die('Curl is not installed!');
		}
 
		$ch = curl_init(); 
        	curl_setopt($ch, CURLOPT_URL,$url.'/'.$path);
                
		$headerst = array($reqtype." ".$path." ".$httpv); 
		
		if ($headers)
			foreach($headers as $k) $headerst[]=$k;
		
		if ($data){
			$headerst[]="Content-length: ".strlen($data);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		
      		curl_setopt($ch, CURLOPT_HTTPHEADER, $headerst);
      		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
      		curl_setopt($ch, CURLOPT_TIMEOUT, 30); 
		
		$result = curl_exec($ch); 		
		curl_close($ch); 		
	 
		return $result;
	}
}

$r=$_REQUEST;

$patht=explode('/',$r['path']);
$path='';
if (isset($patht))
	foreach($patht as $p){
		 $path.='/'.rawurlencode($p);
	}

$version=$r['version'];
$method=$r['method'];

if (isset($r['headerFieldNs'])){
	
	$headers=array();
	
	foreach($r['headerFieldNs'] as $i=>$n){
		if($n=='host')
			$host=$r['headerFieldVs'][$i];
					
		$headers[]=$n.':'.$r['headerFieldVs'][$i];
		
		$dataFields[$r['headerFieldNs'][$i]]=$r['headerFieldVs'][$i];
	}	
}


if (isset($r['postFieldNs'])){
	
	$postFields=array();
	
	foreach($r['postFieldNs'] as $i=>$n)
		$postFields[$r['postFieldNs'][$i]]=$r['postFieldVs'][$i];
	
	$postdata = http_build_query($postFields);
	
}else $postdata=null;

echo quickCurl::run($host,$path,$method,$version,$headers,$postdata);

?>
