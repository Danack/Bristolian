<?php

declare(strict_types = 1);

use Bristolian\Types\DocumentType;
use SlimDispatcher\Response\JsonNoCacheResponse;
use VarMap\ArrayVarMap;
use VarMap\VarMap;
use Bristolian\ToArray;

$injector = null;

/**
 * Format an array of strings to have a count at the start
 * e.g. $lines = ['foo', 'bar'], output is:
 *
 * #0 foo
 * #1 bar
 *
 * @param string[] $lines
 * @return string
 */
function formatLinesWithCount(array $lines): string
{
    $output = '';
    $count = 0;

    foreach ($lines as $line) {
        $output .= '#' . $count . ' '. $line . "\n";
        $count += 1;
    }

    return $output;
}

/**
 * Self-contained monitoring system for system signals
 * returns true if a 'graceful exit' like signal is received.
 *
 * We don't listen for SIGKILL as that needs to be an immediate exit,
 * which PHP already provides.
 *
 * @codeCoverageIgnore
 * @return bool
 */
function checkSignalsForExit()
{
    static $initialised = false;
    static $needToExit = false;
    static $fnSignalHandler = null;

    if ($initialised === false) {
        $fnSignalHandler = function ($signalNumber) use (&$needToExit) {
            $needToExit = true;
        };
        pcntl_signal(SIGINT, $fnSignalHandler, false);
        pcntl_signal(SIGQUIT, $fnSignalHandler, false);
        pcntl_signal(SIGTERM, $fnSignalHandler, false);
        pcntl_signal(SIGHUP, $fnSignalHandler, false);
        pcntl_signal(SIGUSR1, $fnSignalHandler, false);
        $initialised = true;
    }

    pcntl_signal_dispatch();

    return $needToExit;
}


/**
 * Repeatedly calls a callable until it's time to stop
 *
 * @param callable $callable - the thing to run
 * @param int $secondsBetweenRuns - the minimum time between runs
 * @param int $sleepTime - the time to sleep between runs
 * @param int $maxRunTime - the max time to run for, before returning
 */
function continuallyExecuteCallable($callable, int $secondsBetweenRuns, int $sleepTime, int $maxRunTime): void
{
    $startTime = microtime(true);
    $lastRuntime = 0;
    $finished = false;

    echo "starting continuallyExecuteCallable \n";
    while ($finished === false) {
        $shouldRunThisLoop = false;
        if ($secondsBetweenRuns === 0) {
            $shouldRunThisLoop = true;
        }
        else if ((microtime(true) - $lastRuntime) > $secondsBetweenRuns) {
            $shouldRunThisLoop = true;
        }

        if ($shouldRunThisLoop === true) {
            $callable();
            $lastRuntime = microtime(true);
        }

        if (checkSignalsForExit()) {
            break;
        }

        if ($sleepTime > 0) {
            sleep($sleepTime);
        }

        if ((microtime(true) - $startTime) > $maxRunTime) {
            echo "Reach maxRunTime - finished = true\n";
            $finished = true;
        }
    }

    echo "Finishing continuallyExecuteCallable\n";
}


/**
 * Decode JSON with actual error detection
 *
 * @param string|null $json
 * @return mixed[]
 * @throws \Bristolian\Exception\JsonException
 * @throws \Seld\JsonLint\ParsingException
 */
function json_decode_safe(?string $json): array
{
    if ($json === null) {
        throw new \Bristolian\Exception\JsonException("Error decoding JSON: cannot decode null.");
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
        throw new \Bristolian\Exception\JsonException("Error decoding JSON: null returned.");
    }

    throw new \Bristolian\Exception\JsonException("Error decoding JSON: " . json_last_error_msg());
}

/**
 * @param string[] $headers
 * @param mixed[] $items
 * @param callable[] $rowFns
 * @return string
 * @throws \Esprintf\EsprintfException
 */
