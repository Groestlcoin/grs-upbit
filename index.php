<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
$jsonquery = file_get_contents('php://input');
$json = json_decode($jsonquery, true);

$memcache = new Memcached();
$memcache->addServer('localhost', 11211);

// we correct the request data
function mysql_fix_escape_string($text){
    if(is_array($text)) 
        return array_map(__METHOD__, $text); 
    if(!empty($text) && is_string($text)) { 
        return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), 
                           array('', '', '', '', "", '', ''),$text); 
    } 
    $text = str_replace("'","",$text);
    $text = str_replace('"',"",$text);
    return $text;
}

$currency = strtolower($_GET['currency']);
$currency = mysql_fix_escape_string($currency);
$market = $memcache->get($currency);
if ($memcache->getResultCode() == Memcached::RES_SUCCESS) {
    $market = json_encode($market);
	print_r($market);
} else {
    $markets = $memcache->get("markets");
    if ($memcache->getResultCode() == Memcached::RES_SUCCESS) {
        $markets = json_encode($markets);
        print_r($markets);
    } else {
        print_r(json_encode(array(
            "code" => 404,
            "status" => false,
            "error" => "We can't find what you are looking for",
            "server_time" => date("Y-m-d H:i:s")
        )));
    }
}
?>
