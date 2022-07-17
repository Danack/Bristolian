<?php

declare(strict_types=1);

//require_once __DIR__ . "/functions.php";


require_once __DIR__ . "/../../vendor/autoload.php";

$api_key = '9a9708e78f1788e6d9a7d165f469467213ec79c3';

$data_urls = [];

$data_url = [
    "First Bus_Kingswood_Southmead_20220424_2",
    "https://data.bus-data.dft.gov.uk/timetable/dataset/2283/",
    "https://data.bus-data.dft.gov.uk/api/v1/dataset/2283/?api_key=$api_key"
];

[$name, $info, $api_url] = $data_url;


[$statusCode, $body, $headers] = fetchUri($api_url, 'GET');


if ($statusCode !== 200) {
    echo "Failed to get data.\n";
    var_dump($headers);
    exit(-1);
}

echo "Got response, body size is " . strlen($body);

file_put_contents("test_output.xml", $body);


