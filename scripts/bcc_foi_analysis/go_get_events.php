<?php

require_once __DIR__ . '/../../vendor/autoload.php';


$base_url = 'https://www.whatdotheyknow.com/feed/search/requested_from:bristol_city_council.json?request_date_after=2017%2F01%2F01';

$limit = 50000;


for ($page=0; $page<$limit; $page += 1) {

    echo "Processing page $page \n";

    $url = $base_url . '&page=' . $page;

    $cache_file_name = __DIR__ . '/cache/url_pages/page_' . $page . '.json';

    if (file_exists($cache_file_name) === true) {
        $body = file_get_contents($cache_file_name);
    }
    else {
        echo "Fetching page $page\n";
        [$statusCode, $body, $headers] = fetchUri($url, 'GET');
        echo "status code: $statusCode\n";
        if ($statusCode !== 200) {
            echo "page $page status code not 200";
            exit(1);
        }
        file_put_contents($cache_file_name, $body);
        echo "page $page written.\n";
    }

    $events = json_decode($body, true);
    $number_of_events = count($events);

    echo "number of events is " . $number_of_events . "\n";

    if ($number_of_events === 0) {
        echo "Appear to have run out of data at page $page\n";
        exit(0);
    }

    $count = 0;

    foreach ($events as $event) {
        $count += 1;
        if (isset($event['id']) !== true) {
            echo "Cannot process array key $count - missing ['id']\n";
            continue;
        }
        if (isset($event["info_request"]["id"]) !== true) {
            echo "Cannot process array key $count - missing ['info_request']['id']\n";
            continue;
        }
        if (isset($event["info_request"]["url_title"]) !== true) {
            echo "Cannot process array key $count - missing ['info_request']['url_title']\n";
            continue;
        }

        $event_id = $event['id'];
        $info_request_id = $event["info_request"]["id"];
        $url_title = $event["info_request"]["url_title"];

//        echo "event_id $event_id  info_request = $info_request_id url_title $url_title\n";

        $info_request_filename = __DIR__ . '/cache/info_requests/info-request-' . $info_request_id . '.json';

        $info_request = $event["info_request"];

        if (file_exists($info_request_filename) === false) {
            $data = json_encode_safe($info_request);
            file_put_contents($info_request_filename, $data);
            echo "written info_request file $info_request_filename\n";
        }
    }
}


/*



  {
"id": 18973323,
"event_type": "followup_sent",
"created_at": "2025-08-29T20:29:16.797+01:00",
"described_state": "internal_review",
"calculated_state": "internal_review",
"last_described_at": "2025-08-29T20:29:17.145+01:00",
"incoming_message_id": null,
"outgoing_message_id": 1928316,
"comment_id": null,
"display_status": "Internal review request",
"snippet": "Dear FOI Team\n\nI am well aware of your correspondence of 16th July.  I considered this to be a 'holding' response rather than a detailed response to m...",
"info_request": {
  "id": 1292337,
  "url_title": "bus_gates_for_east_bristol_livea",
  "title": "Bus Gates for East Bristol Liveable Neighbourhood",
  "created_at": "2025-06-03T12:19:12.367+01:00",
  "updated_at": "2025-08-29T20:29:16.904+01:00",
  "described_state": "internal_review",
  "display_status": "Awaiting internal review",
  "awaiting_description": false,
  "prominence": "normal",
  "law_used": "foi",
  "tags": []
},



*/






/*

Table of statuses
All the options below can use status or latest_status before the colon. For example, status:not_held will match requests which have ever been marked as not held; latest_status:not_held will match only requests that are currently marked as not held.

status:waiting_response	Waiting for the public authority to reply
status:not_held	The public authority does not have the information requested
status:rejected	The request was refused by the public authority
status:partially_successful	Some of the information requested has been received
status:successful	All of the information requested has been received
status:waiting_clarification	The public authority would like part of the request explained
status:gone_postal	The public authority would like to / has responded by postal mail
status:internal_review	Waiting for the public authority to complete an internal review of their handling of the request
status:error_message	Received an error message, such as delivery failure.
status:requires_admin	A strange response, required attention by the WhatDoTheyKnow team
status:user_withdrawn	The requester has abandoned this request for some reason
                                                                      Table of varieties
All the options below can use variety or latest_variety before the colon. For example, variety:sent will match requests which have ever been sent; latest_variety:sent will match only requests that are currently marked as sent.

variety:sent	Original request sent
variety:followup_sent	Follow up message sent by requester
variety:response	Response from a public authority
variety:comment	Annotation added to request
variety:authority	A public authority
variety:user	A WhatDoTheyKnow user
Back to content

*/