<?php

http_response_code(301);

$qurl = array();

parse_str($_SERVER['QUERY_STRING'], $qurl);

header("Location: " . $qurl['url']);

?>
