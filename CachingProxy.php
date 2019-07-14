<?php

//caching proxy for jQuery addon Ajax Cross Origin http://www.ajax-cross-origin.com/
//works for get requests, post requests untested
//make folder 'proxyCache' and set owner/group www-data and make sure 770 perms are set
//requires php fpm for updating cache after connection to client closed, edit out line 59+ if you don't have php-fpm
//or dont need the feature

//Adjust these values to your needs, values are in seconds
$normalCacheLifeTime = 3600*4; //Max Age to serve from cache without error. Requests from server immediately after this age
$normalCacheUpdateTime = 3600*1; //Update cache after serving result, if cached result age is longer then this value
$cacheLifeTimeError = 3600*96; //If http response fails, and '$normalCacheLifeTime' expires serve upto this age
$cacheDeleteLifeTime = 86400*7; //Delete cache results older then this
$requestTimeout = 180; //Timeout for responses

ini_set('default_socket_timeout', $requestTimeout);

$url = (isset($_GET['url'])) ? $_GET['url'] : false;
if(!$url) exit;

$referer = (isset($_SERVER['HTTP_REFERER'])) ? strtolower($_SERVER['HTTP_REFERER']) : false;
$is_allowed = $referer && strpos($referer, strtolower($_SERVER['SERVER_NAME'])) !== false; //deny abuse of your proxy from outside your site


if($is_allowed == true){

    $cacheFile = "proxyCache/".hash('md5', $url).".html"; // Create a unique name for the cache file using a quick md5 hash

    if(file_exists($cacheFile) && filemtime($cacheFile) > (time() - $normalCacheLifeTime)){
        $response = file_get_contents($cacheFile);
    } else {
        $response = utf8_encode(file_get_contents($url));
        if(strlen($response) > 0){ //A response should produce a result longer then 0
            file_put_contents($cacheFile, $response, LOCK_EX);
        } else{
            if(file_exists($cacheFile) && filemtime($cacheFile) > (time() - $cacheLifeTimeError)){
                $response = file_get_contents($cacheFile);
            } else {
                $response = "No Results Found"; //In the case no result/response was gotten
            }
        }
    }
} else {
    $response = "You aren't not allowed to use this resource";
}

$json = json_encode($response);
$callback = (isset($_GET['callback'])) ? $_GET['callback'] : false;
if($callback){
    $jsonp = "$callback($json)";
    header('Content-Type: application/javascript');
    echo $jsonp;
    //exit;
} else {
    echo $json;
}

//Closes connection to user and continues running script
fastcgi_finish_request();

if($is_allowed == true){

    //Deletes old cache files
    deleteOldFilesInCache($cacheDeleteLifeTime);

    //Updates cache if request is older then fetchCacheUpDateLifeTime seconds
    if(file_exists($cacheFile) && filemtime($cacheFile) < (time() - $normalCacheUpdateTime)){
        $response = utf8_encode(file_get_contents($url));
        if(strlen($response) > 0){ //A response should produce a result longer then 0
            file_put_contents($cacheFile, $response, LOCK_EX);
        }
    }
}

//*********************************************Functions****************************************************************
function deleteOldFilesInCache($maxAgeSec){
    $files = glob("proxyCache/*"."html");

    foreach ($files as $file) {
        if(time() - filemtime($file) >= $maxAgeSec){
            unlink($file);
        }
    }
}

?>
