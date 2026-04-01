<?php
$cookies = $_SERVER['HTTP_COOKIE'];

$h_user_agent = $_SERVER['HTTP_USER_AGENT'];

if (str_contains($h_user_agent, 'Android') || str_contains($h_user_agent, 'iPhone')) {
    $p_user_agent = 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36';
}
else {
    $p_user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36';
}

$response_headers = [];

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
$search = "https://$hname/";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $search);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Host: www.google.com',
    'User-Agent: ' . $p_user_agent,
    'Cookie: ' . $cookies,
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

curl_setopt($ch, CURLOPT_HEADERFUNCTION,
    function($curl, $header) use (&$response_headers) {
        $len = strlen($header);
        $header = explode(':', $header, 2);
        if (count($header) < 2) // ignore invalid headers
          return $len;

        $response_headers[strtolower(trim($header[0]))][] = trim($header[1]);

        return $len;
    }
);

$response = curl_exec($ch);

$response = str_replace("<head>",'<head><script src="/blocker.js"></script>',$response);

$response = str_replace("www.googletagmanager.com","[::]",$response);

$response = str_replace("www.googleadservices.com","[::]",$response);

$response = str_replace("www.google.com/pagead","[::]",$response);

$response = str_replace("ogs.google.com/widget/callout","[::]/",$response);

$response = str_replace("</body>","<center><h1>This website is a proxy for Google Search. <a href='https://github.com/IsHacker003/gsearch_proxy'>Source code.</a></h1></center></body>",$response);

foreach($response_headers as $name => $values)
    if ($name == "content-type") {
      foreach($values as $value)
          header("$name: $value");
    }

echo $response;

curl_close($ch);

?>
