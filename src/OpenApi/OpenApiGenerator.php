<?php

namespace OpenApi;

use SlimDispatcher\Response\JsonResponse;

/**
 * @codeCoverageIgnore
 *
 * This isn't functioning well enough yet to test.
 */
class OpenApiGenerator
{
    // https://swagger.io/docs/specification/basic-structure/
    public function getApiData()
    {
        $basicData = [
            'openapi' => '3.1.0',
            'info' => [
                'title' => 'Sample API',
                'description' => 'Hopefully, a representation of the api endpoints on this site.',
                'version' => "0"
            ],
            'components' => $this->getComponentsData(),
            'paths' => $this->getPathsData(),
        ];

        return $basicData;
    }


    public function getPathsData()
    {
        $data = [
        '/users/{id}' => [
        'get' => [
        'tags' => [
        'Users',
        ],
        'summary' => 'Gets a user by ID.',
        'description' => "A detailed description of the operation.
        Use markdown for rich text representation,
                         such as **bold**, *italic*, and [links](https://swagger.io).",
        'operationId' => 'getUserById',
        'parameters' => [
        'name' => 'id',
        'in' => 'path',
        'description' => 'User ID',
        'required' => true,
        'schema' => [
          'type' => 'integer',
          'format' => 'int64'
         ],
         'responses' => [
           '200' => [
           'description' => 'Successful operation',
           'content' => [
             'application/json' => [
               'schema' => [
                 '$ref' => '#/components/schemas/User',
                ]
              ],
            ],
           ],
        ],
        'externalDocs' => [
          'description' => "Learn more about user operations provided by this API.",
          'url' => 'http://api.example.com/docs/user-operations/',
        ]
        ]
        ]
        ]
        ];

        return $data;
    }



    public function getComponentsData()
    {
        $apiData = [
//  # Reusable schemas (data models)
        "schemas"=> [
        "GeneralError"=> [
        "type" => "object",
        "properties"=> [
        "code"=> [
          "type" => "integer",
          "format" => "int32"
        ],
        "message" => [
          "type" => "string"
        ]
        ]
        ],
//  # Reusable path, query, header and cookie parameters
//  parameters:
//    ...
//  # Security scheme definitions (see Authentication)
//  securitySchemes:
//    ...
//  # Reusable request bodies
//  requestBodies:
//    ...
//  # Reusable responses, such as 401 Unauthorized or 400 Bad Request
//  responses:
//    ...
//  # Reusable response headers
//  headers:
//    ...
//  # Reusable examples
//  examples:
//    ...
//  # Reusable links
//  links:
//    ...
//  # Reusable callbacks
//  callbacks:
//    ...
        ],
        "parameters" => [
        "skipParam" => [
        "name" => "skip",
        "in" => "query",
        "description" => "number of items to skip",
        "required" => true,
        "schema" => [
        "type" => "integer",
        "format" => "int32"
        ]
        ],
        "limitParam"=> [
        "name" => "limit",
        "in" => "query",
        "description" => "max records to return",
        "required" => true,
        "schema" => [
        "type" => "integer",
        "format"=> "int32"
        ]
        ]
        ],
        "responses" => [
        "NotFound"=> [
        "description" => "Entity not found."
        ],
        "IllegalInput"=> [
        "description" => "Illegal input for operation."
        ],
        "GeneralError" => [
        "description" => "General Error",
        "content" => [
        "application/json"=> [
        "schema"=> [
          '$ref' => "#/components/schemas/GeneralError"
        ]
        ]
        ]
        ]
        ],
        "securitySchemes"=> [
        "api_key" => [
        "type" => "apiKey",
        "name" => "api_key",
        "in"=> "header"
        ],
        ]
        ];

        return $apiData;
    }
}