<?php

require_once __DIR__ . '/../../vendor/autoload.php';

$pattern = '/Section\s*(\d+)(?:\((\d+)\))?/iu';

$json = file_get_contents(__DIR__ . "/requests_with_section_entries.json");

$all_data = json_decode_safe($json);

$known_reasons = [
    '12' => 'Exceeds cost limit',
    '21' => 'Information accessible by other means',
    '22(1)(c)' => 'Information intended for future publication',
    '30(1)(b)' => 'Investigations',
    '40(2)' => 'Personal data of third parties',
    '1(1)'   => 'General right of access',
    '10(1)'  => 'Time for compliance (20 working days)',
    '10'     => 'Time for compliance',
    '11(3)'  => 'Means by which communication is to be made',
    '12(4)'  => 'Aggregating costs of related requests',
    '14'     => 'Vexatious or repeated requests',
    '11'     => 'Means by which communication is to be made',
    '12(1)'  => 'Cost of compliance exceeds appropriate limit',
    '13(1)'  => 'Fees where cost limit is not exceeded',
    '13'     => 'Fees',
    '14(1)'  => 'Vexatious requests',
    '14(2)'  => 'Repeated requests',
    '15'     => 'Advice about cost limit',
    '16(1)'  => 'Duty to provide advice and assistance',
    '16'     => 'Duty to provide advice and assistance',
    '17(1)'  => 'Refusal notice requirements',
    '17(4)'  => 'Withholding reasons where disclosure would prejudice',
    '17'     => 'Refusal notice requirements',
    '1'      => 'General right of access',
    '2(2)'   => 'Public interest test for qualified exemptions',
    '21(1)'  => 'Information accessible by other means',
    '21(2)'  => 'Information accessible by inspection, publication, or request',
    '21(3)'  => 'Accessibility by other statutory means',
    '22(1)'  => 'Information intended for future publication',
    '22'     => 'Information intended for future publication',
    '24(1)'  => 'National security exemption',
    '28'     => 'Relations within the United Kingdom',
    '3(1)'   => 'Public authorities to which Act applies',
    '3(2)'   => 'Information held on behalf of a public authority',
    '30(1)'  => 'Investigations and proceedings conducted by public authorities',
    '30(3)'  => 'Neither confirm nor deny duty in relation to investigations',
    '30'     => 'Investigations and proceedings',
    '31(1)'  => 'Law enforcement exemption',
    '31(2)'  => 'Law enforcement purposes (further categories)',
    '31(3)'  => 'Neither confirm nor deny duty in relation to law enforcement',
    '31'     => 'Law enforcement exemption',
    '34(1)'  => 'Parliamentary privilege',
    '36(2)'  => 'Prejudice to effective conduct of public affairs',
    '36'     => 'Effective conduct of public affairs',
    '39'     => 'Environmental information exemption',
    '40(5)'  => 'Neither confirm nor deny duty in relation to personal data',
    '40'     => 'Personal information exemption',
    '41(2)'  => 'Neither confirm nor deny duty in relation to information provided in confidence',
    '41'     => 'Information provided in confidence',
    '43(2)'  => 'Commercial interests exemption',
    '43'     => 'Commercial interests exemption',
    '45(1)'  => 'Secretary of State code of practice on discharge of functions',
    '45'     => 'Secretary of State code of practice',
    '58'     => 'Appeals to the Tribunal',
    '61'     => 'Offence of altering records with intent to prevent disclosure',
    '64(1)'  => 'Interpretation of Part VI (definitions)',
    '73'     => 'Orders, rules and regulations (power to make secondary legislation)',
    '81'     => 'Short title, commencement and extent',
    '84'     => 'General interpretation (definitions)',
    '8'      => 'Request for information',
];

$interesting_reasons = [
//    '17(4)'  => 'Withholding reasons where disclosure would prejudice',
//    '17'     => 'Refusal notice requirements',
    '21(1)'  => 'Information accessible by other means',
    '21(2)'  => 'Information accessible by inspection, publication, or request',
    '21(3)'  => 'Accessibility by other statutory means',
    '22(1)'  => 'Information intended for future publication',
    '22'     => 'Information intended for future publication',
//    '30(1)'  => 'Investigations and proceedings conducted by public authorities',
//    '30(3)'  => 'Neither confirm nor deny duty in relation to investigations',
//    '30'     => 'Investigations and proceedings',
//    '31(1)'  => 'Law enforcement exemption',
];


$ignored_requests = [
    'latest_bridge_inspection_report',
    'foi_request_bshhra_supply_of_com'
];

$matching_fois = [];
$data_with_found_section = [];
$needed_reasons = [];

$interesting_requests = [];

foreach ($all_data as $id => $request) {

    $title = $request["url_title"];

    if (in_array($title, $ignored_requests) === true) {
        continue;
    }

    if (array_key_exists("grep_results", $request) !== true) {
        continue;
    }

    if ($id == '1027929') {
        echo "hmm";
    }

    foreach ($request["grep_results"] as $filename => $grep_results) {
        foreach ($grep_results as $grep_result) {
            if (preg_match($pattern, $grep_result, $matches, PREG_OFFSET_CAPTURE)) {
                $fullMatch = $matches[0][0];
                $matchStart = $matches[0][1];
                $matchEnd = $matchStart + strlen($fullMatch);

                // Extract substring from start of match until 200 chars past end
                $substring = substr($grep_result, $matchStart, strlen($fullMatch) + 200);

//                echo "$title\n";

                // Section numbers
                $section = $matches[1][0];
                $subsection = isset($matches[2][0]) ? $matches[2][0] : null;

//                echo "Extracted: " . $substring . PHP_EOL;
//                echo "$title\n";
                $section_string = "$section";
                if ($subsection !== null) {
                    $section_string .= "($subsection)";
                }
//                echo $section_string . "\n";
                $matching_fois[$title] = true;

                if (array_key_exists($section_string, $interesting_reasons) === true ) {
                    if (array_key_exists($id, $interesting_requests) === false) {
                        $interesting_requests[$id] = $request;
                        $interesting_requests[$id]['reasons'] = [];
                    }
                    $interesting_requests[$id]['reasons'][] = [
                        'section' =>  $section_string,
                        'section_string' => $substring,
                    ];
                }

                if (array_key_exists($section_string, $known_reasons) !== true ) {
                    if($section_string == 360) {
                        echo "wat\n";
                    }
                    $needed_reasons[$section_string] = $substring;
                }
            } else {
//                echo "No match found for ($grep_result)\n";
            }
        }

    }
}

//var_dump($interesting_requests);


$json = json_encode_safe($interesting_requests);

file_put_contents(__DIR__ . "/requests_interesting.json", $json);
