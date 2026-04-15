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


function getParam($qparam) {
  $a = array();
  foreach (explode ("&", $_SERVER["QUERY_STRING"]) as $q) {
    $p = explode ('=', $q, 2);
    $a[$p[0]] = isset ($p[1]) ? $p[1] : '';
  }
  return $a[$qparam];
}


$h_user_agent = $_SERVER['HTTP_USER_AGENT'];

if (str_contains($h_user_agent, 'Android') || str_contains($h_user_agent, 'iPhone')) {
    $p_user_agent = 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36';
}
else {
    $p_user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36';
}

$response_headers = [];

require_once '../vendor/autoload.php';
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

if ($_GET['q'] == "" && !str_contains($_GET['client'], "gws")) {
    http_response_code(403);
    die("BLOCKED!");
}

$sq = $_SERVER['REQUEST_URI'];
$sq_b = strtok($sq, '?');

$allowed_qstrs = [ 'q', 'xssi', 'hl', 'ie' ];

if ($sq == $sq_b || $sq == $sq_b . '?') {
    $searchq = $sq;
}
else {
    $searchq = $sq_b . '?client=' . urlencode($_GET['client']);
    foreach ($allowed_qstrs as $a_qstr) {
          if (array_key_exists($a_qstr, $_GET)) {
              $searchq = $searchq . '&' . $a_qstr . '=' . getParam($a_qstr);
          }
    }
    if ($sq != $searchq) {
       error_log("Warning: effective URL: " . $searchq);
    }
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

$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

http_response_code($httpcode);

foreach($response_headers as $name => $values) {
    if ($name == "content-type") {
      foreach($values as $value) {
          header("$name: $value");
      }
    }
}

echo $response;

curl_close($ch);

?>
