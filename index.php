<?php

if (!isset($use_base)) {
    $use_base = false;
}

$html = file_get_contents('template.html', true);

if ($use_base) {
    $html = str_replace('<head>', '<head><base href="landing/">', $html);
}

$replace = ["{clickid}", "{px}"];
$replace_to = [request("clickid"), request("px")];
$html = str_replace($replace, $replace_to, $html);

echo $html;

function request($field)
{
    return isset($_REQUEST[$field]) ? $_REQUEST[$field] : '';
}