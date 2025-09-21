<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dir = __DIR__ . '/html_output';
$files = scandir($dir);

$links = [];

foreach ($files as $file) {
    echo $file . "\n";
    $contents = file_get_contents($dir . '/' . $file);

//    echo "contents length: " . strlen($contents) . "\n";

    // Use preg_match_all to capture all href and title pairs
    preg_match_all('/href="([^"]+)"\s+title="([^"]+)"/', $contents, $matches, PREG_SET_ORDER);

    // Loop through all matches
    foreach ($matches as $match) {
        $url = $match[1];
        $title = $match[2];
//        echo "URL: $url\n";
//        echo "Title: $title\n\n";
        $links[$title] = $url;
    }
}

$count = 0;
$total = count($links);

$ignore_list = [
    '/',
    '/files/documents/1253-hotwells-harbourside-ward/file'
];


foreach ($links as $title => $url) {
    echo "$count / $total \n";
    $full_url = "https://www.bristol.gov.uk" . $url;

    $count += 1;

    $filename = __DIR__ . "/bcc_files_2025_07_25/$title";

    if (in_array($url, $ignore_list, true)) {
        echo "Skipping $count $filename as in ignore list exists \n";
        continue;
    }

    if (file_exists($filename)) {
        echo "Skipping $count $filename as already exists \n";
        continue;
    }

    echo "Getting $full_url \n";

    sleep(10);
    [$statusCode, $body, $headers] = fetchUri($full_url, 'GET');

    if ($statusCode !== 200) {
        fwrite(STDERR, "Failed to download $url, status was $statusCode\n");
        continue;
    }

    file_put_contents($filename, $body);
}
