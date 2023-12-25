<?php

declare(strict_types = 1);

use Bristolian\Types\DocumentType;
use VarMap\ArrayVarMap;
use VarMap\VarMap;
use Bristolian\ToArray;

$injector = null;

//function hackVarMap($varMap)
//{
//    $params = $varMap->toArray();
//
//    $unwantedParams = ['q', 'time'];
//
//    foreach ($unwantedParams as $unwantedParam) {
//        if (array_key_exists($unwantedParam, $params) === true) {
//            unset($params[$unwantedParam]);
//        }
//    }
//
//    $hackedVarMap = new ArrayVarMap($params);
//
//    return $hackedVarMap;
//}

//function purgeExceptionMessage(\Throwable $exception)
//{
//    $rawMessage = $exception->getMessage();
//
//    $purgeAfterPhrases = [
//        'with params'
//    ];
//
//    $message = $rawMessage;
//
//    foreach ($purgeAfterPhrases as $purgeAfterPhrase) {
//        $matchPosition = strpos($message, $purgeAfterPhrase);
//        if ($matchPosition !== false) {
//            $message = substr($message, 0, $matchPosition + strlen($purgeAfterPhrase));
//            $message .= '**PURGED**';
//        }
//    }
//
//    return $message;
//}

//function getTextForException(\Throwable $exception)
//{
//    $currentException = $exception;
//    $text = '';
//
//    do {
//        $text .= sprintf(
//            "Exception type:\n  %s\n\nMessage:\n  %s \n\nStack trace:\n%s\n",
//            get_class($currentException),
//            purgeExceptionMessage($currentException),
//            formatLinesWithCount(getExceptionStackAsArray($currentException))
//        );
//
//
//        $currentException = $currentException->getPrevious();
//    } while ($currentException !== null);
//
//    return $text;
//}

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


///**
// * @param Throwable $exception
// * @return string[]
// */
//function getExceptionStackAsArray(\Throwable $exception)
//{
//    $lines = [];
//    foreach ($exception->getTrace() as $trace) {
//        $lines[] = formatTraceLine($trace);
//    }
//
//    return $lines;
//}


//function formatTraceLine(array $trace)
//{
//    $location = '??';
//    $function = 'unknown';
//
//    if (isset($trace["file"]) && isset($trace["line"])) {
//        $location = $trace["file"]. ':' . $trace["line"];
//    }
//    else if (isset($trace["file"])) {
//        $location = $trace["file"] . ':??';
//    }
//
//    $baseDir = realpath(__DIR__ . '/../');
//    if ($baseDir === false) {
//        throw new \Exception("Couldn't find parent directory from " . __DIR__);
//    }
//
//    $location = str_replace($baseDir, '', $location);
//
//    if (isset($trace["class"]) && isset($trace["type"]) && isset($trace["function"])) {
//        $function = $trace["class"] . $trace["type"] . $trace["function"];
//    }
//    else if (isset($trace["class"]) && isset($trace["function"])) {
//        $function = $trace["class"] . '_' . $trace["function"];
//    }
//    else if (isset($trace["function"])) {
//        $function = $trace["function"];
//    }
//    else {
//        $function = "Function is weird: " . json_encode(var_export($trace, true));
//    }
//
//    return sprintf(
//        "%s %s",
//        $location,
//        $function
//    );
//}


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