function renderTableHtml(
    array $headers,
    array $items,
    array $rowFns
): string {
    $thead = '';
    foreach ($headers as $header) {
        $thead .= esprintf("<th>:html_header</th>\n", [':html_header' => $header]);
    }

    $trow_template =  "<tr>";
    foreach ($rowFns as $placeholder => $fn) {
        $trow_template .= "<td>$placeholder</td>";
    }
    $trow_template .= "</tr>";

    $tbody = '';
    foreach ($items as $item) {
        $data = [];
        foreach ($rowFns as $placeholder => $fn) {
            $data[$placeholder] = $fn($item);
        }
        $tbody .= esprintf($trow_template, $data);
    }

    $table = <<< TABLE
<table>
  <thead>
    <tr>
$thead
    </tr>
  </thead>
  <tbody>
$tbody
  </tbody>
</table>
TABLE;

    return $table;
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
        throw new \Bristolian\Exception\JsonException("Failed to encode data as json: " . json_last_error_msg());
    }

    return $result;
}

/**
 * @param Throwable $exception
 * @return mixed[]
 */
function getExceptionInfoAsArray(\Throwable $exception): array
{
    $data = [
        'status' => 'error',
        'message' => $exception->getMessage(),
    ];

    $previousExceptions = [];

    do {
        $exceptionInfo = [
            'type' => get_class($exception),
            'message' => $exception->getMessage(),
            'trace' => getExceptionStackAsArray($exception),
        ];

        $previousExceptions[] = $exceptionInfo;
    } while (($exception = $exception->getPrevious()) !== null);

    $data['details'] = $previousExceptions;

    return $data;
}


function peak_memory(bool $real_usage = false): string
{
    return number_format(memory_get_peak_usage($real_usage));
}


/**
 * @param $value
 *
 * @return array{string, null}|array{null, mixed}
 */
function convertToValue(mixed $value)
{
    if (is_scalar($value) === true) {
        return [
            null,
            $value
        ];
    }
    if ($value === null) {
        return [
            null,
            null
        ];
    }

    $callable = [$value, 'toArray'];
    if (is_object($value) === true && is_callable($callable)) {
        return [
            null,
            $callable()
        ];
    }
    if (is_object($value) === true) {
        if ($value instanceof \DateTimeInterface) {
            // Format as Atom time with microseconds
            return [
                null,
                $value->format("Y-m-d\TH:i:s.uP")
            ];
        }
    }

    if (is_array($value) === true) {
        $values = [];
        foreach ($value as $key => $entry) {
            [$error, $value] = convertToValue($entry);
            // TODO - why is error being ignored....
            $values[$key] = $value;
        }

        return [
            null,
            $values
        ];
    }

    if (is_object($value) === true) {
        return [
            sprintf(
                "Unsupported type [%s] of class [%s] for toArray.",
                gettype($value),
                get_class($value)
            ),
            null
        ];
    }

    return [
        sprintf(
            "Unsupported type [%s] for toArray.",
            gettype($value)
        ),
        null
    ];
}


//function convertToValue($name, $value)
//{
//    if (is_scalar($value) === true) {
//        return $value;
//    }
//    if ($value === null) {
//        return null;
//    }
//
//    $callable = [$value, 'toArray'];
//    if (is_object($value) === true && is_callable($callable)) {
//        return $callable();
//    }
//    if (is_object($value) === true && $value instanceof \DateTime) {
//        return $value->format(\Bristolian\App::DATE_TIME_EXACT_FORMAT);
//    }
//
//    if (is_array($value) === true) {
//        $values = [];
//        foreach ($value as $key => $entry) {
//            $values[$key] = convertToValue($key, $entry);
//        }
//
//        return $values;
//    }
//
//    $message = "Unsupported type [" . gettype($value) . "] for toArray for property $name.";
//
//    if (is_object($value) === true) {
//        $message = "Unsupported type [" . gettype($value) . "] of class [" . get_class($value) . "] for toArray for property $name.";
//    }
//
//    throw new \Exception($message);
//}


/**
 * @throws \DataType\Exception\ValidationException
 * @throws \Bristolian\BristolianException
 */
