<?php

declare(strict_types = 1);


use Psr\Http\Message\ResponseInterface;

// Holds functions that convert exceptions into command
// line output for use in the command line tools.

function fillJsonResponseData(ResponseInterface $response, array $data, int $statusCode)
{
    $builtResponse = new \SlimAuryn\Response\JsonResponse($data, [], $statusCode);
    $reasonPhrase = getReasonPhrase($statusCode);
    $response = $response->withStatus($builtResponse->getStatus(), $reasonPhrase);
    foreach ($builtResponse->getHeaders() as $key => $value) {
        $response = $response->withAddedHeader($key, $value);
    }

    $response->getBody()->write($builtResponse->getBody());

    return $response;
}

function paramsValidationExceptionMapperApi(
    \TypeSpec\Exception\ValidationException $ve,
    ResponseInterface $response
) {
    $data = [];
    $data['status'] = 'There were validation errors';
    $data['errors'] = $ve->getValidationProblems();

    $response = fillJsonResponseData($response, $data, 400);

    return $response;
}

function pdoExceptionMapperApi(\PDOException $pdoe, ResponseInterface $response)
{
    $text = getTextForException($pdoe);

    $statusMessage = 'Unknown error';
    $knownStatusCodes = [
        1045 => 'Configuration error, could not connect to DB.', // Config error
        42000 => 'Database error, could not query', // SQL syntax
    ];

    if (array_key_exists($pdoe->getCode(), $knownStatusCodes) === true) {
        $statusMessage = $knownStatusCodes[$pdoe->getCode()];
    }

    $data = [];
    $data['status'] = $statusMessage;
    $data['errors'] = 'PDOException code is ' . $pdoe->getCode();

    $data['stack'] = $pdoe->getTraceAsString();

    $response = fillJsonResponseData($response, $data, 500);

    return $response;
}




// Duplicate?
function debuggingCaughtExceptionExceptionMapperForApi(
    \Bristolian\Exception\DebuggingCaughtException $pdoe,
    ResponseInterface $response
) {
    $text = getTextForException($pdoe);


    $data = [];
    $data['status'] = App::ERROR_CAUGHT_BY_MIDDLEWARE_API_MESSAGE;

    // Custom error code to avoid collision
    $response = fillJsonResponseData($response, $data, 512);

    return $response;
}
