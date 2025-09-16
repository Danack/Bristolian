<?php

require_once __DIR__ . '/../../vendor/autoload.php';

$json = file_get_contents(__DIR__ . "/requests_to_process.json");

$all_data = json_decode_safe($json);

foreach ($all_data as $id => $request) {

    $title = $request["url_title"];

    $directory = __DIR__ . '/cache/downloads/' . $title;

    $result = @mkdir($directory);

    $filename = __DIR__ . '/cache/downloads/' . $title . '.zip';

    printf("unzip $filename -d $directory\n");
}

/*
      "1000770": {
        "id": 1000770,
        "url_title": "council_tax_debt_25",
        "title": "Council tax debt",
        "created_at": "2023-07-07T18:13:51.629+01:00",
        "updated_at": "2023-09-13T13:54:05.117+01:00",
        "described_state": "successful",
        "display_status": "Successful",
        "awaiting_description": false,
        "prominence": "normal",
        "law_used": "foi",
        "tags": []
    },
*/

