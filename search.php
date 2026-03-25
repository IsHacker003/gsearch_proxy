<?php
$cookies = $_SERVER['HTTP_COOKIE'];
require_once 'vendor/autoload.php';
$resolver = new \NetDNS2\Resolver(
[
    'nameservers'   => [ '2606:4700:4700::1111', '1.1.1.1' ],

    'cache_type'    => \NetDNS2\Cache::CACHE_TYPE_FILE,
    'cache_options' => [

        'file'  => '/dev/shm/google_cache.txt',
        'size'  => 50000,
        'ttl_override' => 3600
    ]
]);
$ip = $_SERVER["REMOTE_ADDR"];
if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
    $resp = $resolver->query("www.google.com", 'A');
    $hname = $resp->answer[0]->address;
}
elseif(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
    $resp = $resolver->query("www.google.com", 'AAAA');
    $rawhname = $resp->answer[0]->address;
    $hname = "[$rawhname]";
}
else {
  die("Unable to determine IP version.");
}
$searchq = $_SERVER['REQUEST_URI'];
$search = "https://$hname$searchq";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $search);
curl_setopt($ch, CURLOPT_ENCODING, "");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_AUTOREFERER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
//curl_setopt($ch, CURLOPT_COOKIEJAR, "cookies.txt");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Host: www.google.com',
    'X-Forwarded-For: 2a02:6ea0:d411:2415::11',
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',
    'Cookie: ' . $cookies,
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);

$response = str_replace("<head>",'<head><script src="/blocker.js"></script>',$response);

$response = str_replace("www.googletagmanager.com","[::]",$response);

$response = str_replace("www.googleadservices.com","[::]",$response);

$response = str_replace("www.google.com/pagead","[::]",$response);

$response = str_replace("ogs.google.com/callout","[::]/",$response);

$response = str_replace("</body>","<center><h1>This website is a proxy for Google Search.</h1></center></body>",$response);

//$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

//http_response_code($httpcode);

//$response = preg_replace('/([\s\S]*)(<div class="bzXtMb M8OgIe dRpWwb">)([\s\S]*)(<\/div>)([\s\S]*)/', '$1$5', $response);

echo $response;

curl_close($ch);

?>
