<?php

declare(strict_types = 1);

use Bristolian\FloatInfo\FloatTwiddleControl;
use TypeSpec\Create\CreateFromRequest;
use TypeSpec\Create\CreateFromVarMap;
use TypeSpec\Create\CreateOrErrorFromVarMap;
use TypeSpec\InputTypeSpec;
use TypeSpec\ExtractRule\GetBoolOrDefault;
use TypeSpec\ExtractRule\GetIntOrDefault;
use TypeSpec\ExtractRule\GetStringOrDefault;
use TypeSpec\ProcessRule\MaxIntValue;
use TypeSpec\ProcessRule\MinIntValue;
use TypeSpec\SafeAccess;
use VarMap\ArrayVarMap;
use VarMap\VarMap;
use TypeSpec\ProcessRule\EnumMap;
use Bristolian\ToArray;



function hackVarMap($varMap)
{
    $params = $varMap->toArray();

    $unwantedParams = ['q', 'time'];

    foreach ($unwantedParams as $unwantedParam) {
        if (array_key_exists($unwantedParam, $params) === true) {
            unset($params[$unwantedParam]);
        }
    }

    $hackedVarMap = new ArrayVarMap($params);

    return $hackedVarMap;
}

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
 */
function formatLinesWithCount(array $lines): string
{
    $output = '';
    $count = 0;

    foreach ($lines as $line) {
        $output .= '  #' . $count . ' '. $line . "\n";
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
function continuallyExecuteCallable($callable, int $secondsBetweenRuns, int $sleepTime, int $maxRunTime)
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
 */
function json_decode_safe(?string $json)
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


function getExceptionInfoAsArray(\Throwable $exception)
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


function peak_memory($real_usage = false)
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



// Define a function that writes a string into the response object.
function convertStringToHtmlResponse(
    string $result,
    \Psr\Http\Message\RequestInterface $request,
    \Psr\Http\Message\ResponseInterface $response
): \Psr\Http\Message\ResponseInterface {
    $response = $response->withHeader('Content-Type', 'text/html');
    $response->getBody()->write($result);
    return $response;
}


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

function getMask($name)
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

function twiddleWithShit(FloatTwiddleControl $floatTwiddleControl )
{
    /** @var FloatTwiddleControl $floatTwiddleControl */
    $mask = getMask($floatTwiddleControl->getName());

    $sign = $floatTwiddleControl->getSign();
    $exponent = $floatTwiddleControl->getExponent();
    $mantissa = $floatTwiddleControl->getMantissa();

    $name = $floatTwiddleControl->getName();
    if ($name === 'sign') {
        $value = bindec($sign);
        $value = ($value + ($mask) + ($floatTwiddleControl->getDelta() << $floatTwiddleControl->getIndex())) % ($mask);
        $sign = decbin($value);
    }

    if ($name === 'mantissa') {
        $value = bindec($mantissa);
//        echo "value = $value\n";
//        echo "delta = " . $floatTwiddleControl->getDelta() . "\n";
//        echo "index = " . $floatTwiddleControl->getIndex() . "\n";

        $value = ($value + ($mask) + ($floatTwiddleControl->getDelta() << $floatTwiddleControl->getIndex())) % ($mask);
        $mantissa = decbin($value);
        $mantissa = str_pad($mantissa, 52, '0', STR_PAD_LEFT);
    }

    if ($name === 'exponent') {
        $value = bindec($exponent);
        $value = ($value + ($mask) + ($floatTwiddleControl->getDelta() << $floatTwiddleControl->getIndex())) % $mask;
        $exponent = decbin($value);
        $exponent = str_pad($exponent, 11, '0', STR_PAD_LEFT);
    }

    return [$sign, $exponent, $mantissa];
}

function showTotalErrorPage(\Throwable $exception)
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


