<?php

require_once __DIR__ . '/../../vendor/autoload.php';

$json = file_get_contents(__DIR__ . "/requests_to_process.json");

$all_data = json_decode_safe($json);
$data_with_found_section = [];

foreach ($all_data as $id => $request) {

    $title = $request["url_title"];

    $directory = __DIR__ . '/cache/downloads/' . $title;

    $result = @mkdir($directory);

    $filename = __DIR__ . '/cache/downloads/' . $title . '.zip';

    $zip = new ZipArchive();
    if ($zip->open($filename) !== true) {
        return $result; // early return if cannot open zip
    }

    for ($i = 0; $i < $zip->numFiles; $i++) {

        if ($title === 'a37a4018_strategic_corridor_vict') {
            continue;
        }

        $stat = $zip->statIndex($i);
        if ($stat === false) {
            continue; // skip invalid entry
        }

        $result[] = [
            'name' => $stat['name'],
            'size' => $stat['size']
        ];

        $extension = strtolower(pathinfo($stat['name'], PATHINFO_EXTENSION));

        $extracted_filename = __DIR__ . '/cache/downloads/' . $title . '/' . $stat['name'];

        $commmand = null;

        if ($extension == 'pdf') {
            $command = sprintf("pdfgrep -i section \"%s\"", $extracted_filename);
        }
        else if ($extension == 'txt') {
//            $command = sprintf("grep -i section \"%s\"", $extracted_filename);
        }

        if ($command !== null) {
            printf("$command \n");
            $output = [];

            exec($command, $output, $returnVar);

            if ($returnVar === 1) {
                // no lines found
                continue;
            }
            else if ($returnVar !== 0) {
                // Handle error case if needed
                echo "Failed to execute ($command)\n";
                exit(-1);
            }

            if (count($output) !== 0) {
                $data_with_found_section[$id] = $request;
                $data_with_found_section[$id]['grep_results'][$extracted_filename] = $output;
            }
//            $all_data[$id]['grep_results'][$extracted_filename] = $output;
        }
    }
}


$json = json_encode_safe($data_with_found_section);

file_put_contents(__DIR__ . "/requests_with_section_entries.json", $json);


