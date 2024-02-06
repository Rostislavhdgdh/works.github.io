<?php

if (!isset($_GET['api_key']) || empty($_GET['api_key'])) {
    die('Specify valid api_key parameter.');
}

$api_key = $_GET['api_key'];

$file_path = __DIR__ . "/orders-0.txt";
if (!file_exists($file_path)) {
    die('Log file not found');
}

$file_resource = fopen($file_path, "rw+");

$ch = curl_init('https://api.aff1.com/v3/lead.create');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, true);

while (!feof($file_resource)) {

    $json_string = fgets($file_resource);
    $params = json_decode($json_string, true);

    if (is_null($params) || $params['success']) {
        continue;
    }

    $date = $params['date'];
    unset($params['date'], $params['success']);

    curl_setopt($ch, CURLOPT_POSTFIELDS, array_merge($params, array('api_key' => $api_key)));
    $response = curl_exec($ch);

    if (in_array(curl_getinfo($ch)['http_code'], array(200, 202, 422))) {
        fseek($file_resource, ftell($file_resource) - mb_strlen($json_string));

        $new_json_string = json_encode(array_merge(['date' => $date], $params, ['success' => 1]));

        fwrite($file_resource, $new_json_string);
    }
}

fclose($file_resource);
curl_close($ch);
echo "Done";