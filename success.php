<?php

include("PublisherApi.php");

$api = new \PublisherApi\PublisherApi();

$api->setProperty('api_key', '4fUUsYxmc9xXt8R7');
$api->setProperty('flow_hash', '');
$api->setProperty('target_hash', 'JwkpVJQN');
$api->setProperty('country_code', request('country_code'));
$api->setPrice(39);
$api->setProperty('first_name', custom('first_name'));
$api->setProperty('last_name', custom('last_name'));
$api->setProperty('address', request('address'));
$api->setProperty('state', custom('state'));
$api->setProperty('city', custom('city'));
$api->setProperty('zipcode', custom('zipcode'));
$api->setProperty('email', request('email'));
$api->setProperty('comment', request('comment'));
$api->setProperty('size', custom('size'));
$api->setProperty('quantity', custom('quantity'));
$api->setProperty('password', custom('password'));
$api->setProperty('language', custom('language'));
$api->setProperty('tz_name', custom('tz_name'));
$api->setProperty('call_time_frame', custom('call_time_frame'));
$api->setProperty('call_language', custom('call_language'));
$api->setProperty('messenger_code', custom('messenger_code'));
$api->setProperty('sale_code', custom('sale_code'));
$api->setProperty('browser_locale', $api->getBrowserLocale());
$api->setProperty('phone2', request('phone2'));
$api->setProperty("clickid", request("clickid"));
$api->setProperty("data1", request("px"));

if (isset($_GET['success'])) {
    die(getSuccessPage(request('name'), request('phone'), request('hide_custom_code')));
}

$response = $api->makeOrder(request('client'), request('phone'));

if (false) {
    writeLog($api);
}

$response = json_decode($response, true);
$is_double_error = isset($response['error_code']) && $response['error_code'] === 'LEAD_DOUBLE';

if ($response['status'] !== 'success' && !$is_double_error) {
    die(var_dump($response));
}

$hide_custom_code = $is_double_error;
$client_info = getClientInfo();

$redirect_path = parse_url($_SERVER['REQUEST_URI'],  PHP_URL_PATH);
$redirect_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$redirect_path";
$redirect_url = $redirect_url . '?' . http_build_query(array_merge($_GET, $client_info, ['hide_custom_code' => $hide_custom_code]));

header('Location: ' . $redirect_url);


/** Functions */
function getSuccessPage($name, $phone, $hide_custom_code)
{
    $html = str_replace(['{NAME}', '{PHONE}'], [$name, $phone], file_get_contents('success.html'));

    if ($hide_custom_code) {
        $html = preg_replace('/<!--custom.code-->( .*?)<!--end.custom.code-->/imsx', '', $html);
    }

    $replace = ["{clickid}", "{px}"];
$replace_to = [request("clickid"), request("px")];
$html = str_replace($replace, $replace_to, $html);

    return $html;
}

function getClientInfo() {
    $name = request('client');

    if (!$name && (request('first_name') || request('last_name'))) {
        $name = trim(request('first_name') . ' ' . request('last_name'));
    }

    return [
        'success' => 1,
        'name' => $name,
        'phone' => request('phone'),
    ];
}

function request($field)
{
    return isset($_REQUEST[$field]) ? $_REQUEST[$field] : '';
}

function custom($field)
{
    return isset($_REQUEST['custom'], $_REQUEST['custom'][$field]) ? $_REQUEST['custom'][$field] : '';
}

function writeLog($api)
{
    $params = array_merge($api->getRequestParams(), array(
        'date' => date("Y-m-d H:i:s"),
        'success' => (int)in_array($api->getCurlInfo()['http_code'], array(200, 202, 422)),
    ));

    @file_put_contents(__DIR__ . "/orders-0.txt", sprintf("%s\n", json_encode($params)), FILE_APPEND);
}