//function getExceptionText(\Throwable $exception): string
//{
//    $text = "";
//    do {
//        $text .= get_class($exception) . ":" . $exception->getMessage() . "\n\n";
//
//        if ($exception instanceof Auryn\InjectionException) {
//            $text .= "DependencyChains is:\n";
//            foreach ($exception->getDependencyChain() as $item) {
//                $text .= "  " . $item . "\n";
//            }
//        }
//
//        $text .= $exception->getTraceAsString();
//
//
//        $exception = $exception->getPrevious();
//    } while ($exception !== null);
//
//    return $text;
//}

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
        if ($value instanceof \DateTime) {
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

/*
  TODO - is this convertToValue better?

/**
 * @param string $name
 * @param mixed $value
 * @return mixed
 * @throws Exception
 * /
function convertToValue(string $name, $value)
{
    if (is_scalar($value) === true) {
        return $value;
    }
    if ($value === null) {
        return null;
    }

    $callable = [$value, 'toArray'];
    if (is_object($value) === true && is_callable($callable)) {
        return $callable();
    }
    if (is_object($value) === true && $value instanceof \DateTime) {
        return $value->format(\Bristolian\App::DATE_TIME_EXACT_FORMAT);
    }

    if (is_array($value) === true) {
        $values = [];
        foreach ($value as $key => $entry) {
            $values[$key] = convertToValue($key, $entry);
        }

        return $values;
    }

    $message = "Unknown error converting to param '$name' to value.";

    if (is_object($value) === true) {
        $message = "Unsupported type [" . gettype($value) . "] of class [" . get_class($value) . "] for toArray for property $name.";
    }

    throw new \Exception($message);
}

*/







///**
// * @param string $name
// * @param mixed $value
// * @return mixed
// * @throws Exception
// */
//function convertToValue(string $name, $value)
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
//    $message = "Unknown error converting to param '$name' to value.";
//
//    if (is_object($value) === true) {
//        $message = "Unsupported type [" . gettype($value) . "] of class [" . get_class($value) . "] for toArray for property $name.";
//    }
//
//    throw new \Exception($message);
//}

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


//// Define a function that writes a string into the response object.
//function convertStringToHtmlResponse(
//    string $result,
//    \Psr\Http\Message\RequestInterface $request,
//    \Psr\Http\Message\ResponseInterface $response
//): \Psr\Http\Message\ResponseInterface {
//    $response = $response->withHeader('Content-Type', 'text/html');
//    $response->getBody()->write($result);
//    return $response;
//}


//function getEyeColorSpaceStringFromValue(int $value)
//{
//    $colorspaceOptions = getEyeColourSpaceOptions();
//
//    foreach ($colorspaceOptions as $string => $int) {
//        if ($value === $int) {
//            return $string;
//        }
//    }
//
//    throw new \Exception("Bad option for getEyeColorSpaceStringFromValue $value");
//}


//function getImagePathForOption(string $selected_option)
//{
//    $imageOptions = getImagePathOptions();
//
//    foreach ($imageOptions as $path => $option) {
//        if ($option === $selected_option) {
//            return $path;
//        }
//    }
//
//    foreach ($imageOptions as $key => $value) {
//        return $key;
//    }
//
//
//    return array_key_first($imageOptions);
//}


//function image(
//    ?string $activeCategory,
//    ?string $activeExample,
//    array $values,
//    Example $example
//) {
//    $imgUrl = sprintf(
//        "/image/%s/%s",
//        $activeCategory,
//        $activeExample
//    );
//    $pageBaseUrl = sprintf("/%s/%s",
//        $activeCategory,
//        $activeExample
//    );
//
//    return createReactImagePanel(
//        $imgUrl,
//        $pageBaseUrl,
//        $example
//    );
//
//}

//function customImage(
//    ?string $activeCategory,
//    ?string $activeExample,
//    array $values,
//    Example $example
//) {
//    $imgUrl = sprintf(
//        "/customImage/%s/%s",
//        $activeCategory,
//        $activeExample
//    );
//    $pageBaseUrl = sprintf("/%s/%s",
//        $activeCategory,
//        $activeExample
//    );
//
//    return createReactImagePanel(
//        $imgUrl,
//        $pageBaseUrl,
//        $example
//    );
//}

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

//function twiddleWithShit(FloatTwiddleControl $floatTwiddleControl)
//{
//    /** @var FloatTwiddleControl $floatTwiddleControl */
//    $mask = getMask($floatTwiddleControl->getName());
//
//    $sign = $floatTwiddleControl->getSign();
//    $exponent = $floatTwiddleControl->getExponent();
//    $mantissa = $floatTwiddleControl->getMantissa();
//
//    $name = $floatTwiddleControl->getName();
//    if ($name === 'sign') {
//        $value = bindec($sign);
//        $value = ($value + ($mask) + ($floatTwiddleControl->getDelta() << $floatTwiddleControl->getIndex())) % ($mask);
//        $sign = decbin($value);
//    }
//
//    if ($name === 'mantissa') {
//        $value = bindec($mantissa);
////        echo "value = $value\n";
////        echo "delta = " . $floatTwiddleControl->getDelta() . "\n";
////        echo "index = " . $floatTwiddleControl->getIndex() . "\n";
//
//        $value = ($value + ($mask) + ($floatTwiddleControl->getDelta() << $floatTwiddleControl->getIndex())) % ($mask);
//        $mantissa = decbin($value);
//        $mantissa = str_pad($mantissa, 52, '0', STR_PAD_LEFT);
//    }
//
//    if ($name === 'exponent') {
//        $value = bindec($exponent);
//        $value = ($value + ($mask) + ($floatTwiddleControl->getDelta() << $floatTwiddleControl->getIndex())) % $mask;
//        $exponent = decbin($value);
//        $exponent = str_pad($exponent, 11, '0', STR_PAD_LEFT);
//    }
//
//    return [$sign, $exponent, $mantissa];
//}

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
function getMemoryLimit()
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

function getPercentMemoryUsed() : int
{
    $maxMemory = memory_get_peak_usage();

    $memoryLimitValue = getMemoryLimit();

    $percentMemoryUsed = (int)((100 * $maxMemory) / $memoryLimitValue);

    return $percentMemoryUsed;
}

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

function getEnvString(string $name): string
{
    $value = getenv($name);

    if (is_string($value) !== true) {
        echo "Getting env variable $name resulted in " . var_export($value, true) . " which is not a string.";
        exit(-1);
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
 * held in a gist. And then renders it as a link.
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
