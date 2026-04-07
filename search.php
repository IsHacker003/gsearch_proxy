<?php

// Copyright (C) 2026  IsHacker
//
//  This program is free software: you can redistribute it and/or modify
//  it under the terms of the GNU Affero General Public License as published
//  by the Free Software Foundation, either version 3 of the License, or
//  (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU Affero General Public License for more details.
//
//  You should have received a copy of the GNU Affero General Public License
//  along with this program.  If not, see <https://www.gnu.org/licenses/>.


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

$searchq = $_SERVER['REQUEST_URI'];

$blocked_qstrs = [ 'client', 'oq', 'sourceid' ];

$search_query = urlencode($_GET['q']);
$searchq_qstr = $_SERVER['QUERY_STRING'];

function str_contains_any_qstr($i_var, $a_var) {
    foreach ($a_var as $a_items)
    {
        $a_items = '&' . $a_items . '=';
        if (str_contains($i_var, $a_items))
        {
            return true;
        }
    }
    return false;
}

if (str_contains_any_qstr($searchq, $blocked_qstrs)) {
    parse_str($searchq_qstr, $qstrs);

    foreach ($blocked_qstrs as $blocked_qstr) {
        unset($qstrs[$blocked_qstr]);
    }
    $qstrs['q'] = $search_query;

    $searchq_qstr = rawurldecode(http_build_query($qstrs));

    $searchq = strtok($searchq, '?') . '?' . $searchq_qstr;
}


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

//$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

//http_response_code($httpcode);

foreach($response_headers as $name => $values)
    if ($name == "content-type") {
      foreach($values as $value)
          header("$name: $value");
    }

echo $response;

curl_close($ch);

?>
