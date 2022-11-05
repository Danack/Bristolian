<?php

require_once __DIR__ . "/../credentials.php";
require_once __DIR__ . "/traityboys.php";
require_once __DIR__ . "/classes.php";
require_once __DIR__ . "/functions.php";



$api_key = getApiKey();

$part = "snippet";
$videoId = "M7FIvfx5J10";
$url = "https://www.googleapis.com/youtube/v3/captions";


$params = [
    'part' => $part,
    'videoId' => $videoId,
    'key' => $api_key
];

$headers = [
    'x-goog-api-key: '. $api_key,
    ''
];

[$statusCode, $body, $headers] = fetchUri(
    $url,
    "GET",
    $params,
    null,
    $headers
);

var_dump($statusCode, $body, $headers);


//https://www.googleapis.com/auth/youtube.force-ssl
//https://www.googleapis.com/auth/youtubepartner

//static const char 	YOUTUBE [] = {"https://www.googleapis.com/auth/youtube"}
//static const char 	YOUTUBE_FORCE_SSL [] = {"https://www.googleapis.com/auth/youtube.force-ssl"}
//static const char 	YOUTUBE_READONLY [] = {"https://www.googleapis.com/auth/youtube.readonly"}
//static const char 	YOUTUBE_UPLOAD [] = {"https://www.googleapis.com/auth/youtube.upload"}
//static const char 	YOUTUBEPARTNER [] = {"https://www.googleapis.com/auth/youtubepartner"}
//static const char 	YOUTUBEPARTNER_CHANNEL_AUDIT [] = {"https://www.googleapis.com/auth/youtubepartner-channel-audit"}

// Client ID
// 851073262956-jmid965fhqu8mnsmsseaugus66tlt5gp.apps.googleusercontent.com

// client secret
// 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