function convertToArrayOfObjects(string $classname, array $data)
{
//    $callable = [$classname, 'fromArray'];
//    if (is_callable($callable)) {
//        return call_user_func($callable, $args);
//    }

    $interfaces = class_implements($classname);

    if (isset($interfaces[\DataType\DataType::class])) {
        $objects = \DataType\createArrayOfType($classname, $data);
        return $objects;
    }

    throw \Bristolian\BristolianException::cannot_instantiate();
}


/**
 * Fetch data and return statusCode, body and headers
 *
 * @param string $uri
 * @param string $method
 * @param mixed[] $queryParams
 * @param string|null $body
 * @param mixed[] $headers
 * @return array{0:int, 1:string, 2:mixed[]}
 */
function fetchUri(string $uri, string $method, array $queryParams = [], string $body = null, array $headers = [])
{
    $query = http_build_query($queryParams);
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $uri . $query);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

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
 *
 * @param string $uri
 * @param mixed[] $headers
 * @return mixed[]
 * @throws JsonException
 * @throws \Bristolian\Exception\JsonException
 * @throws \Seld\JsonLint\ParsingException
 */
function fetchDataWithHeaders(string $uri, array $headers): array
{
    [$statusCode, $body, $responseHeaders] = fetchUri($uri, 'GET', [], null, $headers);

    if ($statusCode === 200) {
        return json_decode_safe($body);
    }

    throw new \Exception("Failed to fetch data from " . $uri);
}

function getMask(string $name): int
{
    if ($name === 'sign') {
        return 0x2;
    }
    if ($name === 'exponent') {
        return 0x800;
    }
    if ($name === 'mantissa') {
        return 0x80000000000000;
    }

    throw new \Exception("Unknown type $name");
}


function showTotalErrorPage(\Throwable $exception): void
{
    $exceptionText = null;

    $exceptionText = "Failed to get exception text.";

    try {
        $exceptionText = getExceptionText($exception);

        \error_log("Exception in code and Slim error handler failed also: " . get_class($exception) . " " . $exceptionText);
    }
    catch (\Throwable $exception) {
        // Does nothing.
    }

    http_response_code(503);

    if ($exceptionText !== null) {
        var_dump(get_class($exception));
        echo nl2br($exceptionText);
    }
}


/**
 * @return int
 * @throws Exception
 */
// @codeCoverageIgnoreStart
function getMemoryLimit(): int
{
    $memoryLimit = ini_get('memory_limit');

    /** @phpstan-ignore-next-line better to be correct than brief */
    if ($memoryLimit === false) {
        throw new \Exception("Failed to get memory_limit.");
    }

    if (strrpos($memoryLimit, 'M') === (strlen($memoryLimit) - 1)) {
        $memoryLimitValue = ((int)$memoryLimit) * 1024 * 1024;
    }
    else if (strrpos($memoryLimit, 'G') === (strlen($memoryLimit))) {
        $memoryLimitValue = ((int)$memoryLimit) * 1024 * 1024 * 1024;
    }
    else {
        throw new \Exception("Could not understand memory limit of [$memoryLimit]");
    }

    return $memoryLimitValue;
}
// @codeCoverageIgnoreEnd

// @codeCoverageIgnoreStart
function getPercentMemoryUsed(): int
{
    $maxMemory = memory_get_peak_usage();

    $memoryLimitValue = getMemoryLimit();

    $percentMemoryUsed = (int)((100 * $maxMemory) / $memoryLimitValue);

    return $percentMemoryUsed;
}
// @codeCoverageIgnoreEnd

/**
 * Remove the installation directory prefix from a filename
 */
function remove_install_prefix_from_path(string $file): string
{
    if (strpos($file, "/var/app/") === 0) {
        $file = substr($file, strlen("/var/app/"));
    }

    return $file;
}

/**
 * Gets a environment variable and checks that the value is a string.
 * throws an exception if value is not a string.
 *
 * @param string $name
 * @return string
 * @throws \Bristolian\BristolianException
 */
