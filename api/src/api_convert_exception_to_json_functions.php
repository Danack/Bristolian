<?php

declare(strict_types = 1);

// Holds functions that convert exceptions into command
// line output for use in the api environment.

use Bristolian\App;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use DataType\Exception\ValidationException;
use SlimDispatcher\Response\JsonResponse;

/**
 * @param ResponseInterface $response
 * @param array $data
 * @param int $statusCode
 * @return ResponseInterface
 * @throws \SlimDispatcher\Response\InvalidDataException
 */
function fillJsonResponseData(ResponseInterface $response, array $data, int $statusCode)
{
    if (array_key_exists('status_code', $data) === false) {
        $data['status_code'] = $statusCode;
    }

    $builtResponse = new JsonResponse($data, [], $statusCode);
    $reasonPhrase = getReasonPhrase($statusCode);
    $response = $response->withStatus($builtResponse->getStatus(), $reasonPhrase);
    foreach ($builtResponse->getHeaders() as $key => $value) {
        $response = $response->withAddedHeader($key, $value);
    }

    $response->getBody()->write($builtResponse->getBody());

    return $response;
}

function convertValidationExceptionMapperApi(
    ValidationException $ve,
    RequestInterface $request,
    ResponseInterface $response
): ResponseInterface {
    $data = [];

    // JSON Responses are inspired by:
    // https://github.com/omniti-labs/jsend

    $data['status'] = 'fail'; ;
    $data['message'] = 'There were validation errors';
    $data['data'] = [];

    foreach ($ve->getValidationProblems() as $validationProblem) {
        $name = $validationProblem->getInputStorage()->getPath();
        $data['data'][$name] = $validationProblem->getProblemMessage();
    }

    $response = fillJsonResponseData($response, $data, 400);

    return $response;
}


function convertHttpNotFoundExceptionToResponse(
    \Slim\Exception\HttpNotFoundException $hnfe,
    RequestInterface $request,
    ResponseInterface $response
): ResponseInterface {
    $data = [];
    $data['status'] = 'Route not found';
    // TODO - match for closest route?
    // $data['errors'] = $hnfe->getValidationProblems();

    $response = fillJsonResponseData($response, $data, 404);

    return $response;
}

//function paramsValidationExceptionMapperApi(
//    ValidationException $ve,
//    ResponseInterface $response
//) {
//    $data = [];
//    $data['status'] = 'There were validation errors';
//    $data['errors'] = $ve->getValidationProblems();
//
//    $response = fillJsonResponseData($response, $data, 400);
//
//    return $response;
//}

function pdoExceptionMapperApi(
    \PDOException $pdoe,
    RequestInterface $request,
    ResponseInterface $response
): ResponseInterface {
    $text = getTextForException($pdoe);
    \error_log($text);

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

    $data['stack'] = $pdoe->getTrace();

    $response = fillJsonResponseData($response, $data, 500);

    return $response;
}




// Duplicate?
function debuggingCaughtExceptionExceptionMapperForApi(
    \Bristolian\Exception\DebuggingCaughtException $pdoe,
    RequestInterface $request,
    ResponseInterface $response
) {
    $text = getTextForException($pdoe);
    \error_log($text);

    $data = [];
    $data['status'] = App::ERROR_CAUGHT_BY_MIDDLEWARE_API_MESSAGE;

    // Custom error code to avoid collision
    $response = fillJsonResponseData($response, $data, 512);

    return $response;
}
