<?php

// Auto-generated file do not edit

// generated with 'php cli.php generate:php_response_types'
//
// The information used to generate this file comes from:
// api/src/api_routes.php - specifically from routes that have type information
//
// In api_routes.php, each route is an array with the format:
// [path, method, controller, type_info, setup_callable]
//
// The type_info (at index 3) is an array of field definitions:
// [
//     ['field_name', ClassName::class, is_array],
//     ...
// ]
//
// Each field definition is: [field_name, fully_qualified_class_name, is_array]
// - field_name: the name of the field in the JSON response
// - fully_qualified_class_name: the model class (usually from Bristolian\Model\Generated)
// - is_array: true for arrays of objects, false for single objects
//
// This response class is used by the route:
//   Path: /api/rooms/{room_id:.*}/files
//   Method: GET
//
// The actual field definitions for this route are:
//   ['files', \Bristolian\Model\Generated\RoomFileObjectInfo::class, true]
//
// The code for the generation is in:
// \Bristolian\CliController\GenerateFiles::generateResponseClassContent
namespace Bristolian\Response\Typed;

use Bristolian\Exception\DataEncodingException;
use Bristolian\Model\Generated\RoomFileObjectInfo;
use SlimDispatcher\Response\StubResponse;

class GetRoomsFilesResponse implements StubResponse
{
    private string $body;

    /**
     * @param RoomFileObjectInfo[] $files
     */
    public function __construct(array $files)
    {
        $converted_data = [];
        [$error, $converted_files] = \convertToValue($files);
        if ($error !== null) {
            throw new DataEncodingException("Could not convert files to a value. ", $error);
        }
        $converted_data['files'] = $converted_files;

        $response_ok = [
            'result' => 'success',
            'data' => $converted_data
        ];

        $this->body = json_encode($response_ok, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function getStatus(): int
    {
        return 200;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/json'
        ];
    }

    public function getBody(): string
    {
        return $this->body;
    }
}