function getEnvString(string $name): string
{
    $value = getenv($name);

    if (is_string($value) !== true) {
        throw \Bristolian\BristolianException::env_variable_is_not_string($name, $value);
    }

    return $value;
}


/**
 * @param mixed $needle
 * @param array<mixed> $haystack
 * @return bool
 */
function array_contains($needle, array $haystack): bool
{
    return in_array($needle, $haystack, true);
}


/**
 * @param string $password
 * @return string
 */
function generate_password_hash(string $password): string
{
    $options = get_password_options();
    return password_hash($password, PASSWORD_BCRYPT, $options);

//    if ($hash === false) {
//        throw new \Exception('Failed to hash password.');
//    }
//
//    return $hash;
}


/**
 * Get the options to use when hashing passwords.
 * The cost should be tuned for the hash to take something like a
 * quarter of a second of CPU time to hash.
 *
 * @return array<mixed>
 */
function get_password_options(): array
{
    $options = [
        'cost' => 12,
    ];

    return $options;
}


function getRandomId(): string
{
    $foo = random_bytes(32);

    return hash("sha256", $foo);
}

//Guardian was taken over:
//twitter.com/davidgraeber/status/1210322505229094912
//https://twitter.com/davidgraeber/status/1210322505229094912


/**
 * Escape characters that are meaningful in SQL like searches
 *
 * @param string $string
 * @return string
 */
function escapeMySqlLikeString(string $string)
{
    return str_replace(
        ['\\', '_', '%', ],
        ['\\\\', '\\_', '\\%'],
        $string
    );
}


function render_markdown_file(Bristolian\Model\UserDocument $document): string
{
    global $injector;

    $renderer = $injector->make(\Bristolian\MarkdownRenderer\MarkdownRenderer::class);

    // TODO - escaper needs a file path type.
    $path = standardise_username_to_filename($document->getUser()->username);

    $filename = __DIR__ . '/../user_data/' . $path . '/' . $document->source;

    return $renderer->renderFile($filename);
}

/**
 * Extracts a link to a Github gist from the link to a raw file
 * held in a gist, and then renders it as a href.
 *
 * If the url does not contain the string '/raw/' a string is rendered
 * without a href
 * @param string $raw
 * @return string
 */
function get_external_source_link(string $raw): string
{
    $raw_position = strpos($raw, '/raw/');

    if ($raw_position === false) {
        return "External source is: " . $raw;
    }

    $link = substr($raw, 0, $raw_position);

    return sprintf(
        "External source is: <a href='%s'>%s</a>",
        $link,
        $raw
    );
}

function render_markdown_url(Bristolian\Model\UserDocument $document): string
{
    global $injector;

    $renderer = $injector->make(\Bristolian\ExternalMarkdownRenderer\ExternalMarkdownRenderer::class);

    $contents = $renderer->renderUrl($document->source);

    $contents .= "<hr/>";



    $contents .= get_external_source_link($document->source);

    return $contents;
}


/**
 * @param \Bristolian\Model\UserDocument $document
 * @return string
 */
function render_user_document(Bristolian\Model\UserDocument $document): string
{
    global $injector;

    if ($injector === null) {
        return "Oops, injector is null.";
    }

    return match ($document->type) {
        DocumentType::markdown_file => render_markdown_file($document),
        DocumentType::markdown_url => render_markdown_url($document)
    };
}


/**
 * @param string $string
 * @return string
 *
 * Code taken from
 * https://ourcodeworld.com/articles/read/253/creating-url-slugs-properly-in-php-including-transliteration-support-for-utf-8
 * and assumed to be http://creativecommons.org/publicdomain/zero/1.0/
 */
function slugify(string $string): string
{
    $entities_removed = htmlentities($string, ENT_QUOTES, 'UTF-8');

    $accents_removed = preg_replace(
        '~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i',
        '$1',
        $entities_removed
    );

    $something = html_entity_decode($accents_removed, ENT_QUOTES, 'UTF-8');

    $normalised_to_ascii = preg_replace(
        '~[^0-9a-z]+~i', '-', $something
    );

    $trimmed = trim(
        $normalised_to_ascii,
        '-'
    );

    return strtolower($trimmed);
}

