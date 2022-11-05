<?php

/**
 * Fetch data and return statusCode, body and headers
 * @param string $uri
 * @param string $method
 * @param array<mixed> $queryParams
 * @param string|null $body
 * @param array<mixed> $headers
 * @return array<mixed>
 */
function fetchUri(
    string $uri,
    string $method,
    array $queryParams = [],
    string $body = null,
    array $headers = []
) {
    $query = http_build_query($queryParams);
    $curl = curl_init();


    curl_setopt($curl, CURLOPT_URL, $uri . '/' . $query);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

    $allHeaders = [];

    if ($body !== null) {
        $allHeaders[] = 'Content-Type: application/json';
        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
    }


    foreach ($headers as $header) {
        $allHeaders[] = $header;
    }

    curl_setopt($curl, CURLOPT_HTTPHEADER, $allHeaders);

    $headers = [];
    $handleHeaderLine = function ($curl, $headerLine) use (&$headers) {
        $headers[] = $headerLine;
        return strlen($headerLine);
    };
    curl_setopt($curl, CURLOPT_HEADERFUNCTION, $handleHeaderLine);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $body = curl_exec($curl);
    $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    return [$statusCode, $body, $headers];
}

/**
 * Fetch data and only return successful request
 * @param string $uri
 * @param array<string, string> $headers
 * @return mixed
 */
function fetchDataWithHeaders(string $uri, array $headers)
{
    [$statusCode, $body, $responseHeaders] = fetchUri($uri, 'GET', [], null, $headers);

    if ($statusCode === 200) {
        return json_decode($body);
    }

    throw new \Exception("Failed to fetch data from " . $uri);
}

/**
 * Fetch data and only return successful request
 * @param string $uri
 * @return mixed
 */
function fetchData(string $uri)
{
    [$statusCode, $body, $headers] = fetchUri($uri, 'GET');

    if ($statusCode === 200) {
        return json_decode($body);
    }

    throw new \Exception("Failed to fetch data from " . $uri);
}



/**
 * Decode JSON with actual error detection
 * @return mixed
 */
function json_decode_safe(?string $json)
{
    if ($json === null) {
        throw new \PhpOpenDocs\Exception\JsonException("Error decoding JSON: cannot decode null.");
    }

    $data = json_decode($json, true);

    if (json_last_error() === JSON_ERROR_NONE) {
        return $data;
    }

    $parser = new \Seld\JsonLint\JsonParser();
    $parsingException = $parser->lint($json);

    if ($parsingException !== null) {
        throw $parsingException;
    }

    if ($data === null) {
        throw new \Exception("Error decoding JSON: null returned.");
    }

    throw new \JsonException("Error decoding JSON: " . json_last_error_msg());
}


/**
 * @param mixed $data
 * @param int $options
 * @return string
 * @throws Exception
 */
function json_encode_safe($data, $options = 0): string
{
    $result = json_encode($data, $options);

    if ($result === false) {
        throw new \Exception("Failed to encode data as json: " . json_last_error_msg());
    }

    return $result;
}