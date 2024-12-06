<?php

declare(strict_types = 1);

function getExceptionText(\Throwable $exception): string
{
    $text = "";
    do {
        $text .= get_class($exception) . ":" . $exception->getMessage() . "\n\n";
        $text .= $exception->getTraceAsString();

        $exception = $exception->getPrevious();
    } while ($exception !== null);

    return $text;
}

function purgeExceptionMessage(\Throwable $exception): string
{
    $rawMessage = $exception->getMessage();
    $purgeAfterPhrases = [
        'with params'
    ];

    $message = $rawMessage;

    foreach ($purgeAfterPhrases as $purgeAfterPhrase) {
        $matchPosition = strpos($message, $purgeAfterPhrase);
        if ($matchPosition !== false) {
            $message = substr($message, 0, $matchPosition + strlen($purgeAfterPhrase));
            $message .= '**PURGED**';
        }
    }

    $file = $exception->getFile();

//    // normaliseFilePath
//    if (strpos($file, "/var/app/") === 0) {
//        $file = substr($file, strlen("/var/app/"));
//    }

//    $message .= sprintf(
//        "file %s:%s",
//        $file,
//        $exception->getLine()
//    );

    return $message;
}


function getTextForException(\Throwable $exception): string
{
    $currentException = $exception;
    $text = '';

    do {
        $text .= sprintf(
            "Exception type: %s\nMessage:  %s\nFile:  %s \n\nStack trace:\n%s\n",
            get_class($currentException),
            purgeExceptionMessage($currentException),
            remove_install_prefix_from_path($currentException->getFile()) . ':' . $currentException->getLine(),
            formatLinesWithCount(getExceptionStackAsArray($currentException))
        );

        $currentException = $currentException->getPrevious();
    } while ($currentException !== null);

    return $text;
}

function getStacktraceForException(\Throwable $exception): string
{
    return formatLinesWithCount(getExceptionStackAsArray($exception));
}


function saneErrorHandler(
    int $errorNumber,
    string $errorMessage,
    string $errorFile,
    int $errorLine
): bool {

    $someFineConstant = E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR | E_PARSE;

    // TODO - wtf.
    // Prior to PHP 8.0.0, the error_reporting() called inside the custom
    // error handler always returned 0 if the error was suppressed by
    // the @ operator. As of PHP 8.0.0, it returns the value of this
    // (bitwise) expression: E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR |
    // E_USER_ERROR | E_RECOVERABLE_ERROR | E_PARSE.
    if (error_reporting() === 0 || error_reporting() === $someFineConstant) {
        // Error reporting has been silenced
        if ($errorNumber !== E_USER_DEPRECATED) {
            // Check it isn't this value, as this is used by twig, with error suppression. :-/
            return true;
        }
    }
    if ($errorNumber === E_DEPRECATED) {
        return false;
    }
    if ($errorNumber === E_CORE_ERROR || $errorNumber === E_ERROR) {
        // For these two types, PHP is shutting down anyway. Return false
        // to allow shutdown to continue
        return false;
    }
    $message = "Error: [$errorNumber] $errorMessage in file $errorFile:$errorLine.";
    throw new \Exception($message);
}

/**
 * @param array<string, string> $trace
 * @return string
 * @throws Exception
 */
function formatTraceLine(array $trace): string
{
    $location = '??';
    $function = 'unknown';

    if (isset($trace["file"]) && isset($trace["line"])) {
        $location = $trace["file"]. ':' . $trace["line"];
    }
    else if (isset($trace["file"])) {
        $location = $trace["file"] . ':??';
    }
//    else {
//        var_dump($trace);
//        exit(0);
//    }

    $baseDir = realpath(__DIR__ . '/../');
    if ($baseDir === false) {
        throw new \Exception("Couldn't find parent directory from " . __DIR__);
    }

    $location = str_replace($baseDir, '', $location);

    if (isset($trace["class"]) && isset($trace["type"]) && isset($trace["function"])) {
        $function = $trace["class"] . $trace["type"] . $trace["function"];
    }
    else if (isset($trace["class"]) && isset($trace["function"])) {
        $function = $trace["class"] . '_' . $trace["function"];
    }
    else if (isset($trace["function"])) {
        $function = $trace["function"];
    }
    else {
        $function = "Function is weird: " . json_encode(var_export($trace, true));
    }

    return sprintf(
        "%s %s",
        $location,
        $function
    );
}

function getExceptionStack(\Throwable $exception): string
{
    $line = "Exception of type " . get_class($exception). "\n";

    foreach ($exception->getTrace() as $trace) {
        $line .=  formatTraceLine($trace);
    }

    return $line;
}


/**
 * @param Throwable $exception
 * @return string[]
 */
function getExceptionStackAsArray(\Throwable $exception)
{
    $count = 0;
    do {
        $lines = [];
        foreach ($exception->getTrace() as $trace) {
            $lines[] = formatTraceLine($trace);
        }
        $exception = $exception->getPrevious();

        // sanity limit previous exceptions to prevent
        // infinite loops.
        $count += 1;
    } while ($exception !== null && $count < 10);

    return $lines;
}
