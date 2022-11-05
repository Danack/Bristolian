<?php

require_once __DIR__ . "/../youtube/functions.php";

require_once __DIR__ . "/InfoRequest.php";
require_once __DIR__ . "/RequestState.php";

$json = file_get_contents(__DIR__ . "/info_request.json");

$data = json_decode_safe($json);

//$dt = new DateTimeImmutable();
//
//echo $dt->format('Y-m-d\TH:i:s.vP');
//echo "\n";
//exit(0);

//public const RFC3339_EXTENDED = 'Y-m-d\TH:i:s.vP';


$created_at = DateTimeImmutable::createFromFormat(
    DateTimeImmutable::RFC3339_EXTENDED,
    $data['created_at']
);

$updated_at = DateTimeImmutable::createFromFormat(
    DateTimeImmutable::RFC3339_EXTENDED,
    $data['updated_at']
);

$info_request = new InfoRequest(
    $data['url_title'],
    $data['title'],
    $data['display_status'],
    RequestState::from($data['described_state']),
    $created_at,
    $updated_at
);



