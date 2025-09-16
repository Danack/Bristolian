<?php

require_once __DIR__ . '/../../vendor/autoload.php';

$known_files_extensions_count = [];

// $cache_file_name = __DIR__ . '/cache/url_pages/page_' . $page . '.json';



function analyzeTextFile(string $contents)
{
    $contents = strtolower($contents);


}

function listZipContents(string $zipFile): array {
    global $known_files_extensions_count;
    $result = [];

    if (!file_exists($zipFile)) {
        return $result; // early return if file doesn't exist
    }

    $zip = new ZipArchive();
    if ($zip->open($zipFile) !== true) {
        return $result; // early return if cannot open zip
    }

    for ($i = 0; $i < $zip->numFiles; $i++) {
        $stat = $zip->statIndex($i);
        if ($stat === false) {
            continue; // skip invalid entry
        }

        $result[] = [
            'name' => $stat['name'],
            'size' => $stat['size']
        ];

        $extension = strtolower(pathinfo($stat['name'], PATHINFO_EXTENSION));

        $known_files_extensions_count[$extension] = ($known_files_extensions_count[$extension] ?? 0) + 1;

        $handler = match($extension) {
            "txt" => null,
            "doc" => null,
            "pdf" => null,
            "xlsx" => null,
            "png" => null,
            "docx" => null,
            "csv" => null,
            "html" => null,
            "xls" => null,
            "jpg" => null,
            "text" => null,
            "zip" => null,
            default => null,
        };

        if ($handler === null) {
            continue;
        }

        $contents = $zip->getFromIndex($i);
        analyzeTextFile($contents);
    }

    $zip->close();
    return $result;
}

$dir = __DIR__ . '/cache/info_requests';

function loadInfoRequests(): array
{
    $dir = __DIR__ . '/cache/info_requests';

    if (!is_dir($dir)) {
        return []; // Directory missing
    }

    $decoded = [];

    foreach (scandir($dir) as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        $filePath = $dir . DIRECTORY_SEPARATOR . $file;

        if (!is_file($filePath)) {
            continue;
        }

        if (!preg_match('/^info-request-\d+\.json$/', $file)) {
            continue;
        }

        $contents = file_get_contents($filePath);
        if ($contents === false) {
            return []; // Fail fast if a file can’t be read
        }

        $data = json_decode($contents, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return []; // Fail fast if invalid JSON
        }

        $decoded[] = $data;
    }

    return $decoded;
}

$titles = [];

$all_requests = loadInfoRequests();

$all_data = [];

foreach ($all_requests as $request) {
    $created_at = new DateTime($request['created_at']);
    $start_of_2023 = new DateTime("2023-01-01T00:00:00+00:00");

    if ($created_at > $start_of_2023) {
        $titles[] = $request['url_title'];

        $all_data[$request['id']] = $request;
    }
}



sort($titles);

$total_file_count = count($titles);
$files_downloaded_count = 0;


$output = fopen(__DIR__ . '/download.html', "w+");

fprintf($output, "<html>\n<body>\n");



foreach ($titles as $title) {
    $download_url = "https://www.whatdotheyknow.com/request/$title/download";

    $filename = __DIR__ . '/cache/downloads/' . $title . '.zip';

    if (file_exists($filename) !== true) {
        fprintf($output, "<a href='$download_url'>$title</a><br/>\n");
    } else {
        $files_downloaded_count += 1;
    }
}

fprintf($output, "</body>\n</html>\n");

echo "Have downloaded $files_downloaded_count out of $total_file_count\n";

if ($files_downloaded_count !== $total_file_count) {
    echo "Still need to download some files.";
    exit(1);
}


$json = json_encode_safe($all_data);

file_put_contents(__DIR__ . "/requests_to_process.json", $json);

