<?php
$cookies = $_SERVER['HTTP_COOKIE'];
require_once 'vendor/autoload.php';
$req_method = $_SERVER['REQUEST_METHOD'];
$req_body = file_get_contents('php://input');
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
if (str_starts_with($searchq, '/pagead')) {
    http_response_code(403);
    die("BLOCKED!");
}
if (str_starts_with($searchq, '/async') && !(str_starts_with($searchq, '/async/imgv'))) {
    http_response_code(403);
    die("BLOCKED!");
}
if (str_contains($searchq, 'gen204')) {
    http_response_code(204);
    die("BLOCKED!");
}
if (str_contains($searchq, 'browserinfo?')) {
    http_response_code(403);
    die("BLOCKED!");
}
if (str_contains($searchq, 'errorlogging?')) {
    http_response_code(403);
    die("BLOCKED!");
}
$search = "https://$hname$searchq";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $search);
curl_setopt($ch, CURLOPT_ENCODING, "");
//curl_setopt($ch, CURLOPT_MAXREDIRS,10);
//curl_setopt($ch, CURLOPT_VERBOSE, 0);
//curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_AUTOREFERER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $req_method);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
//curl_setopt($ch, CURLOPT_COOKIEJAR, "cookies.txt");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Host: www.google.com',
    'X-Forwarded-For: 2a02:6ea0:d411:2415::11',
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',
    'Cookie: $cookies',
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

if ($req_method == 'POST') {
    curl_setopt($ch, CURLOPT_POSTFIELDS, $req_body);
}

$response = curl_exec($ch);

if (!str_contains($searchq, '/recaptcha')) {

    $response = str_replace("<head>",'<head><script src="/blocker.js"></script>',$response);

    $response = str_replace("www.googletagmanager.com","[::]",$response);

    $response = str_replace("</body>","<center><h1>This website is a proxy for Google Search.</h1></center></body>",$response);

    $response = str_replace("https://www.gstatic.com/marketing-cms/reviewed-scripts/gtm","https://[::]",$response);
}

//$response = str_replace("https://www.gstatic.com/feedback/js/","https://[::]/",$response);

//$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

//http_response_code($httpcode);

echo $response;

curl_close($ch);

?>
