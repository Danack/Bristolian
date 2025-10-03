<?php

declare(strict_types = 1);

use Bristolian\Model\StoredFile;
use Bristolian\Types\DocumentType;
use SlimDispatcher\Response\JsonNoCacheResponse;

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

    $result = json_encode($data, $options | JSON_PRETTY_PRINT);

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
            // Format as Atom time without microseconds
            return [
                null,
//                $value->format("Y-m-d\TH:i:s.uP")
                $value->format("Y-m-d\TH:i:sP")
            ];
        }
    }

    if (is_array($value) === true) {
        $values = [];
        foreach ($value as $key => $entry) {
            [$error, $value] = convertToValue($entry);

            if ($error !== null) {
                return [$error, null];
            }
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


/**
 * @template T of object
 * @param class-string<T> $classname
 * @param array<mixed> $data
 * @return T[]
 * @throws \Bristolian\Exception\BristolianException
 * @throws \DataType\Exception\ValidationException
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

    throw \Bristolian\Exception\BristolianException::cannot_instantiate();
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

function human_readable_value(int $size): string
{

    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $formattedSize = $size;

    for ($i = 0; $size >= 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
        $formattedSize = round($size, 2);
    }

    return $formattedSize . ' ' . $units[$i];
}


// @codeCoverageIgnoreStart
/**
 * @return array{0:int, 1:int}
 * @throws Exception
 */
function getPercentMemoryUsed(): array
{
    $maxMemory = memory_get_peak_usage();

    $memoryLimitValue = getMemoryLimit();

    $percentMemoryUsed = (int)((100 * $maxMemory) / $memoryLimitValue);

    return [$percentMemoryUsed, $memoryLimitValue];
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
 * @throws \Bristolian\Exception\BristolianException
 */
function getEnvString(string $name): string
{
    $value = getenv($name);

    if (is_string($value) !== true) {
        throw \Bristolian\Exception\BristolianException::env_variable_is_not_string($name, $value);
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


//function time_it()
//{
//    global $start_time;
//
//    $end_time = microtime(true);
//
//    var_dump($end_time, $start_time);
//
//    $time_taken = ($end_time - $start_time);
//
//    if ($time_taken < 0.001) {
//        echo "Basically nothing.";
//        exit(0);
//    }
//
//    echo "Time taken = " . $time_taken  . " m'kay.\n";
//    exit(0);
//}

/**
 * @param array<mixed> $data
 * @return string
 * @throws Exception
 */
function encodeWidgetyData(array $data): string
{
    $widget_json = json_encode_safe($data);
    $widget_data = htmlspecialchars($widget_json);

    return $widget_data;
}


/**
 * @return string[]
 */
function get_supported_room_file_extensions(): array
{
    return [
        'docx',
        'jpeg',
        'jpg',
        'md',
        'pdf',
        'png',
        'txt',
        'xls'
    ];
}

/**
 * @return string[]
 */
function get_supported_bristolian_stair_image_extensions(): array
{
    return [
        'jpeg',
        'jpg',
    ];
}


/**
 * @return string[]
 */
function get_supported_meme_file_extensions(): array
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
): string|null {
    foreach ($supported_file_extensions as $supported_file_extension) {
        if (strcmp($supported_file_extension, strtolower($supported_file_extension))) {
            $message = sprintf(
                "Supported file extension must be lower case. [%s]",
                implode(", ", $supported_file_extensions)
            );

            throw new \Bristolian\Exception\BristolianException($message);
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

function get_mime_type_from_extension(string $extension): string|null
{
    $contentTypesByExtension = [
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'gif' => 'image/gif',
        'jpeg' => 'image/jpeg',
        'jpg'  => 'image/jpeg',
        'md' => 'text/markdown',
        'mp4' => 'video/mp4',
        'pdf'  => 'application/pdf',
        'png'  => 'image/png',
        'txt'  => 'text/plain',
        'webp' => 'image/webp',
        'xls' => 'application/vnd.ms-excel',
    ];

    if (array_key_exists($extension, $contentTypesByExtension) === false) {
        return null;
    }

    return $contentTypesByExtension[$extension];
}

function getMimeTypeFromFilename(string $filename): string
{
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    $extension = strtolower($extension);

    $mime_type = get_mime_type_from_extension($extension);

    if ($mime_type === null) {
        throw new \Bristolian\Exception\BristolianException("Unknown file type [$extension]");
    }

    return $mime_type;
}



/**
 * @param $name
 * @param array<string, mixed> $values
 * @return JsonNoCacheResponse
 * @throws \SlimDispatcher\Response\InvalidDataException
 */
function createJsonResponse(array $values): JsonNoCacheResponse
{
    [$error, $data] = convertToValue($values);

    if ($error !== null) {
        $response_error = [
            'result' => 'failure',
            'error' => $error
        ];
        return new JsonNoCacheResponse(
            $response_error,
            [],
            500
        );
    }

    $response_ok = [
        'result' => 'success',
        'data' => $data
    ];

    return new JsonNoCacheResponse($response_ok);
}


/**
 * Get a standard 'reason phrase' for a HTTP status code.
 *
 * TODO - probably should be an enum since PHP has enums.
 *
 * @param int $status
 * @return string
 */
function getReasonPhrase(int $status): string
{
    $knownStatusReasons = [
        420 => 'Enhance Your Calm',
        421 => 'what the heck',
        512 => 'Server known limitation',
    ];

    return $knownStatusReasons[$status] ?? '';
}


/**
 * Returns an easy to read string describing the type of
 * the variable.
 *
 * @param mixed $value
 * @return string
 */
function get_readable_variable_type(mixed $value): string
{
    if (is_object($value) === true) {
        return "an object of type [" . get_class($value). "]";
    }

    $debug_type = get_debug_type($value);
    $known_types = [
        'null' => 'null',
        'bool' => 'a bool',
        'int' => 'an int',
        'float' => 'a float',
        'string' => 'a string',
        'array' => 'an array',
    ];

    if (array_key_exists($debug_type, $known_types) === true) {
        return $known_types[$debug_type];
    }

    return $debug_type;
}


function getRouteForStoredFile(string $room_id, StoredFile $storedFile): string
{
    $template = '/rooms/:uri_room_id/file/:uri_file_id/:uri_filename';
    $params = [
        ':uri_room_id' => $room_id,
        ':uri_file_id' => $storedFile->id,
        ':uri_filename' => $storedFile->original_filename,
    ];

    return esprintf($template, $params);
}


/**
 * Sorts strings so that the follow these rules:
 * i) any element that is just 'id' is first.
 * ii) any elements that contain '_id' are next, with those sorted alphabetically.
 * iii) any element that is 'modified_at' is last.
 * iv) any element that is 'created_at' is second from last.
 * v) all other elements are sorted alphabetically.
 *
 * This is used in generating the DB helper classes, so that they are stable,
 * and so don't differ randomly between different machines.
 *
 * @param list<string> $array
 * @return list<string>
 */
function customSort(array $array): array
{
    usort($array, function ($a, $b) {
        // Define custom priorities
        $priorities = [
            'id' => 0,                // Highest priority
            'created_at' => 998,      // Second to last
            'modified_at' => 999,     // Last
        ];

        // Get priorities or default to a middle range for other elements
        $aPriority = $priorities[$a] ?? 500;
        $bPriority = $priorities[$b] ?? 500;

        // Compare by priority
        if ($aPriority !== $bPriority) {
            return $aPriority <=> $bPriority;
        }

        // Handle cases where priorities are equal
        // For '_id' elements, sort alphabetically
        $aIsId = str_ends_with($a, '_id');
        $bIsId = str_ends_with($b, '_id');

        if ($aIsId && $bIsId) {
            return $a <=> $b;
        }

        if ($aIsId) {
            return -1;
        }
        if ($bIsId) {
            return 1;
        }

        // For all other elements, sort alphabetically
        return $a <=> $b;
    });

    return $array;
}



function generateSystemInfoEmailContent(): string
{
    $body = "Shamoan";

    $body .= "\n\n";

    $exec_output = [];
    $result_code = null;
    exec("df -h", $exec_output, $result_code);

    if ($result_code !== 0) {
        $body .= "Failed to run disk info command";
    }
    else {
        $body .= implode("\n", $exec_output);
    }

    return $body;
}


/**
 * @param class-string<BackedEnum> $typeString
 * @return array<BackedEnum>
 */
function getEnumCases(string $typeString): array
{
    // Check if the class exists
    if (!class_exists($typeString)) {
        throw new InvalidArgumentException("Class '$typeString' does not exist.");
    }

    // Use Reflection to inspect the class
    $reflection = new ReflectionClass($typeString);

    // Check if it's an enum
    if (!$reflection->isEnum()) {
        throw new InvalidArgumentException("Class '$typeString' is not an enum.");
    }

    // Get enum cases
    return $cases = $typeString::cases();
}
/**
 * @param class-string<BackedEnum> $typeString
 * @return int[]|string[]
 */
function getEnumCaseValues(string $typeString): array
{
    // Check if the class exists
    if (!class_exists($typeString)) {
        throw new InvalidArgumentException("Class '$typeString' does not exist.");
    }

    // Use Reflection to inspect the class
    $reflection = new ReflectionClass($typeString);

    // Check if it's an enum
    if (!$reflection->isEnum()) {
        throw new InvalidArgumentException("Class '$typeString' is not an enum.");
    }

    // Get enum cases
    $cases = $typeString::cases();

    // Convert cases to array of names (or values, depending on your needs)
    return array_map(fn($case) => $case->value, $cases);
}


function mapStreamingResponseToPSR7(
    \Bristolian\Response\StreamingResponse $streamingResponse
): \Psr\Http\Message\ResponseInterface {

    $response = new \Laminas\Diactoros\Response();
    $status_code = $streamingResponse->getStatusCode();

    $response = $response->withStatus(
        $status_code,
        \SlimDispatcher\getReasonPhrase($status_code)
    );

    foreach ($streamingResponse->getHeaders() as $key => $value) {
        /** @var \Psr\Http\Message\ResponseInterface $response */
        $response = $response->withHeader($key, $value);
    }

    $response = $response->withBody($streamingResponse->getBodyStream());

    return $response;
}


function purgeVarnish(string $urlPath): bool
{
    $varnishHost = 'varnish';
    $varnishPort = 80;

    $errno = 0;
    $errstr = '';
    $fp = fsockopen($varnishHost, $varnishPort, $errno, $errstr, 2);
    if (!$fp) {
        \error_log(sprintf("Failed to connect to Varnish: %s (%d)\n", $errstr, $errno));
        return false;
    }

    $request = "PURGE $urlPath HTTP/1.1\r\n";
    $request .= "Host: bristolian.org\r\n";
    $request .= "Connection: close\r\n\r\n";

    fwrite($fp, $request);

    // Read the response from Varnish
    $response = '';
    while (!feof($fp)) {
        $response .= fgets($fp, 1024);
    }

    fclose($fp);

    // Check HTTP status code in response
    if (preg_match('#HTTP/\d\.\d (\d{3})#', $response, $matches)) {
        $status = (int)$matches[1];
        if ($status >= 200 && $status < 300) {
            \error_log(sprintf("Varnish purge successful: HTTP %d\n", $status));
            return true;
        } else {
            \error_log(sprintf("Varnish purge failed: HTTP %d\n%s", $status, $response));
            return false;
        }
    } else {
        \error_log("Could not read HTTP response from Varnish\n");
        return false;
    }
}