/**
 * Sanitises a file name to remove any directory escaping
 * character sequences i.e. '/', '\' or '..'
 *
 * @param string $filename
 * @return string
 */
function sanitise_filename(string $filename): string
{
    $search = [
        '/',
        '\\',
        '..',
    ];

    return str_replace($search, '_', strtolower($filename));
}

/**
 * Helper function to convert user name to safe file name
 * to allow hard-coded data to be served safely from the
 * user_data directory.
 *
 * @param string $username
 * @return string
 */
function standardise_username_to_filename(string $username)
{
    $filename  = sanitise_filename($username);

    return str_replace(' ', '_', strtolower($filename));
}

/**
 * Creates a JsonResponse with appropriate error status code set
 * if validations_problems are not empty.
 * Returns null if there are no problems.
 *
 *
 * @param \DataType\ValidationProblem[] $validation_problems
 * @return \SlimDispatcher\Response\JsonResponse|null
 * @throws \SlimDispatcher\Response\InvalidDataException
 */
function createErrorJsonResponse(array $validation_problems): SlimDispatcher\Response\JsonResponse|null
{
    if (count($validation_problems) === 0) {
        return null;
    }

    $data = ['success' => false];
    $data['errors'] = [];
    foreach ($validation_problems as $validation_problem) {
        $data['errors'][] = $validation_problem->toString();
    }

    return new SlimDispatcher\Response\JsonResponse($data, [], 400);
}

//$start_time = microtime(true);

function time_it()
{
    global $start_time;

    $end_time = microtime(true);

    var_dump($end_time, $start_time);

    $time_taken = ($end_time - $start_time);

    if ($time_taken < 0.001) {
        echo "Basically nothing.";
        exit(0);
    }

    echo "Time taken = " . $time_taken  . " m'kay.\n";
    exit(0);
}

function encodeWidgetyData(array $data)
{
    $widget_json = json_encode_safe($data);
    $widget_data = htmlspecialchars($widget_json);

    return $widget_data;
}





function get_supported_file_extensions()
{
    return [
        'gif',
        'jpg',
        'jpeg',
        'mp4',
        'png',
        'pdf',
        'webp'
    ];
}

function get_supported_room_file_extensions()
{
    return [
        'docx',
        'jpg',
        'md',
        'pdf',
        'png',
        'txt',
        'xls'
    ];
}


/**
 * Normalizes a supported extension to lower case or returns null if the extension
 * is not supported.
 *
 * @param string $original_name
 * @param string $contents
 * @param string[] $supported_file_extensions
 * @return string
 */
function normalize_file_extension(
    string $original_name,
    string $contents, // TODO - use this, and change it to a filestream
    array $supported_file_extensions
) {
    foreach ($supported_file_extensions as $supported_file_extension) {
        if (strcmp($supported_file_extension, strtolower($supported_file_extension))) {
            $message = sprintf(
                "Supported file extension must be lower case. [%s]",
                implode(", ", $supported_file_extensions)
            );

            throw new \Bristolian\BristolianException($message);
        }
    }

    $extension = pathinfo($original_name, PATHINFO_EXTENSION);

    if (strlen($extension) === 0) {
        return null;
    }

    $lower_case_extension = strtolower($extension);

    // Contents is passed to allow mime type magic checking
    if (array_contains($lower_case_extension, $supported_file_extensions) === true) {
        return $lower_case_extension;
    }

    return null;
}


/**
 * @param $name
 * @param $values
 * @return JsonNoCacheResponse
 * @throws \SlimDispatcher\Response\InvalidDataException
 */
function createJsonResponse($name, $values): JsonNoCacheResponse
{
    [$error, $data] = convertToValue($values);

    if ($error !== null) {
        $response_error = [
            'error' => $error
        ];
        return new JsonNoCacheResponse(
            $response_error,
            [],
            500
        );
    }

    $response_ok = [
        $name => $data
    ];

    return new JsonNoCacheResponse($response_ok);
}
